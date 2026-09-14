<?php
/**
 * Module: Fartak Dashboard — داشبورد آمار فروش
 *
 * Adds an admin page under the Fartak menu ("داشبورد فرتاک") plus a
 * WordPress dashboard widget. Shows real sales, order, customer and
 * inquiry stats from WP/WooCommerce, with a CSS bar chart for the past
 * 7 days. All queries are cached with transients (5 min).
 *
 * @package Fartak
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ---------------------------------------------------------- data helpers */

/**
 * Collect today's statistics (cached for 5 minutes).
 *
 * @return array {
 *   @type float $sales_today
 *   @type int   $orders_today
 *   @type int   $new_customers
 *   @type int   $new_inquiries
 *   @type int   $low_stock_count
 *   @type int   $pending_wholesale
 *   @type int   $visitors
 *   @type array $week_days  array of [ 'label' => 'YYYY-MM-DD', 'sales' => float ]
 *   @type array $top_products array of [ 'name' => , 'qty' => , 'revenue' => ]
 *   @type array $recent_orders
 *   @type array $recent_inquiries
 * }
 */
function fartak_dash_stats() {
    global $wpdb;
    $cached = get_transient( 'fartak_dash_stats' );
    if ( is_array( $cached ) ) return $cached;

    $now      = current_time( 'mysql' );
    $today    = wp_date( 'Y-m-d 00:00:00' );
    $stats    = array(
        'sales_today'      => 0.0,
        'orders_today'     => 0,
        'new_customers'    => 0,
        'new_inquiries'    => 0,
        'low_stock_count'  => 0,
        'pending_wholesale' => 0,
        'visitors'         => 0,
        'week_days'        => array(),
        'top_products'     => array(),
        'recent_orders'    => array(),
        'recent_inquiries' => array(),
    );

    if ( class_exists( 'WooCommerce' ) ) {
        $order_stats = $wpdb->prefix . 'wc_order_stats';
        $has_order_stats = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $order_stats ) ) === $order_stats;
        if ( $has_order_stats ) {
            $today_row = $wpdb->get_row( $wpdb->prepare(
                "SELECT COUNT(*) AS orders_count, COALESCE(SUM(total_sales),0) AS sales_total FROM {$order_stats} WHERE status IN ('wc-completed','wc-processing') AND date_created >= %s AND date_created <= %s",
                $today,
                $now
            ) );
            $stats['orders_today'] = $today_row ? (int) $today_row->orders_count : 0;
            $stats['sales_today']  = $today_row ? (float) $today_row->sales_total : 0.0;

            $week_start = wp_date( 'Y-m-d 00:00:00', strtotime( '-6 days' ) );
            $rows = $wpdb->get_results( $wpdb->prepare(
                "SELECT DATE(date_created) AS day_key, COALESCE(SUM(total_sales),0) AS sales_total FROM {$order_stats} WHERE status IN ('wc-completed','wc-processing') AND date_created >= %s AND date_created <= %s GROUP BY DATE(date_created)",
                $week_start,
                $now
            ) );
            $sales_by_day = array();
            foreach ( (array) $rows as $row ) { $sales_by_day[ $row->day_key ] = (float) $row->sales_total; }
            for ( $i = 6; $i >= 0; $i-- ) {
                $day = wp_date( 'Y-m-d', strtotime( "-{$i} days" ) );
                $stats['week_days'][] = array(
                    'label' => wp_date( 'l', strtotime( $day ) ),
                    'date'  => $day,
                    'sales' => $sales_by_day[ $day ] ?? 0.0,
                );
            }
        } else {
            // Legacy fallback only when WooCommerce lookup tables are unavailable.
            $today_orders = wc_get_orders( array(
                'status'       => array( 'wc-completed', 'wc-processing' ),
                'date_created' => $today . '...' . $now,
                'limit'        => 100,
                'return'       => 'ids',
            ) );
            $stats['orders_today'] = count( $today_orders );
            $sales = 0.0;
            foreach ( $today_orders as $oid ) { $o = wc_get_order( $oid ); if ( $o ) $sales += (float) $o->get_total(); }
            $stats['sales_today'] = $sales;
        }

        // Top products — use WooCommerce lookup tables when available (HPOS-safe).
        $stats['top_products'] = array();
        $week_ago = wp_date( 'Y-m-d H:i:s', strtotime( '-7 days' ) );
        $order_stats = $wpdb->prefix . 'wc_order_stats';
        $product_lookup = $wpdb->prefix . 'wc_order_product_lookup';
        if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $order_stats ) ) === $order_stats && $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $product_lookup ) ) === $product_lookup ) {
                $rows = $wpdb->get_results( $wpdb->prepare( "SELECT opl.product_id, SUM(opl.product_qty) qty FROM {$product_lookup} opl INNER JOIN {$order_stats} os ON os.order_id=opl.order_id WHERE os.status IN ('wc-completed','wc-processing') AND os.date_created >= %s GROUP BY opl.product_id ORDER BY qty DESC LIMIT 5", $week_ago ) );
                foreach ( (array) $rows as $row ) { $prod=wc_get_product((int)$row->product_id); if($prod) $stats['top_products'][]=(object)array('name'=>$prod->get_name(),'qty'=>(int)$row->qty); }
        }

        // Low stock products (stock <= 5)
        $low_args = array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => 100,
            'meta_query'     => array(
                'relation' => 'AND',
                array( 'key' => '_manage_stock', 'value' => 'yes' ),
                array( 'key' => '_stock', 'value' => 5, 'compare' => '<=', 'type' => 'NUMERIC' ),
                array( 'key' => '_stock_status', 'value' => 'instock' ),
            ),
            'fields'         => 'ids',
            'no_found_rows'  => true,
        );
        $low_q = new WP_Query( $low_args );
        $stats['low_stock_count'] = count( $low_q->posts );

        // Recent orders (last 5)
        $recent = wc_get_orders( array(
            'limit'   => 5,
            'orderby' => 'date',
            'order'   => 'DESC',
        ) );
        foreach ( $recent as $o ) {
            $stats['recent_orders'][] = array(
                'id'     => $o->get_id(),
                'name'   => trim( $o->get_billing_first_name() . ' ' . $o->get_billing_last_name() ),
                'total'  => (float) $o->get_total(),
                'status' => $o->get_status(),
                'date'   => wp_date( 'Y/m/d H:i', $o->get_date_created() ? $o->get_date_created()->getTimestamp() : time() ),
            );
        }
    }

    // New customers (registered today) — use WP_User_Query directly.
    $uq = new WP_User_Query( array(
        'date_query' => array(
            array( 'column' => 'user_registered', 'after' => $today ),
        ),
        'number'      => 1,
        'count_total' => true,
        'fields'      => 'ID',
    ) );
    $stats['new_customers'] = (int) $uq->get_total();

    // New inquiries (fartak_inquiry CPT today)
    $inquiry_q = new WP_Query( array(
        'post_type'      => 'fartak_inquiry',
        'posts_per_page' => 100,
        'no_found_rows'  => true,
        'fields'         => 'ids',
        'date_query'     => array(
            array( 'column' => 'post_date', 'after' => $today ),
        ),
    ) );
    $stats['new_inquiries'] = count( $inquiry_q->posts );

    // Recent inquiries (last 5)
    $recent_inq = get_posts( array(
        'post_type'      => 'fartak_inquiry',
        'posts_per_page' => 5,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ) );
    foreach ( $recent_inq as $iq ) {
        $stats['recent_inquiries'][] = array(
            'id'     => $iq->ID,
            'title'  => get_the_title( $iq ),
            'name'   => get_post_meta( $iq->ID, '_fartak_customer', true ),
            'phone'  => get_post_meta( $iq->ID, '_fartak_phone', true ),
            'product' => get_post_meta( $iq->ID, '_fartak_product', true ),
            'date'   => wp_date( 'Y/m/d H:i', strtotime( $iq->post_date ) ),
        );
    }

    // Pending wholesale requests (stored in `fartak_inquiry` posts with stage=new)
    $pending_q = new WP_Query( array(
        'post_type'      => 'fartak_inquiry',
        'posts_per_page' => 100,
        'no_found_rows'  => true,
        'fields'         => 'ids',
        'meta_query'     => array(
            array( 'key' => '_fartak_stage', 'value' => 'new' ),
        ),
    ) );
    $stats['pending_wholesale'] = count( $pending_q->posts );

    // Visitors (from transient counter, set elsewhere; default to 0)
    $visitors = (int) get_transient( 'fartak_visitors_today' );
    if ( ! $visitors ) {
        // Quick fallback: count today's unique cookies isn't available — use total sessions of the day.
        $visitors = (int) get_transient( 'fartak_visitors_count' );
    }
    $stats['visitors'] = $visitors;

    set_transient( 'fartak_dash_stats', $stats, 5 * MINUTE_IN_SECONDS );
    return $stats;
}

/**
 * Track today's visitors using a transient (one entry per session).
 */
add_action( 'init', 'fartak_dash_count_visitor', 30 );
function fartak_dash_count_visitor() {
    if ( is_admin() ) return;
    $cookie_name = 'fartak_visit';
    $already     = isset( $_COOKIE[ $cookie_name ] );
    if ( ! $already ) {
        setcookie( $cookie_name, '1', time() + DAY_IN_SECONDS, '/' );
        $today = wp_date( 'Y-m-d' );
        $key   = 'fartak_visitors_' . $today;
        $count = (int) get_transient( $key );
        set_transient( $key, $count + 1, DAY_IN_SECONDS );
        set_transient( 'fartak_visitors_today', $count + 1, DAY_IN_SECONDS );
        set_transient( 'fartak_visitors_count', $count + 1, DAY_IN_SECONDS );
    }
}

/**
 * Flush dashboard transient on new order / inquiry.
 */
add_action( 'woocommerce_order_status_changed', 'fartak_dash_flush', 10 );
add_action( 'woocommerce_checkout_order_processed', 'fartak_dash_flush', 10 );
add_action( 'save_post_fartak_inquiry', 'fartak_dash_flush' );
add_action( 'user_register', 'fartak_dash_flush' );
function fartak_dash_flush() {
    delete_transient( 'fartak_dash_stats' );
}

/* ----------------------------------------------------------- admin page */

add_action( 'admin_menu', 'fartak_dash_menu', 5 );
function fartak_dash_menu() {
    add_submenu_page(
        'fartak-panel',
        __( 'داشبورد فرتاک', 'fartak' ),
        __( 'داشبورد فرتاک', 'fartak' ),
        'manage_options',
        'fartak-panel-dashboard',
        'fartak_dash_page'
    );
}

function fartak_dash_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    $stats = fartak_dash_stats();
    ?>
    <div class="wrap fartak-dash">
        <h1><span class="dashicons dashicons-chart-bar"></span> <?php esc_html_e( 'داشبورد فرتاک', 'fartak' ); ?>
            <button type="button" class="button button-small" onclick="window.location.reload(true)" style="margin-right:8px"><?php esc_html_e( 'به‌روزرسانی', 'fartak' ); ?></button>
        </h1>

        <div class="fartak-dash-grid">
            <?php
            $cards = array(
                array(
                    'label' => __( 'فروش امروز', 'fartak' ),
                    'value' => fartak_fa_num( number_format( (float) $stats['sales_today'] ) ) . ' تومان',
                    'icon'  => 'coins',
                    'color' => '#46b450',
                ),
                array(
                    'label' => __( 'سفارش‌های امروز', 'fartak' ),
                    'value' => fartak_fa_num( $stats['orders_today'] ),
                    'icon'  => 'package',
                    'color' => '#3b82f6',
                ),
                array(
                    'label' => __( 'اعضای جدید امروز', 'fartak' ),
                    'value' => fartak_fa_num( $stats['new_customers'] ),
                    'icon'  => 'user',
                    'color' => '#06b6d4',
                ),
                array(
                    'label' => __( 'استعلام‌های امروز', 'fartak' ),
                    'value' => fartak_fa_num( $stats['new_inquiries'] ),
                    'icon'  => 'phone',
                    'color' => '#a855f7',
                ),
                array(
                    'label' => __( 'محصولات کم‌موجودی', 'fartak' ),
                    'value' => fartak_fa_num( $stats['low_stock_count'] ),
                    'icon'  => 'alert',
                    'color' => '#f59e0b',
                ),
                array(
                    'label' => __( 'استعلام‌های در انتظار', 'fartak' ),
                    'value' => fartak_fa_num( $stats['pending_wholesale'] ),
                    'icon'  => 'clock',
                    'color' => '#ff3543',
                ),
                array(
                    'label' => __( 'بازدیدکنندگان امروز', 'fartak' ),
                    'value' => fartak_fa_num( $stats['visitors'] ),
                    'icon'  => 'user',
                    'color' => '#06b6d4',
                ),
            );
            foreach ( $cards as $c ) :
                ?>
                <div class="fartak-dash-card">
                    <div class="fd-icon" style="color:<?php echo esc_attr( $c['color'] ); ?>"><?php echo fartak_icon( $c['icon'] ); // phpcs:ignore ?></div>
                    <div class="fd-body">
                        <span class="fd-label"><?php echo esc_html( $c['label'] ); ?></span>
                        <strong class="fd-value"><?php echo esc_html( $c['value'] ); ?></strong>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="fartak-dash-row">
            <!-- Weekly sales chart -->
            <div class="fartak-dash-block" style="flex:2">
                <h3><span class="dashicons dashicons-chart-bar"></span> <?php esc_html_e( 'فروش هفته اخیر', 'fartak' ); ?></h3>
                <div class="fartak-dash-chart">
                    <?php
                    $max = 1;
                    foreach ( $stats['week_days'] as $d ) {
                        if ( $d['sales'] > $max ) $max = $d['sales'];
                    }
                    foreach ( $stats['week_days'] as $d ) :
                        $pct = round( ( $d['sales'] / $max ) * 100 );
                        ?>
                        <div class="fd-bar">
                            <div class="fd-bar-fill" style="height:<?php echo esc_attr( max( 4, $pct ) ); ?>%; background:linear-gradient(180deg,#ff3543,#e11d2a)" title="<?php echo esc_attr( number_format( $d['sales'] ) ); ?> تومان"></div>
                            <span class="fd-bar-label"><?php echo esc_html( $d['label'] ); ?></span>
                            <span class="fd-bar-value"><?php echo esc_html( fartak_fa_num( number_format( $d['sales'] / 1000000, 1 ) ) ); ?>م</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Top products -->
            <div class="fartak-dash-block" style="flex:1">
                <h3><span class="dashicons dashicons-star"></span> <?php esc_html_e( 'پرفروش‌ترین‌های هفته', 'fartak' ); ?></h3>
                <?php if ( empty( $stats['top_products'] ) ) : ?>
                    <p style="color:#8b95ab;font-size:13px;padding:20px 0"><?php esc_html_e( 'هنوز فروشی ثبت نشده است.', 'fartak' ); ?></p>
                <?php else : ?>
                    <ol class="fd-top">
                        <?php foreach ( $stats['top_products'] as $row ) : ?>
                            <li>
                                <span class="fd-rank">#<?php echo esc_html( fartak_fa_num( 0 ) ); ?></span>
                                <span class="fd-name"><?php echo esc_html( $row->name ); ?></span>
                                <span class="fd-qty"><?php echo esc_html( fartak_fa_num( (int) $row->qty ) ); ?> <?php esc_html_e( 'فروخته', 'fartak' ); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
            </div>
        </div>

        <div class="fartak-dash-row">
            <!-- Recent orders -->
            <div class="fartak-dash-block" style="flex:1">
                <h3><span class="dashicons dashicons-cart"></span> <?php esc_html_e( 'آخرین سفارش‌ها', 'fartak' ); ?></h3>
                <?php if ( empty( $stats['recent_orders'] ) ) : ?>
                    <p style="color:#8b95ab;font-size:13px;padding:20px 0"><?php esc_html_e( 'سفارشی ثبت نشده است.', 'fartak' ); ?></p>
                <?php else : ?>
                    <table class="widefat striped fd-tbl">
                        <thead>
                            <tr><th>#</th><th><?php esc_html_e( 'مشتری', 'fartak' ); ?></th><th><?php esc_html_e( 'مبلغ', 'fartak' ); ?></th><th><?php esc_html_e( 'وضعیت', 'fartak' ); ?></th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $stats['recent_orders'] as $o ) : ?>
                                <tr>
                                    <td><a href="<?php echo esc_url( admin_url( 'post.php?post=' . $o['id'] . '&action=edit' ) ); ?>"><?php echo esc_html( fartak_fa_num( $o['id'] ) ); ?></a></td>
                                    <td><?php echo esc_html( $o['name'] ?: '—' ); ?></td>
                                    <td><?php echo esc_html( fartak_fa_num( number_format( $o['total'] ) ) ); ?></td>
                                    <td><span class="fd-status"><?php echo esc_html( $o['status'] ); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Recent inquiries -->
            <div class="fartak-dash-block" style="flex:1">
                <h3><span class="dashicons dashicons-phone"></span> <?php esc_html_e( 'آخرین استعلام‌ها', 'fartak' ); ?></h3>
                <?php if ( empty( $stats['recent_inquiries'] ) ) : ?>
                    <p style="color:#8b95ab;font-size:13px;padding:20px 0"><?php esc_html_e( 'استعلامی ثبت نشده است.', 'fartak' ); ?></p>
                <?php else : ?>
                    <table class="widefat striped fd-tbl">
                        <thead>
                            <tr><th><?php esc_html_e( 'نام', 'fartak' ); ?></th><th><?php esc_html_e( 'محصول', 'fartak' ); ?></th><th><?php esc_html_e( 'موبایل', 'fartak' ); ?></th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $stats['recent_inquiries'] as $iq ) : ?>
                                <tr>
                                    <td><a href="<?php echo esc_url( admin_url( 'post.php?post=' . $iq['id'] . '&action=edit' ) ); ?>"><?php echo esc_html( $iq['name'] ?: $iq['title'] ); ?></a></td>
                                    <td><?php echo esc_html( $iq['product'] ?: '—' ); ?></td>
                                    <td><?php echo esc_html( $iq['phone'] ?: '—' ); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <style>
        .fartak-dash h1{display:flex;align-items:center;gap:8px}
        .fartak-dash-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;margin:16px 0}
        .fartak-dash-card{background:#0e1626;color:#e8ecf4;border:1px solid rgba(255,255,255,.07);border-radius:12px;padding:18px;display:flex;align-items:center;gap:14px}
        .fd-icon .icon{width:32px;height:32px}
        .fd-body{display:flex;flex-direction:column}
        .fd-label{font-size:12px;color:#8b95ab;margin-bottom:4px}
        .fd-value{font-size:18px;font-weight:800;color:#e8ecf4}
        .fartak-dash-row{display:flex;gap:16px;margin-bottom:16px;flex-wrap:wrap}
        .fartak-dash-block{background:#0e1626;color:#e8ecf4;border:1px solid rgba(255,255,255,.07);border-radius:12px;padding:18px;min-width:280px}
        .fartak-dash-block h3{margin:0 0 14px;font-size:14px;color:#e8ecf4;display:flex;align-items:center;gap:6px}
        .fartak-dash-block h3 .dashicons{color:#ff3543;font-family:dashicons;font-size:18px}
        .fartak-dash-chart{display:flex;align-items:flex-end;gap:12px;height:200px;padding-top:30px}
        .fd-bar{flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;height:100%}
        .fd-bar-fill{width:60%;border-radius:6px 6px 0 0;transition:height .4s ease}
        .fd-bar-label{font-size:11px;color:#8b95ab;white-space:nowrap}
        .fd-bar-value{font-size:11px;color:#ff3543;font-weight:700}
        .fd-top{list-style:none;padding:0;margin:0;counter-reset:fd-rank}
        .fd-top li{display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.05);counter-increment:fd-rank}
        .fd-top li:last-child{border-bottom:none}
        .fd-rank{width:24px;height:24px;border-radius:50%;background:rgba(255,53,67,.15);color:#ff3543;font-size:11px;font-weight:800;display:flex;align-items:center;justify-content:center}
        .fd-rank::after{content:counter(fd-rank)}
        .fd-name{flex:1;font-size:13px;color:#e8ecf4;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .fd-qty{font-size:11px;color:#8b95ab}
        .fd-tbl th,.fd-tbl td{font-size:12px;color:#e8ecf4;text-align:right}
        .fd-tbl thead th{background:rgba(255,255,255,.04)}
        .fd-status{padding:2px 8px;border-radius:6px;background:rgba(70,180,80,.15);color:#46b450;font-size:11px;font-weight:700}
        </style>
    </div>
    <?php
}

/* ----------------------------------------------------------- dashboard widget */

add_action( 'wp_dashboard_setup', 'fartak_dash_widget_register' );
function fartak_dash_widget_register() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    wp_add_dashboard_widget( 'fartak_dash_widget', __( 'داشبورد فرتاک', 'fartak' ), 'fartak_dash_widget_render' );
}

function fartak_dash_widget_render() {
    $stats = fartak_dash_stats();
    ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;font-family:Vazirmatn,sans-serif">
        <?php
        $cards = array(
            array( 'label' => __( 'فروش امروز', 'fartak' ),     'value' => fartak_fa_num( number_format( (float) $stats['sales_today'] ) ) ),
            array( 'label' => __( 'سفارش‌های امروز', 'fartak' ), 'value' => fartak_fa_num( $stats['orders_today'] ) ),
            array( 'label' => __( 'استعلام امروز', 'fartak' ),   'value' => fartak_fa_num( $stats['new_inquiries'] ) ),
            array( 'label' => __( 'بازدید امروز', 'fartak' ),    'value' => fartak_fa_num( $stats['visitors'] ) ),
        );
        foreach ( $cards as $c ) :
            ?>
            <div style="background:#0e1626;color:#e8ecf4;border:1px solid rgba(255,255,255,.07);border-radius:10px;padding:14px;text-align:center">
                <div style="font-size:11px;color:#8b95ab;margin-bottom:6px"><?php echo esc_html( $c['label'] ); ?></div>
                <div style="font-size:18px;font-weight:800;color:#ff3543"><?php echo esc_html( $c['value'] ); ?></div>
            </div>
        <?php endforeach; ?>
    </div>
    <p style="margin-top:12px;text-align:left">
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=fartak-panel-dashboard' ) ); ?>" class="button button-primary button-small"><?php esc_html_e( 'مشاهده داشبورد کامل', 'fartak' ); ?></a>
    </p>
    <?php
}
