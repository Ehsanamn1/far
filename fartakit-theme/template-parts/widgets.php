<?php
/**
 * Fartak — ویجت‌های شناور: چت هوشمند منشی AI، مودال استعلام قیمت
 * (گردونه شانس حذف شد - طبق درخواست)
 *
 * @package Fartak
 */

defined( 'ABSPATH' ) || exit;

$fartak_ai_on  = get_option( 'fartak_ai_enabled', '1' ) === '1';
/* مودال استعلام قیمت مستقل از منشی است — خاموش کردن منشی نباید دکمه‌های استعلام را بکشد */

$fartak_phone = get_option( 'fartak_support_phone', '01732000180' );
$fartak_welcome = get_option( 'fartak_ai_welcome', 'سلام! من دستیار هوشمند فرتاک هستم. چطور می‌توانم کمکتان کنم؟' );

/* پرسش‌وپاسخ‌های منشی از پنل */
$fartak_qa = array();
$fartak_qa_json = get_option( 'fartak_ai_qa', '' );
if ( $fartak_qa_json ) {
	$fartak_decoded = json_decode( $fartak_qa_json, true );
	if ( is_array( $fartak_decoded ) ) $fartak_qa = $fartak_decoded;
}
if ( empty( $fartak_qa ) ) {
	$fartak_qa = array(
		array( 'قیمت امروز قطعات؟', 'برای استعلام قیمت دقیق با پشتیبانی فرتاک تماس بگیرید: ' . $fartak_phone ),
		array( 'شرایط گارانتی؟', 'گارانتی هر کالا روی صفحه خودش درج شده است. برای اطلاع دقیق‌تر تماس بگیرید: ' . $fartak_phone ),
		array( 'هزینه ارسال؟', 'ارسال به سراسر کشور با تیپاکس انجام می‌شود. برای اطلاع از هزینه دقیق تماس بگیرید: ' . $fartak_phone ),
		array( 'خرید عمده دارید؟', 'بله! برای خرید عمده با شماره ' . $fartak_phone . ' تماس بگیرید تا بهترین شرایط را فراهم کنیم.' ),
	);
}
?>

<?php if ( $fartak_ai_on ) : ?>
<!-- چت‌باکس منشی آنلاین -->
<button type="button" class="chat-fab" data-chat-toggle aria-label="<?php esc_attr_e( 'منشی آنلاین', 'fartak' ); ?>">
        <span class="ring"></span>
        <span data-chat-open-ic><?php echo fartak_icon( 'bot' ); ?></span>
        <span data-chat-close-ic class="ft-hidden"><?php echo fartak_icon( 'x' ); ?></span>
</button>
<div class="chat-panel" id="chat-panel">
        <div class="chat-hd">
                <span class="ava"><?php echo fartak_icon( 'bot' ); ?></span>
                <div>
                        <b><?php esc_html_e( 'منشی آنلاین فرتاک', 'fartak' ); ?></b>
                        <small><i></i> <?php esc_html_e( 'آنلاین — پاسخ در چند ثانیه', 'fartak' ); ?></small>
                </div>
                <button type="button" class="chat-close-btn" data-chat-close aria-label="<?php esc_attr_e( 'بستن گفتگو', 'fartak' ); ?>"><?php echo fartak_icon( 'x' ); ?></button>
        </div>
        <div class="chat-body" id="chat-body">
                <div class="chat-msg bot"><?php echo esc_html( $fartak_welcome ); ?></div>
        </div>
        <div class="chat-quick" id="chat-quick"></div>
        <div class="chat-input">
                <input type="text" id="chat-text" placeholder="<?php esc_attr_e( 'پیام خود را بنویسید…', 'fartak' ); ?>">
                <button type="button" class="btn-copper" data-chat-send aria-label="<?php esc_attr_e( 'ارسال', 'fartak' ); ?>"><?php echo fartak_icon( 'send' ); ?></button>
        </div>
</div>
<script type="application/json" id="fartak-chat-faq"><?php echo wp_json_encode( $fartak_qa ); ?></script>
<script type="application/json" id="fartak-chat-phone"><?php echo wp_json_encode( $fartak_phone ); ?></script>
<?php endif; ?>
