<?php
/**
 * Fartak — تنظیمات سفارشی‌سازی (Customizer)
 *
 * @package Fartak
 */

defined( 'ABSPATH' ) || exit;

function fartak_def( $key ) {
        $defs = array(
                'phone'    => '017-32000180',
                'address'  => 'گلستان، گرگان، خیابان ولیعصر، عدالت هفتم، مجتمع مرسل، واحد یک، مجموعه پویا شبکه فرتاک',
                'hours'    => 'شنبه تا پنجشنبه ۹ تا ۲۱',
                'email'    => 'info@fartakit.com',
                'telegram' => 'https://t.me/fartakit',
                'instagram'=> 'https://instagram.com/fartak.it',
                'brands'   => "ASUS\nMSI\nGIGABYTE\nLOGITECH\nRAZER\nREDRAGON\nSAMSUNG\nCORSAIR\nKINGSTON\nHYPERX\nINTEL\nAMD\nNVIDIA\nLENOVO\nCOOLER MASTER\nNZXT",
        );
        return isset( $defs[ $key ] ) ? $defs[ $key ] : '';
}

function fartak_hero_defaults( $i ) {
        $slides = array(
                1 => array(
                        'eyebrow' => 'مونتاژ حرفه‌ای در فرتاک',
                        'title'   => "قدرت خالص،\nداخل یک بدنه.",
                        'sub'     => 'کیس‌های گیمینگ شیشه‌ای با نورپردازی ARGB و اسمبل رایگان توسط مهندسان فرتاک',
                        'img'     => FARTAK_URI . '/assets/images/hero-case.png',
                        'cta_l'   => 'خرید کیس گیمینگ',
                        'cta_u'   => '',
                        'stat'    => 'اسمبل رایگان',
                ),
                2 => array(
                        'eyebrow' => 'نسل جدید رسید',
                        'title'   => "گرافیک‌های پرچمدار\nبا گارانتی معتبر",
                        'sub'     => 'RTX و Radeon با قیمت لحظه‌ای بازار، تست بنچمارک قبل از تحویل',
                        'img'     => FARTAK_URI . '/assets/images/hero-gpu.png',
                        'cta_l'   => 'مشاهده کارت‌های گرافیک',
                        'cta_u'   => '',
                        'stat'    => 'گارانتی شرکتی',
                ),
                3 => array(
                        'eyebrow' => 'ستاپ رؤیایی تو',
                        'title'   => "سیستمت را\nخودت طراحی کن",
                        'sub'     => 'با ابزار اسمبل آنلاین، قطعات سازگار را مرحله‌به‌مرحله انتخاب کن و قیمت نهایی را همینجا ببین',
                        'img'     => FARTAK_URI . '/assets/images/hero-case.png',
                        'cta_l'   => 'شروع اسمبل آنلاین',
                        'cta_u'   => '',
                        'stat'    => 'سازگاری هوشمند',
                ),
        );
        return isset( $slides[ $i ] ) ? $slides[ $i ] : $slides[1];
}

add_action( 'customize_register', 'fartak_customizer' );
function fartak_customizer( $wp ) {
        $wp->add_panel( 'fartak_panel', array( 'title' => __( 'تنظیمات قالب فرتاک', 'fartak' ), 'priority' => 10 ) );

        /* اطلاعات تماس */
        $wp->add_section( 'fartak_contact', array( 'title' => __( 'اطلاعات تماس و فوتر', 'fartak' ), 'panel' => 'fartak_panel' ) );
        $contact = array(
                'phone'    => __( 'تلفن فروشگاه', 'fartak' ),
                'address'  => __( 'نشانی فروشگاه', 'fartak' ),
                'hours'    => __( 'ساعات کاری', 'fartak' ),
                'email'    => __( 'ایمیل فروشگاه', 'fartak' ),
                'telegram' => __( 'لینک تلگرام', 'fartak' ),
                'instagram'=> __( 'لینک اینستاگرام', 'fartak' ),
        );
        foreach ( $contact as $id => $label ) {
                $wp->add_setting( "fartak_{$id}", array( 'default' => fartak_def( $id ), 'sanitize_callback' => 'sanitize_text_field' ) );
                $wp->add_control( "fartak_{$id}", array( 'label' => $label, 'section' => 'fartak_contact', 'type' => 'text' ) );
        }
        $wp->add_setting( 'fartak_brands', array( 'default' => fartak_def( 'brands' ), 'sanitize_callback' => 'sanitize_textarea_field' ) );
        $wp->add_control( 'fartak_brands', array( 'label' => __( 'برندها (هر خط یک برند)', 'fartak' ), 'section' => 'fartak_contact', 'type' => 'textarea' ) );

        /* اسلایدهای هیرو */
        $wp->add_section( 'fartak_hero', array( 'title' => __( 'اسلایدر صفحه اصلی', 'fartak' ), 'panel' => 'fartak_panel' ) );
        for ( $i = 1; $i <= 3; $i++ ) {
                $d = fartak_hero_defaults( $i );
                $fields = array(
                        'eyebrow' => array( __( 'برچسب کوچک', 'fartak' ), 'text' ),
                        'title'   => array( __( 'تیتر', 'fartak' ), 'textarea' ),
                        'sub'     => array( __( 'زیرتیتر', 'fartak' ), 'textarea' ),
                        'img'     => array( __( 'تصویر', 'fartak' ), 'image' ),
                        'cta_l'   => array( __( 'متن دکمه', 'fartak' ), 'text' ),
                        'cta_u'   => array( __( 'لینک دکمه', 'fartak' ), 'url' ),
                        'stat'    => array( __( 'برچسب کنار دکمه', 'fartak' ), 'text' ),
                );
                foreach ( $fields as $f => $cfg ) {
                        $id = "fartak_hero_{$i}_{$f}";
                        $wp->add_setting( $id, array( 'default' => $d[ $f ], 'sanitize_callback' => ( 'image' === $cfg[1] || 'url' === $cfg[1] ) ? 'esc_url_raw' : 'sanitize_textarea_field' ) );
                        if ( 'image' === $cfg[1] ) {
                                $wp->add_control( new WP_Customize_Image_Control( $wp, $id, array( 'label' => sprintf( __( 'اسلاید %d — %s', 'fartak' ), $i, $cfg[0] ), 'section' => 'fartak_hero' ) ) );
                        } else {
                                $wp->add_control( $id, array( 'label' => sprintf( __( 'اسلاید %d — %s', 'fartak' ), $i, $cfg[0] ), 'section' => 'fartak_hero', 'type' => $cfg[1] === 'textarea' ? 'textarea' : 'text' ) );
                        }
                }
        }
}

/* گرفتن مود با پیش‌فرض */
function fartak_mod( $id, $default = '' ) {
        return get_theme_mod( $id, $default );
}
