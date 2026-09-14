<?php
/**
 * Fartak — هندلرهای AJAX
 *
 * @package Fartak
 */

defined( 'ABSPATH' ) || exit;

function fartak_ajax_check() {
        check_ajax_referer( 'fartak', 'nonce' );
}

/* ------------------------------------------------- جستجوی زنده محصولات */
add_action( 'wp_ajax_fartak_search', 'fartak_search' );
add_action( 'wp_ajax_nopriv_fartak_search', 'fartak_search' );
function fartak_search() {
        // Compatibility alias: the unified implementation is in smart-search.php.
        if ( function_exists( 'fartak_smart_search' ) ) {
                fartak_smart_search();
        }
        wp_send_json( array() );
}

/* ------------------------------------------------- افزودن به سبد (AJAX) */
add_action( 'wp_ajax_fartak_add_to_cart', 'fartak_add_to_cart' );
add_action( 'wp_ajax_nopriv_fartak_add_to_cart', 'fartak_add_to_cart' );
function fartak_add_to_cart() {
        fartak_ajax_check();
        if ( ! class_exists( 'WooCommerce' ) ) {
                wp_send_json_error( array( 'message' => __( 'ووکامرس فعال نیست.', 'fartak' ) ) );
        }
        $id  = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
        $qty = isset( $_POST['qty'] ) ? max( 1, absint( $_POST['qty'] ) ) : 1;
        if ( ! $id ) {
                wp_send_json_error( array( 'message' => __( 'محصول نامعتبر است.', 'fartak' ) ) );
        }
        // محصولات «تماس بگیرید» قابل خرید نیستند
        if ( function_exists( 'fartak_product_is_call' ) && fartak_product_is_call( $id ) ) {
                wp_send_json_error( array( 'message' => __( 'برای قیمت این کالا با پشتیبانی تماس بگیرید.', 'fartak' ) ) );
        }
        if ( null === WC()->cart ) {
                wc_load_cart();
        }
        if ( ! WC()->cart ) {
                wp_send_json_error( array( 'message' => __( 'سبد خرید در دسترس نیست.', 'fartak' ) ) );
        }
        // محصول متغیر: باید از صفحه محصول گزینه انتخاب شود (قبلاً پیام عمومی بی‌ربط می‌داد)
        $product_obj = wc_get_product( $id );
        if ( ! $product_obj || ! $product_obj->exists() || ! $product_obj->is_purchasable() ) {
                wp_send_json_error( array( 'message' => __( 'این محصول در حال حاضر قابل خرید نیست.', 'fartak' ) ) );
        }
        if ( $product_obj->is_sold_individually() ) { $qty = 1; }
        $existing = 0;
        foreach ( WC()->cart->get_cart() as $item ) { if ( (int) $item['product_id'] === $id ) { $existing += (int) $item['quantity']; } }
        $max_purchase = (int) $product_obj->get_max_purchase_quantity();
        if ( $max_purchase > 0 && $existing + $qty > $max_purchase ) {
                wp_send_json_error( array( 'message' => __( 'تعداد انتخاب‌شده بیشتر از سقف خرید این کالا است.', 'fartak' ) ) );
        }
        if ( $product_obj->managing_stock() && ! $product_obj->backorders_allowed() ) {
                $stock = $product_obj->get_stock_quantity();
                if ( null !== $stock && $existing + $qty > (int) $stock ) {
                        wp_send_json_error( array( 'message' => __( 'تعداد انتخاب‌شده بیشتر از موجودی قابل خرید است.', 'fartak' ) ) );
                }
        }
        if ( $product_obj && $product_obj->is_type( 'variable' ) ) {
                wp_send_json_error( array( 'message' => __( 'این محصول دارای سایز/گزینه است — از صفحه محصول انتخاب کنید.', 'fartak' ) ) );
        }
        $added = WC()->cart->add_to_cart( $id, $qty );
        if ( $added ) {
                if ( function_exists( 'wc_maybe_define_constant' ) ) { /* no-op: keep compatibility */ }
                WC()->cart->calculate_totals();
                wp_send_json_success( array( 'count' => WC()->cart->get_cart_contents_count() ) );
        }
        $notices = wc_get_notices( 'error' );
        $msg = __( 'امکان افزودن محصول نیست.', 'fartak' );
        if ( ! empty( $notices ) ) {
                $msg = wp_strip_all_tags( html_entity_decode( wp_kses_post( wp_list_pluck( $notices, 'notice' )[0] ?? '' ) ) );
        } elseif ( $product_obj && ! $product_obj->is_in_stock() ) {
                $msg = __( 'این محصول فعلاً ناموجود است.', 'fartak' );
        }
        wc_clear_notices();
        wp_send_json_error( array( 'message' => $msg ) );
}

/* ------------------------------------------------- استعلام قیمت (لید) */
add_action( 'wp_ajax_fartak_inquiry', 'fartak_inquiry' );
add_action( 'wp_ajax_nopriv_fartak_inquiry', 'fartak_inquiry' );
function fartak_inquiry() {
        fartak_ajax_check();
        // ضداسپم: حداکثر یک درخواست در ۳۰ ثانیه برای هر IP + honeypot
        // (قبلاً هر ربات می‌توانست بی‌نهایت post خصوصی + ایمیل بسازد)
        $honeypot = isset( $_POST['website'] ) ? sanitize_text_field( wp_unslash( $_POST['website'] ) ) : '';
        if ( '' !== $honeypot ) {
                wp_send_json_error( array( 'message' => __( 'درخواست نامعتبر است.', 'fartak' ) ) );
        }
        $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0';
        $rl_key = 'fartak_inq_rl_' . md5( $ip );
        if ( get_transient( $rl_key ) ) {
                wp_send_json_error( array( 'message' => __( 'درخواست‌ها را پشت‌سرهم نمی‌توان ثبت کرد؛ چند لحظه بعد تلاش کنید.', 'fartak' ) ) );
        }
        set_transient( $rl_key, 1, 30 );

        $name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
        $phone   = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
        $product = isset( $_POST['product_name'] ) ? sanitize_text_field( wp_unslash( $_POST['product_name'] ) ) : '';
        $note    = isset( $_POST['note'] ) ? sanitize_text_field( wp_unslash( $_POST['note'] ) ) : '';

        if ( '' === $name || mb_strlen( $phone ) < 10 ) {
                wp_send_json_error( array( 'message' => __( 'نام و شماره موبایل معتبر الزامی است.', 'fartak' ) ) );
        }

        $post_id = wp_insert_post(
                array(
                        'post_type'    => 'fartak_inquiry',
                        'post_status'  => 'private',
                        'post_title'   => $name . ' — ' . $phone,
                        'post_content' => "کالا: {$product}\nیادداشت: {$note}",
                )
        );

        if ( $post_id ) {
                update_post_meta( $post_id, '_fartak_customer', $name );
                update_post_meta( $post_id, '_fartak_phone', $phone );
                update_post_meta( $post_id, '_fartak_product', $product );
                update_post_meta( $post_id, '_fartak_stage', 'new' );
        }

        $body = sprintf(
                "لید جدید استعلام قیمت\nنام: %s\nموبایل: %s\nکالا: %s\nیادداشت: %s",
                $name,
                $phone,
                $product,
                $note
        );
        wp_mail( get_option( 'admin_email' ), '[فرتاک] استعلام قیمت جدید', $body );

        // هوک برای پیامک و نوتیفیکیشن
        do_action( 'fartak_inquiry_submitted', array(
                'name'    => $name,
                'phone'   => $phone,
                'product' => $product,
                'note'    => $note,
                'post_id' => $post_id,
        ) );

        // نوتیفیکیشن ادمین
        if ( function_exists( 'fartak_add_admin_notification' ) ) {
                fartak_add_admin_notification( 'inquiry', 'استعلام قیمت جدید', $name . ' درخواست استعلام برای ' . ( $product ?: 'محصول' ) . ' ارسال کرد.' );
        }

        wp_send_json_success( array( 'ok' => true, 'id' => $post_id ) );
}

/* ------------------------------------------------- جدول مقایسه */
add_action( 'wp_ajax_fartak_compare', 'fartak_compare' );
add_action( 'wp_ajax_nopriv_fartak_compare', 'fartak_compare' );
function fartak_compare() {
        fartak_ajax_check();
        $ids = isset( $_GET['ids'] ) ? array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_GET['ids'] ) ) ) ) ) : array();
        $ids = array_slice( $ids, 0, 3 );
        if ( empty( $ids ) || ! class_exists( 'WooCommerce' ) ) {
                wp_send_json_error( array( 'message' => 'empty' ) );
        }
        $products = array();
        foreach ( $ids as $id ) {
                $p = wc_get_product( $id );
                if ( $p ) {
                        $products[] = $p;
                }
        }
        if ( empty( $products ) ) {
                wp_send_json_error( array( 'message' => 'empty' ) );
        }

        // اجتماع برچسب ویژگی‌ها
        $keys = array();
        $rows = array();
        foreach ( $products as $p ) {
                $rows[ $p->get_id() ] = array();
                foreach ( $p->get_attributes() as $attr ) {
                        if ( ! $attr->get_visible() ) {
                                continue;
                        }
                        $label = wc_attribute_label( $attr->get_name(), $p );
                        if ( ! in_array( $label, $keys, true ) ) {
                                $keys[] = $label;
                        }
                        $options = $attr->get_options();
                        $value   = '';
                        if ( $attr->is_taxonomy() ) {
                                $value = wc_get_product_terms( $p->get_id(), $attr->get_name(), array( 'fields' => 'names' ) );
                                $value = is_array( $value ) ? implode( '، ', $value ) : '';
                        } else {
                                $value = is_array( $options ) ? implode( '، ', $options ) : $options;
                        }
                        $rows[ $p->get_id() ][ $label ] = $value;
                }
        }

        ob_start();
        ?>
        <table class="compare-tbl">
                <thead>
                        <tr>
                                <th><?php esc_html_e( 'مشخصات', 'fartak' ); ?></th>
                                <?php foreach ( $products as $p ) : ?>
                                        <th>
                                                <button type="button" class="cmp-rm" data-compare-toggle="<?php echo esc_attr( $p->get_id() ); ?>" aria-label="<?php esc_attr_e( 'حذف', 'fartak' ); ?>"><?php echo fartak_icon( 'x' ); ?></button>
                                                <a href="<?php echo esc_url( get_permalink( $p->get_id() ) ); ?>">
                                                        <?php echo wp_kses_post( wp_get_attachment_image( $p->get_image_id(), 'thumbnail', false, array( 'class' => 'cmp-img' ) ) ); ?>
                                                        <span class="cmp-name clamp2" style="display:block"><?php echo esc_html( $p->get_name() ); ?></span>
                                                </a>
                                                <div class="cmp-cat"><?php echo esc_html( fartak_product_cat_label( $p ) ); ?></div>
                                                <div class="cmp-price"><?php echo $p->get_price_html() ? wp_kses_post( $p->get_price_html() ) : esc_html__( 'استعلامی', 'fartak' ); ?></div>
                                                <?php if ( $p->is_type( 'simple' ) && $p->is_in_stock() && $p->get_price() ) : ?>
                                                        <button type="button" class="btn-copper btn-xs" style="margin-top:8px" data-fartak-add="<?php echo esc_attr( $p->get_id() ); ?>"><?php echo fartak_icon( 'cart' ); ?> <?php esc_html_e( 'افزودن', 'fartak' ); ?></button>
                                                <?php endif; ?>
                                        </th>
                                <?php endforeach; ?>
                        </tr>
                </thead>
                <tbody>
                        <?php foreach ( $keys as $key ) : ?>
                                <tr>
                                        <td><?php echo esc_html( $key ); ?></td>
                                        <?php foreach ( $products as $p ) : ?>
                                                <td><?php echo ! empty( $rows[ $p->get_id() ][ $key ] ) ? esc_html( $rows[ $p->get_id() ][ $key ] ) : '<span style="opacity:.35">—</span>'; ?></td>
                                        <?php endforeach; ?>
                                </tr>
                        <?php endforeach; ?>
                </tbody>
        </table>
        <?php
        $html = ob_get_clean();
        wp_send_json_success( array( 'html' => $html ) );
}
