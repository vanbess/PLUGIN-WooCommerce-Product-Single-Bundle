<?php

defined('ABSPATH') ?: exit();

if (!trait_exists('PBS_Shortcode')) :

    trait PBS_Shortcode
    {

        /**
         * Register shortcode
         *
         * @return void
         */
        public static function pbs_shortcode()
        {
            add_shortcode('pp_bundle', [__TRAIT__, 'pbs_render_shortcode']);
        }

        /**
         * Attributes passed to shortcode
         * 
         * - product id
         * - bundle ids
         *
         * @param array $atts
         * @return void
         */
        public static function pbs_render_shortcode($atts)
        {

            global $post;

            // make bundle ids global so we can access them outside main shortcode
            global $pbs_bundle_ids;

            // make post id global so that we can retrieve the correct post id for checks on pages where shortcode is rendered
            global $pbs_post_id;
            $pbs_post_id = $post->ID;

            // make default bundle id global so that we can access it outside main shortcode
            global $pbs_default_id;
            $pbs_default_id = isset($atts['default']) ? $atts['default'] : null;

            // grab attributes
            global $pbs_product_id;
            $pbs_product_id = (int)$atts['product_id'];
            $pbs_bundle_ids = $atts['bundle_ids'];

            // check if gallery images are enabled
            global $pbs_gallery;
            $pbs_gallery = isset($atts['gallery']) ? $atts['gallery'] : null;

            // product shortcode
            echo do_shortcode('[product_page id="' . $pbs_product_id . '"]');
        }

        /**
         * JS fixes
         *
         * @return void
         */
        public static function pbs_js_fixes()
        {

            global $post;

            // only run on landings and offers pages
            if ($post->post_type === 'offer' || $post->post_type === 'landing') :

                // only load if product single bundle shortcode is present
                if (is_int(stripos($post->post_content, '[pp_bundle'))) : ?>

                    <script id="pbs_js_fixes">
                        $ = jQuery;

                        var pbs_std_gallery = $('.pbs_custom_std_gallery');

                        $('.woocommerce-product-gallery').each(function(index, element) {
                            var is_pbs_gall = $(this).hasClass('pbs_custom_std_gallery');

                            if (!is_pbs_gall) {
                                $(this).remove();
                            }
                        });

                        // set correct BS classes for product image and product summary containers
                        $('.product-single > div.col-md-6:first-child').append(pbs_std_gallery);

                        // remove unneeded elements
                        $('form.variations_form.cart, .post-meta, h2.post-title.page-title, .product_meta, .navplugify, table.variations, .social-icons, p.price, .product-divider, ul.nav.nav-tabs.tabs.wc-tabs, ul.breadcrumb.home-icon').remove();
                    </script>

                <?php

                endif;
            endif;
        }

        /**
         * CSS fixes
         *
         * @return void
         */
        public static function pbs_css_fixes()
        {

            global $post;

            // only run on landings and offers pages
            if ($post->post_type === 'offer' || $post->post_type === 'landing') : ?>

                <style>
                    section.related-posts,
                    .woocommerce-notices-wrapper {
                        display: none;
                    }

                    .elementor-element-3b6c7eb,
                    .elementor-element-a642997 {
                        padding-bottom: 0 !important;
                    }
                </style>

<?php endif;
        }
    }

endif;
