<?php
/**
 * Module: Price Alert — هشدار کاهش قیمت
 *
 * Lets customers request an email/SMS notification when a product's price
 * drops below a target. Stores alerts in a private `fartak_price_alert`
 * custom post type and processes them on an hourly WP-Cron.
 *
 * @package Fartak
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ----------------------------------------------------------------- CPT */

/**
 * Register the `fartak_price_alert` custom post type.
 */
add_action( 'init', 'fartak_pa_register_cpt' );
function fartak_pa_register_cpt() {
    register_post_type(
        'fartak_price_alert',
        array(
            'labels' => array(
                'name'               => __( 'هشدارهای قیمت', 'fartak' ),
                'singular_name'      => __( 'هشدار قیمت', 'fartak' ),
                'add_new'            => __( 'افزودن هشدار', 'fartak' ),
                'add_new_item'       => __( 'افزودن هشدار قیمت جدید', 'fartak' ),
                'edit_item'          => __( 'ویرایش هشدار قیمت', 'fartak' ),
                'all_items'          => __( 'همه هشدارها', 'fartak' ),
                'search_items'       => __( 'جستجوی هشدارها', 'fartak' ),
                'not_found'          => __( 'هشداری یافت نشد', 'fartak' ),
            ),
            'public'        => false,
            'show_ui'       => true,
            'show_in_menu'  => 'fartak-panel',
            'menu_icon'     => 'dashicons-bell',
            'supports'      => array( 'title' ),
            'capability_type' => 'post',
            'map_meta_cap'   => true,
        )
    );
}

/**
 * Meta box for the price alert CPT.
 */
add_action( 'add_meta_boxes', 'fartak_pa_metabox' );
function fartak_pa_metabox() {
    add_meta_box(
        'fartak_pa_details',
        __( 'جزئیات هشدار قیمت', 'fartak' ),
        'fartak_pa_metabox_html',
        'fartak_price_alert',
        'normal',
        'high'
    );
}

function fartak_pa_metabox_html( $post ) {
    wp_nonce_field( 'fartak_pa_save', 'fartak_pa_nonce' );
    $name    = get_post_meta( $post->ID, '_alert_user_name', true );
    $phone   = get_post_meta( $post->ID, '_alert_user_phone', true );
    $target  = get_post_meta( $post->ID, '_alert_target_price', true );
    $pid     = get_post_meta( $post->ID, '_alert_product_id', true );
    $status  = get_post_meta( $post->ID, '_alert_status', true );
    if ( ! $status ) $status = 'active';

    $product = $pid ? wc_get_product( $pid ) : null;
    ?>
    <table class="form-table">
        <tr>
            <th><label for="_alert_user_name"><?php esc_html_e( 'نام مشتری', 'fartak' ); ?></label></th>
            <td><input type="text" id="_alert_user_name" name="_alert_user_name" value="<?php echo esc_attr( $name ); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label for="_alert_user_phone"><?php esc_html_e( 'شماره موبایل', 'fartak' ); ?></label></th>
            <td><input type="tel" id="_alert_user_phone" name="_alert_user_phone" value="<?php echo esc_attr( $phone ); ?>" class="regular-text" placeholder="09xxxxxxxxx"></td>
        </tr>
        <tr>
            <th><label for="_alert_target_price"><?php esc_html_e( 'قیمت هدف (تومان)', 'fartak' ); ?></label></th>
            <td><input type="number" id="_alert_target_price" name="_alert_target_price" value="<?php echo esc_attr( $target ); ?>" class="regular-text" min="0" step="1000"></td>
        </tr>
        <tr>
            <th><label for="_alert_product_id"><?php esc_html_e( 'محصول', 'fartak' ); ?></label></th>
            <td>
                <select id="_alert_product_id" name="_alert_product_id" class="regular-text">
                    <?php if ( $product ) : ?>
                        <option value="<?php echo esc_attr( $pid ); ?>" selected><?php echo esc_html( $product->get_name() ); ?> (#<?php echo esc_html( $pid ); ?>)</option>
                    <?php else : ?>
                        <option value=""><?php esc_html_e( '— انتخاب —', 'fartak' ); ?></option>
                    <?php endif; ?>
                </select>
                <p class="description"><?php esc_html_e( 'برای تغییر محصول، ID آن را در فیلد زیر وارد کنید.', 'fartak' ); ?></p>
                <input type="number" name="_alert_product_id_manual" value="<?php echo esc_attr( $pid ); ?>" style="width:120px;margin-top:6px" min="1">
            </td>
        </tr>
        <tr>
            <th><label for="_alert_status"><?php esc_html_e( 'وضعیت', 'fartak' ); ?></label></th>
            <td>
                <select id="_alert_status" name="_alert_status">
                    <?php
                    $statuses = array(
                        'active'    => __( 'فعال', 'fartak' ),
                        'notified'  => __( 'اعلان شد', 'fartak' ),
                        'cancelled' => __( 'لغو شده', 'fartak' ),
                    );
                    foreach ( $statuses as $k => $label ) {
                        printf( '<option value="%s" %s>%s</option>', esc_attr( $k ), selected( $status, $k, false ), esc_html( $label ) );
                    }
                    ?>
                </select>
            </td>
        </tr>
    </table>
    <?php
}

add_action( 'save_post_fartak_price_alert', 'fartak_pa_save' );
function fartak_pa_save( $post_id ) {
    if ( ! isset( $_POST['fartak_pa_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['fartak_pa_nonce'] ), 'fartak_pa_save' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['_alert_user_name'] ) ) {
        update_post_meta( $post_id, '_alert_user_name', sanitize_text_field( wp_unslash( $_POST['_alert_user_name'] ) ) );
    }
    if ( isset( $_POST['_alert_user_phone'] ) ) {
        $phone = sanitize_text_field( wp_unslash( $_POST['_alert_user_phone'] ) );
        // Normalize Iranian mobile format.
        $phone = preg_replace( '/[^0-9]/', '', $phone );
        if ( substr( $phone, 0, 2 ) === '98' ) $phone = '0' . substr( $phone, 2 );
        update_post_meta( $post_id, '_alert_user_phone', $phone );
    }
    if ( isset( $_POST['_alert_target_price'] ) ) {
        update_post_meta( $post_id, '_alert_target_price', absint( $_POST['_alert_target_price'] ) );
    }
    // Product ID — prefer manual entry.
    $pid = 0;
    if ( isset( $_POST['_alert_product_id_manual'] ) && absint( $_POST['_alert_product_id_manual'] ) ) {
        $pid = absint( $_POST['_alert_product_id_manual'] );
    } elseif ( isset( $_POST['_alert_product_id'] ) && absint( $_POST['_alert_product_id'] ) ) {
        $pid = absint( $_POST['_alert_product_id'] );
    }
    if ( $pid ) {
        update_post_meta( $post_id, '_alert_product_id', $pid );
        // Set the post title for readability.
        $product = wc_get_product( $pid );
        if ( $product ) {
            remove_action( 'save_post_fartak_price_alert', 'fartak_pa_save' );
            wp_update_post( array( 'ID' => $post_id, 'post_title' => $product->get_name() . ' — ' . ( isset( $_POST['_alert_user_name'] ) ? sanitize_text_field( wp_unslash( $_POST['_alert_user_name'] ) ) : '' ) ) );
            add_action( 'save_post_fartak_price_alert', 'fartak_pa_save' );
        }
    }
    if ( isset( $_POST['_alert_status'] ) ) {
        update_post_meta( $post_id, '_alert_status', sanitize_key( $_POST['_alert_status'] ) );
    }
}

/* ----------------------------------------------------------- frontend */

/**
 * Inject the "هشدار کاهش قیمت" button below add-to-cart on single products.
 */
add_action( 'woocommerce_single_product_summary', 'fartak_pa_button', 35 );
function fartak_pa_button() {
    if ( ! function_exists( 'is_product' ) || ! is_product() ) return;
    global $product;
    if ( ! $product ) return;
    $price = (float) $product->get_price();
    ?>
    <button type="button" class="btn-ghost fartak-pa-trigger" data-pa-open
        style="display:flex;align-items:center;gap:8px;margin-top:14px;padding:10px 16px;border:1px solid rgba(255,53,67,.3);border-radius:10px;color:var(--copper2, #ff3543);font-weight:700;width:100%;justify-content:center;cursor:pointer;background:rgba(255,53,67,.05)">
        <?php echo fartak_icon( 'alert' ); ?>
        <?php esc_html_e( 'هشدار کاهش قیمت', 'fartak' ); ?>
    </button>
    <div id="fartak-pa-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);backdrop-filter:blur(6px);z-index:9999;align-items:center;justify-content:center;padding:20px">
        <div class="fartak-pa-box" style="background:var(--panel, #0e1626);border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:24px;max-width:420px;width:100%;position:relative">
            <button type="button" data-pa-close style="position:absolute;top:12px;left:12px;background:none;border:none;color:#8b95ab;font-size:22px;cursor:pointer">×</button>
            <h3 style="margin:0 0 4px;font-size:18px;font-weight:800;color:#e8ecf4"><?php esc_html_e( 'هشدار کاهش قیمت', 'fartak' ); ?></h3>
            <p style="margin:0 0 18px;font-size:12px;color:#8b95ab"><?php esc_html_e( 'وقتی قیمت محصول به عدد دلخواه شما رسید، پیامک یادآوری می‌گیرید.', 'fartak' ); ?></p>
            <form data-pa-form>
                <input type="hidden" name="product_id" value="<?php echo esc_attr( $product->get_id() ); ?>">
                <div style="margin-bottom:12px">
                    <label style="display:block;margin-bottom:6px;font-size:12px;color:#8b95ab"><?php esc_html_e( 'نام و نام خانوادگی', 'fartak' ); ?></label>
                    <input type="text" name="name" required style="width:100%;padding:10px 12px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:8px;color:#e8ecf4;font-family:inherit;font-size:13px">
                </div>
                <div style="margin-bottom:12px">
                    <label style="display:block;margin-bottom:6px;font-size:12px;color:#8b95ab"><?php esc_html_e( 'شماره موبایل', 'fartak' ); ?></label>
                    <input type="tel" name="phone" required placeholder="09xxxxxxxxx" pattern="09[0-9]{9}" style="width:100%;padding:10px 12px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:8px;color:#e8ecf4;font-family:inherit;font-size:13px">
                </div>
                <div style="margin-bottom:16px">
                    <label style="display:block;margin-bottom:6px;font-size:12px;color:#8b95ab"><?php esc_html_e( 'قیمت هدف (تومان)', 'fartak' ); ?></label>
                    <input type="number" name="target" required min="1000" step="1000" value="<?php echo esc_attr( max( 1000, (int) ( $price * 0.9 ) ) ); ?>" style="width:100%;padding:10px 12px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:8px;color:#e8ecf4;font-family:inherit;font-size:13px">
                </div>
                <button type="submit" class="btn-copper" style="width:100%;padding:12px;border:none;border-radius:10px;background:var(--copper2, #ff3543);color:#fff;font-weight:800;cursor:pointer;font-family:inherit"><?php esc_html_e( 'ثبت هشدار', 'fartak' ); ?></button>
                <div data-pa-msg style="margin-top:10px;font-size:12px;text-align:center"></div>
            </form>
        </div>
    </div>
    <?php
}

/**
 * AJAX handler: fartak_price_alert_submit
 */
add_action( 'wp_ajax_fartak_price_alert_submit', 'fartak_pa_submit' );
add_action( 'wp_ajax_nopriv_fartak_price_alert_submit', 'fartak_pa_submit' );
function fartak_pa_submit() {
    check_ajax_referer( 'fartak', 'nonce' );

    $name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
    $phone   = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
    $target  = isset( $_POST['target'] ) ? absint( $_POST['target'] ) : 0;
    $pid     = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;

    if ( '' === $name || mb_strlen( $phone ) < 11 || ! $target || ! $pid ) {
        wp_send_json_error( array( 'message' => __( 'لطفاً همه فیلدها را به‌درستی پر کنید.', 'fartak' ) ) );
    }

    // Normalize phone
    $phone = preg_replace( '/[^0-9]/', '', $phone );
    if ( substr( $phone, 0, 2 ) === '98' ) $phone = '0' . substr( $phone, 2 );
    if ( ! preg_match( '/^09[0-9]{9}$/', $phone ) ) {
        wp_send_json_error( array( 'message' => __( 'شماره موبایل معتبر نیست.', 'fartak' ) ) );
    }

    $product = wc_get_product( $pid );
    if ( ! $product ) {
        wp_send_json_error( array( 'message' => __( 'محصول یافت نشد.', 'fartak' ) ) );
    }

    $post_id = wp_insert_post( array(
        'post_type'    => 'fartak_price_alert',
        'post_status'  => 'publish',
        'post_title'   => $product->get_name() . ' — ' . $name,
    ) );

    if ( ! $post_id || is_wp_error( $post_id ) ) {
        wp_send_json_error( array( 'message' => __( 'خطا در ثبت. دوباره تلاش کنید.', 'fartak' ) ) );
    }

    update_post_meta( $post_id, '_alert_user_name', $name );
    update_post_meta( $post_id, '_alert_user_phone', $phone );
    update_post_meta( $post_id, '_alert_target_price', $target );
    update_post_meta( $post_id, '_alert_product_id', $pid );
    update_post_meta( $post_id, '_alert_status', 'active' );
    update_post_meta( $post_id, '_alert_created_at', current_time( 'mysql' ) );

    wp_send_json_success( array(
        'message' => __( 'هشدار قیمت شما ثبت شد. به‌محض رسیدن قیمت به عدد هدف، پیامک دریافت می‌کنید.', 'fartak' ),
        'id'      => $post_id,
    ) );
}

/* ------------------------------------------------------- WP-Cron job */

add_action( 'init', 'fartak_pa_schedule' );
function fartak_pa_schedule() {
    if ( ! wp_next_scheduled( 'fartak_pa_cron' ) ) {
        wp_schedule_event( time() + 60, 'hourly', 'fartak_pa_cron' );
    }
}

add_action( 'fartak_pa_cron', 'fartak_pa_process' );
function fartak_pa_process() {
    $alerts = get_posts( array(
        'post_type'      => 'fartak_price_alert',
        'posts_per_page' => 200,
        'meta_key'       => '_alert_status',
        'meta_value'     => 'active',
        'no_found_rows'  => true,
    ) );

    if ( empty( $alerts ) ) return;

    $admin_email = get_option( 'admin_email' );
    $site_name   = 'فروشگاه فرتاک';

    foreach ( $alerts as $alert ) {
        $pid        = (int) get_post_meta( $alert->ID, '_alert_product_id', true );
        $target     = (float) get_post_meta( $alert->ID, '_alert_target_price', true );
        $name       = get_post_meta( $alert->ID, '_alert_user_name', true );
        $phone      = get_post_meta( $alert->ID, '_alert_user_phone', true );

        if ( ! $pid || ! $target ) continue;

        $product = wc_get_product( $pid );
        if ( ! $product ) continue;

        $current = (float) $product->get_price();
        if ( $current <= 0 ) continue;

        if ( $current <= $target ) {
            // Send admin email
            $subject = sprintf( __( '[%s] هشدار قیمت فعال شد', 'fartak' ), $site_name );
            $body = sprintf(
                __( "محصول: %s\nقیمت فعلی: %s تومان\nقیمت هدف: %s تومان\nنام مشتری: %s\nموبایل: %s\nلینک: %s", 'fartak' ),
                $product->get_name(),
                number_format( $current ),
                number_format( $target ),
                $name,
                $phone,
                get_permalink( $pid )
            );
            wp_mail( $admin_email, $subject, $body );

            // Send SMS via the theme's SMS module hook (if present).
            $sms_message = sprintf(
                __( '%s عزیز، قیمت «%s» به %s تومان رسید. اکنون می‌توانید خرید کنید.', 'fartak' ),
                $name,
                $product->get_name(),
                number_format( $current )
            );
            do_action( 'fartak_sms_send', $phone, $sms_message );

            update_post_meta( $alert->ID, '_alert_status', 'notified' );
            update_post_meta( $alert->ID, '_alert_notified_at', current_time( 'mysql' ) );
            update_post_meta( $alert->ID, '_alert_notified_price', $current );
        }
    }
}

/* ----------------------------------------------------------- admin page */

add_action( 'admin_menu', 'fartak_pa_admin_page', 30 );
function fartak_pa_admin_page() {
    add_submenu_page(
        'fartak-panel',
        __( 'هشدارهای قیمت', 'fartak' ),
        __( 'هشدار قیمت', 'fartak' ),
        'manage_options',
        'fartak-panel-price-alert',
        'fartak_pa_admin_html'
    );
}

function fartak_pa_admin_html() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    // Count by status — با posts_per_page -1 و fields=ids، count() واقعی است (found_posts با no_found_rows همیشه صفر بود)
    $counts = array( 'active' => 0, 'notified' => 0, 'cancelled' => 0 );
    foreach ( array_keys( $counts ) as $st ) {
        $q = new WP_Query( array(
            'post_type'      => 'fartak_price_alert',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_key'       => '_alert_status',
            'meta_value'     => $st,
            'no_found_rows'  => true,
        ) );
        $counts[ $st ] = count( $q->posts );
    }

    $filter = isset( $_GET['pa_filter'] ) ? sanitize_key( wp_unslash( $_GET['pa_filter'] ) ) : 'active';
    if ( ! in_array( $filter, array( 'active', 'notified', 'cancelled', 'all' ), true ) ) {
        $filter = 'active';
    }

    $args = array(
        'post_type'      => 'fartak_price_alert',
        'posts_per_page' => 50,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'no_found_rows'  => true,
    );
    if ( $filter !== 'all' ) {
        $args['meta_key']   = '_alert_status';
        $args['meta_value'] = $filter;
    }
    $alerts = get_posts( $args );
    ?>
    <div class="wrap">
        <h1><span class="dashicons dashicons-bell"></span> <?php esc_html_e( 'هشدارهای قیمت', 'fartak' ); ?></h1>
        <p class="description"><?php esc_html_e( 'افرادی که برای کاهش قیمت محصول درخواست هشدار ثبت کرده‌اند در این لیست نمایش داده می‌شوند. پردازش خودکار هر یک‌ساعت یک‌بار اجرا می‌شود.', 'fartak' ); ?></p>

        <ul class="subsubsub">
            <?php
            $labels = array(
                'active'    => __( 'فعال', 'fartak' ),
                'notified'  => __( 'اعلان شده', 'fartak' ),
                'cancelled' => __( 'لغو شده', 'fartak' ),
                'all'       => __( 'همه', 'fartak' ),
            );
            foreach ( $labels as $st => $label ) :
                $url = add_query_arg( 'pa_filter', $st );
                $count = $st === 'all' ? array_sum( $counts ) : $counts[ $st ];
                ?>
                <li><a href="<?php echo esc_url( $url ); ?>" <?php echo $filter === $st ? 'class="current"' : ''; ?>><?php echo esc_html( $label ); ?> <span class="count">(<?php echo esc_html( fartak_fa_num( $count ) ); ?>)</span></a><?php echo $st !== 'all' ? ' |' : ''; ?></li>
            <?php endforeach; ?>
        </ul>

        <table class="widefat striped" style="margin-top:16px">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'مشتری', 'fartak' ); ?></th>
                    <th><?php esc_html_e( 'موبایل', 'fartak' ); ?></th>
                    <th><?php esc_html_e( 'محصول', 'fartak' ); ?></th>
                    <th><?php esc_html_e( 'قیمت هدف', 'fartak' ); ?></th>
                    <th><?php esc_html_e( 'قیمت فعلی', 'fartak' ); ?></th>
                    <th><?php esc_html_e( 'وضعیت', 'fartak' ); ?></th>
                    <th><?php esc_html_e( 'تاریخ', 'fartak' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( empty( $alerts ) ) : ?>
                    <tr><td colspan="7" style="text-align:center;color:#8b95ab;padding:20px"><?php esc_html_e( 'هیچ هشداری ثبت نشده است.', 'fartak' ); ?></td></tr>
                <?php else : foreach ( $alerts as $alert ) :
                    $name    = get_post_meta( $alert->ID, '_alert_user_name', true );
                    $phone   = get_post_meta( $alert->ID, '_alert_user_phone', true );
                    $target  = (int) get_post_meta( $alert->ID, '_alert_target_price', true );
                    $pid     = (int) get_post_meta( $alert->ID, '_alert_product_id', true );
                    $status  = get_post_meta( $alert->ID, '_alert_status', true );
                    $product = $pid ? wc_get_product( $pid ) : null;
                    $current = $product ? (float) $product->get_price() : 0;
                    $status_labels = array(
                        'active'    => '<span style="color:#46b450;font-weight:700">' . __( 'فعال', 'fartak' ) . '</span>',
                        'notified'  => '<span style="color:var(--copper2, #ff3543);font-weight:700">' . __( 'اعلان شد', 'fartak' ) . '</span>',
                        'cancelled' => '<span style="color:#8b95ab;font-weight:700">' . __( 'لغو شده', 'fartak' ) . '</span>',
                    );
                    ?>
                    <tr>
                        <td><a href="<?php echo esc_url( get_edit_post_link( $alert->ID ) ); ?>"><strong><?php echo esc_html( $name ?: '—' ); ?></strong></a></td>
                        <td><?php echo esc_html( $phone ?: '—' ); ?></td>
                        <td><?php if ( $product ) : ?><a href="<?php echo esc_url( get_permalink( $pid ) ); ?>"><?php echo esc_html( $product->get_name() ); ?></a><?php else : echo '—'; endif; ?></td>
                        <td><?php echo esc_html( fartak_fa_num( number_format( $target ) ) ); ?></td>
                        <td><?php echo $current > 0 ? esc_html( fartak_fa_num( number_format( $current ) ) ) : '—'; ?></td>
                        <td><?php echo $status_labels[ $status ] ?? esc_html( $status ); ?></td>
                        <td><?php echo esc_html( wp_date( 'Y/m/d H:i', strtotime( $alert->post_date ) ) ); ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

/* ----------------------------------------------------------- frontend JS */

add_action( 'wp_footer', 'fartak_pa_assets', 30 );
function fartak_pa_assets() {
    if ( ! function_exists( 'is_product' ) || ! is_product() ) return;
    ?>
    <script>
    (function(){
        if (typeof FARTAK === 'undefined' || !FARTAK.ajax) return;
        var modal = document.getElementById('fartak-pa-modal');
        if (!modal) return;
        document.addEventListener('click', function(e){
            if (e.target.closest('[data-pa-open]')) { modal.style.display = 'flex'; }
            if (e.target.closest('[data-pa-close]') || e.target === modal) { modal.style.display = 'none'; }
        });
        var form = modal.querySelector('[data-pa-form]');
        var msg  = modal.querySelector('[data-pa-msg]');
        form.addEventListener('submit', function(e){
            e.preventDefault();
            var data = new FormData(form);
            data.append('action', 'fartak_price_alert_submit');
            data.append('nonce', FARTAK.nonce);
            msg.style.color = '#8b95ab';
            msg.textContent = '<?php esc_html_e( 'در حال ارسال…', 'fartak' ); ?>';
            fetch(FARTAK.ajax, {method:'POST', body:data})
                .then(function(r){return r.json();})
                .then(function(res){
                    if (res.success) {
                        msg.style.color = '#46b450';
                        msg.textContent = res.data.message;
                        form.reset();
                        setTimeout(function(){ modal.style.display = 'none'; msg.textContent = ''; }, 3500);
                    } else {
                        msg.style.color = 'var(--copper2, #ff3543)';
                        msg.textContent = (res.data && res.data.message) ? res.data.message : '<?php esc_html_e( 'خطا در ارسال.', 'fartak' ); ?>';
                    }
                }).catch(function(){
                    msg.style.color = 'var(--copper2, #ff3543)';
                    msg.textContent = '<?php esc_html_e( 'خطا در ارتباط.', 'fartak' ); ?>';
                });
        });
    })();
    </script>
    <?php
}
