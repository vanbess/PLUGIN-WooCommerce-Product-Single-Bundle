<?php

defined('ABSPATH') ?: exit();

if (!class_exists('PBS_Bundle')) {

    // include shortcode trait
    require_once __DIR__ . '/traits/trait-add-pll-support.php';
    require_once __DIR__ . '/traits/trait-add-url-order-note.php';
    require_once __DIR__ . '/traits/trait-apply-reg-price-cart.php';
    require_once __DIR__ . '/traits/trait-apply-reg-price-mini-cart.php';
    require_once __DIR__ . '/traits/trait-atc-multiple.php';
    require_once __DIR__ . '/traits/trait-calc-cart-fees.php';
    require_once __DIR__ . '/traits/trait-cart-updated.php';
    require_once __DIR__ . '/traits/trait-shortcode-main.php';
    require_once __DIR__ . '/traits/trait-render-bundle.php';
    require_once __DIR__ . '/traits/trait-render-size-chart.php';
    require_once __DIR__ . '/traits/trait-remove-co-coupons.php';
    require_once __DIR__ . '/traits/trait-reset-tracking.php';
    require_once __DIR__ . '/traits/trait-update-clicks.php';
    require_once __DIR__ . '/traits/trait-update-impressions-action-scheduler.php';
    require_once __DIR__ . '/traits/trait-update-tracking-ty-page.php';
    require_once __DIR__ . '/traits/trait-gallery-images.php';
    require_once __DIR__ . '/traits/trait-generate-return-bun-total.php';

    class PBS_Bundle
    {

        // Traits
        use PBS_Shortcode,
            PBS_Render_Bundles,
            PBS_Add_URL_Order_Note,
            PBS_Apply_Regular_Price_Cart,
            PBS_Apply_Regular_Price_Mini_Cart,
            PBS_Add_To_Cart_Multiple,
            PBS_Calculate_Cart_Fees,
            PBS_Cart_Updated,
            PBS_Remove_CO_Coupons,
            PBS_Reset_Tracking,
            PBS_Update_Clicks,
            PBS_Update_Impressions_Action_Scheduler,
            PBS_Update_Tracking_TY_Page,
            PBS_Render_Size_Chart,
            PBS_Gallery_Images,
            PBS_Add_PLL_Support,
            PBS_Generate_Return_Bun_Total;

        /**
         * Constructor
         */
        public function __construct()
        {

            // generate and return accurate bundle totals on dropdown change/swatch click
            add_action('wp_ajax_nopriv_pbs_generate_return_bun_total', [__CLASS__, 'pbs_generate_return_bun_total']);
            add_action('wp_ajax_pbs_generate_return_bun_total', [__CLASS__, 'pbs_generate_return_bun_total']);

            // init shortcodes
            self::pbs_shortcode();

            // DEBUG
            // add_action('wp_footer', function(){
            //     echo '<pre>';
            //     print_r($_SERVER);
            //     echo '</pre>';
            // });

            // hover image and stock js and other misc js functionality
            add_action('wp_footer', function () {

                global $post;

                if ($post->post_type === 'offer' || $post->post_type === 'landing') {
                    if (is_int(stripos($post->post_content, '[pp_bundle'))) {
                        self::pbs_regional_sizes();
                        self::pbs_pkg_variations_js();
                        self::pbs_generate_return_bun_total_js();
                    }
                }
            });

            // init load gallery images on swatch click
            add_action('wp_ajax_pbs_swatch_gallery_fetch', [__CLASS__, 'pbs_swatch_gallery_fetch']);
            add_action('wp_ajax_nopriv_pbs_swatch_gallery_fetch', [__CLASS__, 'pbs_swatch_gallery_fetch']);

            // load CSS & JS fixes, Photoswipe et al
            add_action('wp_enqueue_scripts', [__CLASS__, 'pbs_css_js'], PHP_INT_MAX);

            // load product_single shortcode css & js fixes and var selectors js
            add_action('wp_footer', [__CLASS__, 'pbs_js_fixes']);
            add_action('wp_footer', [__CLASS__, 'pbs_css_fixes']);

            // add polylang support for bundle_dropdown post type
            add_action('init', [__CLASS__, 'pbs_add_pll_support']);

            // reset tracking bulk action
            add_filter('bulk_actions-edit-bundle_dropdown', [__CLASS__, 'pbs_reset_tracking_bulk_action']);

            // process tracking bulk action submission
            add_filter('handle_bulk_actions-edit-bundle_dropdown', [__CLASS__, 'pbs_handle_reset_tracking_bulk_action'], 10, 3);

            // update clicks/add bundle products to session data
            add_action('wp_footer', [__CLASS__, 'pbs_update_clicks']);
            add_action('wp_ajax_nopriv_pbs_update_clicks_ajax', [__CLASS__, 'pbs_update_clicks_ajax']);
            add_action('wp_ajax_pbs_update_clicks_ajax', [__CLASS__, 'pbs_update_clicks_ajax']);
            add_action('wp_ajax_nopriv_pbs_add_products_to_session', [__CLASS__, 'pbs_add_products_to_session']);
            add_action('wp_ajax_pbs_add_products_to_session', [__CLASS__, 'pbs_add_products_to_session']);

            // update tracking on thank you page (conversions)
            add_action('woocommerce_thankyou', [__CLASS__, 'pbs_update_tracking_ty_page']);

            add_action('woocommerce_before_single_product_summary', function () {

                // retrieve product shortcode parent post id and post type
                global $pbs_post_id;
                $ptype = get_post_type($pbs_post_id);

                if ($ptype === 'offer' || $ptype === 'landing') {
                    self::pbs_render_gallery_images();
                }
            }, PHP_INT_MAX);

            // init impressions tracking periodic update via Action Scheduler
            self::pbs_update_impressions_action_scheduler();

            // display size chart if present
            add_action('woocommerce_single_product_summary', function () {
                self::pbs_render_size_chart();
            });

            // hook bundle data to product single IF post type is offer or landing
            add_action('woocommerce_single_product_summary', function () {

                // retrieve product shortcode parent post id and post type
                global $pbs_post_id;
                $ptype = get_post_type($pbs_post_id);

                // render bundles
                if ($ptype === 'offer' || $ptype === 'landing') {
                    self::pbs_render_bundles();
                }
            });


            // insert add to cart button   
            add_action('woocommerce_share', function () { ?>
                <button id="pbs_bundle_atc" class="button button-primary button-large">
                    <?php pll_e('Buy Now!'); ?>
                </button>
            <?php });

            // remove non relevant coupons from cart
            add_action('woocommerce_before_calculate_totals', [__CLASS__, 'pbs_remove_co_coupons']);

            // remove coupon messages from cart
            add_action('wp_footer', [__CLASS__, 'pbs_remove_co_coupon_notices']);

            // add to cart multiple
            add_action('wp_ajax_nopriv_pbs_add_to_cart_multiple', [__CLASS__, 'pbs_add_to_cart_multiple']);
            add_action('wp_ajax_pbs_add_to_cart_multiple', [__CLASS__, 'pbs_add_to_cart_multiple']);

            // apply regular pricing to cart
            add_action('woocommerce_before_calculate_totals', [__CLASS__, 'pbs_apply_regular_price_cart']);

            // apply regular pricing to mini cart
            add_filter('woocommerce_cart_item_price', [__CLASS__, 'pbs_apply_regular_price_mini_cart'], 30, 3);

            // remove discount if cart qtys updated
            add_action('woocommerce_update_cart_action_cart_updated', [__CLASS__, 'pbs_cart_updated'], 20, 1);

            // calculate discount fees and apply to cart
            add_action('woocommerce_cart_calculate_fees', [__CLASS__, 'pbs_calculate_cart_fees'], PHP_INT_MAX);

            // action to add referer to order note
            add_action('woocommerce_order_status_processing', [__CLASS__, 'pbs_add_url_order_note'], 10, 1);

            add_filter('add_to_cart_redirect', function () {
                global $woocommerce;
                $checkout_url = wc_get_checkout_url();

                $new_co_url = esc_url(add_query_arg('is_pbs_bundle', 'yes', $checkout_url));

                return $new_co_url;
            });
        }

        /**
         * CSS and JS scripts IF PS Bundle shortcode present to avoid conflicts with MWC
         *
         * @return void
         * @todo - check if photoswipe is really needed
         */
        public static function pbs_css_js()
        {

            global $post;

            if ($post->post_type === 'offer' || $post->post_type === 'landing') {

                // only load if product single bundle shortcode is present
                if (is_int(stripos($post->post_content, '[pp_bundle'))) {

                    wp_enqueue_style('pbs-front', PBS_Bundle_URL . 'inc/class/front/assets/css/front.css', [], time(), 'all');
                    wp_enqueue_script('pbs-front', PBS_Bundle_URL . 'inc/class/front/assets/js/front.js', ['jquery'], time(), true);

                    global $woocommerce;

                    // $cart_url     = '/cart/?is_pbs_bundle=true';
                    // $checkout_url = '/checkout/?is_pbs_bundle=true';
                    $cart_url     = '/cart/';
                    $checkout_url = '/checkout/';

                    if (!empty($woocommerce)) {
                        // $cart_url     = wc_get_cart_url() . '?is_pbs_bundle=true';
                        // $checkout_url = wc_get_checkout_url() . '?is_pbs_bundle=true';
                        $cart_url     = wc_get_cart_url();
                        $checkout_url = wc_get_checkout_url();
                    }

                    wp_localize_script(
                        'pbs-front',
                        'bd_infos',
                        array(
                            'ajax_url'     => admin_url('admin-ajax.php'),
                            'home_url'     => home_url(),
                            'cart_url'     => $cart_url,
                            'checkout_url' => $checkout_url
                        )
                    );
                }
            }
        }

      
    }

    // init class
    new PBS_Bundle;
} // end class_exists check

?>