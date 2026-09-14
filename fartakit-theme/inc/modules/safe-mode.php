<?php
/**
 * Module: Safe Mode — حالت ایمن اضطراری
 *
 * When the `fartak_safe_mode_active` option is ON:
 *   - Other Fartak modules are not loaded (handled by inc/modules.php).
 *   - Custom CSS / JS-head / JS-footer / PHP injection is bypassed at the
 *     `option_*` filter level (settings are not deleted, just bypassed).
 *   - The companion plugin's `fartak_code_snippets` option is also bypassed.
 *   - An admin warning notice and a red admin-bar indicator are shown.
 *
 * Toggle is available via AJAX (`fartak_safe_mode_toggle`) and an admin
 * page under the Fartak panel.
 *
 * @package Fartak
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Helper: is safe mode currently active?
 *
 * @return bool
 */
function fartak_is_safe_mode() {
    return get_option( 'fartak_safe_mode_active', '0' ) === '1';
}

/**
 * List of theme/plugin option keys that are bypassed while safe mode is on.
 *
 * @return array
 */
function fartak_safe_mode_bypass_keys() {
    $keys = array(
        'fartak_custom_css',
        'fartak_custom_js_head',
        'fartak_custom_js_footer',
        'fartak_code_snippets',
    );
    return apply_filters( 'fartak_safe_mode_bypass_keys', $keys );
}

/**
 * Register filter callbacks that blank out the bypassed options whenever
 * safe mode is active. This way the values stay intact in wp_options and
 * are restored automatically the moment safe mode is turned off.
 */
add_action( 'plugins_loaded', 'fartak_safe_mode_register_filters', 1 );
function fartak_safe_mode_register_filters() {
    if ( ! fartak_is_safe_mode() ) {
        return;
    }
    foreach ( fartak_safe_mode_bypass_keys() as $opt ) {
        add_filter( 'option_' . $opt, 'fartak_safe_mode_blank_option', PHP_INT_MAX, 1 );
        add_filter( 'default_option_' . $opt, 'fartak_safe_mode_blank_option', PHP_INT_MAX, 1 );
    }
}

/**
 * Filter callback that returns an empty replacement value for bypassed
 * options. Returns `''` for string options and `array()` for the snippets
 * list.
 *
 * @param mixed $value Current stored value.
 * @return mixed
 */
function fartak_safe_mode_blank_option( $value ) {
    // Heuristic: array-shaped options become arrays, scalars become empty strings.
    if ( is_array( $value ) ) {
        return array();
    }
    return '';
}

/* ----------------------------------------------------------- admin notice */

add_action( 'admin_notices', 'fartak_safe_mode_admin_notice' );
function fartak_safe_mode_admin_notice() {
    if ( ! fartak_is_safe_mode() ) return;
    if ( ! current_user_can( 'manage_options' ) ) return;
    $off_url = wp_nonce_url(
        admin_url( 'admin-ajax.php?action=fartak_safe_mode_toggle&value=0' ),
        'fartak',
        'nonce'
    );
    ?>
    <div class="notice notice-warning" style="border-right:4px solid #e11d2a;background:#fef3f4">
        <p style="font-size:13px">
            <span class="dashicons dashicons-shield" style="color:#e11d2a;font-family:dashicons;vertical-align:middle"></span>
            <strong><?php esc_html_e( 'حالت ایمن قالب فرتاک فعال است.', 'fartak' ); ?></strong>
            <?php esc_html_e( 'کدهای سفارشی (CSS/JS/PHP) و سایر ماژول‌ها به‌صورت موقت غیرفعال شده‌اند تا سایت در دسترس بماند.', 'fartak' ); ?>
            <a href="<?php echo esc_url( $off_url ); ?>" class="button button-primary button-small" style="margin-right:8px"><?php esc_html_e( 'خروج از حالت ایمن', 'fartak' ); ?></a>
        </p>
    </div>
    <?php
}

/* ----------------------------------------------------------- admin bar indicator */

add_action( 'admin_bar_menu', 'fartak_safe_mode_admin_bar', 1 );
function fartak_safe_mode_admin_bar( $wp_admin_bar ) {
    if ( ! fartak_is_safe_mode() ) return;
    if ( ! current_user_can( 'manage_options' ) ) return;
    $wp_admin_bar->add_node( array(
        'id'    => 'fartak-safe-mode',
        'title' => '<span style="background:#e11d2a;color:#fff;padding:2px 10px;border-radius:4px;font-size:11px;font-weight:700;display:inline-flex;align-items:center;gap:4px">
            <span class="dashicons dashicons-shield" style="font-family:dashicons;font-size:13px;margin-top:1px"></span>'
            . esc_html__( 'حالت ایمن فعال', 'fartak' ) .
            '</span>',
        'href'  => admin_url( 'admin.php?page=fartak-panel-safe-mode' ),
        'meta'  => array( 'class' => 'fartak-safe-mode-node' ),
    ) );
}

/**
 * Add a small inline style on the admin bar so the node sits on the right.
 */
add_action( 'wp_head', 'fartak_safe_mode_admin_bar_style', 1 );
add_action( 'admin_head', 'fartak_safe_mode_admin_bar_style', 1 );
function fartak_safe_mode_admin_bar_style() {
    if ( ! fartak_is_safe_mode() ) return;
    echo '<style>#wp-admin-bar-fartak-safe-mode{order:-1}#wpadminbar .fartak-safe-mode-node a{background:#e11d2a !important}</style>' . "\n";
}

/* ----------------------------------------------------------- AJAX toggle */

add_action( 'wp_ajax_fartak_safe_mode_toggle', 'fartak_safe_mode_toggle_ajax' );
function fartak_safe_mode_toggle_ajax() {
    check_ajax_referer( 'fartak', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'دسترسی غیرمجاز', 'fartak' ) ) );
    }

    $value = isset( $_REQUEST['value'] )
        ? ( sanitize_text_field( wp_unslash( $_REQUEST['value'] ) ) )
        : ( fartak_is_safe_mode() ? '0' : '1' );
    $active = $value === '1' || $value === 'on' || $value === true;

    if ( $active ) {
        update_option( 'fartak_safe_mode_active', '1' );
        // Clear any caches that might still serve custom code.
        wp_cache_flush();
    } else {
        delete_option( 'fartak_safe_mode_active' );
    }

    wp_send_json_success( array(
        'active' => $active,
        'message' => $active
            ? __( 'حالت ایمن فعال شد. ماژول‌ها و کدهای سفارشی موقتاً غیرفعال هستند.', 'fartak' )
            : __( 'حالت ایمن غیرفعال شد. همه ماژول‌ها و کدهای سفارشی فعال شدند.', 'fartak' ),
    ) );
}

/* ----------------------------------------------------------- admin page */

add_action( 'admin_menu', 'fartak_safe_mode_admin_menu', 60 );
function fartak_safe_mode_admin_menu() {
    add_submenu_page(
        'fartak-panel',
        __( 'حالت ایمن', 'fartak' ),
        __( 'حالت ایمن', 'fartak' ),
        'manage_options',
        'fartak-panel-safe-mode',
        'fartak_safe_mode_page'
    );
}

function fartak_safe_mode_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $active = fartak_is_safe_mode();
    $toggle_url = wp_nonce_url(
        admin_url( 'admin-ajax.php?action=fartak_safe_mode_toggle&value=' . ( $active ? '0' : '1' ) ),
        'fartak',
        'nonce'
    );
    $bypassed = fartak_safe_mode_bypass_keys();
    ?>
    <div class="wrap">
        <h1><span class="dashicons dashicons-shield" style="color:<?php echo $active ? '#e11d2a' : '#46b450'; ?>"></span> <?php esc_html_e( 'حالت ایمن قالب فرتاک', 'fartak' ); ?></h1>

        <div class="notice <?php echo $active ? 'notice-warning' : 'notice-info'; ?>" style="border-right:4px solid <?php echo $active ? '#e11d2a' : '#3b82f6'; ?>">
            <p style="font-size:13px;line-height:1.8">
                <?php if ( $active ) : ?>
                    <strong style="color:#e11d2a"><?php esc_html_e( 'حالت ایمن اکنون فعال است.', 'fartak' ); ?></strong>
                    <?php esc_html_e( 'کدهای سفارشی تزریق‌شده (CSS / JS Head / JS Footer / PHP) و تمام ماژول‌ها به‌جز خودِ Safe Mode موقتاً غیرفعال شده‌اند تا سایت در دسترس بماند. داده‌ها حذف نشده‌اند — با غیرفعال‌سازی حالت ایمن، همه چیز به حالت قبل برمی‌گردد.', 'fartak' ); ?>
                <?php else : ?>
                    <strong><?php esc_html_e( 'حالت ایمن غیرفعال است.', 'fartak' ); ?></strong>
                    <?php esc_html_e( 'در صورت بروز مشکل در سایت (خطای fatal، صفحه سفید، تداخل افزونه)، با فعال‌سازی حالت ایمن می‌توانید موقتاً تمام کدهای سفارشی و ماژول‌ها را بدون حذف، غیرفعال کنید.', 'fartak' ); ?>
                <?php endif; ?>
            </p>
            <p>
                <a href="<?php echo esc_url( $toggle_url ); ?>" class="button <?php echo $active ? 'button-primary' : 'button-secondary'; ?> button-large">
                    <?php $active ? esc_html_e( 'غیرفعال‌سازی حالت ایمن', 'fartak' ) : esc_html_e( 'فعال‌سازی حالت ایمن', 'fartak' ); ?>
                </a>
            </p>
        </div>

        <h3><?php esc_html_e( 'در حالت ایمن چه چیزی غیرفعال می‌شود؟', 'fartak' ); ?></h3>
        <table class="widefat striped" style="max-width:760px">
            <thead>
                <tr><th><?php esc_html_e( 'قابلیت', 'fartak' ); ?></th><th><?php esc_html_e( 'وضعیت هنگام حالت ایمن', 'fartak' ); ?></th></tr>
            </thead>
            <tbody>
                <tr><td><?php esc_html_e( 'تزریق CSS سفارشی', 'fartak' ); ?></td><td><span style="color:#e11d2a;font-weight:700"><?php esc_html_e( 'غیرفعال', 'fartak' ); ?></span></td></tr>
                <tr><td><?php esc_html_e( 'تزریق JS در head', 'fartak' ); ?></td><td><span style="color:#e11d2a;font-weight:700"><?php esc_html_e( 'غیرفعال', 'fartak' ); ?></span></td></tr>
                <tr><td><?php esc_html_e( 'تزریق JS در footer', 'fartak' ); ?></td><td><span style="color:#e11d2a;font-weight:700"><?php esc_html_e( 'غیرفعال', 'fartak' ); ?></span></td></tr>
                <tr><td><?php esc_html_e( 'اجرای PHP سفارشی', 'fartak' ); ?></td><td><span style="color:#e11d2a;font-weight:700"><?php esc_html_e( 'غیرفعال', 'fartak' ); ?></span></td></tr>
                <tr><td><?php esc_html_e( 'افزونه تزریق کد (Fartak Code Injection)', 'fartak' ); ?></td><td><span style="color:#e11d2a;font-weight:700"><?php esc_html_e( 'غیرفعال', 'fartak' ); ?></span></td></tr>
                <tr><td><?php esc_html_e( 'ماژول‌های قالب (به‌جز خود Safe Mode)', 'fartak' ); ?></td><td><span style="color:#e11d2a;font-weight:700"><?php esc_html_e( 'بارگذاری نمی‌شوند', 'fartak' ); ?></span></td></tr>
                <tr><td><?php esc_html_e( 'ووکامرس و قالب پایه', 'fartak' ); ?></td><td><span style="color:#46b450;font-weight:700"><?php esc_html_e( 'فعال می‌مانند', 'fartak' ); ?></span></td></tr>
            </tbody>
        </table>

        <h3><?php esc_html_e( 'گزینه‌های bypass‌شده (بدون حذف)', 'fartak' ); ?></h3>
        <p class="description"><?php esc_html_e( 'مقادیر این گزینه‌ها از wp_options حذف نمی‌شوند — فقط خروجی آن‌ها در حالت ایمن خالی فرض می‌شود.', 'fartak' ); ?></p>
        <ul style="font-family:monospace;background:#0e1626;color:#e8ecf4;padding:14px 18px;border-radius:8px;max-width:760px;direction:ltr">
            <?php foreach ( $bypassed as $key ) : ?>
                <li><?php echo esc_html( $key ); ?></li>
            <?php endforeach; ?>
        </ul>

        <p class="description" style="margin-top:16px">
            <?php esc_html_e( 'راهنما: حالت ایمن از طریق فیلترهای option_* عمل می‌کند. هر ماژول یا افزونه‌ای که می‌خواهد در حالت ایمن رفتار متفاوتی داشته‌باشد، می‌تواند تابع fartak_is_safe_mode() را بررسی کند.', 'fartak' ); ?>
        </p>
    </div>
    <?php
}

/* ----------------------------------------------------------- AJAX toggle button (JS) */

add_action( 'admin_footer', 'fartak_safe_mode_admin_js' );
function fartak_safe_mode_admin_js() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    ?>
    <script>
    (function(){
        // Allow direct toggle via query-string links (graceful fallback to URL).
        document.addEventListener('click', function(e){
            var link = e.target.closest('a[href*="fartak_safe_mode_toggle"]');
            if (!link) return;
            // Only intercept if fetch is available; otherwise let the link navigate.
            if (!window.fetch) return;
            e.preventDefault();
            var url = link.getAttribute('href');
            fetch(url, { credentials: 'same-origin' })
                .then(function(r){ return r.json(); })
                .then(function(res){
                    if (res && res.success) {
                        alert(res.data.message);
                        window.location.reload();
                    } else {
                        alert((res && res.data && res.data.message) || '<?php esc_html_e( 'خطا در تغییر وضعیت.', 'fartak' ); ?>');
                    }
                })
                .catch(function(){ window.location.href = url; });
        });
    })();
    </script>
    <?php
}
