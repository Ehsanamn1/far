<?php
/**
 * Fartak Theme — functions
 *
 * @package Fartak
 */

defined( 'ABSPATH' ) || exit;

define( 'FARTAK_VER', '13.0.0' );
define( 'FARTAK_DIR', get_template_directory() );
define( 'FARTAK_URI', get_template_directory_uri() );

/* ------------------------------------------------------------------ includes */
$fartak_includes = array(
        '/inc/helpers.php',
        '/inc/customizer.php',
        '/inc/ajax.php',
        '/inc/theme-options.php',
        '/inc/code-injection.php',
        '/inc/sms.php',
        '/inc/ai-assistant.php',
        '/inc/notifications.php',
        '/inc/modules.php',
        '/inc/security.php',
);
if ( class_exists( 'WooCommerce' ) ) {
        $fartak_includes[] = '/inc/woocommerce.php';
}
foreach ( $fartak_includes as $fartak_inc ) {
        $fartak_path = FARTAK_DIR . $fartak_inc;
        if ( file_exists( $fartak_path ) ) {
                require_once $fartak_path;
        }
}

/* ------------------------------------------------------------------ setup */
add_action( 'after_setup_theme', 'fartak_setup' );
function fartak_setup() {
        load_theme_textdomain( 'fartak', FARTAK_DIR . '/languages' );
        add_theme_support( 'title-tag' );
        add_theme_support( 'post-thumbnails' );
        add_theme_support( 'custom-logo', array( 'height' => 72, 'width' => 72, 'flex-height' => true, 'flex-width' => true ) );
        add_theme_support( 'automatic-feed-links' );
        add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
        add_theme_support( 'woocommerce' );
        add_theme_support( 'wc-product-gallery-zoom' );
        add_theme_support( 'wc-product-gallery-lightbox' );
        add_theme_support( 'wc-product-gallery-slider' );
        register_nav_menus(
                array(
                        'topbar' => __( 'نوار بالای سایت', 'fartak' ),
                        'footer' => __( 'فوتر — پیوندهای مفید', 'fartak' ),
                )
        );
        set_post_thumbnail_size( 640, 640, true );
        add_image_size( 'fartak-card', 480, 480, true );
        add_image_size( 'fartak-hero', 1280, 720, true );
}

add_action( 'widgets_init', 'fartak_widgets' );
function fartak_widgets() {
        register_sidebar(
                array(
                        'name'          => __( 'سایدبار وبلاگ', 'fartak' ),
                        'id'            => 'blog-sidebar',
                        'before_widget' => '<section id="%1$s" class="widget card-compact %2$s" style="padding:16px">',
                        'after_widget'  => '</section>',
                        'before_title'  => '<h4 class="widget-title">',
                        'after_title'   => '</h4>',
                )
        );
}

/* ---------------------------------------------------------------- assets */
add_action( 'wp_enqueue_scripts', 'fartak_assets' );
function fartak_assets() {
        // فقط وزن‌های لازم فونت از گوگل (مثل قبل — نسخه self-hosted حذف شد)
        wp_enqueue_style( 'fartak-fonts', FARTAK_URI . '/assets/fonts/fonts.css', array(), FARTAK_VER );
        $needs_swiper = is_front_page() || is_home();
        if ( $needs_swiper ) {
                wp_enqueue_style( 'fartak-swiper', FARTAK_URI . '/assets/css/swiper-bundle.min.css', array(), '12.0.0' );
                wp_enqueue_script( 'fartak-swiper', FARTAK_URI . '/assets/js/swiper-bundle.min.js', array(), '12.0.0', true );
        }
        wp_enqueue_style( 'fartak-style', get_stylesheet_uri(), $needs_swiper ? array( 'fartak-swiper' ) : array(), FARTAK_VER );
        wp_enqueue_script( 'fartak-theme', FARTAK_URI . '/assets/js/theme.js', $needs_swiper ? array( 'fartak-swiper' ) : array(), FARTAK_VER, true );
        wp_localize_script(
                'fartak-theme',
                'FARTAK',
                array(
                        'ajax'      => admin_url( 'admin-ajax.php' ),
                        'nonce'     => wp_create_nonce( 'fartak' ),
                        'isWc'      => class_exists( 'WooCommerce' ) ? 1 : 0,
                        'homeUrl'   => home_url( '/' ),
                        'currency'  => function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : 'تومان',
                        'cartUrl'   => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' ),
                        'shopUrl'   => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ),
                        'placeholder' => function_exists( 'wc_placeholder_img_src' ) ? wc_placeholder_img_src( 'woocommerce_thumbnail' ) : ( FARTAK_URI . '/assets/images/placeholder.png' ),
                        'builderUrl' => fartak_tpl_url( 'page-templates/template-builder.php' ),
                        'i18n'      => array(
                                'added'    => __( 'به سبد خرید اضافه شد', 'fartak' ),
                                'compareOn' => __( 'به مقایسه اضافه شد', 'fartak' ),
                                'compareOff' => __( 'از مقایسه حذف شد', 'fartak' ),
                                'compareMax' => __( 'حداکثر ۳ کالا قابل مقایسه است', 'fartak' ),
                                'copied'   => __( 'کد تخفیف کپی شد', 'fartak' ),
                                'formErr'  => __( 'نام و شماره موبایل معتبر وارد کنید', 'fartak' ),
                                'sendErr'  => __( 'خطا در ارسال؛ دوباره تلاش کنید', 'fartak' ),
                        ),
                )
        );
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}

/* تضمین نسخه‌دار شدن فایل‌های قالب (style.css / theme.js / …) تا مرورگر بعد هر آپدیت، CSS/JS قدیمی کش‌شده نشان ندهد */
add_filter( 'style_loader_src', 'fartak_asset_version', 20 );
add_filter( 'script_loader_src', 'fartak_asset_version', 20 );
function fartak_asset_version( $src ) {
    if ( false === strpos( $src, '/themes/' . get_stylesheet() . '/' ) ) { return $src; }
    return add_query_arg( 'v', FARTAK_VER, remove_query_arg( array( 'ver', 'v' ), $src ) );
}

/* ------------------------------------------------- بهینه‌سازی سرعت */
add_filter( 'heartbeat_settings', function( $settings ) { $settings['interval'] = 60; return $settings; } );

/* ------------------------------------------------- post types (استعلام/لیدها) */
add_action( 'init', 'fartak_register_cpts' );
function fartak_register_cpts() {
        register_post_type(
                'fartak_inquiry',
                array(
                        'labels'       => array( 'name' => __( 'لیدها و استعلام‌ها', 'fartak' ), 'singular_name' => __( 'استعلام', 'fartak' ) ),
                        'public'       => false,
                        'show_ui'      => true,
                        'menu_position' => 27,
                        'menu_icon'    => 'dashicons-phone',
                        'supports'     => array( 'title', 'editor' ),
                )
        );
}

/* metabox ساده برای جزئیات استعلام */
add_action( 'add_meta_boxes', 'fartak_inquiry_box' );
function fartak_inquiry_box() {
        add_meta_box( 'fartak_inquiry_meta', __( 'جزئیات استعلام قیمت', 'fartak' ), 'fartak_inquiry_box_html', 'fartak_inquiry', 'normal', 'high' );
}
function fartak_inquiry_box_html( $post ) {
        wp_nonce_field( 'fartak_inquiry_save', 'fartak_inquiry_nonce' );
        $fields = array(
                '_fartak_customer' => __( 'نام مشتری', 'fartak' ),
                '_fartak_phone'    => __( 'شماره تماس', 'fartak' ),
                '_fartak_product'  => __( 'محصول مورد نظر', 'fartak' ),
                '_fartak_quantity' => __( 'تعداد', 'fartak' ),
        );
        foreach ( $fields as $key => $label ) {
                printf(
                        '<p><label for="%1$s"><strong>%2$s</strong></label><br><input type="text" id="%1$s" name="%1$s" value="%3$s" class="widefat"></p>',
                        esc_attr( $key ),
                        esc_html( $label ),
                        esc_attr( get_post_meta( $post->ID, $key, true ) )
                );
        }
        $stage = get_post_meta( $post->ID, '_fartak_stage', true );
        echo '<p><label><strong>' . esc_html__( 'وضعیت استعلام', 'fartak' ) . '</strong></label><br>';
        echo '<select name="_fartak_stage">';
        $stages = array( 'new' => 'جدید', 'contacted' => 'تماس گرفته شد', 'quoted' => 'قیمت ارسال شد', 'closed' => 'بسته شد' );
        foreach ( $stages as $k => $s ) {
                printf( '<option value="%s" %s>%s</option>', $k, selected( $stage, $k, false ), esc_html( $s ) );
        }
        echo '</select></p>';
}
add_action( 'save_post_fartak_inquiry', 'fartak_inquiry_save' );
function fartak_inquiry_save( $post_id ) {
        if ( ! isset( $_POST['fartak_inquiry_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['fartak_inquiry_nonce'] ), 'fartak_inquiry_save' ) ) {
                return;
        }
        foreach ( array( '_fartak_customer', '_fartak_phone', '_fartak_product', '_fartak_quantity' ) as $key ) {
                if ( isset( $_POST[ $key ] ) ) {
                        update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
                }
        }
        if ( isset( $_POST['_fartak_stage'] ) ) {
                update_post_meta( $post_id, '_fartak_stage', sanitize_text_field( $_POST['_fartak_stage'] ) );
        }
}

/* محتوای سایت توسط فعال‌سازی قالب ساخته یا حذف نمی‌شود. */

/* اخطار ادمین در نبود ووکامرس */
add_action( 'admin_notices', 'fartak_woo_notice' );
function fartak_woo_notice() {
        if ( class_exists( 'WooCommerce' ) ) {
                return;
        }
        $plugin_url = esc_url( admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ) );
        echo '<div class="notice notice-warning is-dismissible"><p>';
        esc_html_e( 'قالب فرتاک: برای فروشگاه کامل (محصولات، سبد و تسویه‌حساب) افزونه WooCommerce را نصب و فعال کنید. وبلاگ و صفحات اصلی بدون آن هم کار می‌کنند.', 'fartak' );
        echo ' <a href="' . $plugin_url . '"><strong>' . esc_html__( 'نصب ووکامرس', 'fartak' ) . '</strong></a></p></div>';
}

/* WP 6.7+: فیلد تصویر دلخواه برای بندانگشتی دسته محصول توسط خود ووکامرس پشتیبانی می‌شود. */

