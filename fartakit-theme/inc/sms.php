<?php
/**
 * Fartak — یکپارچه‌سازی پیامک و نوتیفیکیشن
 * پل ارتباطی با افزونه Fartak SMS Notifications
 *
 * @package Fartak
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * ارسال پیامک (در صورت نصب افزونه یا تنظیمات قالب)
 */
function fartak_send_sms( $to, $message ) {
    // اگر افزونه نصب است، از آن استفاده کن
    if ( function_exists( 'fartak_sms_send' ) ) {
        return fartak_sms_send( $to, $message );
    }

    // در غیر این صورت از تنظیمات قالب استفاده کن
    $provider = fartak_opt( 'sms_provider', 'none' );
    if ( $provider === 'none' ) return false;

    $api_key = fartak_opt( 'sms_api_key', '' );
    $sender  = fartak_opt( 'sms_sender', '' );

    if ( empty( $api_key ) ) return false;

    // نرمال‌سازی شماره
    $to = fartak_normalize_phone( $to );

    $result = false;

    switch ( $provider ) {
        case 'kavenegar':
            $result = fartak_sms_kavenegar( $api_key, $sender, $to, $message );
            break;
        case 'melipayamak':
            $result = fartak_sms_melipayamak( $api_key, $sender, $to, $message );
            break;
        case 'farapayamak':
            $result = fartak_sms_farapayamak( $api_key, $sender, $to, $message );
            break;
        case 'smsir':
            $result = fartak_sms_smsir( $api_key, $sender, $to, $message );
            break;
        case 'payamresan':
            $result = fartak_sms_payamresan( $api_key, $sender, $to, $message );
            break;
    }

    do_action( 'fartak_sms_sent', $to, $message, $result ? 'success' : 'failed' );
    return $result;
}

/**
 * نرمال‌سازی شماره تلفن
 */
function fartak_normalize_phone( $phone ) {
    $phone = preg_replace( '/\s+/', '', $phone );
    $phone = preg_replace( '/[^0-9]/', '', $phone );
    if ( substr( $phone, 0, 2 ) === '98' ) {
        $phone = '0' . substr( $phone, 2 );
    } elseif ( substr( $phone, 0, 3 ) === '098' ) {
        $phone = '0' . substr( $phone, 3 );
    } elseif ( substr( $phone, 0, 1 ) === '9' && strlen( $phone ) === 10 ) {
        $phone = '0' . $phone;
    }
    return $phone;
}

/**
 * ارائه‌دهنده کاوه‌نگار
 */
function fartak_sms_kavenegar( $api_key, $sender, $to, $message ) {
    $url = "https://api.kavenegar.com/v1/$api_key/sms/send.json";
    $response = wp_remote_post( $url, array(
        'body' => array( 'receptor' => $to, 'sender' => $sender, 'message' => $message ),
        'timeout' => 15,
    ) );
    if ( is_wp_error( $response ) ) return false;
    $body = json_decode( wp_remote_retrieve_body( $response ), true );
    return isset( $body['return']['status'] ) && $body['return']['status'] == 200;
}

function fartak_sms_melipayamak( $api_key, $sender, $to, $message ) {
    $parts = explode( ',', $api_key . ',,' );
    $response = wp_remote_post( 'https://rest.payamak-panel.com/api/SendSMS/SendSMS', array(
        'body' => json_encode( array(
            'username' => isset( $parts[0] ) ? $parts[0] : '',
            'password' => isset( $parts[1] ) ? $parts[1] : '',
            'from' => $sender, 'to' => $to, 'text' => $message, 'isFlash' => false,
        ) ),
        'headers' => array( 'Content-Type' => 'application/json' ),
        'timeout' => 15,
    ) );
    if ( is_wp_error( $response ) ) return false;
    $body = json_decode( wp_remote_retrieve_body( $response ), true );
    return isset( $body['Value'] ) && $body['Value'] > 0;
}

function fartak_sms_farapayamak( $api_key, $sender, $to, $message ) {
    $parts = explode( ',', $api_key . ',,' );
    $response = wp_remote_post( 'https://rest.ippanel.com/v1/messages/patterns/send/plain', array(
        'body' => json_encode( array( 'from' => $sender, 'to' => array( $to ), 'message' => $message ) ),
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode( ( isset($parts[0]) ? $parts[0] : '' ) . ':' . ( isset($parts[1]) ? $parts[1] : '' ) ),
        ),
        'timeout' => 15,
    ) );
    return ! is_wp_error( $response );
}

function fartak_sms_smsir( $api_key, $sender, $to, $message ) {
    $response = wp_remote_post( 'https://api.sms.ir/v1/send/like', array(
        'body' => json_encode( array( 'Messages' => array( $message ), 'MobileNumbers' => array( $to ), 'LineNumber' => $sender ) ),
        'headers' => array( 'Content-Type' => 'application/json', 'X-API-KEY' => $api_key ),
        'timeout' => 15,
    ) );
    if ( is_wp_error( $response ) ) return false;
    $body = json_decode( wp_remote_retrieve_body( $response ), true );
    return isset( $body['IsSuccessful'] ) && $body['IsSuccessful'];
}

function fartak_sms_payamresan( $api_key, $sender, $to, $message ) {
    $parts = explode( ',', $api_key . ',,' );
    $response = wp_remote_post( 'https://api.payamresan.com/v1/send/sms', array(
        'body' => json_encode( array(
            'Username' => isset($parts[0]) ? $parts[0] : '',
            'Password' => isset($parts[1]) ? $parts[1] : '',
            'From' => $sender, 'To' => $to, 'Text' => $message,
        ) ),
        'headers' => array( 'Content-Type' => 'application/json' ),
        'timeout' => 15,
    ) );
    return ! is_wp_error( $response );
}

/**
 * پل اکشن: ماژول هشدار قیمت روی اکشن fartak_sms_send پیام می‌فرستد
 * (قبلاً هیچ listenerی روی این اکشن نبود و پیامک هشدار قیمت هرگز ارسال نمی‌شد)
 */
add_action( 'fartak_sms_send', function( $to, $message ) {
    if ( $to && $message ) {
        fartak_send_sms( $to, $message );
    }
}, 10, 2 );

/**
 * ارسال نوتیفیکیشن به مدیر هنگام استعلام قیمت
 */
add_action( 'fartak_inquiry_submitted', 'fartak_notify_admin_inquiry' );
function fartak_notify_admin_inquiry( $data ) {
    if ( fartak_opt( 'sms_notify_quote', '0' ) !== '1' ) return;

    $admin_phone = fartak_opt( 'sms_admin_phone', '' );
    if ( empty( $admin_phone ) ) return;

    $site_name = 'فروشگاه فرتاک';
    $message = "[$site_name] استعلام قیمت جدید از: {$data['name']} - {$data['phone']}";
    if ( ! empty( $data['product'] ) ) {
        $message .= " محصول: {$data['product']}";
    }

    fartak_send_sms( $admin_phone, $message );
}

/**
 * نوتیفیکیشن مرورگر برای مدیر (ذخیره در ترانزینت)
 */
function fartak_add_admin_notification( $type, $title, $message, $url = '' ) {
    $notifications = get_transient( 'fartak_admin_notifications' );
    if ( ! is_array( $notifications ) ) $notifications = array();
    array_unshift( $notifications, array(
        'type'    => $type,
        'title'   => $title,
        'message' => $message,
        'time'    => current_time( 'H:i' ),
        'url'     => $url ?: admin_url( 'edit.php?post_type=fartak_inquiry' ),
    ) );
    $notifications = array_slice( $notifications, 0, 10 );
    set_transient( 'fartak_admin_notifications', $notifications, DAY_IN_SECONDS );
}
