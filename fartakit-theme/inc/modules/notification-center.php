<?php
/**
 * Module: Notification Center — مرکز اعلان‌های مدیریت
 *
 * Captures WooCommerce + Fartak events into a wp_options log of structured
 * notifications. Renders a floating admin panel on the frontend (when an
 * administrator is logged in) and exposes AJAX endpoints for fetching,
 * marking-as-read and clearing notifications.
 *
 * Note: the existing transient-based `fartak_admin_notifications` (from
 * inc/sms.php) is intentionally left untouched — this module provides a
 * richer, persistent option-based log alongside it.
 *
 * @package Fartak
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* -------------------------------------------------------- storage helpers */

/**
 * Get all notifications (newest first), max 50.
 *
 * @return array
 */
function fartak_nc_get_all() {
    $items = get_option( 'fartak_notifications', array() );
    return is_array( $items ) ? $items : array();
}

/**
 * Persist the notifications list (truncated to 50 newest).
 *
 * @param array $items
 */
function fartak_nc_set_all( $items ) {
    if ( ! is_array( $items ) ) $items = array();
    $items = array_slice( array_values( $items ), 0, 50 );
    update_option( 'fartak_notifications', $items, false );
}

/**
 * Push a new notification.
 *
 * @param string $type    Internal type (order_new, order_failed, low_stock, no_stock, inquiry, wholesale, user).
 * @param string $title   Short title (emoji-prefixed).
 * @param string $message Description body.
 * @param string $url     Click URL.
 */
function fartak_nc_push( $type, $title, $message, $url = '' ) {
    $items = fartak_nc_get_all();

    $items[] = array(
        'id'      => uniqid( 'nc_' ),
        'type'    => $type,
        'title'   => $title,
        'message' => $message,
        'url'     => $url ?: admin_url(),
        'time'    => current_time( 'mysql' ),
        'read'    => false,
    );

    // newest first
    array_unshift( $items, array_pop( $items ) );
    fartak_nc_set_all( $items );
}

/* ----------------------------------------------------------- event wiring */

add_action( 'woocommerce_checkout_order_processed', 'fartak_nc_order_new', 10, 3 );
function fartak_nc_order_new( $order_id, $posted_data, $order ) {
    if ( ! $order ) return;
    fartak_nc_push(
        'order_new',
        sprintf( '🟢 %s #%s', __( 'سفارش جدید', 'fartak' ), $order_id ),
        sprintf( '%s %s تومان', $order->get_billing_first_name() ?: __( 'مشتری', 'fartak' ), number_format( (float) $order->get_total() ) ),
        admin_url( 'post.php?post=' . $order_id . '&action=edit' )
    );
}

add_action( 'woocommerce_order_status_failed', 'fartak_nc_order_failed', 10, 2 );
function fartak_nc_order_failed( $order_id, $order ) {
    if ( ! $order ) return;
    fartak_nc_push(
        'order_failed',
        sprintf( '🔴 %s #%s', __( 'سفارش ناموفق', 'fartak' ), $order_id ),
        sprintf( '%s — %s تومان', $order->get_billing_first_name() ?: __( 'مشتری', 'fartak' ), number_format( (float) $order->get_total() ) ),
        admin_url( 'post.php?post=' . $order_id . '&action=edit' )
    );
}

add_action( 'woocommerce_low_stock', 'fartak_nc_low_stock' );
function fartak_nc_low_stock( $product ) {
    if ( ! $product ) return;
    fartak_nc_push(
        'low_stock',
        sprintf( '🟡 %s: %s', __( 'محصول کم موجودی', 'fartak' ), $product->get_name() ),
        sprintf( __( 'موجودی: %s عدد', 'fartak' ), $product->get_stock_quantity() ),
        admin_url( 'post.php?post=' . $product->get_id() . '&action=edit' )
    );
}

add_action( 'woocommerce_no_stock', 'fartak_nc_no_stock' );
function fartak_nc_no_stock( $product ) {
    if ( ! $product ) return;
    fartak_nc_push(
        'no_stock',
        sprintf( '🔴 %s: %s', __( 'محصول ناموجود', 'fartak' ), $product->get_name() ),
        __( 'محصول به اتمام رسیده است.', 'fartak' ),
        admin_url( 'post.php?post=' . $product->get_id() . '&action=edit' )
    );
}

add_action( 'fartak_inquiry_submitted', 'fartak_nc_inquiry' );
function fartak_nc_inquiry( $data ) {
    if ( ! is_array( $data ) ) return;
    $name = isset( $data['name'] ) ? $data['name'] : '';
    $product = isset( $data['product'] ) ? $data['product'] : '';
    $type = isset( $data['type'] ) ? $data['type'] : '';
    // درخواست خرید عمده هم از همین اکشن می‌آید — با برچسب درست ثبت شود
    if ( 'wholesale' === $type ) {
        fartak_nc_push(
            'wholesale',
            '🟣 ' . __( 'درخواست خرید عمده', 'fartak' ),
            sprintf( '%s — %s', $name, $product ),
            admin_url( 'edit.php?post_type=fartak_inquiry' )
        );
        return;
    }
    fartak_nc_push(
        'inquiry',
        '🔵 ' . __( 'استعلام قیمت جدید', 'fartak' ),
        sprintf( '%s — %s', $name, $product ),
        admin_url( 'edit.php?post_type=fartak_inquiry' )
    );
}

add_action( 'fartak_wholesale_submitted', 'fartak_nc_wholesale' );
function fartak_nc_wholesale( $data ) {
    if ( ! is_array( $data ) ) return;
    $name = isset( $data['name'] ) ? $data['name'] : '';
    fartak_nc_push(
        'wholesale',
        '🟣 ' . __( 'درخواست خرید عمده', 'fartak' ),
        $name,
        admin_url( 'edit.php?post_type=fartak_inquiry' )
    );
}

add_action( 'user_register', 'fartak_nc_user_register' );
function fartak_nc_user_register( $user_id ) {
    $user = get_userdata( $user_id );
    if ( ! $user ) return;
    fartak_nc_push(
        'user',
        sprintf( '👤 %s: %s', __( 'عضو جدید', 'fartak' ), $user->display_name ),
        $user->user_email,
        admin_url( 'user-edit.php?user_id=' . $user_id )
    );
}

/* ----------------------------------------------------------- admin bar - حذف شد */
// دکمه اعلان در admin bar حذف شد

/* ----------------------------------------------------------- AJAX endpoints */

add_action( 'wp_ajax_fartak_get_notifications', 'fartak_nc_ajax_get' );
function fartak_nc_ajax_get() {
    check_ajax_referer( 'fartak', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'دسترسی غیرمجاز', 'fartak' ) ) );
    }
    $items  = fartak_nc_get_all();
    $latest = array_slice( $items, 0, 10 );
    $unread = 0;
    foreach ( $items as $n ) {
        if ( empty( $n['read'] ) ) $unread++;
    }
    wp_send_json_success( array(
        'notifications' => array_map( 'fartak_nc_decorate', $latest ),
        'unread'        => $unread,
        'total'         => count( $items ),
    ) );
}

add_action( 'wp_ajax_fartak_mark_notification_read', 'fartak_nc_ajax_mark_read' );
function fartak_nc_ajax_mark_read() {
    check_ajax_referer( 'fartak', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

    $id    = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
    $items = fartak_nc_get_all();
    $found = false;
    foreach ( $items as $i => $n ) {
        if ( isset( $n['id'] ) && $n['id'] === $id ) {
            $items[ $i ]['read'] = true;
            $found = true;
            break;
        }
    }
    if ( $found ) fartak_nc_set_all( $items );
    wp_send_json_success( array( 'marked' => $found ) );
}

add_action( 'wp_ajax_fartak_mark_all_notifications_read', 'fartak_nc_ajax_mark_all_read' );
function fartak_nc_ajax_mark_all_read() {
    check_ajax_referer( 'fartak', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
    $items = fartak_nc_get_all();
    foreach ( $items as $i => $n ) {
        $items[ $i ]['read'] = true;
    }
    fartak_nc_set_all( $items );
    wp_send_json_success( array( 'cleared' => count( $items ) ) );
}

/* ثبت هم‌نام قدیمی حذف شد — fartak_clear_notifications در inc/notifications.php تعریف می‌شود
   (قبلاً دوبار با یک نام ثبت می‌شد و نسخه دوم هرگز اجرا نمی‌شد) */

/**
 * Decorate a notification record with a human-readable relative time string.
 */
function fartak_nc_decorate( $n ) {
    $n['time_ago'] = human_time_diff( strtotime( $n['time'] ), current_time( 'timestamp' ) ) . ' ' . __( 'پیش', 'fartak' );
    return $n;
}

/* ------------------------------------------------- floating frontend panel - حذف شد */
// دکمه شناور اعلانات در frontend حذف شد - فقط در پیشخوان ادمین قابل مشاهده است
