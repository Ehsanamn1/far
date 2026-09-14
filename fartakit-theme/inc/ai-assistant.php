<?php
/**
 * Fartak — منشی هوش مصنوعی (پل با افزونه)
 *
 * @package Fartak
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * هندلر AJAX fallback منشی (در صورت عدم نصب افزونه)
 */
if ( ! function_exists( 'fartak_ai_process_message' ) ) {
    add_action( 'wp_ajax_fartak_ai_chat', 'fartak_ai_chat_fallback' );
    add_action( 'wp_ajax_nopriv_fartak_ai_chat', 'fartak_ai_chat_fallback' );

    function fartak_ai_chat_fallback() {
        check_ajax_referer( 'fartak', 'nonce' );

        $message = isset( $_POST['message'] ) ? sanitize_text_field( $_POST['message'] ) : '';

        $responses = array(
            'default'  => 'سلام! برای پاسخ دقیق به سوال شما، با فروشگاه فرتاک تماس بگیرید.',
            'price'    => 'برای استعلام قیمت دقیق، از دکمه «استعلام هوشمند قیمت» روی کارت محصول استفاده کنید یا با فروشگاه تماس بگیرید.',
            'hours'    => 'ساعات کاری ما: شنبه تا پنجشنبه ۹ تا ۲۱',
            'address'  => 'آدرس ما: ' . fartak_mod( 'fartak_address', fartak_def( 'address' ) ),
            'shipping' => 'ارسال به سراسر کشور با تیپاکس انجام می‌شود. سفارش‌های بالای ۵ میلیون تومان ارسال رایگان دارند.',
            'warranty' => 'تمام قطعات فرتاک دارای گارانتی شرکتی معتبر هستند.',
            'wholesale'=> 'برای خرید عمده با فروشگاه تماس بگیرید تا بهترین شرایط را فراهم کنیم.',
        );

        $msg_lower = mb_strtolower( $message, 'UTF-8' );
        $response = $responses['default'];

        if ( mb_strpos( $msg_lower, 'قیمت' ) !== false || mb_strpos( $msg_lower, 'نرخ' ) !== false ) {
            $response = $responses['price'];
        } elseif ( mb_strpos( $msg_lower, 'ساعت' ) !== false || mb_strpos( $msg_lower, 'کار' ) !== false ) {
            $response = $responses['hours'];
        } elseif ( mb_strpos( $msg_lower, 'آدرس' ) !== false || mb_strpos( $msg_lower, 'کجا' ) !== false ) {
            $response = $responses['address'];
        } elseif ( mb_strpos( $msg_lower, 'ارسال' ) !== false || mb_strpos( $msg_lower, 'پست' ) !== false ) {
            $response = $responses['shipping'];
        } elseif ( mb_strpos( $msg_lower, 'گارانتی' ) !== false ) {
            $response = $responses['warranty'];
        } elseif ( mb_strpos( $msg_lower, 'عمده' ) !== false || mb_strpos( $msg_lower, 'خرید عمده' ) !== false ) {
            $response = $responses['wholesale'];
        }

        wp_send_json_success( array(
            'response' => $response,
            'source'   => 'fallback',
        ) );
    }
}

/**
 * هندلر AJAX درخواست خرید عمده
 */
add_action( 'wp_ajax_fartak_wholesale_submit', 'fartak_handle_wholesale_submit' );
add_action( 'wp_ajax_nopriv_fartak_wholesale_submit', 'fartak_handle_wholesale_submit' );
function fartak_handle_wholesale_submit() {
    check_ajax_referer( 'fartak', 'nonce' );

    $company = isset( $_POST['company'] ) ? sanitize_text_field( $_POST['company'] ) : '';
    $person  = isset( $_POST['person'] ) ? sanitize_text_field( $_POST['person'] ) : '';
    $phone   = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
    $product = isset( $_POST['product'] ) ? sanitize_text_field( $_POST['product'] ) : '';
    $qty     = isset( $_POST['quantity'] ) ? sanitize_text_field( $_POST['quantity'] ) : '';

    if ( empty( $person ) || empty( $phone ) ) {
        wp_send_json_error( array( 'message' => 'نام و شماره تماس الزامی است' ) );
    }
    if ( ! preg_match( '/^09[0-9]{9}$/', $phone ) ) {
        wp_send_json_error( array( 'message' => 'شماره موبایل نامعتبر است' ) );
    }

    // ذخیره در پست تایپ استعلام
    $post_id = wp_insert_post( array(
        'post_type'    => 'fartak_inquiry',
        'post_title'   => 'خرید عمده: ' . $person . ' - ' . $company,
        'post_status'  => 'publish',
        'post_content' => 'شرکت: ' . $company . "\nتعداد: " . $qty . "\nمحصول: " . $product,
    ) );

    if ( $post_id ) {
        update_post_meta( $post_id, '_fartak_customer', $person );
        update_post_meta( $post_id, '_fartak_phone', $phone );
        update_post_meta( $post_id, '_fartak_product', $product );
        update_post_meta( $post_id, '_fartak_quantity', $qty );
        update_post_meta( $post_id, '_fartak_stage', 'new' );
        update_post_meta( $post_id, '_fartak_type', 'wholesale' );
    }

    // ایمیل به مدیر
    $admin_email = get_option( 'admin_email' );
    $subject = '[' . 'فروشگاه فرتاک' . '] درخواست خرید عمده جدید';
    $body = "درخواست خرید عمده جدید:\n\nشرکت: $company\nشخص: $person\nموبایل: $phone\nمحصول: $product\nتعداد: $qty";
    wp_mail( $admin_email, $subject, $body );

    // هوک برای پیامک و نوتیفیکیشن
    do_action( 'fartak_inquiry_submitted', array(
        'name'    => $person,
        'phone'   => $phone,
        'product' => $product,
        'type'    => 'wholesale',
    ) );

    // نوتیفیکیشن ادمین
    if ( function_exists( 'fartak_add_admin_notification' ) ) {
        fartak_add_admin_notification( 'wholesale', 'درخواست خرید عمده', $person . ' درخواست خرید عمده برای ' . $product . ' ارسال کرد.' );
    }

    wp_send_json_success( array( 'message' => 'درخواست شما ثبت شد. به‌زودی تماس می‌گیریم.' ) );
}
