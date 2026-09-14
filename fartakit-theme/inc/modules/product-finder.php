<?php
/**
 * Module: Product Finder — یابنده هوشمند محصول
 *
 * Shortcode `[fartak_product_finder]` renders a 3-step wizard:
 *   1. Use case (Gaming, Programming, Design, Office, General)
 *   2. Budget tier
 *   3. Priority (performance, design, lightness, battery)
 *
 * AJAX handler `fartak_product_finder_search` returns 4 matching products
 * based on the user's answers, rendered as product cards.
 *
 * @package Fartak
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Configuration of wizard steps.
 *
 * Each step has a slug (used as the JS data attribute), a question,
 * and a list of {value, label, icon} options.
 *
 * @return array
 */
function fartak_pf_steps() {
    return array(
        array(
            'slug' => 'usage',
            'question' => __( 'چه استفاده‌ای دارید؟', 'fartak' ),
            'options'  => array(
                array( 'value' => 'gaming',       'label' => __( 'گیمینگ', 'fartak' ),       'icon' => 'gamepad' ),
                array( 'value' => 'programming', 'label' => __( 'برنامه‌نویسی', 'fartak' ), 'icon' => 'keyboard' ),
                array( 'value' => 'design',       'label' => __( 'طراحی', 'fartak' ),        'icon' => 'sparkles' ),
                array( 'value' => 'office',       'label' => __( 'اداری', 'fartak' ),        'icon' => 'clipboard' ),
                array( 'value' => 'general',      'label' => __( 'کاربری عمومی', 'fartak' ), 'icon' => 'grid' ),
            ),
        ),
        array(
            'slug' => 'budget',
            'question' => __( 'بودجه شما؟', 'fartak' ),
            'options'  => array(
                array( 'value' => 'low',     'label' => __( 'زیر ۲۰ میلیون', 'fartak' ), 'icon' => 'coins' ),
                array( 'value' => 'medium',  'label' => __( '۲۰ تا ۴۰ میلیون', 'fartak' ), 'icon' => 'coins' ),
                array( 'value' => 'high',    'label' => __( '۴۰ تا ۷۰ میلیون', 'fartak' ), 'icon' => 'coins' ),
                array( 'value' => 'premium', 'label' => __( 'بالای ۷۰ میلیون', 'fartak' ), 'icon' => 'coins' ),
            ),
        ),
        array(
            'slug' => 'priority',
            'question' => __( 'اهمیت چه چیزی است؟', 'fartak' ),
            'options'  => array(
                array( 'value' => 'performance', 'label' => __( 'عملکرد', 'fartak' ), 'icon' => 'zap' ),
                array( 'value' => 'design',       'label' => __( 'ظاهر', 'fartak' ),    'icon' => 'sparkles' ),
                array( 'value' => 'lightness',    'label' => __( 'سبکی', 'fartak' ),    'icon' => 'feather' ),
                array( 'value' => 'battery',      'label' => __( 'باتری', 'fartak' ),    'icon' => 'plug' ),
            ),
        ),
    );
}

/**
 * Map answers to product query arguments.
 *
 * @param string $usage   Use case slug.
 * @param string $budget  Budget tier slug.
 * @param string $priority Priority slug.
 * @return array Arguments for wc_get_products() / WP_Query.
 */
function fartak_pf_build_query( $usage, $budget, $priority ) {
    $tax_query = array();
    $meta_query = array();
    $orderby = 'date';
    $order = 'DESC';

    switch ( $usage ) {
        case 'gaming':
            $tax_query[] = array(
                'relation' => 'OR',
                array( 'taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => array( 'gpu', 'laptop', 'case' ) ),
            );
            $meta_query[] = array( 'key' => '_price', 'value' => 20000000, 'compare' => '>=', 'type' => 'NUMERIC' );
            break;
        case 'programming':
            $tax_query[] = array(
                array( 'taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => array( 'laptop' ) ),
            );
            $meta_query[] = array( 'key' => '_price', 'value' => 15000000, 'compare' => '>=', 'type' => 'NUMERIC' );
            break;
        case 'design':
            $tax_query[] = array(
                'relation' => 'OR',
                array( 'taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => array( 'laptop', 'monitor' ) ),
            );
            $meta_query[] = array( 'key' => '_price', 'value' => 18000000, 'compare' => '>=', 'type' => 'NUMERIC' );
            break;
        case 'office':
            $tax_query[] = array(
                'relation' => 'OR',
                array( 'taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => array( 'laptop', 'monitor', 'keyboard', 'mouse' ) ),
            );
            break;
        case 'general':
        default:
            // no specific category
            break;
    }

    switch ( $budget ) {
        case 'low':
            // بازه‌ها نباید هم‌پوشان باشند: low فقط زیر ۲۰م (gaming از ۲۰م به بالا)
            $meta_query[] = array( 'key' => '_price', 'value' => 20000000, 'compare' => '<', 'type' => 'NUMERIC' );
            break;
        case 'medium':
            $meta_query[] = array( 'key' => '_price', 'value' => array( 20000000, 40000000 ), 'compare' => 'BETWEEN', 'type' => 'NUMERIC' );
            break;
        case 'high':
            $meta_query[] = array( 'key' => '_price', 'value' => array( 40000000, 70000000 ), 'compare' => 'BETWEEN', 'type' => 'NUMERIC' );
            break;
        case 'premium':
            $meta_query[] = array( 'key' => '_price', 'value' => 70000000, 'compare' => '>=', 'type' => 'NUMERIC' );
            break;
    }

    switch ( $priority ) {
        case 'performance':
            $orderby = 'meta_value_num';
            $meta_query[] = array( 'key' => 'total_sales', 'compare' => 'EXISTS' );
            break;
        case 'design':
            // Featured products first
            $meta_query[] = array(
                'relation' => 'OR',
                array( 'key' => '_featured', 'value' => 'yes' ),
                array( 'key' => '_featured', 'compare' => 'NOT EXISTS' ),
            );
            break;
        case 'lightness':
        case 'battery':
            // Sort by newest
            $orderby = 'date';
            $order = 'DESC';
            break;
    }

    // Filter for in-stock products first
    $meta_query[] = array(
        'relation' => 'OR',
        array( 'key' => '_stock_status', 'value' => 'instock' ),
        array( 'key' => '_stock_status', 'compare' => 'NOT EXISTS' ),
    );

    return array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => 4,
        'orderby'        => $orderby,
        'order'          => $order,
        'tax_query'      => $tax_query,
        'meta_query'     => $meta_query,
        'no_found_rows'  => true,
    );
}

/**
 * Find matching products for the given answers.
 *
 * @return WC_Product[]
 */
function fartak_pf_find( $usage, $budget, $priority ) {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return array();
    }
    $args = fartak_pf_build_query( $usage, $budget, $priority );

    $cache_key = 'fartak_pf_' . md5( wp_json_encode( $args ) );
    $cached    = get_transient( $cache_key );
    if ( is_array( $cached ) ) {
        return array_map( 'wc_get_product', array_filter( $cached ) );
    }

    $query = new WP_Query( $args );

    // Fallback: if too few results, relax by stripping tax_query.
    if ( count( $query->posts ) < 4 ) {
        $args['tax_query'] = array();
        $args['posts_per_page'] = 4;
        $query = new WP_Query( $args );
    }

    // Final fallback: best sellers.
    if ( count( $query->posts ) < 4 ) {
        $args = array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => 4,
            'meta_key'       => 'total_sales',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
            'no_found_rows'  => true,
        );
        $query = new WP_Query( $args );
    }

    $products = array();
    foreach ( $query->posts as $post ) {
        $p = wc_get_product( $post->ID );
        if ( $p ) {
            $products[] = $p;
        }
    }

    set_transient(
        $cache_key,
        array_map(
            function( $p ) {
                return $p->get_id();
            },
            $products
        ),
        5 * MINUTE_IN_SECONDS
    );
    return $products;
}

/**
 * Shortcode: [fartak_product_finder]
 */
add_shortcode( 'fartak_product_finder', 'fartak_pf_shortcode' );
function fartak_pf_shortcode( $atts ) {
    $atts = shortcode_atts(
        array(
            'count' => 4,
        ),
        $atts,
        'fartak_product_finder'
    );

    $steps = fartak_pf_steps();
    ob_start();
    ?>
    <div class="fartak-pf" data-fartak-pf>
        <div class="fartak-pf__progress">
            <div class="fartak-pf__bar" style="width:33%"></div>
        </div>
        <div class="fartak-pf__step" data-step="0">
            <?php foreach ( $steps as $i => $step ) : ?>
                <div class="fartak-pf__panel<?php echo $i === 0 ? ' is-active' : ''; ?>" data-panel="<?php echo esc_attr( $i ); ?>">
                    <h3 class="fartak-pf__question"><?php echo esc_html( $step['question'] ); ?></h3>
                    <div class="fartak-pf__options">
                        <?php foreach ( $step['options'] as $opt ) : ?>
                            <button type="button"
                                class="fartak-pf__option"
                                data-pf-answer="<?php echo esc_attr( $step['slug'] ); ?>"
                                data-pf-value="<?php echo esc_attr( $opt['value'] ); ?>">
                                <span class="fartak-pf__icon"><?php echo fartak_icon( $opt['icon'] ); // phpcs:ignore ?></span>
                                <span class="fartak-pf__label"><?php echo esc_html( $opt['label'] ); ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="fartak-pf__nav">
            <button type="button" class="btn-ghost fartak-pf__back" data-pf-back disabled><?php esc_html_e( 'قبلی', 'fartak' ); ?></button>
            <span class="fartak-pf__count"><?php esc_html_e( 'گام', 'fartak' ); ?> <b data-pf-current>۱</b> <?php esc_html_e( 'از', 'fartak' ); ?> <?php echo esc_html( fartak_fa_num( count( $steps ) ) ); ?></span>
            <button type="button" class="btn-copper fartak-pf__next" data-pf-next disabled><?php esc_html_e( 'بعدی', 'fartak' ); ?></button>
        </div>

        <div class="fartak-pf__results" data-pf-results style="display:none">
            <div class="fartak-pf__results-head">
                <h3><?php esc_html_e( 'پیشنهادهای فرتاک برای شما', 'fartak' ); ?></h3>
                <button type="button" class="btn-ghost btn-xs" data-pf-reset><?php esc_html_e( 'شروع دوباره', 'fartak' ); ?></button>
            </div>
            <div class="fartak-pf__grid" data-pf-grid></div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * AJAX handler: fartak_product_finder_search
 */
add_action( 'wp_ajax_fartak_product_finder_search', 'fartak_pf_search' );
add_action( 'wp_ajax_nopriv_fartak_product_finder_search', 'fartak_pf_search' );
function fartak_pf_search() {
    check_ajax_referer( 'fartak', 'nonce' );

    $usage    = isset( $_POST['usage'] ) ? sanitize_key( wp_unslash( $_POST['usage'] ) ) : 'general';
    $budget   = isset( $_POST['budget'] ) ? sanitize_key( wp_unslash( $_POST['budget'] ) ) : 'medium';
    $priority = isset( $_POST['priority'] ) ? sanitize_key( wp_unslash( $_POST['priority'] ) ) : 'performance';

    $allowed_usage    = array( 'gaming', 'programming', 'design', 'office', 'general' );
    $allowed_budget   = array( 'low', 'medium', 'high', 'premium' );
    $allowed_priority = array( 'performance', 'design', 'lightness', 'battery' );

    if ( ! in_array( $usage, $allowed_usage, true ) ) $usage = 'general';
    if ( ! in_array( $budget, $allowed_budget, true ) ) $budget = 'medium';
    if ( ! in_array( $priority, $allowed_priority, true ) ) $priority = 'performance';

    $products = fartak_pf_find( $usage, $budget, $priority );

    if ( empty( $products ) ) {
        wp_send_json_success( array(
            'html'   => '<div class="fartak-pf__empty">' . esc_html__( 'پیشنهادی برای این ترکیب موجود نیست. لطفاً گزینه دیگری را امتحان کنید.', 'fartak' ) . '</div>',
            'empty'  => true,
        ) );
    }

    ob_start();
    echo '<div class="fartak-pf__grid">';
    foreach ( $products as $product ) {
        fartak_product_card( $product, 'raw' );
    }
    echo '</div>';
    $html = ob_get_clean();

    wp_send_json_success( array( 'html' => $html, 'empty' => false ) );
}

/**
 * CSS/JS assets.
 */
add_action( 'wp_footer', 'fartak_pf_assets', 30 );
function fartak_pf_assets() {
    if ( ! class_exists( 'WooCommerce' ) ) return;
    ?>
    <style>
    .fartak-pf{max-width:720px;margin:32px auto;padding:24px;background:#0e1626;border:1px solid rgba(255,255,255,.07);border-radius:16px;backdrop-filter:blur(8px);box-shadow:0 24px 48px -24px rgba(0,0,0,.6)}
    .fartak-pf__progress{height:6px;background:rgba(255,255,255,.06);border-radius:99px;overflow:hidden;margin-bottom:24px}
    .fartak-pf__bar{height:100%;background:linear-gradient(90deg,#e11d2a,#ff3543);border-radius:99px;transition:width .3s}
    .fartak-pf__panel{display:none}
    .fartak-pf__panel.is-active{display:block;animation:ftpf-fade .3s ease}
    @keyframes ftpf-fade{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
    .fartak-pf__question{margin:0 0 18px;font-size:18px;font-weight:800;color:#e8ecf4;text-align:center}
    .fartak-pf__options{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px}
    .fartak-pf__option{display:flex;flex-direction:column;align-items:center;gap:8px;padding:18px 12px;background:rgba(255,255,255,.04);border:2px solid rgba(255,255,255,.08);border-radius:12px;color:#e8ecf4;cursor:pointer;transition:all .2s;font-family:inherit;font-size:13px;font-weight:600}
    .fartak-pf__option:hover{border-color:rgba(255,53,67,.4);background:rgba(255,53,67,.06);transform:translateY(-2px)}
    .fartak-pf__option.selected{border-color:#ff3543;background:rgba(255,53,67,.1)}
    .fartak-pf__icon .icon{width:30px;height:30px;color:#ff3543}
    .fartak-pf__label{font-size:13px;color:#e8ecf4}
    .fartak-pf__nav{display:flex;align-items:center;justify-content:space-between;margin-top:24px;padding-top:18px;border-top:1px solid rgba(255,255,255,.06)}
    .fartak-pf__count{font-size:12px;color:#8b95ab}
    .fartak-pf__count b{color:#ff3543;font-weight:800}
    .fartak-pf__nav button:disabled{opacity:.4;cursor:not-allowed}
    .fartak-pf__results{margin-top:24px}
    .fartak-pf__results-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px}
    .fartak-pf__results-head h3{margin:0;font-size:15px;font-weight:800;color:#e8ecf4}
    .fartak-pf__grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px}
    .fartak-pf__empty{padding:40px 20px;text-align:center;color:#8b95ab;font-size:13px;background:rgba(255,255,255,.04);border-radius:12px}
    .btn-xs{padding:4px 12px;font-size:12px;border-radius:8px}
    @media (max-width:560px){.fartak-pf__options{grid-template-columns:repeat(2,1fr)}.fartak-pf{padding:16px}}
    </style>
    <script>
    (function(){
        if (typeof FARTAK === 'undefined' || !FARTAK.ajax) return;
        var root = document.querySelector('[data-fartak-pf]');
        if (!root) return;

        var steps   = root.querySelectorAll('.fartak-pf__panel');
        var total   = steps.length;
        var current = 0;
        var answers = {};

        var nextBtn  = root.querySelector('[data-pf-next]');
        var backBtn  = root.querySelector('[data-pf-back]');
        var curLabel = root.querySelector('[data-pf-current]');
        var bar      = root.querySelector('.fartak-pf__bar');
        var results  = root.querySelector('[data-pf-results]');
        var grid     = root.querySelector('[data-pf-grid]');

        function fa(n){ return String(n).replace(/[0-9]/g, function(d){return '۰۱۲۳۴۵۶۷۸۹'[+d];}); }

        function update(){
            steps.forEach(function(p, i){ p.classList.toggle('is-active', i === current); });
            curLabel.textContent = fa(current + 1);
            bar.style.width = (((current + 1) / total) * 100) + '%';
            backBtn.disabled = current === 0;
            var stepSlug = steps[current].querySelector('[data-pf-answer]').getAttribute('data-pf-answer');
            nextBtn.disabled = !answers[stepSlug];
            nextBtn.textContent = (current === total - 1) ? '<?php esc_attr_e( 'مشاهده پیشنهادها', 'fartak' ); ?>' : '<?php esc_attr_e( 'بعدی', 'fartak' ); ?>';
        }

        // Option click handler
        root.addEventListener('click', function(e){
            var opt = e.target.closest('[data-pf-answer]');
            if (!opt) return;
            var slug = opt.getAttribute('data-pf-answer');
            var value = opt.getAttribute('data-pf-value');
            answers[slug] = value;
            var panel = opt.closest('.fartak-pf__panel');
            panel.querySelectorAll('.fartak-pf__option').forEach(function(o){ o.classList.remove('selected'); });
            opt.classList.add('selected');
            nextBtn.disabled = false;
        });

        nextBtn.addEventListener('click', function(){
            if (current < total - 1) { current++; update(); return; }
            // submit
            nextBtn.disabled = true;
            nextBtn.textContent = '<?php esc_attr_e( 'در حال جستجو…', 'fartak' ); ?>';
            var data = new FormData();
            data.append('action', 'fartak_product_finder_search');
            data.append('nonce', FARTAK.nonce);
            data.append('usage', answers.usage || 'general');
            data.append('budget', answers.budget || 'medium');
            data.append('priority', answers.priority || 'performance');
            fetch(FARTAK.ajax, {method:'POST', body:data})
                .then(function(r){return r.json();})
                .then(function(res){
                    if (!res || !res.success) { nextBtn.disabled = false; nextBtn.textContent = '<?php esc_attr_e( 'بعدی', 'fartak' ); ?>'; return; }
                    grid.innerHTML = res.data.html;
                    results.style.display = 'block';
                    results.scrollIntoView({behavior:'smooth', block:'start'});
                }).catch(function(){ nextBtn.disabled = false; nextBtn.textContent = '<?php esc_attr_e( 'بعدی', 'fartak' ); ?>'; });
        });

        backBtn.addEventListener('click', function(){
            if (current > 0) { current--; update(); }
        });

        var reset = root.querySelector('[data-pf-reset]');
        if (reset) reset.addEventListener('click', function(){
            current = 0;
            answers = {};
            results.style.display = 'none';
            root.querySelectorAll('.fartak-pf__option').forEach(function(o){ o.classList.remove('selected'); });
            update();
            root.scrollIntoView({behavior:'smooth', block:'start'});
        });

        update();
    })();
    </script>
    <?php
}
