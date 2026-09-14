<?php
/**
 * Fartak — تنظیمات قالب (پنل مدیریت کامل)
 * ذخیره تنظیمات در wp_options با پیشوند fartak_
 *
 * @package Fartak
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * دریافت تنظیم با مقدار پیش‌فرض
 */
function fartak_opt( $key, $default = '' ) {
    $val = get_option( 'fartak_' . $key, null );
    if ( $val === null || $val === '' ) {
        $defaults = fartak_default_opts();
        return isset( $defaults[ $key ] ) ? $defaults[ $key ] : $default;
    }
    return $val;
}

function fartak_default_opts() {
    return array(
        // رنگ‌ها
        'color_primary'       => '#e11d2a',
        'color_primary_dark'  => '#b81622',
        'color_primary_light' => '#ff3543',
        // ووکامرس
        'woo_guest_checkout'  => '1',
        'woo_columns'         => '4',
        'woo_per_page'        => '12',
        // تزریق کد
        'custom_css'          => '',
        'custom_js_head'      => '',
        'custom_js_footer'    => '',
        // منشی AI
        'ai_enabled'          => '1',
        'ai_welcome'          => 'سلام! من دستیار هوشمند فرتاک هستم. چطور می‌توانم کمکتان کنم؟',
        // پیامک
        'sms_provider'        => 'none',
        'sms_api_key'         => '',
        'sms_sender'          => '',
        'sms_admin_phone'     => '',
        'sms_notify_quote'    => '0',
    );
}

/**
 * ثبت منوی پنل مدیریت
 */
add_action( 'admin_menu', 'fartak_admin_menu' );
function fartak_admin_menu() {
    add_menu_page(
        'تنظیمات قالب فرتاک',
        'قالب فرتاک',
        'manage_options',
        'fartak-panel',
        'fartak_panel_page',
        'dashicons-admin-customizer',
        58
    );

    add_submenu_page( 'fartak-panel', 'عمومی', 'عمومی', 'manage_options', 'fartak-panel', 'fartak_panel_page' );
    add_submenu_page( 'fartak-panel', 'داشبورد', 'داشبورد', 'manage_options', 'fartak-panel-dashboard', 'fartak_dashboard_fallback' );
    add_submenu_page( 'fartak-panel', 'رنگ‌ها', 'رنگ‌ها و تم', 'manage_options', 'fartak-panel-colors', 'fartak_colors_page' );
    add_submenu_page( 'fartak-panel', 'محصولات صفحه اصلی', 'محصولات صفحه اصلی', 'manage_options', 'fartak-panel-homepage', 'fartak_homepage_products_page' );
    add_submenu_page( 'fartak-panel', 'ووکامرس', 'ووکامرس', 'manage_options', 'fartak-panel-woo', 'fartak_woo_page' );
    add_submenu_page( 'fartak-panel', 'تزریق کد', 'تزریق کد', 'manage_options', 'fartak-panel-code', 'fartak_code_page' );
    add_submenu_page( 'fartak-panel', 'پیامک', 'پیامک و نوتیفیکیشن', 'manage_options', 'fartak-panel-sms', 'fartak_sms_page' );
    add_submenu_page( 'fartak-panel', 'منشی AI', 'منشی هوش مصنوعی', 'manage_options', 'fartak-panel-ai', 'fartak_ai_page' );
    add_submenu_page( 'fartak-panel', 'پشتیبان', 'پشتیبان‌گیری', 'manage_options', 'fartak-panel-backup', 'fartak_backup_page' );
}

/**
 * ثبت تنظیمات
 */
add_action( 'admin_init', 'fartak_register_settings' );
function fartak_register_settings() {
    $opts = array_keys( fartak_default_opts() );
    foreach ( $opts as $opt ) {
        register_setting( 'fartak_settings_group', 'fartak_' . $opt );
    }
    register_setting( 'fartak_settings_group', 'fartak_inquiry_mode' );
    register_setting( 'fartak_settings_group', 'fartak_support_phone' );
register_setting( 'fartak_settings_group', 'fartak_call_ids' );
}

/**
 * صف استایل و اسکریپت ادمین
 */
add_action( 'admin_enqueue_scripts', 'fartak_admin_assets' );
function fartak_admin_assets( $hook ) {
    if ( strpos( $hook, 'fartak-panel' ) === false ) return;
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'wp-color-picker' );
    wp_enqueue_style( 'fartak-admin-css', FARTAK_URI . '/assets/css/admin.css', array( 'wp-color-picker' ), FARTAK_VER );
    wp_enqueue_script( 'fartak-admin-js', FARTAK_URI . '/assets/js/admin.js', array( 'wp-color-picker', 'jquery' ), FARTAK_VER, true );
}

/**
 * صفحه عمومی
 */
function fartak_panel_page() {
    if ( isset( $_POST['fartak_save'] ) && check_admin_referer( 'fartak_save', 'fartak_nonce' ) ) {
        echo '<div class="notice notice-success is-dismissible"><p>تنظیمات ذخیره شد.</p></div>';
    }
    $reseed = isset( $_GET['reseed'] ) ? sanitize_text_field( $_GET['reseed'] ) : '';
    if ( $reseed === '1' ) {
        echo '<div class="notice notice-success is-dismissible"><p>داده‌های نمونه با موفقیت ایجاد شدند!</p></div>';
    }
    ?>
    <div class="wrap">
        <h1><span class="dashicons dashicons-admin-customizer"></span> تنظیمات قالب فرتاک IT</h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'fartak_settings_group' ); ?>
            <?php wp_nonce_field( 'fartak_save', 'fartak_nonce' ); ?>
            <table class="form-table">
                <tr>
                    <th>واحد پول</th>
                    <td><p class="description">واحد پول و فرمت قیمت‌ها از تنظیمات ووکامرس (پیشخوان ← ووکامرس ← تنظیمات ← عمومی) مدیریت می‌شود.</p></td>
                </tr>
            </table>
            <?php submit_button( 'ذخیره تنظیمات' ); ?>
        </form>
    </div>
    <?php
}

/**
 * صفحه رنگ‌ها
 */
function fartak_colors_page() {
    ?>
    <div class="wrap">
        <h1><span class="dashicons dashicons-art"></span> رنگ‌ها و تم</h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'fartak_settings_group' ); ?>
            <table class="form-table">
                <tr>
                    <th>رنگ اصلی (قرمز)</th>
                    <td><input type="text" name="fartak_color_primary" value="<?php echo esc_attr( fartak_opt( 'color_primary' ) ); ?>" class="fartak-color-field" data-default-color="#e11d2a"></td>
                </tr>
                <tr>
                    <th>رنگ اصلی تیره</th>
                    <td><input type="text" name="fartak_color_primary_dark" value="<?php echo esc_attr( fartak_opt( 'color_primary_dark' ) ); ?>" class="fartak-color-field" data-default-color="#b81622"></td>
                </tr>
                <tr>
                    <th>رنگ اصلی روشن</th>
                    <td><input type="text" name="fartak_color_primary_light" value="<?php echo esc_attr( fartak_opt( 'color_primary_light' ) ); ?>" class="fartak-color-field" data-default-color="#ff3543"></td>
                </tr>
            </table>
            <p class="description">رنگ‌ها بلافاصله بعد از ذخیره و بارگذاری مجدد صفحه در سایت اعمال می‌شوند.</p>
            <?php submit_button( 'ذخیره رنگ‌ها' ); ?>
        </form>
    </div>
    <?php
}

/**
 * صفحه محصولات صفحه اصلی — انتخابگر ترتیبی محصولات (۱ تا ۲۰ برای هر بخش)
 */
function fartak_homepage_products_page() {
    if ( isset( $_POST['fartak_save_homepage'] ) && check_admin_referer( 'fartak_homepage', 'fartak_nonce' ) ) {
        $sections = array( 'sale', 'best', 'recent', 'popular', 'story', 'call' );
        foreach ( $sections as $sec ) {
            $ids = isset( $_POST['home_' . $sec] ) ? sanitize_text_field( wp_unslash( $_POST['home_' . $sec] ) ) : '';
            if ( 'call' === $sec ) {
                update_option( 'fartak_call_ids', $ids );
                continue;
            }
            if ( 'story' === $sec ) {
                /* استوری حداکثر ۱۰ محصول */
                $ids = implode( ',', array_slice( array_filter( array_map( 'absint', explode( ',', $ids ) ) ), 0, 10 ) );
                update_option( 'fartak_home_story_ids', $ids );
                continue;
            }
            update_option( 'fartak_home_' . $sec . '_ids', $ids );
        }
        // عناوین
        update_option( 'fartak_home_sale_title', sanitize_text_field( $_POST['sale_title'] ?? 'تخفیف‌های ویژه' ) );
        update_option( 'fartak_home_best_title', sanitize_text_field( $_POST['best_title'] ?? 'پرفروش‌ترین‌های فرتاک' ) );
        update_option( 'fartak_home_recent_title', sanitize_text_field( $_POST['recent_title'] ?? 'تازه‌رسیده‌ها' ) );
        update_option( 'fartak_home_popular_title', sanitize_text_field( $_POST['popular_title'] ?? 'محبوب‌ترین‌ها' ) );
        echo '<div class="notice notice-success is-dismissible"><p>تنظیمات محصولات صفحه اصلی ذخیره شد.</p></div>';
    }

    // لیست محصولات با کش ۱۰ دقیقه‌ای + قیمت عددی سبک (قبلاً در هر بازدید limit -1 با get_price_html سنگین)
    $products = get_transient( 'fartak_admin_product_list' );
    if ( ! is_array( $products ) ) {
        $products = array();
        if ( function_exists( 'wc_get_products' ) ) {
            foreach ( wc_get_products( array( 'status' => 'publish', 'limit' => 100, 'orderby' => 'title', 'order' => 'ASC' ) ) as $p ) {
                $price = (float) $p->get_price();
                $products[] = array(
                    'id'    => $p->get_id(),
                    'name'  => $p->get_name(),
                    'price' => $price > 0 ? fartak_fa_num( $price ) : '',
                    'img'   => wp_get_attachment_image_url( $p->get_image_id(), 'thumbnail' ),
                );
            }
        }
        set_transient( 'fartak_admin_product_list', $products, 10 * MINUTE_IN_SECONDS );
    }

    $sections = array(
        'sale'    => array( 'label' => 'تخفیف‌های شگفت‌انگیز', 'title_opt' => 'fartak_home_sale_title',    'title_def' => 'تخفیف‌های شگفت‌انگیز' ),
        'best'    => array( 'label' => 'پرفروش‌ترین‌ها',  'title_opt' => 'fartak_home_best_title',    'title_def' => 'پرفروش‌ترین‌های هفته' ),
        'recent'  => array( 'label' => 'جدیدترین‌ها',   'title_opt' => 'fartak_home_recent_title',  'title_def' => 'جدیدترین‌ها' ),
        'popular' => array( 'label' => 'محبوب‌ترین‌ها',   'title_opt' => 'fartak_home_popular_title', 'title_def' => 'محبوب‌ترین‌ها' ),
        'story'   => array( 'label' => 'استوری‌های صفحه اصلی (حداکثر ۱۰)', 'title_opt' => '', 'title_def' => '' ),
        'call'    => array( 'label' => 'محصولات «تماس بگیرید» (انتخاب دستی از لیست کامل محصولات)', 'title_opt' => '', 'title_def' => '' ),
    );
    ?>
    <div class="wrap">
        <h1><span class="dashicons dashicons-products"></span> محصولات صفحه اصلی</h1>
        <p class="description">برای هر بخش، محصولات را از لیست «همه محصولات» جستجو کنید و با کلیک، به ترتیب دلخواه (از ۱ تا ۲۰) اضافه کنید. با دکمه‌های ▲▼ ترتیب را جابه‌جا کنید. اگر بخشی خالی بماند، محصولات به‌صورت خودکار نمایش داده می‌شوند.</p>
        <form method="post" action="">
            <?php wp_nonce_field( 'fartak_homepage', 'fartak_nonce' ); ?>
            <?php foreach ( $sections as $key => $conf ) :
                $csv      = (string) ( 'call' === $key ? get_option( 'fartak_call_ids', '' ) : get_option( 'fartak_home_' . $key . '_ids', '' ) );
                $selected = array_values( array_filter( array_map( 'absint', explode( ',', $csv ) ) ) );
            ?>
            <div class="ft-pick" data-max="<?php echo 'story' === $key ? '10' : ( 'call' === $key ? '50' : '20' ); ?>">
                <h2 class="ft-pick-head"><span class="dashicons dashicons-star-filled"></span> <?php echo esc_html( $conf['label'] ); ?></h2>
                <?php if ( $conf['title_opt'] ) : ?>
                <table class="form-table">
                    <tr>
                        <th>عنوان بخش</th>
                        <td><input type="text" name="<?php echo esc_attr( $key ); ?>_title" value="<?php echo esc_attr( get_option( $conf['title_opt'], $conf['title_def'] ) ); ?>" class="regular-text"></td>
                    </tr>
                </table>
                <?php endif; ?>
                <input type="hidden" name="home_<?php echo esc_attr( $key ); ?>" class="ft-pk-csv" value="<?php echo esc_attr( implode( ',', $selected ) ); ?>">
                <div class="ft-pick-cols">
                    <div class="ft-pick-left">
                        <p class="ft-pick-cap"><b>همه محصولات</b> — جستجو کنید و برای افزودن کلیک کنید</p>
                        <input type="search" class="ft-pk-search" placeholder="جستجو با نام یا آیدی محصول…">
                        <div class="ft-pick-box">
                            <?php if ( empty( $products ) ) : ?>
                                <p class="ft-pk-none">محصولی یافت نشد (ووکامرس یا محصولات منتشر نشده).</p>
                            <?php endif; ?>
                            <?php foreach ( $products as $p ) : ?>
                                <button type="button" class="ft-pk-item" data-id="<?php echo esc_attr( $p['id'] ); ?>" data-name="<?php echo esc_attr( $p['name'] ); ?>" data-price="<?php echo esc_attr( $p['price'] ); ?>" data-img="<?php echo esc_attr( $p['img'] ? $p['img'] : '' ); ?>">
                                    <span class="ft-pk-thumb"><?php echo $p['img'] ? '<img src="' . esc_url( $p['img'] ) . '" alt="">' : '<span class="ft-pk-noimg">؟</span>'; ?></span>
                                    <span class="ft-pk-txt">
                                        <span class="ft-pk-name"><?php echo esc_html( $p['name'] ); ?></span>
                                        <span class="ft-pk-meta">#<?php echo esc_html( $p['id'] ); ?><?php echo $p['price'] ? ' — ' . esc_html( $p['price'] ) : ''; ?></span>
                                    </span>
                                    <span class="ft-pk-add">+</span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="ft-pick-right">
                        <p class="ft-pick-cap"><b>ترتیب نمایش</b> — با ▲▼ جابه‌جا و با ✕ حذف کنید (حداکثر ۲۰)</p>
                        <div class="ft-pick-list"></div>
                        <p class="description ft-pk-hint" <?php echo empty( $selected ) ? '' : 'style="display:none"'; ?>>هنوز محصولی انتخاب نشده — این بخش خالی = انتخاب خودکار.</p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php submit_button( 'ذخیره محصولات', 'primary', 'fartak_save_homepage' ); ?>
        </form>
    </div>
    <?php
}

/**
 * صفحه ووکامرس
 */
function fartak_woo_page() {
    ?>
    <div class="wrap">
        <h1><span class="dashicons dashicons-cart"></span> تنظیمات ووکامرس</h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'fartak_settings_group' ); ?>
            <table class="form-table">
                <tr>
                    <th>حالت «تماس بگیرید»</th>
                    <td>
                        <label><input type="checkbox" name="fartak_inquiry_mode" value="1" <?php checked( get_option( 'fartak_inquiry_mode', '0' ), '1' ); ?>> همه محصولات بدون قیمت، با دکمه «تماس بگیرید»</label>
                        <p class="description">با فعال‌کردن این تیک، قیمت همه محصولات سایت مخفی و دکمه «تماس بگیرید» (با شماره پشتیبانی) جایگزین می‌شود — یکدست در کارت‌ها، فروشگاه، استوری‌ها و صفحه محصول. برای بازگشت، تیک را بردارید و ذخیره کنید.</p>
                    </td>
                </tr>
                <tr>
                    <th>شماره پشتیبانی</th>
                    <td>
                        <input type="text" name="fartak_support_phone" value="<?php echo esc_attr( get_option( 'fartak_support_phone', '01732000180' ) ); ?>" class="regular-text" dir="ltr">
                        <p class="description">شماره‌ای که دکمه «تماس بگیرید» و منشی آنلاین به آن وصل می‌شوند (بدون خط تیره، مثلاً 01732000180 یا 09123456789).</p>
                    </td>
                </tr>
                <tr>
                    <th>خرید بدون ثبت‌نام</th>
                    <td><label><input type="checkbox" name="fartak_woo_guest_checkout" value="1" <?php checked( fartak_opt( 'woo_guest_checkout' ), '1' ); ?>> امکان خرید به‌صورت مهمان</label></td>
                </tr>
                <tr>
                    <th>تعداد ستون‌ها</th>
                    <td><input type="number" name="fartak_woo_columns" value="<?php echo esc_attr( fartak_opt( 'woo_columns' ) ); ?>" min="2" max="6"></td>
                </tr>
                <tr>
                    <th>تعداد محصول در صفحه</th>
                    <td><input type="number" name="fartak_woo_per_page" value="<?php echo esc_attr( fartak_opt( 'woo_per_page' ) ); ?>" min="4" max="48"></td>
                </tr>
            </table>
            <h3>سازگاری با افزونه‌ها</h3>
            <p>این قالب با افزونه‌های زیر کاملاً سازگار است:</p>
            <ul style="list-style:disc;padding-right:20px">
                <li>ووکامرس و ووکامرس فارسی</li>
                <li>درگاه‌های پرداخت ایرانی (زرین‌پال، ملت، سامان، پی‌پینگ و...)</li>
                <li>افزونه‌های پیامکی (کاوه‌نگار، ملی‌پیامک، فراپیامک، SMS.ir)</li>
                <li>افزونه علاقه‌مندی‌ها (YITH Wishlist)</li>
                <li>فرم‌سازها (Contact Form 7, WPForms)</li>
                <li>افزونه‌های کش و بهینه‌سازی</li>
            </ul>
            <?php submit_button( 'ذخیره' ); ?>
        </form>
    </div>
    <?php
}

/**
 * صفحه تزریق کد
 */
function fartak_code_page() {
    ?>
    <div class="wrap">
        <h1><span class="dashicons dashicons-editor-code"></span> تزریق کد سفارشی</h1>
        <p class="description">کدهای زیر مستقیماً در سایت اعمال می‌شوند. برای تغییرات سریع بدون ویرایش فایل‌های قالب از این بخش استفاده کنید.</p>
        <form method="post" action="options.php">
            <?php settings_fields( 'fartak_settings_group' ); ?>
            <table class="form-table">
                <tr>
                    <th>CSS سفارشی</th>
                    <td><textarea name="fartak_custom_css" rows="10" style="width:100%;font-family:monospace;direction:ltr;text-align:left" placeholder="/* مثال: */&#10;.ft-card { border-radius: 20px; }"><?php echo esc_textarea( fartak_opt( 'custom_css' ) ); ?></textarea></td>
                </tr>
                <tr>
                    <th>JavaScript در Head</th>
                    <td><textarea name="fartak_custom_js_head" rows="6" style="width:100%;font-family:monospace;direction:ltr;text-align:left" placeholder="// بدون تگ script"><?php echo esc_textarea( fartak_opt( 'custom_js_head' ) ); ?></textarea></td>
                </tr>
                <tr>
                    <th>JavaScript در Footer</th>
                    <td><textarea name="fartak_custom_js_footer" rows="6" style="width:100%;font-family:monospace;direction:ltr;text-align:left" placeholder="// بدون تگ script"><?php echo esc_textarea( fartak_opt( 'custom_js_footer' ) ); ?></textarea></td>
                </tr>
            </table>
            <?php submit_button( 'ذخیره کدها' ); ?>
        </form>
    </div>
    <?php
}

/**
 * صفحه پیامک
 */
function fartak_sms_page() {
    ?>
    <div class="wrap">
        <h1><span class="dashicons dashicons-email-alt"></span> پیامک و نوتیفیکیشن</h1>
        <p class="description">برای ارسال پیامک، افزونه Fartak SMS Notifications را نصب کنید. اینجا تنظیمات پایه قرار دارد.</p>
        <form method="post" action="options.php">
            <?php settings_fields( 'fartak_settings_group' ); ?>
            <table class="form-table">
                <tr>
                    <th>سرویس‌دهنده پیامک</th>
                    <td>
                        <select name="fartak_sms_provider">
                            <option value="none" <?php selected( fartak_opt( 'sms_provider' ), 'none' ); ?>>انتخاب کنید</option>
                            <option value="kavenegar" <?php selected( fartak_opt( 'sms_provider' ), 'kavenegar' ); ?>>کاوه‌نگار</option>
                            <option value="melipayamak" <?php selected( fartak_opt( 'sms_provider' ), 'melipayamak' ); ?>>ملی‌پیامک</option>
                            <option value="farapayamak" <?php selected( fartak_opt( 'sms_provider' ), 'farapayamak' ); ?>>فراپیامک</option>
                            <option value="smsir" <?php selected( fartak_opt( 'sms_provider' ), 'smsir' ); ?>>SMS.ir</option>
                            <option value="payamresan" <?php selected( fartak_opt( 'sms_provider' ), 'payamresan' ); ?>>پیام‌رسان</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>API Key</th>
                    <td><input type="text" name="fartak_sms_api_key" value="<?php echo esc_attr( fartak_opt( 'sms_api_key' ) ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th>شماره فرستنده</th>
                    <td><input type="text" name="fartak_sms_sender" value="<?php echo esc_attr( fartak_opt( 'sms_sender' ) ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th>شماره موبایل مدیر</th>
                    <td><input type="text" name="fartak_sms_admin_phone" value="<?php echo esc_attr( fartak_opt( 'sms_admin_phone' ) ); ?>" class="regular-text" placeholder="09123456789"></td>
                </tr>
                <tr>
                    <th>نوتیفیکیشن استعلام قیمت</th>
                    <td><label><input type="checkbox" name="fartak_sms_notify_quote" value="1" <?php checked( fartak_opt( 'sms_notify_quote' ), '1' ); ?>> ارسال پیامک هنگام درخواست استعلام</label></td>
                </tr>
            </table>
            <?php submit_button( 'ذخیره' ); ?>
        </form>
    </div>
    <?php
}

/**
 * صفحه منشی AI
 */
function fartak_ai_page() {
    // ذخیره پرسش‌وپاسخ‌ها
    if ( isset( $_POST['fartak_save_ai'] ) && check_admin_referer( 'fartak_ai_qa', 'fartak_ai_nonce' ) ) {
        $qa = array();
        if ( isset( $_POST['qa_q'] ) && is_array( $_POST['qa_q'] ) ) {
            foreach ( $_POST['qa_q'] as $i => $q ) {
                $q = sanitize_text_field( wp_unslash( $q ) );
                $a = isset( $_POST['qa_a'][ $i ] ) ? sanitize_textarea_field( wp_unslash( $_POST['qa_a'][ $i ] ) ) : '';
                if ( $q && $a ) {
                    $qa[] = array( $q, $a );
                }
            }
        }
        update_option( 'fartak_ai_qa', wp_json_encode( $qa ) );
        update_option( 'fartak_ai_enabled', isset( $_POST['ai_on'] ) ? '1' : '0' );
        update_option( 'fartak_ai_welcome', sanitize_textarea_field( wp_unslash( $_POST['ai_welcome'] ?? 'سلام! من دستیار هوشمند فرتاک هستم.' ) ) );
        echo '<div class="notice notice-success is-dismissible"><p>تنظیمات منشی ذخیره شد.</p></div>';
    }

    $qa_json = get_option( 'fartak_ai_qa', '' );
    $qa = array();
    if ( $qa_json ) {
        $decoded = json_decode( $qa_json, true );
        if ( is_array( $decoded ) ) $qa = $decoded;
    }
    if ( empty( $qa ) ) {
        $phone = get_option( 'fartak_support_phone', '01732000180' );
        $qa = array(
            array( 'قیمت امروز قطعات؟', 'برای استعلام قیمت دقیق با پشتیبانی فرتاک تماس بگیرید: ' . $phone ),
            array( 'شرایط گارانتی؟', 'گارانتی هر کالا روی صفحه خودش درج شده است. برای اطلاع دقیق‌تر تماس بگیرید: ' . $phone ),
            array( 'هزینه ارسال؟', 'ارسال به سراسر کشور با تیپاکس انجام می‌شود. برای اطلاع از هزینه دقیق تماس بگیرید: ' . $phone ),
            array( 'خرید عمده دارید؟', 'بله! برای خرید عمده با شماره ' . $phone . ' تماس بگیرید تا بهترین شرایط را فراهم کنیم.' ),
        );
    }
    ?>
    <div class="wrap">
        <h1><span class="dashicons dashicons-format-chat"></span> منشی هوش مصنوعی</h1>
        <p class="description">پاسخ سوالات پرتکرار را تعریف کنید؛ اگر پاسخی یافت نشود، منشی شماره پشتیبانی را به مشتری پیشنهاد می‌دهد.</p>
        <form method="post" action="">
            <?php wp_nonce_field( 'fartak_ai_qa', 'fartak_ai_nonce' ); ?>
            <table class="form-table">
                <tr>
                    <th>فعال‌سازی منشی</th>
                    <td><label><input type="checkbox" name="ai_on" <?php checked( get_option( 'fartak_ai_enabled', '1' ), '1' ); ?>> نمایش منشی آنلاین در سایت</label></td>
                </tr>
                <tr>
                    <th>پیام خوش‌آمد</th>
                    <td><textarea name="ai_welcome" rows="2" class="large-text"><?php echo esc_textarea( get_option( 'fartak_ai_welcome', 'سلام! من دستیار هوشمند فرتاک هستم. چطور می‌توانم کمکتان کنم؟' ) ); ?></textarea></td>
                </tr>
            </table>
            <h3>سوال‌ها و پاسخ‌ها</h3>
            <table class="form-table" id="fartak-qa-table">
                <?php foreach ( $qa as $i => $pair ) : ?>
                <tr>
                    <th>سوال <?php echo esc_html( fartak_fa_num( $i + 1 ) ); ?></th>
                    <td>
                        <input type="text" name="qa_q[]" value="<?php echo esc_attr( $pair[0] ); ?>" class="regular-text" placeholder="سوال مشتری" style="margin-bottom:6px">
                        <textarea name="qa_a[]" rows="2" class="large-text" placeholder="پاسخ منشی"><?php echo esc_textarea( $pair[1] ); ?></textarea>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            <p><button type="button" class="button" id="fartak-add-qa">+ افزودن سوال جدید</button></p>
            <script>
            document.getElementById('fartak-add-qa').addEventListener('click', function () {
                var tbl = document.getElementById('fartak-qa-table');
                var tr = tbl.insertRow();
                tr.innerHTML = '<th>سوال جدید</th><td><input type="text" name="qa_q[]" class="regular-text" placeholder="سوال مشتری" style="margin-bottom:6px"><textarea name="qa_a[]" rows="2" class="large-text" placeholder="پاسخ منشی"></textarea></td>';
            });
            </script>
            <?php submit_button( 'ذخیره منشی', 'primary', 'fartak_save_ai' ); ?>
        </form>
    </div>
    <?php
}

/**
 * صفحه پشتیبان‌گیری
 */
function fartak_backup_page() {
    if ( isset( $_POST['fartak_export'] ) && check_admin_referer( 'fartak_backup', 'fartak_backup_nonce' ) ) {
        header( 'Content-Type: application/json' );
        header( 'Content-Disposition: attachment; filename="fartak-settings-' . date('Y-m-d') . '.json"' );
        $opts = array();
        foreach ( array_keys( fartak_default_opts() ) as $k ) {
            $opts[ $k ] = get_option( 'fartak_' . $k );
        }
        echo wp_json_encode( $opts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
        exit;
    }
    ?>
    <div class="wrap">
        <h1><span class="dashicons dashicons-backup"></span> پشتیبان‌گیری و بازیابی</h1>
        <div class="card">
            <h3>خروجی تنظیمات</h3>
            <p>تمام تنظیمات قالب را به صورت فایل JSON دریافت کنید.</p>
            <form method="post" action="">
                <?php wp_nonce_field( 'fartak_backup', 'fartak_backup_nonce' ); ?>
                <button type="submit" name="fartak_export" value="1" class="button button-primary"><span class="dashicons dashicons-download"></span> دریافت فایل پشتیبان</button>
            </form>
        </div>
    </div>
    <?php
}

/**
 * داشبورد fallback - اگه ماژول داشبورد فعال نیست، این نمایش داده می‌شه
 */
function fartak_dashboard_fallback() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    // اگه ماژول داشبورد فعاله، از اون استفاده کن
    if ( function_exists( 'fartak_dash_page' ) ) {
        fartak_dash_page();
        return;
    }

    // دریافت آمار واقعی
    $stats = fartak_simple_stats();
    $reseed = isset( $_GET['reseed'] ) ? sanitize_text_field( $_GET['reseed'] ) : '';
    ?>
    <div class="wrap fartak-dashboard">
        <h1 style="display:flex;align-items:center;gap:10px">
            <span class="dashicons dashicons-chart-bar" style="color:#e11d2a;font-size:28px"></span>
            داشبورد فرتاک
            <button type="button" class="button button-small" onclick="window.location.reload()" style="margin-right:auto">
                <span class="dashicons dashicons-update"></span> به‌روزرسانی
            </button>
        </h1>

        <?php if ( $reseed === '1' ) : ?>
            <div class="notice notice-success is-dismissible"><p>✓ داده‌های نمونه با موفقیت ایجاد شدند!</p></div>
        <?php endif; ?>

        <?php if ( ! class_exists( 'WooCommerce' ) ) : ?>
            <div class="notice notice-warning"><p>برای نمایش کامل آمار فروش، افزونه ووکامرس را نصب کنید.</p></div>
        <?php endif; ?>

        <!-- کارت‌های آماری -->
        <div class="fartak-stats-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;margin-top:20px">
            <?php
            $colors = array( '#e11d2a', '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899' );
            $icons = array( 'products', 'admin-post', 'groups', 'phone', 'cart', 'warning' );
            foreach ( $stats as $i => $stat ) :
                $color = isset( $colors[ $i ] ) ? $colors[ $i ] : '#e11d2a';
                $icon = isset( $stat['icon'] ) ? $stat['icon'] : 'chart-bar';
            ?>
            <div class="fartak-stat-card" style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;position:relative;overflow:hidden;transition:all .2s;border-top:3px solid <?php echo esc_attr( $color ); ?>">
                <div style="display:flex;align-items:center;gap:12px">
                    <div style="width:48px;height:48px;border-radius:12px;background:<?php echo esc_attr( $color ); ?>20;display:flex;align-items:center;justify-content:center">
                        <span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>" style="font-size:24px;color:<?php echo esc_attr( $color ); ?>"></span>
                    </div>
                    <div>
                        <div style="font-size:24px;font-weight:900;color:#1a1a2e"><?php echo esc_html( $stat['value'] ); ?></div>
                        <div style="font-size:12px;color:#666"><?php echo esc_html( $stat['label'] ); ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- نمودار هفتگی -->
        <?php if ( class_exists( 'WooCommerce' ) ) : ?>
        <div class="fartak-chart-card" style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:24px;margin-top:20px">
            <h3 style="margin:0 0 16px;font-size:16px;font-weight:700">فروش ۷ روز اخیر</h3>
            <div style="display:flex;align-items:flex-end;gap:8px;height:120px">
                <?php
                /* یک کوئری برای کل ۷ روز — قبلاً ۷ کوئری + یک کوئری به‌ازای هر سفارش (N+1) */
                $weekly = array();
                $totals_by_day = array();
                $range_orders = wc_get_orders( array(
                    'date_created' => date( 'Y-m-d', strtotime( '-6 days' ) ) . '...' . date( 'Y-m-d 23:59:59' ),
                    'status'       => array( 'completed', 'processing' ),
                    'limit'        => 200,
                ) );
                foreach ( $range_orders as $range_order ) {
                    $d = $range_order->get_date_created() ? $range_order->get_date_created()->date( 'Y-m-d' ) : '';
                    if ( '' === $d ) {
                        continue;
                    }
                    $totals_by_day[ $d ] = ( isset( $totals_by_day[ $d ] ) ? $totals_by_day[ $d ] : 0 ) + (float) $range_order->get_total();
                }
                for ( $i = 6; $i >= 0; $i-- ) {
                    $date = date( 'Y-m-d', strtotime( "-{$i} days" ) );
                    $weekly[] = array(
                        'day'   => function_exists( 'jdate' ) ? jdate( 'l', strtotime( $date ) ) : date( 'D', strtotime( $date ) ),
                        'total' => isset( $totals_by_day[ $date ] ) ? $totals_by_day[ $date ] : 0,
                    );
                }
                $max = max( array_map( function( $w ) { return $w['total']; }, $weekly ) ) ?: 1;
                foreach ( $weekly as $w ) :
                    $height = max( 4, ( $w['total'] / $max ) * 100 );
                ?>
                <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:6px">
                    <div style="width:100%;height:<?php echo esc_attr( $height ); ?>%;background:linear-gradient(to top,#e11d2a,#ff3543);border-radius:6px 6px 0 0;min-height:4px;transition:height .3s"></div>
                <div style="font-size:10px;color:#999"><?php echo esc_html( $w['day'] ); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- سفارش‌های اخیر + استعلام‌ها -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px">
            <!-- سفارش‌های اخیر -->
            <div class="fartak-table-card" style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px">
                <h3 style="margin:0 0 12px;font-size:14px;font-weight:700;display:flex;align-items:center;gap:6px">
                    <span class="dashicons dashicons-cart" style="color:#e11d2a"></span>
                    سفارش‌های اخیر
                </h3>
                <?php if ( class_exists( 'WooCommerce' ) ) :
                    $orders = wc_get_orders( array( 'limit' => 5, 'orderby' => 'date', 'order' => 'DESC' ) );
                    if ( $orders ) : ?>
                    <table class="widefat" style="font-size:12px">
                        <thead><tr><th>شماره</th><th>مشتری</th><th>مبلغ</th><th>وضعیت</th></tr></thead>
                        <tbody>
                            <?php foreach ( $orders as $order ) : ?>
                            <tr>
                                <td>#<?php echo esc_html( $order->get_id() ); ?></td>
                                <td><?php echo esc_html( $order->get_billing_first_name() ?: 'مهمان' ); ?></td>
                                <td><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td>
                                <td><span style="padding:2px 8px;border-radius:4px;background:#e11d2a20;color:#e11d2a;font-size:11px"><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else : ?>
                    <p style="color:#999;text-align:center;padding:20px">سفارشی ثبت نشده</p>
                    <?php endif; ?>
                <?php else : ?>
                    <p style="color:#999;text-align:center;padding:20px">ووکامرس نصب نیست</p>
                <?php endif; ?>
            </div>

            <!-- استعلام‌های اخیر -->
            <div class="fartak-table-card" style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px">
                <h3 style="margin:0 0 12px;font-size:14px;font-weight:700;display:flex;align-items:center;gap:6px">
                    <span class="dashicons dashicons-phone" style="color:#e11d2a"></span>
                    استعلام‌های اخیر
                </h3>
                <?php
                $inquiries = get_posts( array( 'post_type' => 'fartak_inquiry', 'posts_per_page' => 5, 'orderby' => 'date', 'order' => 'DESC' ) );
                if ( $inquiries ) :
                ?>
                <table class="widefat" style="font-size:12px">
                    <thead><tr><th>مشتری</th><th>موبایل</th><th>محصول</th><th>وضعیت</th></tr></thead>
                    <tbody>
                        <?php foreach ( $inquiries as $inq ) :
                            $name = get_post_meta( $inq->ID, '_fartak_customer', true ) ?: $inq->post_title;
                            $phone = get_post_meta( $inq->ID, '_fartak_phone', true ) ?: '-';
                            $product = get_post_meta( $inq->ID, '_fartak_product', true ) ?: '-';
                            $status = get_post_meta( $inq->ID, '_fartak_stage', true ) ?: 'new';
                            $status_labels = array(
                                'new'       => '<span style="color:#dc3232">جدید</span>',
                                'contacted' => '<span style="color:#f59e0b">تماس شده</span>',
                                'quoted'    => '<span style="color:#3b82f6">قیمت داده شد</span>',
                                'closed'    => '<span style="color:#10b981">بسته شد</span>',
                            );
                        ?>
                        <tr>
                            <td><?php echo esc_html( $name ); ?></td>
                            <td dir="ltr"><?php echo esc_html( $phone ); ?></td>
                            <td><?php echo esc_html( mb_substr( $product, 0, 20 ) ); ?></td>
                            <td><?php echo isset( $status_labels[ $status ] ) ? $status_labels[ $status ] : esc_html( $status ); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else : ?>
                <p style="color:#999;text-align:center;padding:20px">استعلامی ثبت نشده</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- محصولات کم‌موجودی -->
        <?php if ( class_exists( 'WooCommerce' ) ) :
            $low_stock = wc_get_products( array(
                'status'   => 'publish',
                'limit'    => 5,
                'meta_key' => '_stock',
                'orderby'  => 'meta_value_num',
                'order'    => 'ASC',
            ) );
            $low = array();
            foreach ( $low_stock as $p ) {
                if ( $p->managing_stock() ) {
                    $qty = (int) $p->get_stock_quantity();
                    if ( $qty <= 5 ) $low[] = $p;
                }
            }
            if ( $low ) :
        ?>
        <div class="fartak-table-card" style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;margin-top:20px">
            <h3 style="margin:0 0 12px;font-size:14px;font-weight:700;display:flex;align-items:center;gap:6px">
                <span class="dashicons dashicons-warning" style="color:#f59e0b"></span>
                محصولات کم‌موجودی
            </h3>
            <table class="widefat" style="font-size:12px">
                <thead><tr><th>محصول</th><th>موجودی</th><th>قیمت</th></tr></thead>
                <tbody>
                    <?php foreach ( $low as $p ) : ?>
                    <tr>
                        <td><?php echo esc_html( $p->get_name() ); ?></td>
                        <td><span style="color:#dc3232;font-weight:700"><?php echo esc_html( fartak_fa_num( $p->get_stock_quantity() ) ); ?></span></td>
                        <td><?php echo wp_kses_post( $p->get_price_html() ); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; endif; ?>

        <!-- راهنما و ابزارها -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px">
            <div class="fartak-table-card" style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px">
                <h3 style="margin:0 0 12px;font-size:14px;font-weight:700">
                    <span class="dashicons dashicons-admin-tools" style="color:#e11d2a"></span>
                    ابزارها
                </h3>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=fartak-modules' ) ); ?>" class="button button-secondary" style="margin-right:8px">
                    <span class="dashicons dashicons-admin-plugins"></span> مدیریت ماژول‌ها
                </a>
            </div>

            <div class="fartak-table-card" style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px">
                <h3 style="margin:0 0 12px;font-size:14px;font-weight:700">
                    <span class="dashicons dashicons-info" style="color:#e11d2a"></span>
                    اطلاعات سیستم
                </h3>
                <table style="font-size:12px;width:100%">
                    <tr><td style="color:#999">قالب:</td><td><b>فرتاک نسخه 10.3.2</b></td></tr>
                    <tr><td style="color:#999">ووکامرس:</td><td><?php echo class_exists( 'WooCommerce' ) ? '<b style="color:#10b981">نصب است</b>' : '<b style="color:#dc3232">نصب نیست</b>'; ?></td></tr>
                    <tr><td style="color:#999">PHP:</td><td><b><?php echo esc_html( PHP_VERSION ); ?></b></td></tr>
                    <tr><td style="color:#999">حالت ایمن:</td><td><?php echo get_option( 'fartak_safe_mode_active' ) === '1' ? '<b style="color:#dc3232">فعال</b>' : '<b style="color:#10b981">غیرفعال</b>'; ?></td></tr>
                </table>
            </div>
        </div>

        <style>
        .fartak-stat-card:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.08)}
        .fartak-table-card table{border:none}
        .fartak-table-card table th{font-weight:700;color:#666}
        .fartak-table-card table td{padding:8px 4px}
        .fartak-table-card table tr{border-bottom:1px solid #f0f0f0}
        .fartak-table-card table tr:last-child{border-bottom:none}
        @media(max-width:768px){
            .fartak-stats-grid{grid-template-columns:repeat(2,1fr) !important}
            .fartak-table-card{grid-column:1 / -1 !important}
        }
        </style>
    </div>
    <?php
}

/**
 * آمار ساده برای fallback داشبورد
 */
function fartak_simple_stats() {
    $stats = array();

    // تعداد محصولات
    $product_count = wp_count_posts( 'product' );
    $stats[] = array(
        'label' => 'محصولات',
        'value' => fartak_fa_num( (int) $product_count->publish ),
        'icon'  => 'products',
    );

    // تعداد مقالات
    $post_count = wp_count_posts( 'post' );
    $stats[] = array(
        'label' => 'مقالات وبلاگ',
        'value' => fartak_fa_num( (int) $post_count->publish ),
        'icon'  => 'admin-post',
    );

    // تعداد کاربران
    $users = count_users();
    $stats[] = array(
        'label' => 'کاربران',
        'value' => fartak_fa_num( $users['total_users'] ),
        'icon'  => 'groups',
    );

    // تعداد استعلام‌ها (با post_status private ذخیره می‌شوند — publish هم شمرده شود)
    $inquiry_count = wp_count_posts( 'fartak_inquiry' );
    $inq_total = 0;
    if ( $inquiry_count ) {
        $inq_total = (int) ( $inquiry_count->publish ?? 0 ) + (int) ( $inquiry_count->private ?? 0 );
    }
    $stats[] = array(
        'label' => 'استعلام‌ها',
        'value' => fartak_fa_num( $inq_total ),
        'icon'  => 'phone',
    );

    // سفارش‌های تکمیل‌شده
    if ( class_exists( 'WooCommerce' ) ) {
        $stats[] = array(
            'label' => 'سفارش‌ها',
            'value' => fartak_fa_num( wc_orders_count( 'completed' ) + wc_orders_count( 'processing' ) ),
            'icon'  => 'cart',
        );

        // محصولات کم‌موجودی — فقط آیدی + متا (بدون ساخت آبجکت کامل WC برای همه محصولات)
        $low_count = 0;
        $product_ids = wc_get_products( array( 'status' => 'publish', 'limit' => 200, 'return' => 'ids' ) );
        foreach ( $product_ids as $pid ) {
            if ( 'yes' !== get_post_meta( $pid, '_manage_stock', true ) ) {
                continue;
            }
            $stock = (int) get_post_meta( $pid, '_stock', true );
            if ( $stock > 0 && $stock <= 5 ) {
                $low_count++;
            }
        }
        $stats[] = array(
            'label' => 'کم‌موجودی',
            'value' => fartak_fa_num( $low_count ),
            'icon'  => 'warning',
        );
    }

    return $stats;
}
