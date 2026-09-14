<?php
/**
 * Template Name: خرید عمده
 *
 * @package Fartak
 */

defined( 'ABSPATH' ) || exit;

$ws_phone_raw = get_option( 'fartak_support_phone', '01732000180' );
if ( '' === $ws_phone_raw ) {
        $ws_phone_raw = fartak_mod( 'fartak_phone', fartak_def( 'phone' ) );
}
$ws_phone      = preg_replace( '/\D/', '', (string) $ws_phone_raw );
$ws_phone_disp = fartak_fa( fartak_phone_display( $ws_phone ) );
get_header();
?>
<div class="page-top page-105" style="position:relative;overflow:clip">
        <div style="pointer-events:none;position:absolute;top:-96px;right:0;height:320px;width:320px;border-radius:99px;background:rgba(225,29,42,.12);filter:blur(90px)"></div>
        <div class="container" style="position:relative">
                <div class="student-grid">
                        <div class="student-hero">
                                <span class="chip" style="border-color:rgba(225,29,42,.35);background:rgba(225,29,42,.1);color:var(--copper2)"><?php echo fartak_icon( 'package' ); ?> <?php esc_html_e( 'خرید عمده از فروشگاه فرتاک', 'fartak' ); ?></span>
                                <h1><?php esc_html_e( 'خرید عمده با', 'fartak' ); ?> <span class="copper-text"><?php esc_html_e( 'بهترین قیمت', 'fartak' ); ?></span></h1>
                                <p><?php esc_html_e( 'برای خرید عمده محصولات با فروشگاه فرتاک تماس بگیرید. تیم فروش ما بهترین شرایط و قیمت‌های ویژه عمده‌فروشی را برای شما فراهم می‌کند. ارسال سریع به سراسر کشور.', 'fartak' ); ?></p>
                                <ul class="student-feats">
                                        <li><?php echo fartak_icon( 'badge' ); ?> <?php esc_html_e( 'قیمت ویژه خرید عمده', 'fartak' ); ?></li>
                                        <li><?php echo fartak_icon( 'badge' ); ?> <?php esc_html_e( 'ارسال سریع به سراسر کشور', 'fartak' ); ?></li>
                                        <li><?php echo fartak_icon( 'badge' ); ?> <?php esc_html_e( 'پشتیبانی اختصاصی فروشندگان', 'fartak' ); ?></li>
                                        <li><?php echo fartak_icon( 'badge' ); ?> <?php esc_html_e( 'گارانتی شرکتی روی همه محصولات', 'fartak' ); ?></li>
                                </ul>
                                <a class="ws-call-line" href="tel:<?php echo esc_attr( $ws_phone ); ?>" dir="ltr">
                                        <?php echo fartak_icon( 'phone' ); ?>
                                        <span dir="rtl"><?php esc_html_e( 'تماس مستقیم فروش عمده:', 'fartak' ); ?></span>
                                        <b><bdi dir="ltr"><?php echo esc_html( $ws_phone_disp ); ?></bdi></b>
                                </a>
                                <div class="hero-ctas">
                                        <a href="tel:<?php echo esc_attr( $ws_phone ); ?>" class="btn-copper btn-lg"><?php echo fartak_icon( 'phone' ); ?> <?php esc_html_e( 'تماس بگیرید', 'fartak' ); ?> <span dir="ltr"><bdi dir="ltr"><?php echo esc_html( $ws_phone_disp ); ?></bdi></span></a>
                                        <a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) ); ?>" class="btn-ghost btn-lg"><?php esc_html_e( 'مشاهده محصولات', 'fartak' ); ?></a>
                                </div>
                        </div>

                        <div class="calc card-compact">
                                <div class="backlight"></div>
                                <h2 style="display:flex;align-items:center;gap:8px;margin:0;font-size:15px;font-weight:900;position:relative"><?php echo fartak_icon( 'calc' ); ?> <?php esc_html_e( 'درخواست خرید عمده', 'fartak' ); ?></h2>
                                <div class="calc-body" style="position:relative">
                                        <div>
                                                <label for="wholesale-company"><?php esc_html_e( 'نام شرکت / فروشگاه', 'fartak' ); ?></label>
                                                <input type="text" id="wholesale-company" placeholder="<?php esc_attr_e( 'نام شرکت یا فروشگاه خود', 'fartak' ); ?>">
                                        </div>
                                        <div>
                                                <label for="wholesale-person"><?php esc_html_e( 'نام شخص مسئول', 'fartak' ); ?></label>
                                                <input type="text" id="wholesale-person" placeholder="<?php esc_attr_e( 'نام و نام خانوادگی', 'fartak' ); ?>">
                                        </div>
                                        <div>
                                                <label for="wholesale-phone"><?php esc_html_e( 'شماره تماس', 'fartak' ); ?></label>
                                                <input type="tel" id="wholesale-phone" dir="ltr" placeholder="09xxxxxxxxx" inputmode="numeric">
                                        </div>
                                        <div>
                                                <label for="wholesale-product"><?php esc_html_e( 'محصول مورد نظر', 'fartak' ); ?></label>
                                                <input type="text" id="wholesale-product" placeholder="<?php esc_attr_e( 'نام محصول یا دسته‌بندی', 'fartak' ); ?>">
                                        </div>
                                        <div>
                                                <label for="wholesale-qty"><?php esc_html_e( 'تعداد درخواستی', 'fartak' ); ?></label>
                                                <input type="number" id="wholesale-qty" min="1" value="10" placeholder="<?php esc_attr_e( 'تعداد', 'fartak' ); ?>">
                                        </div>
                                </div>
                                <div class="calc-out">
                                        <small><?php esc_html_e( 'کارشناس فروش ظرف چند ساعت تماس می‌گیرد', 'fartak' ); ?></small>
                                </div>
                                <button type="button" class="btn-copper w-full" id="wholesale-submit" style="margin-top:16px;padding-block:12px"><?php echo fartak_icon( 'send' ); ?> <?php esc_html_e( 'ارسال درخواست خرید عمده', 'fartak' ); ?></button>
                                <p style="margin:10px 0 0;text-align:center;font-size:10.5px;line-height:1.7;color:var(--mist)" id="wholesale-server-note"><?php esc_html_e( 'پاسخ سرور همین‌جا نمایش داده می‌شود.', 'fartak' ); ?></p>
                        </div>
                </div>

                <div class="student-banner">
                        <img src="<?php echo esc_url( FARTAK_URI . '/assets/images/hero-case.png' ); ?>" alt="<?php esc_attr_e( 'خرید عمده', 'fartak' ); ?>" loading="lazy">
                        <div class="shade"></div>
                        <div class="txt">
                                <h3 style="max-width:22rem;margin:0;font-size:18px;font-weight:900;line-height:1.8"><?php esc_html_e( 'قیمت بهتر، خرید بیشتر، سود بیشتر', 'fartak' ); ?></h3>
                                <p style="margin:4px 0 0;font-size:12px;color:var(--mist)"><?php esc_html_e( 'برای فروشندگان و شرکت‌ها شرایط ویژه در نظر گرفته شده است', 'fartak' ); ?></p>
                        </div>
                </div>
        </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
        var btn = document.getElementById('wholesale-submit');
        if (!btn) return;

        // خطای inline به‌جای alert
        var errBox = document.createElement('p');
        errBox.id = 'wholesale-error';
        errBox.style.cssText = 'margin:10px 0 0;text-align:center;font-size:11.5px;font-weight:700;color:#f87171;display:none';
        btn.parentNode.insertBefore(errBox, btn.nextSibling);
        function showErr(msg) { errBox.textContent = msg; errBox.style.display = 'block'; }
        function hideErr() { errBox.style.display = 'none'; }

        // تبدیل ارقام فارسی/عربی به لاتین (قبلاً فقط ارقام لاتین پذیرفته می‌شد)
        function normalizeDigits(s) {
                var fa = '۰۱۲۳۴۵۶۷۸۹', ar = '٠١٢٣٤٥٦٧٨٩', out = '';
                for (var i = 0; i < s.length; i++) {
                        var c = s.charAt(i);
                        var fi = fa.indexOf(c), ai = ar.indexOf(c);
                        out += (fi > -1) ? String(fi) : (ai > -1) ? String(ai) : c;
                }
                return out.replace(/[\s-]/g, '');
        }

        btn.addEventListener('click', function() {
                hideErr();
                var company = document.getElementById('wholesale-company').value.trim();
                var person = document.getElementById('wholesale-person').value.trim();
                var phone = normalizeDigits(document.getElementById('wholesale-phone').value.trim());
                var product = document.getElementById('wholesale-product').value.trim();
                var qty = document.getElementById('wholesale-qty').value.trim();

                if (!person || !phone) {
                        showErr('<?php esc_html_e( 'نام شخص و شماره تماس الزامی است', 'fartak' ); ?>');
                        return;
                }
                if (!/^09\d{9}$/.test(phone)) {
                        showErr('<?php esc_html_e( 'شماره موبایل نامعتبر است — مثال: 09123456789', 'fartak' ); ?>');
                        return;
                }

                var data = new FormData();
                data.append('action', 'fartak_wholesale_submit');
                data.append('nonce', FARTAK.nonce);
                data.append('company', company);
                data.append('person', person);
                data.append('phone', phone);
                data.append('product', product);
                data.append('quantity', qty);

                btn.disabled = true;
                btn.innerHTML = '<?php echo fartak_icon( "loader", "spin" ); ?> <?php esc_html_e( "در حال ارسال...", "fartak" ); ?>';

                fetch(FARTAK.ajax, { method: 'POST', body: data })
                        .then(function(r) { return r.json(); })
                        .then(function(res) {
                                var note = document.getElementById('wholesale-server-note');
                                if (res.success) {
                                        if (note) { note.textContent = res.data.message || 'درخواست شما ثبت شد.'; note.style.color = '#34d399'; }
                                        document.getElementById('wholesale-company').value = '';
                                        document.getElementById('wholesale-person').value = '';
                                        document.getElementById('wholesale-phone').value = '';
                                        document.getElementById('wholesale-product').value = '';
                                } else {
                                        if (note) { note.textContent = res.data.message || 'خطا در ارسال'; note.style.color = '#f87171'; }
                                        else { alert(res.data.message || 'خطا در ارسال'); }
                                }
                        })
                        .catch(function() {
                                var note = document.getElementById('wholesale-server-note');
                                if (note) { note.textContent = 'خطا در ارتباط با سرور'; note.style.color = '#f87171'; }
                        })
                        .finally(function() {
                                btn.disabled = false;
                                btn.innerHTML = '<?php echo fartak_icon( "send" ); ?> <?php esc_html_e( "ارسال درخواست خرید عمده", "fartak" ); ?>';
                        });
        });
});
</script>

<?php
get_footer();
