<?php
/**
 * Fartak — یکپارچه‌سازی ووکامرس
 *
 * @package Fartak
 */

defined( 'ABSPATH' ) || exit;

/* حذف wrapper پیش‌فرض؛ قالب woocommerce.php خودش wrapper می‌سازد */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

/* تعداد محصول در هر ردیف و صفحه - در انتهای فایل به‌صورت پویا از پنل تنظیم می‌شود */

/* محصولات مرتبط */
add_filter( 'woocommerce_output_related_products_args', function ( $args ) {
        $args['posts_per_page'] = 8;
        $args['columns']        = 4;
        return $args;
} );

/* حذف فلش تخفیف پیش‌فرض روی حلقه (کارت خودمان بج دارد) */
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );

/* ناموجود روی حلقه */
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash' );

/* عنوان صفحه فروشگاه را خودمان مدیریت می‌کنیم */
add_filter( 'woocommerce_show_page_title', '__return_false' );

/* چیپس‌های دسته‌بندی بالای لیست فروشگاه */
add_action( 'woocommerce_before_shop_loop', 'fartak_shop_cat_chips', 15 );
function fartak_shop_cat_chips() {
        if ( is_admin() ) {
                return;
        }
        $cats = fartak_top_cats( 10 );
        if ( ! $cats ) {
                return;
        }
        $current = 0;
        if ( is_product_category() ) {
                $current = get_queried_object_id();
        }
        echo '<div class="cat-chips">';
        $all_url = wc_get_page_permalink( 'shop' );
        echo '<a class="chip ' . esc_attr( ! $current ? 'on' : '' ) . '" href="' . esc_url( $all_url ) . '">' . esc_html__( 'همه', 'fartak' ) . '</a>';
        foreach ( $cats as $cat ) {
                printf(
                        '<a class="chip %s" href="%s">%s</a>',
                        esc_attr( $current === $cat->term_id ? 'on' : '' ),
                        esc_url( get_term_link( $cat ) ),
                        esc_html( $cat->name )
                );
        }
        echo '</div>';
}

/* نوار پیشرفت موجودی — طبق درخواست از سایت حذف شد (نمایش موجودی انبار ممنوع) */

/* WooCommerce مالک منطق موجودی است؛ قالب فقط تعداد را در UI کنترل می‌کند و عدد موجودی را نمایش نمی‌دهد. */

/* متاباکس گارانتی محصول (دستی در افزودن/ویرایش محصول) */
add_action( 'add_meta_boxes', 'fartak_warranty_box' );
function fartak_warranty_box() {
    $screens = class_exists( 'WooCommerce' ) ? array( 'product' ) : array( 'post', 'page' );
    add_meta_box( 'fartak_warranty', __( 'گارانتی محصول', 'fartak' ), 'fartak_warranty_box_html', $screens, 'side', 'default' );
}
function fartak_warranty_box_html( $post ) {
    wp_nonce_field( 'fartak_warranty_save', 'fartak_warranty_nonce' );
    $val = get_post_meta( $post->ID, '_fartak_warranty', true );
    // اگر متا خالی بود، از attribute گارانتی محصول بخوان (سازگاری با داده‌های قالب قبلی)
    $attr_val = '';
    if ( class_exists( 'WooCommerce' ) && taxonomy_exists( 'pa_garanti' ) ) {
        $terms = wc_get_product_terms( $post->ID, 'pa_garanti', array( 'fields' => 'names' ) );
        if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
            $attr_val = implode( ', ', $terms );
        }
    }
    $current = '' !== $val ? $val : $attr_val;
    echo '<label for="fartak_warranty_input">' . esc_html__( 'متن گارانتی (مثلاً: گارانتی اصلی تخت جمشید):', 'fartak' ) . '</label>';
    echo '<input type="text" id="fartak_warranty_input" name="fartak_warranty" value="' . esc_attr( $current ) . '" class="widefat" style="margin-top:6px">';
    echo '<p class="description">' . esc_html__( 'اگر خالی باشد، «گارانتی اصالت کالا» نمایش داده می‌شود.', 'fartak' ) . '</p>';
}
add_action( 'save_post_product', 'fartak_warranty_save' );
function fartak_warranty_save( $post_id ) {
    if ( ! isset( $_POST['fartak_warranty_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['fartak_warranty_nonce'] ), 'fartak_warranty_save' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    if ( isset( $_POST['fartak_warranty'] ) ) {
        update_post_meta( $post_id, '_fartak_warranty', sanitize_text_field( wp_unslash( $_POST['fartak_warranty'] ) ) );
    }
}

/* گرفتن متن گارانتی محصول — متا، سپس attribute ووکامرس (pa_garanti) */
function fartak_get_warranty( $product_id ) {
    $val = get_post_meta( $product_id, '_fartak_warranty', true );
    if ( '' !== $val ) {
        return $val;
    }
    if ( class_exists( 'WooCommerce' ) && taxonomy_exists( 'pa_garanti' ) ) {
        $terms = wc_get_product_terms( $product_id, 'pa_garanti', array( 'fields' => 'names' ) );
        if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
            return implode( ', ', $terms );
        }
    }
    return __( 'گارانتی اصالت کالا', 'fartak' );
}

/* متاباکس: «تماس بگیرید» فقط برای همین محصول (به‌جای قیمت و دکمه خرید) */
add_action( 'add_meta_boxes', 'fartak_call_price_box' );
function fartak_call_price_box() {
    add_meta_box( 'fartak_call_price', __( 'نحوه فروش این محصول', 'fartak' ), 'fartak_call_price_box_html', 'product', 'side', 'default' );
}
function fartak_call_price_box_html( $post ) {
    wp_nonce_field( 'fartak_call_price_save', 'fartak_call_price_nonce' );
    $checked = '1' === get_post_meta( $post->ID, '_fartak_call_price', true );
    echo '<label style="display:flex;gap:8px;align-items:flex-start;cursor:pointer">';
    echo '<input type="checkbox" name="fartak_call_price" value="1" ' . checked( $checked, true, false ) . ' style="margin-top:3px">';
    echo '<span>' . esc_html__( 'نمایش دکمه «تماس بگیرید» به‌جای قیمت و افزودن به سبد خرید', 'fartak' ) . '</span>';
    echo '</label>';
    echo '<p class="description">' . esc_html__( 'فقط روی همین محصول اعمال می‌شود. بقیه محصولات عادی قیمت و دکمه خرید خودشان را دارند.', 'fartak' ) . '</p>';
}
add_action( 'save_post_product', 'fartak_call_price_save' );
function fartak_call_price_save( $post_id ) {
    if ( ! isset( $_POST['fartak_call_price_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['fartak_call_price_nonce'] ), 'fartak_call_price_save' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    update_post_meta( $post_id, '_fartak_call_price', isset( $_POST['fartak_call_price'] ) ? '1' : '0' );
}

/* محصولات بدون قیمت واقعی: دکمه «تماس بگیرید» با شماره پشتیبانی (بدون هیچ استعلام هوش مصنوعی) */
add_action( 'woocommerce_single_product_summary', 'fartak_single_inquiry', 32 );
function fartak_single_inquiry() {
        global $product;
        if ( ! $product ) {
                return;
        }
        $is_call = fartak_product_is_call( $product->get_id() );
        if ( $is_call || fartak_has_real_price( $product ) ) {
                return; /* حالت سراسری/محصولی خودش باکس تماس دارد، یا قیمت واقعی موجود است */
        }
        $phone = preg_replace( '/\D/', '', (string) get_option( 'fartak_support_phone', '01732000180' ) );
        echo '<div class="fartak-call-box" style="margin-top:18px">';
        echo '<div class="fartak-call-ic"><span class="ping"></span>' . fartak_icon( 'phone' ) . '</div>';
        echo '<b class="fartak-call-txt">' . esc_html__( 'تماس بگیرید', 'fartak' ) . '</b>';
        echo '<small>' . esc_html__( 'برای اطلاع از قیمت روز و موجودی', 'fartak' ) . '</small>';
        printf(
                '<a class="btn-copper btn-lg w-full fartak-call-btn" href="tel:%s">%s %s <span dir="ltr"><bdi dir="ltr">%s</bdi></span></a>',
                esc_attr( $phone ),
                fartak_icon( 'phone' ),
                esc_html__( 'تماس بگیرید', 'fartak' ),
                esc_html( fartak_fa( fartak_phone_display( $phone ) ) )
        );
        echo '</div>';
}

/* کارت گارانتی مطابق فرمت سایت فعلی + نشان‌های اعتماد زیر سبد خرید صفحه تکی */
add_action( 'woocommerce_single_product_summary', 'fartak_single_trust', 38 );
function fartak_single_trust() {
        global $product;
        if ( ! $product ) {
                return;
        }
        $warranty = fartak_get_warranty( $product->get_id() );
        echo '<div class="fartak-single-warranty-wrap">';
        echo '<div class="fartak-warranty-card">';
        echo '<span class="fartak-warranty-icon">' . fartak_icon( 'shield' ) . '</span>';
        echo '<span class="fartak-warranty-compact-label">' . esc_html__( 'گارانتی:', 'fartak' ) . '</span>';
        echo '<strong class="fartak-warranty-compact-text">' . esc_html( $warranty ) . '</strong>';
        echo '</div>';
        echo '</div>';
        echo '<div class="trust-mini">';
        echo '<div class="card-compact">' . fartak_icon( 'truck' ) . '<span>' . esc_html__( 'ارسال سراسری', 'fartak' ) . '</span></div>';
        echo '<div class="card-compact">' . fartak_icon( 'check' ) . '<span>' . esc_html__( 'تست سلامت', 'fartak' ) . '</span></div>';
        echo '</div>';
}

/* آیکون چیپ مشخصات بر اساس کلید ویژگی */
function fartak_spec_icon( $label ) {
    $l = mb_strtolower( (string) $label );
    $map = array(
        'cpu' => 'cpu', 'پردازنده' => 'cpu', 'پروسسور' => 'cpu',
        'gpu' => 'circuit', 'گرافیک' => 'circuit', 'vga' => 'circuit',
        'ram' => 'ram', 'رم' => 'ram', 'حافظه' => 'hdd', 'ssd' => 'hdd', 'hdd' => 'hdd', 'دیسک' => 'hdd',
        'مانیتور' => 'monitor', 'نمایشگر' => 'monitor', 'پنل' => 'monitor', 'رزولوشن' => 'monitor',
        'پورت' => 'plug', 'usb' => 'plug', 'تغذیه' => 'plug', 'منبع' => 'plug', 'psu' => 'plug', 'وات' => 'plug',
        'مادربرد' => 'stack', 'motherboard' => 'stack',
        'کیس' => 'box', 'case' => 'box', 'ابعاد' => 'box', 'وزن' => 'box',
        'گارانتی' => 'shield', 'ضمانت' => 'shield',
        'لپ' => 'laptop', 'laptop' => 'laptop',
        'کیبورد' => 'keyboard', 'صفحه‌کلید' => 'keyboard',
        'ماوس' => 'mouse', 'mouse' => 'mouse',
        'خنک' => 'fan', 'فن' => 'fan', 'cooling' => 'fan',
        'سریع' => 'zap', 'سرعت' => 'zap', 'فرکانس' => 'zap', 'نرخ' => 'zap', 'هرتز' => 'zap',
        'گیم' => 'dices', 'بازی' => 'dices',
        'بلندگو' => 'headphones', 'صدا' => 'headphones', 'audio' => 'headphones',
        'لینوکس' => 'check', 'ویندوز' => 'check', 'سیستم‌عامل' => 'check',
    );
    foreach ( $map as $needle => $icon ) {
        if ( false !== mb_strpos( $l, $needle ) ) {
            return $icon;
        }
    }
    return 'zap';
}

/* بخش «مشخصات برجسته» — گرید کوچک چیپ‌ها از attributeهای محصول (هویت اختصاصی قالب) */
add_action( 'woocommerce_single_product_summary', 'fartak_single_specs', 39 );
function fartak_single_specs() {
        global $product;
        if ( ! $product || ! function_exists( 'wc_attribute_label' ) ) {
                return;
        }
        $chips = array();
        foreach ( $product->get_attributes() as $attr ) {
                if ( ! $attr || ! $attr->get_visible() ) {
                        continue;
                }
                $label = wc_attribute_label( $attr->get_name(), $product );
                if ( $attr->is_taxonomy() ) {
                        $vals = wc_get_product_terms( $product->get_id(), $attr->get_name(), array( 'fields' => 'names' ) );
                        $value = is_array( $vals ) ? implode( '، ', $vals ) : '';
                } else {
                        $opts  = $attr->get_options();
                        $value = is_array( $opts ) ? implode( '، ', $opts ) : (string) $opts;
                }
                if ( '' === trim( (string) $value ) || '' === trim( (string) $label ) ) {
                        continue;
                }
                $chips[] = array( $label, $value );
                if ( count( $chips ) >= 8 ) {
                        break;
                }
        }
        if ( empty( $chips ) ) {
                return;
        }
        echo '<div class="ft-specs">';
        echo '<h3 class="ft-specs-title">' . fartak_icon( 'cpu' ) . '<span>' . esc_html__( 'مشخصات برجسته', 'fartak' ) . '</span></h3>';
        echo '<div class="ft-specs-grid">';
        foreach ( $chips as $c ) {
                echo '<div class="ft-spec">';
                echo '<span class="ft-spec-ic">' . fartak_icon( fartak_spec_icon( $c[0] ) ) . '</span>';
                echo '<span class="ft-spec-body"><small>' . esc_html( $c[0] ) . '</small><b>' . esc_html( $c[1] ) . '</b></span>';
                echo '</div>';
        }
        echo '</div>';
        echo '</div>';
}

/* فیلدهای تسویه‌حساب فشرده — ترتیب طبق درخواست: نام، نام‌خانوادگی، استان، شهر، خیابان، کدپستی، موبایل، ایمیل(اختیاری) */
add_filter( 'woocommerce_checkout_fields', 'fartak_checkout_fields' );
function fartak_checkout_fields( $fields ) {
        unset( $fields['billing']['billing_company'] );
        unset( $fields['billing']['billing_address_2'] );
        unset( $fields['shipping']['shipping_company'] );
        unset( $fields['shipping']['shipping_address_2'] );
        unset( $fields['shipping']['shipping_state'] );
        unset( $fields['shipping']['shipping_postcode'] );

        /* ایمیل اختیاری — آخر از همه */
        if ( isset( $fields['billing']['billing_email'] ) ) {
                $fields['billing']['billing_email']['required'] = false;
                $fields['billing']['billing_email']['label']    = __( 'ایمیل (اختیاری)', 'fartak' );
        }

        /* استان: تایپ آزاد (نه dropdown)، بالای شهر */
        if ( isset( $fields['billing']['billing_state'] ) ) {
                $fields['billing']['billing_state']['type']     = 'text';
                $fields['billing']['billing_state']['required'] = true;
                $fields['billing']['billing_state']['label']    = __( 'استان', 'fartak' );
        }

        /* ترتیب دقیق: استان → شهر → خیابان → کد پستی → موبایل → ایمیل */
        $prio = array(
                'billing_first_name' => array( 10, __( 'نام', 'fartak' ), 'form-row-first' ),
                'billing_last_name'  => array( 20, __( 'نام خانوادگی', 'fartak' ), 'form-row-last' ),
                'billing_country'    => array( 25, '', 'form-row-wide' ),
                'billing_state'      => array( 30, '', 'form-row-wide' ),
                'billing_city'       => array( 40, __( 'شهر', 'fartak' ), 'form-row-wide' ),
                'billing_address_1'  => array( 50, __( 'خیابان / آدرس پستی', 'fartak' ), 'form-row-wide' ),
                'billing_postcode'   => array( 60, __( 'کد پستی', 'fartak' ), 'form-row-wide' ),
                'billing_phone'      => array( 70, __( 'شماره موبایل', 'fartak' ), 'form-row-wide' ),
                'billing_email'      => array( 80, '', 'form-row-wide' ),
        );
        foreach ( $prio as $key => $cfg ) {
                if ( isset( $fields['billing'][ $key ] ) ) {
                        $fields['billing'][ $key ]['priority'] = $cfg[0];
                        $fields['billing'][ $key ]['class']    = $cfg[2];
                        if ( '' !== $cfg[1] ) {
                                $fields['billing'][ $key ]['label'] = $cfg[1];
                        }
                }
        }
        if ( isset( $fields['billing']['billing_email'] ) ) {
                $fields['billing']['billing_email']['label'] = __( 'ایمیل (اختیاری)', 'fartak' );
        }
        return $fields;
}

/* کشوی تعداد سبد در هدر همگام می‌ماند (نوتیف پیش‌فرض آجاکس خودمان) */
add_filter( 'woocommerce_get_image_size_fartak_card', function () { return array( 'width' => 480, 'height' => 480, 'crop' => 1 ); } );

/* ============================================================
   واحد پول: قالب کاملاً از تنظیمات ووکامرس پیروی می‌کند.
   (فیلترهای قدیمی تبدیل ریال/تومان حذف شدند)
   ============================================================ */

/**
 * نرمال‌سازی جداکننده هزارگان قیمت.
 * اگر جداکننده تنظیم‌شده در ووکامرس فاصله یا «٬» (U+066C) باشد که در فونت سایت
 * به‌صورت فاصله رندر می‌شود، به کامای استاندارد تبدیل می‌شود.
 * سایر مقادیر (نماد دلخواه مدیر) دست‌نخورده می‌مانند.
 */
add_filter( 'wc_get_price_thousand_separator', function( $sep ) {
    $trim = trim( (string) $sep );
    if ( $trim === '' || $trim === '٬' || $trim === '،' ) {
        return ',';
    }
    return $sep;
} );

/* ============================================================
   حالت استعلام: دکمه «تماس بگیرید» به‌جای افزودن به سبد
   (موقع به‌روزرسانی قیمت‌ها از پنل فعال می‌شود)
   ============================================================ */

/* کلاس حالت استعلام روی body (مثل سایت فعلی) — سراسری یا per-product یا بدون قیمت واقعی */
add_filter( 'body_class', function( $classes ) {
    if ( get_option( 'fartak_inquiry_mode', '0' ) === '1' ) {
        $classes[] = 'fartak-call-for-price-mode';
    }
    if ( function_exists( 'is_product' ) && is_product() ) {
        $pid = get_queried_object_id();
        $prod = function_exists( 'wc_get_product' ) ? wc_get_product( $pid ) : null;
        if ( fartak_product_is_call( $pid ) || ( $prod && ! fartak_has_real_price( $prod ) ) ) {
            $classes[] = 'ft-call-product';
        }
    }
    return $classes;
} );

/* دکمه ساده صفحه تکی محصول هم در حالت استعلام تبدیل به تماس می‌شود (سراسری یا per-product) */
add_filter( 'woocommerce_is_purchasable', function( $purchasable, $product ) {
    if ( $product && ( fartak_product_is_call( $product->get_id() ) || ! fartak_has_real_price( $product ) ) ) {
        return false;
    }
    return $purchasable;
}, 10, 2 );

/* در حالت استعلام، دکمه خرید صفحه تکی محصول تبدیل به تماس می‌شود (فرمت سایت فعلی) */
add_action( 'woocommerce_single_product_summary', 'fartak_single_inquiry_mode', 35 );
function fartak_single_inquiry_mode() {
    global $product;
    if ( ! $product || ! fartak_product_is_call( $product->get_id() ) ) {
        return;
    }
    $phone   = get_option( 'fartak_support_phone', '01732000180' );
    $display = fartak_fa( fartak_phone_display( $phone ) );
    echo '<div class="fartak-call-box">';
    echo '<div class="fartak-call-ic"><span class="ping"></span>' . fartak_icon( 'phone' ) . '</div>';
    echo '<b class="fartak-call-txt">' . esc_html__( 'تماس بگیرید', 'fartak' ) . '</b>';
    echo '<small>' . esc_html__( 'برای اطلاع از قیمت روز و موجودی', 'fartak' ) . '</small>';
    echo '</div>';
    echo '<a class="btn-copper btn-lg w-full fartak-call-btn" href="tel:' . esc_attr( $phone ) . '">' . fartak_icon( 'phone' ) . ' ' . esc_html__( 'تماس بگیرید', 'fartak' ) . ' <span dir="ltr"><bdi dir="ltr">' . esc_html( $display ) . '</bdi></span></a>';
}

/* ============================================================
   صفحه پرداخت: برچسب‌ها و حذف جمع حمل‌ونقل
   ============================================================ */

/* «حمل و نقل» → «پس کرایه» */
add_filter( 'woocommerce_order_shipping_to_display', function( $shipping, $order ) {
    $shipping = str_replace( 'حمل و نقل', 'پس کرایه', $shipping );
    $shipping = str_replace( 'حمل‌ونقل', 'پس کرایه', $shipping );
    return $shipping;
}, 10, 2 );

add_filter( 'woocommerce_cart_shipping_method_full_label', function( $label, $method ) {
    return str_replace( array( 'حمل و نقل', 'حمل‌ونقل' ), 'پس کرایه', $label );
}, 10, 2 );

add_filter( 'woocommerce_shipping_rate_label', function( $label ) {
    return str_replace( array( 'حمل و نقل', 'حمل‌ونقل' ), 'پس کرایه', $label );
} );

/* هزینه و منطق ارسال کاملاً در اختیار WooCommerce و افزونه‌های ارسال است. */

/**
 * AJAX افزودن به سبد (در inc/ajax.php مدیریت می‌شود)
 * برای جلوگیری از تداخل، اینجا ثبت نمی‌شود.
 */

/**
 * فرگمنت سبد
 */
add_filter( 'woocommerce_add_to_cart_fragments', function( $fragments ) {
    $count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
    $fragments['[data-cart-count]'] = '<span class="count-badge' . ( $count > 0 ? '' : ' ft-hidden' ) . '" data-cart-count>' . esc_html( fartak_fa_num( $count ) ) . '</span>';
    return $fragments;
} );

/**
 * تعداد ستون‌های فروشگاه
 */
add_filter( 'loop_shop_columns', function() {
    return intval( get_option( 'fartak_woo_columns', 4 ) );
}, 99 );

add_filter( 'loop_shop_per_page', function() {
    return intval( get_option( 'fartak_woo_per_page', 12 ) );
}, 99 );

/**
 * تغییر متن دکمه افزودن به سبد
 */
add_filter( 'woocommerce_product_add_to_cart_text', function( $text ) {
    return 'افزودن به سبد';
} );
add_filter( 'woocommerce_product_single_add_to_cart_text', function( $text ) {
    return 'افزودن به سبد خرید';
} );

/* ============================================================
   سازگاری با درگاه‌های پرداخت ایرانی - استایل موبایل
   ============================================================ */

// استایل درگاه‌های پرداخت در موبایل
add_action( 'wp_head', function() {
    echo '<style>
    @media (max-width: 768px) {
        .woocommerce-checkout #payment ul.payment_methods li{font-size:13px;padding:8px}
        .woocommerce-checkout #payment ul.payment_methods li label{font-size:13px}
        .woocommerce-checkout #payment .place-order{padding:12px}
        .woocommerce-checkout #payment .place-order button{width:100%;font-size:14px;padding:12px}
    }
    </style>';
} );

// رفع تداخل با افزونه‌های ووکامرس (فقط یک بار)
if ( ! has_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash' ) ) {
    // قبلاً حذف شده، نیازی نیست دوباره
}
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );

/* ============================================================
   سازگاری با افزونه‌های ایرانی (همیار وردپرس و...)
   ============================================================ */

// قالب دیگر فیلتر قیمت/واحد پول ثبت نمی‌کند؛ افزونه‌ها بدون تداخل کار می‌کنند.

// سازگاری با افزونه‌های کش (WP Rocket, W3 Total Cache, LiteSpeed)
add_action( 'init', function() {
    // اگه افزونه کش نصب است، فرگمنت‌های سبد رو فعال نگه دار
    if ( defined( 'WP_ROCKET_VERSION' ) || defined( 'W3TC' ) || defined( 'LSCWP_V' ) ) {
        add_filter( 'woocommerce_add_to_cart_fragments', function( $fragments ) {
            if ( ! WC()->cart ) return $fragments;
            $count = WC()->cart->get_cart_contents_count();
            $fragments['[data-cart-count]'] = '<span class="count-badge' . ( $count > 0 ? '' : ' ft-hidden' ) . '" data-cart-count>' . esc_html( fartak_fa_num( $count ) ) . '</span>';
            return $fragments;
        } );
    }
}, 20 );

// سازگاری با افزونه‌های فرم‌ساز (Contact Form 7, WPForms, Gravity Forms)

// سازگاری با Yoast SEO و Rank Math
add_theme_support( 'title-tag' );

// سازگاری با Elementor (اگه نصب باشه)
if ( defined( 'ELEMENTOR_VERSION' ) ) {
    add_theme_support( 'elementor' );
}

// سازگاری با ووکامرس فارسی و افزونه‌های واحد پول: قالب دخالتی در قیمت‌ها ندارد.

/* فیلتر لینک «مشاهده همه» تخفیف‌ها: shop?onsale=1 → فقط محصولات تخفیف‌دار */
add_action( 'pre_get_posts', function( $q ) {
    if ( is_admin() || ! $q->is_main_query() ) {
        return;
    }
    $is_archive = $q->is_post_type_archive( 'product' ) || $q->is_tax( 'product_cat' ) || $q->is_tax( 'product_tag' );
    if ( ! $is_archive || empty( $_GET['onsale'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return;
    }
    $meta = $q->get( 'meta_query' );
    $meta = is_array( $meta ) ? $meta : array();
    $meta[] = array(
        'key'     => '_sale_price',
        'value'   => 0,
        'compare' => '>',
        'type'    => 'NUMERIC',
    );
    $q->set( 'meta_query', $meta );
} );

/* ردیابی بازدید عادی صفحه محصول (قبلاً فقط quick-view ردیابی می‌کرد) + data-product-id برای exclude ریل «دیده‌شده‌ها» */
add_action( 'woocommerce_before_single_product', function() {
    global $product;
    if ( ! $product ) {
        return;
    }
    echo '<span class="ft-hidden" data-product-id="' . esc_attr( $product->get_id() ) . '"></span>';
    do_action( 'fartak_track_product_view', $product->get_id() );
}, 5 );
