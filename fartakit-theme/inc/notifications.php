<?php
/**
 * Fartak - نوتیفیکیشن‌ها (فقط در پیشخوان ادمین، نه در سایت)
 *
 * @package Fartak
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// هیچ دکمه شناوری در frontend رندر نمیشه
// این فایل فقط AJAX هندلرهای ادمین رو نگه می‌داره

add_action( 'wp_ajax_fartak_clear_notifications', 'fartak_clear_notifications' );
function fartak_clear_notifications() {
    check_ajax_referer( 'fartak', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
    // هر دو مخزن اعلان پاک شود (transient قدیمی + option مرکز اعلان‌ها)
    delete_transient( 'fartak_admin_notifications' );
    delete_option( 'fartak_notifications' );
    wp_send_json_success();
}
