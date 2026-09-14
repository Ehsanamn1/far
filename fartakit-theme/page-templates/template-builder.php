<?php
/**
 * Template Name: اسمبل آنلاین فرتاک (PC Builder)
 *
 * @package Fartak
 */

defined( 'ABSPATH' ) || exit;

get_header();

$steps = array(
        'cpu'         => array( __( 'پردازنده', 'fartak' ), 'cpu' ),
        'motherboard' => array( __( 'مادربرد', 'fartak' ), 'stack' ),
        'ram'         => array( __( 'رم', 'fartak' ), 'ram' ),
        'gpu'         => array( __( 'کارت گرافیک', 'fartak' ), 'circuit' ),
        'storage'     => array( __( 'حافظه', 'fartak' ), 'hdd' ),
        'case'        => array( __( 'کیس', 'fartak' ), 'box' ),
        'psu'         => array( __( 'پاور', 'fartak' ), 'plug' ),
);

$does = class_exists( 'WooCommerce' );
?>
<div class="container page-top page-105">
        <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px">
                <div>
                        <h1 style="display:flex;align-items:center;gap:8px;margin:0;font-size:20px;font-weight:900"><?php echo fartak_icon( 'zap' ); ?> <?php esc_html_e( 'اسمبل آنلاین فرتاک', 'fartak' ); ?></h1>
                        <p style="margin:4px 0 0;font-size:12px;color:var(--mist)"><?php esc_html_e( 'هفت مرحله تا سیستم رؤیایی — سازگاری قطعات خودکار بررسی می‌شود.', 'fartak' ); ?></p>
                </div>
                <div class="xp-card card-compact">
                        <?php echo fartak_icon( 'trophy' ); ?>
                        <div><b id="builder-xp">۰</b><small><?php esc_html_e( 'امتیاز اسمبل', 'fartak' ); ?></small></div>
                </div>
        </div>

        <?php if ( ! $does ) : ?>
                <div class="card-compact empty-state" style="margin-top:24px">
                        <?php echo fartak_icon( 'wrench' ); ?>
                        <h3><?php esc_html_e( 'ووکامرس فعال نیست', 'fartak' ); ?></h3>
                        <p><?php esc_html_e( 'برای استفاده از ابزار اسمبل، افزونه WooCommerce را نصب و فعال کنید.', 'fartak' ); ?></p>
                </div>
        <?php else : ?>

        <div class="builder-steps" id="builder-steps" style="margin-top:18px">
                <?php $i = 0; foreach ( $steps as $slug => $cfg ) : ?>
                        <button type="button" class="builder-step <?php echo 0 === $i ? 'on' : ''; ?>" data-step="<?php echo esc_attr( $slug ); ?>" data-step-i="<?php echo esc_attr( $i ); ?>">
                                <?php echo fartak_icon( $cfg[1] ); ?> <?php echo esc_html( $cfg[0] ); ?>
                                <span class="done-mark ft-hidden"><?php echo fartak_icon( 'check' ); ?></span>
                        </button>
                <?php $i++; endforeach; ?>
        </div>
        <div class="builder-progress">
                <div class="stock-track"><div class="stock-fill" id="builder-progress-fill" style="width:0%"></div></div>
                <small id="builder-progress-note"><?php echo esc_html( sprintf( __( '۰ از %s مرحله تکمیل شد', 'fartak' ), fartak_fa_num( count( $steps ) ) ) ); ?></small>
        </div>

        <div class="builder-grid">
                <div id="builder-panels">
                        <?php $i = 0; foreach ( $steps as $slug => $cfg ) : ?>
                                <div class="builder-panel <?php echo 0 === $i ? '' : 'ft-hidden'; ?>" data-panel="<?php echo esc_attr( $slug ); ?>">
                                        <h2 style="display:flex;align-items:center;gap:8px;font-size:15px;font-weight:900;margin-bottom:12px"><?php echo fartak_icon( $cfg[1] ); ?> <?php echo esc_html( sprintf( __( 'انتخاب %s', 'fartak' ), $cfg[0] ) ); ?></h2>
                                        <div class="builder-options">
                                                <?php
                                                // اول سعی کن محصولات دسته‌بندی خاص رو پیدا کنه
                                                $products = array();
                                                if ( class_exists( 'WooCommerce' ) ) {
                                                    $term = get_term_by( 'slug', $slug, 'product_cat' );
                                                    if ( $term && ! is_wp_error( $term ) ) {
                                                        $products = wc_get_products( array(
                                                            'status'   => 'publish',
                                                            'limit'    => 12,
                                                            'category' => array( $slug ),
                                                            'orderby'  => 'meta_value_num',
                                                            'meta_key' => 'total_sales',
                                                            'order'    => 'DESC',
                                                        ) );
                                                    }
                                                    // فقط اگر این مرحله واقعاً دسته ندارد، از پرفروش‌ها fallback کن —
                                                    // قبلاً هر ۷ مرحله یک لیست یکسان می‌گرفت و می‌شد ۷ تا GPU انتخاب کرد
                                                    if ( empty( $products ) && ! $term ) {
                                                        $products = wc_get_products( array(
                                                            'status'   => 'publish',
                                                            'limit'    => 12,
                                                            'orderby'  => 'meta_value_num',
                                                            'meta_key' => 'total_sales',
                                                            'order'    => 'DESC',
                                                        ) );
                                                    }
                                                }
                                                if ( ! $products ) {
                                                        echo '<p style="grid-column:1/-1;font-size:12.5px;color:var(--mist)">' . esc_html__( 'هنوز محصولی اضافه نشده. از پنل مدیریت وردپرس محصول اضافه کنید.', 'fartak' ) . '</p>';
                                                }
                                                foreach ( $products as $product ) :
                                                        if ( ! $product->is_in_stock() || '' === $product->get_price() ) {
                                                                continue;
                                                        }
                                                        $socket = trim( strtolower( $product->get_attribute( 'socket' ) ) );
                                                        if ( ! $socket ) {
                                                                $socket = trim( strtolower( $product->get_attribute( 'pa_socket' ) ) );
                                                        }
                                                        $ramtype = trim( strtolower( $product->get_attribute( 'ram-type' ) ) );
                                                        if ( ! $ramtype ) {
                                                                $ramtype = trim( strtolower( $product->get_attribute( 'pa_ram-type' ) ) );
                                                        }
                                                        ?>
                                                        <div class="bo-card" data-build-pick="<?php echo esc_attr( $slug ); ?>" data-id="<?php echo esc_attr( $product->get_id() ); ?>" data-name="<?php echo esc_attr( $product->get_name() ); ?>" data-price="<?php echo esc_attr( (float) $product->get_price() ); ?>" data-socket="<?php echo esc_attr( $socket ); ?>" data-ram="<?php echo esc_attr( $ramtype ); ?>">
                                                                <span class="sel-mark"><?php echo fartak_icon( 'check' ); ?></span>
                                                                <div class="im"><?php echo wp_kses_post( wp_get_attachment_image( $product->get_image_id(), 'fartak-card', false, array( 'loading' => 'lazy' ) ) ); ?></div>
                                                                <div class="bd">
                                                                        <div class="nm clamp2"><?php echo esc_html( $product->get_name() ); ?></div>
                                                                        <div class="warn ft-hidden" data-warn></div>
                                                                        <div class="pr"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
                                                                        <button type="button" class="bo-pick"><?php esc_html_e( 'انتخاب', 'fartak' ); ?></button>
                                                                </div>
                                                        </div>
                                                <?php endforeach; ?>
                                        </div>
                                </div>
                        <?php $i++; endforeach; ?>
                </div>

                <aside class="builder-summary card-compact">
                        <div class="bs-head"><?php esc_html_e( 'سیستم تو', 'fartak' ); ?></div>
                        <div class="bs-list" id="builder-summary">
                                <?php foreach ( $steps as $slug => $cfg ) : ?>
                                        <div class="bs-item empty" data-sum="<?php echo esc_attr( $slug ); ?>">
                                                <span class="b-i"><?php echo fartak_icon( $cfg[1] ); ?></span>
                                                <div class="tt">
                                                        <small><?php echo esc_html( $cfg[0] ); ?></small>
                                                        <b data-sum-name>— <?php esc_html_e( 'انتخاب نشده', 'fartak' ); ?></b>
                                                </div>
                                        </div>
                                <?php endforeach; ?>
                        </div>
                        <div class="bs-foot">
                                <div class="bs-row"><span><?php esc_html_e( 'توان مصرفی تخمینی', 'fartak' ); ?></span><b id="builder-watt">۰ <?php esc_html_e( 'وات', 'fartak' ); ?></b></div>
                                <div class="bs-row"><span><?php esc_html_e( 'مونتاژ و بنچمارک', 'fartak' ); ?></span><b style="color:#34d399"><?php esc_html_e( 'رایگان', 'fartak' ); ?></b></div>
                                <div class="bs-total">
                                        <span><?php esc_html_e( 'جمع قطعات', 'fartak' ); ?></span>
                                        <b><span id="builder-total">۰</span> <small><?php echo esc_html( get_woocommerce_currency_symbol() ); ?></small></b>
                                </div>
                                <button type="button" class="btn-copper w-full" id="builder-add-all" disabled style="margin-top:12px;padding-block:11px">
                                        <?php echo fartak_icon( 'cart' ); ?> <?php esc_html_e( 'افزودن همه به سبد', 'fartak' ); ?>
                                </button>
                                <p style="margin:8px 0 0;text-align:center;font-size:10px;line-height:1.6;color:var(--mist)" id="builder-hint"><?php esc_html_e( 'برای برآیند نهایی، همه مراحل را کامل کن.', 'fartak' ); ?></p>
                        </div>
                </aside>
        </div>
        <?php endif; ?>
</div>
<?php
get_footer();
