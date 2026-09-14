<?php
/**
 * Fartak Modular System - هسته ماژولار قالب
 * بارگذاری ماژول‌ها بر اساس فعال بودن
 *
 * @package Fartak
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * دریافت لیست ماژول‌ها
 */
function fartak_get_modules() {
    $modules = array(
        'quick-view'      => array(
            'name'        => 'مشاهده سریع محصول',
            'description' => 'نمایش سریع اطلاعات محصول بدون Reload',
            'file'        => 'inc/modules/quick-view.php',
            'default'     => true,
        ),
        'wishlist'        => array(
            'name'        => 'علاقه‌مندی‌ها',
            'description' => 'سیستم لیست علاقه‌مندی‌ها',
            'file'        => 'inc/modules/wishlist.php',
            'default'     => true,
        ),
        'compare'         => array(
            'name'        => 'مقایسه محصولات',
            'description' => 'مقایسه ویژگی‌های محصولات IT',
            'file'        => 'inc/modules/compare.php',
            'default'     => true,
        ),
        'recently-viewed' => array(
            'name'        => 'محصولات دیده‌شده',
            'description' => 'ذخیره و نمایش محصولات اخیر',
            'file'        => 'inc/modules/recently-viewed.php',
            'default'     => true,
        ),
        'smart-search'    => array(
            'name'        => 'جستجوی هوشمند',
            'description' => 'جستجوی Ajax با پیشنهاد محصول',
            'file'        => 'inc/modules/smart-search.php',
            'default'     => true,
        ),
        'product-finder'  => array(
            'name'        => 'یابنده محصول',
            'description' => 'ابزار راهنمای انتخاب محصول',
            'file'        => 'inc/modules/product-finder.php',
            'default'     => false,
        ),
        'price-alert'     => array(
            'name'        => 'هشدار قیمت',
            'description' => 'اعلان تغییر قیمت محصول',
            'file'        => 'inc/modules/price-alert.php',
            'default'     => false,
        ),
        'notification-center' => array(
            'name'        => 'مرکز اعلان‌ها',
            'description' => 'اعلان‌های فروش و استعلام',
            'file'        => 'inc/modules/notification-center.php',
            'default'     => true,
        ),
        'dashboard'       => array(
            'name'        => 'داشبورد فرتاک',
            'description' => 'آمار فروش و سفارش‌ها',
            'file'        => 'inc/modules/dashboard.php',
            'default'     => true,
        ),
        'safe-mode'       => array(
            'name'        => 'حالت ایمن',
            'description' => 'غیرفعال‌سازی کدهای مشکل‌ساز',
            'file'        => 'inc/modules/safe-mode.php',
            'default'     => true,
        ),
        'ai-assistant'    => array(
            'name'        => 'منشی آنلاین فرتاک',
            'description' => 'ویجت شناور چت هوشمند (پرسش و پاسخ)',
            'file'        => '',
            'default'     => true,
        ),
    );

    return apply_filters( 'fartak_modules', $modules );
}

/**
 * بررسی فعال بودن ماژول
 */
function fartak_module_enabled( $module_id ) {
    $modules = fartak_get_modules();
    if ( ! isset( $modules[ $module_id ] ) ) return false;

    // ماژول منشی از آپشن ویجت چت پیروی می‌کند
    if ( 'ai-assistant' === $module_id ) {
        return get_option( 'fartak_ai_enabled', '1' ) === '1';
    }

    $enabled = get_option( 'fartak_module_' . $module_id, null );
    if ( $enabled === null ) {
        return $modules[ $module_id ]['default'];
    }
    return $enabled === '1' || $enabled === true;
}

/**
 * بارگذاری ماژول‌های فعال
 */
add_action( 'after_setup_theme', 'fartak_load_modules', 20 );
function fartak_load_modules() {
    // حالت ایمن فقط با nonce (قبلاً با یک GET ساده بدون CSRF هر کسی می‌توانست برای ادمین لاگین‌شده بفرستد)
    if ( isset( $_GET['fartak_safe_mode'] ) && current_user_can( 'manage_options' )
        && check_admin_referer( 'fartak_safe_mode_toggle' ) ) {
        update_option( 'fartak_safe_mode_active', '1' );
    }
    if ( isset( $_GET['fartak_safe_mode_off'] ) && current_user_can( 'manage_options' )
        && check_admin_referer( 'fartak_safe_mode_toggle' ) ) {
        delete_option( 'fartak_safe_mode_active' );
    }

    $safe_mode = get_option( 'fartak_safe_mode_active', '0' ) === '1';

    $modules = fartak_get_modules();
    foreach ( $modules as $id => $module ) {
        // در حالت ایمن، فقط ماژول‌های ضروری لود می‌شوند
        if ( $safe_mode && ! in_array( $id, array( 'safe-mode' ), true ) ) {
            continue;
        }
        if ( ! fartak_module_enabled( $id ) ) {
            continue;
        }
        // ماژول‌های بدون فایل (فقط قابلیت‌های تابعی) نیازی به لود ندارند
        if ( empty( $module['file'] ) ) {
            continue;
        }
        $path = FARTAK_DIR . '/' . $module['file'];
        if ( file_exists( $path ) ) {
            require_once $path;
        }
    }
}

/**
 * منوی مدیریت ماژول‌ها
 */
add_action( 'admin_menu', 'fartak_modules_menu', 25 );
function fartak_modules_menu() {
    add_submenu_page(
        'fartak-panel',
        'ماژول‌ها',
        'ماژول‌ها',
        'manage_options',
        'fartak-modules',
        'fartak_modules_page'
    );
}

/**
 * صفحه مدیریت ماژول‌ها
 */
function fartak_modules_page() {
    // ذخیره تنظیمات
    if ( isset( $_POST['fartak_save_modules'] ) && check_admin_referer( 'fartak_modules', 'fartak_modules_nonce' ) ) {
        $modules = fartak_get_modules();
        foreach ( $modules as $id => $module ) {
            $enabled = isset( $_POST['module_' . $id] ) ? '1' : '0';
            update_option( 'fartak_module_' . $id, $enabled );
            // همگام‌سازی ماژول منشی با آپشن ویجت چت
            if ( 'ai-assistant' === $id ) {
                update_option( 'fartak_ai_enabled', $enabled );
            }
        }
        echo '<div class="notice notice-success is-dismissible"><p>ماژول‌ها به‌روزرسانی شدند.</p></div>';
    }

    $modules = fartak_get_modules();
    $safe_mode = get_option( 'fartak_safe_mode_active', '0' ) === '1';
    ?>
    <div class="wrap">
        <h1><span class="dashicons dashicons-admin-plugins"></span> ماژول‌های قالب فرتاک</h1>
        <?php if ( $safe_mode ) : ?>
            <div class="notice notice-warning"><p>
                <strong>حالت ایمن فعال است!</strong> تمام ماژول‌ها به جز خود Safe Mode غیرفعال شده‌اند.
                <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'fartak_safe_mode_off', '1' ), 'fartak_safe_mode_toggle' ) ); ?>" class="button button-small">خروج از حالت ایمن</a>
            </p></div>
        <?php else : ?>
            <p><a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'fartak_safe_mode', '1' ), 'fartak_safe_mode_toggle' ) ); ?>" class="button button-secondary" onclick="return confirm('با فعال‌سازی حالت ایمن، تمام ماژول‌ها غیرفعال می‌شوند. ادامه می‌دهید؟')">فعال‌سازی حالت ایمن</a></p>
        <?php endif; ?>

        <p class="description">ماژول‌ها را فعال یا غیرفعال کنید. غیرفعال کردن ماژول سایت را خراب نمی‌کند، فقط قابلیت آن را حذف می‌کند.</p>

        <form method="post" action="">
            <?php wp_nonce_field( 'fartak_modules', 'fartak_modules_nonce' ); ?>
            <div class="fartak-modules-grid">
                <?php foreach ( $modules as $id => $module ) : ?>
                    <div class="fartak-module-card <?php echo fartak_module_enabled( $id ) ? 'enabled' : 'disabled'; ?>">
                        <h3><?php echo esc_html( $module['name'] ); ?></h3>
                        <p class="description"><?php echo esc_html( $module['description'] ); ?></p>
                        <label class="fartak-toggle">
                            <input type="checkbox" name="module_<?php echo esc_attr( $id ); ?>" <?php checked( fartak_module_enabled( $id ) ); ?> <?php disabled( $safe_mode && $id !== 'safe-mode' ); ?>>
                            <span><?php echo fartak_module_enabled( $id ) ? 'فعال' : 'غیرفعال'; ?></span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            <p class="submit">
                <button type="submit" name="fartak_save_modules" value="1" class="button button-primary button-large">ذخیره ماژول‌ها</button>
            </p>
        </form>
        <style>
        .fartak-modules-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px; margin-top:16px; }
        .fartak-module-card { background:#fff; border:2px solid #e5e7eb; border-radius:12px; padding:20px; transition:all .2s; }
        .fartak-module-card.enabled { border-color:#46b450; }
        .fartak-module-card.disabled { opacity:.7; }
        .fartak-module-card h3 { margin:0 0 8px; }
        .fartak-module-card .description { margin:0 0 12px; font-size:12px; }
        .fartak-toggle input { margin-left:6px; }
        </style>
    </div>
    <?php
}
