<?php


defined('ABSPATH') ?: exit();


if (!trait_exists('PBS_Gallery_Images')) :

    trait PBS_Gallery_Images
    {

        /**
         * Renders custom per bundle gallery images, or standard product gallery images
         * Also display normal product single gallery layout on mobile
         *
         * @return void
         */
        public static function pbs_render_gallery_images()
        {

            global $pbs_gallery, $pbs_product_id, $pbs_post_id, $gall_img_ids, $pbs_is_default_gall;

            // defualt gallery flag
            $is_default = false;

            $gall_img_ids = [];

            // if $pbs_gallery is null, get default product gallery images
            if (is_null($pbs_gallery)) {
                $product        = wc_get_product($pbs_product_id);
                $gall_img_ids   = $product->get_gallery_image_ids();
                $gall_img_ids[] = $product->get_image_id();
                $is_default     = true;
            }

            // if $pbs_gallery is true, get default product gallery images
            if ($pbs_gallery == 'true') {
                $product        = wc_get_product($pbs_product_id);
                $gall_img_ids   = $product->get_gallery_image_ids();
                $gall_img_ids[] = $product->get_image_id();
                $is_default     = true;
            } else {

                // get bundle custom gallery images
                $is_default   = false;
                $gall_img_ids = get_post_meta($pbs_post_id, '_gall_images', true);

                // if no custom bundle gallery images, get default product gallery images
                if (empty($gall_img_ids)) {
                    $product        = wc_get_product($pbs_product_id);
                    $gall_img_ids   = $product->get_gallery_image_ids();
                    $gall_img_ids[] = $product->get_image_id();
                    $is_default     = true;
                }
            }

            asort($gall_img_ids);

            $pbs_is_default_gall = $is_default;

            // render PBS gallery
            self::pbs_gallery_images_render_insert($gall_img_ids, $is_default);
        }

        /**
         * Render/insert gallery images as needed
         *
         * @param array $gall_img_ids - array of gallery image ids
         * @param bool $is_dedault - whether or not $gall_img_ids represents default product gallery image ids
         * @return void
         */
        public static function pbs_gallery_images_render_insert($gall_img_ids, $is_default)
        {

            global $pbs_product_id;

            // enqueue photoswipe scripts
            wp_enqueue_script('pbs_photoswipe', get_theme_file_uri('assets/vendor/photoswipe/photoswipe.min.js'), [], PHP_INT_MAX, false);
            wp_enqueue_script('pbs_photoswipe_defualt_ui', get_theme_file_uri('assets/vendor/photoswipe/photoswipe-ui-default.min.js'), [], PHP_INT_MAX, false);
            wp_enqueue_style('pbs_photoswipe', get_theme_file_uri('assets/vendor/photoswipe/photoswipe.min.css'));
            wp_enqueue_style('pbs_photoswipe_default_skin', get_theme_file_uri('assets/vendor/photoswipe/default-skin/default-skin.min.css'));

            // filter ids so we don't have duplicats
            $attachments = array_filter($gall_img_ids);

            // loop to retrieve attachment src
            if (!empty($attachments)) :

                // build array of attachment html src
                $attachments_html = [];

                foreach ($attachments as $attachment_id) :
                    $attachments_html[] = wp_get_attachment_image($attachment_id, 'full');
                endforeach; ?>

                <!-- holds attachments and status for ref in JS -->
                <input type="hidden" id="pbs_attachments" name="pbs_attachments" data-is-default="<?php echo $is_default; ?>" value="<?php echo base64_encode(json_encode($attachments_html)); ?>">

                <?php

                // if is mobile view
                if (wp_is_mobile()) : ?>

                    <?php if (!$is_default) : ?>

                        <style>
                            #pbs-alt-gallery {
                                display: block;
                            }

                            .owl-nav-fade.owl-nav-inner .owl-next {
                                margin-right: -1rem;
                                opacity: 1;
                            }

                            .owl-nav-fade.owl-nav-inner .owl-prev {
                                margin-left: -1rem;
                                opacity: 1;
                            }
                        </style>

                        <script id="wtf2">
                            // completely remove original gallery html after hiding, just in case
                            $ = jQuery;

                            $(document).ready(function() {

                                // console.log('the doc is ready');

                                // gallery thumbs on click
                                $('.product-thumb').click(function(e) {
                                    e.preventDefault();

                                    $('.product-thumb').removeClass('active');
                                    $(this).addClass('active');

                                    var target = $(this).data('target');
                                    var tg_pos = $(target).position().left;

                                    $('#pbs-alt-gallery').find('.owl-stage').css('transform', 'translate3d(-' + tg_pos + 'px, 0px, 0px)');

                                    $('.owl-item').removeClass('active');
                                    $(target).addClass('active');

                                });
                            });
                        </script>

                        <?php
                        // render alternative mobile gallery
                        self::pbs_render_alt_mobile_gallery($attachments);
                        ?>

                    <?php endif;

                endif;

                // if is not mobile view
                if (!wp_is_mobile()) : ?>

                    <style>
                        .row.pbs_gall_img_row {
                            padding-top: 1.5rem;
                            padding-right: 1.5rem;
                        }

                        .product-gallery.row .product-image-full {
                            right: 1rem;
                        }

                        .product-gallery .product-image-full {
                            position: absolute;
                            width: auto;
                        }

                        .product-image-full {
                            padding: 1rem;
                            right: 1rem;
                            bottom: 1rem;
                            border: 0;
                            color: #999;
                            background: transparent;
                            font-size: calc(2rem * var(--rio-typo-ratio, 1));
                            line-height: 1;
                            opacity: 0;
                            transition: opacity 0.3s, color 0.3s;
                            z-index: 1;
                            cursor: pointer;
                        }

                        .pswp__top-bar {
                            top: 30px !important;
                            opacity: 1 !important;
                        }

                        .product-image-full:hover {
                            color: var(--rio-primary-color, #27c);
                        }

                        .pswp__item {
                            top: 30px !important;
                        }

                        .pswp__bg {
                            opacity: 0.75 !important;
                        }

                        button.product-image-full.d-icon-zoom:before {
                            content: "\e94b";
                        }
                    </style>

                    <script>
                        $ = jQuery;

                        $(document).ready(function() {
                            $('.woocommerce-product-gallery__image').hover(function() {
                                // in
                                $(this).find('.d-icon-zoom').css('opacity', '1');
                            }, function() {
                                // out
                                $(this).find('.d-icon-zoom').css('opacity', '0');
                            });
                        });
                    </script>

            <?php

                    self::pbs_render_alt_non_mobile_gallery($gall_img_ids);

                endif;
            endif;
        }

        /**
         * Attempts to render standard mobile gallery for non-standard product gallery images
         *
         * @param array $gall_img_ids
         * @return void
         */
        public static function pbs_render_alt_mobile_gallery($gall_img_ids)
        { ?>
            <div id="pbs-alt-gallery" class="woocommerce-product-gallery woocommerce-product-gallery--with-images woocommerce-product-gallery--columns-4 images" data-columns="4" style="opacity: 1;">
                <figure class="woocommerce-product-gallery__wrapper product-gallery pg-vertical">

                    <!-- gallery main image cont -->
                    <div class="product-single-carousel owl-carousel owl-theme owl-nav-inner owl-nav-fade owl-loaded owl-drag">
                        <div class="owl-stage-outer">
                            <div class="owl-stage">

                                <?php
                                $counter = 0;

                                foreach ($gall_img_ids as $img_id) : ?>

                                    <?php
                                    $medium_src = wp_get_attachment_image_src($img_id, 'medium')[0];
                                    $lrg_src    = wp_get_attachment_image_src($img_id, 'full')[0];
                                    $src_main   = wp_get_attachment_image($img_id, 'full');
                                    ?>

                                    <?php if ($counter === 0) : ?>
                                        <div id="owl-item-<?php echo $img_id; ?>" class="owl-item active">
                                            <div data-thumb="<?php echo $medium_src; ?>">
                                                <a href="<?php echo $lrg_src; ?>">
                                                    <?php echo $src_main; ?>
                                                </a>
                                            </div>
                                        </div>
                                    <?php else : ?>
                                        <div id="owl-item-<?php echo $img_id; ?>" class="owl-item">
                                            <div data-thumb="<?php echo $medium_src; ?>">
                                                <a href="<?php echo $lrg_src; ?>">
                                                    <?php echo $src_main; ?>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <?php $counter++; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- gallery thumbnails cont -->
                    <div class="product-thumbs-wrap">
                        <div class="product-thumbs row gutter-no">

                            <?php $tcounter = 0 ?>

                            <?php foreach ($gall_img_ids as $img_id) : ?>

                                <?php
                                $thumb_src = wp_get_attachment_image_src($img_id, 'thumbnail')[0];
                                ?>

                                <?php if ($tcounter === 0) : ?>
                                    <div class="product-thumb active" data-target="#owl-item-<?php echo $img_id; ?>">
                                        <img alt="<?php echo get_the_title($img_id); ?>" src="<?php echo $thumb_src; ?>" width="150" height="150">
                                    </div>
                                <?php else : ?>
                                    <div class="product-thumb" data-target="#owl-item-<?php echo $img_id; ?>">
                                        <img alt="<?php echo get_the_title($img_id); ?>" src="<?php echo $thumb_src; ?>" width="150" height="150">
                                    </div>
                                <?php endif; ?>

                                <?php $tcounter++; ?>
                            <?php endforeach; ?>

                        </div>
                        <button class="thumb-up fas fa-chevron-left disabled"></button>
                        <button class="thumb-down fas fa-chevron-right disabled"></button>
                    </div>
                </figure>


            </div>
        <?php }

        /**
         * Attempts to render gallery as per product single template
         *
         * @param array $gall_img_ids
         * @return void
         */
        public static function pbs_render_alt_non_mobile_gallery($gall_img_ids)
        {
            global $pbs_product_id, $pbs_gallery, $pbs_is_default_gall;

            $product = wc_get_product($pbs_product_id);

            do_action('riode_single_product_before_image');

            $columns           = apply_filters('woocommerce_product_thumbnails_columns', 4);
            $wrapper_classes   = apply_filters(
                'woocommerce_single_product_image_gallery_classes',
                array(
                    'woocommerce-product-gallery',
                    'woocommerce-product-gallery--' . ($product->get_image_id() ? 'with-images' : 'without-images'),
                    'woocommerce-product-gallery--columns-' . absint($columns),
                    'images',
                )
            );
        ?>
            <div id="pbs_custom_std_gallery_<?php echo $pbs_product_id ?>" class="<?php echo esc_attr(implode(' ', array_map('sanitize_html_class', $wrapper_classes))); ?> pbs_custom_std_gallery" data-columns="<?php echo esc_attr($columns); ?>" style="display: block;">

                <?php do_action('riode_before_wc_gallery_figure'); ?>

                <figure class="woocommerce-product-gallery__wrapper product-gallery row pg-custom grid-gallery row cols-lg-2 cols-md-2 cols-sm-1 cols-1">

                    <?php
                    do_action('riode_before_product_gallery');
                    ?>

                    <?php foreach ($gall_img_ids as $img_id) :

                        $lrg_src    = wp_get_attachment_image_src($img_id, 'full')[0];
                        $lrg_width  = wp_get_attachment_image_src($img_id, 'full')[1];
                        $lrg_height = wp_get_attachment_image_src($img_id, 'full')[2];
                        $medium_src = wp_get_attachment_image_src($img_id, 'large')[0];
                        $full_src   = wp_get_attachment_image($img_id, 'full', false, ['data-large_image' => $lrg_src, 'data-large_image_width' => $lrg_width, 'data-large_image_height' => $lrg_height]);

                    ?>

                        <div data-thumb="<?php echo $medium_src; ?>" class="woocommerce-product-gallery__image" style="padding: var(--rio-gutter-md);">
                            <a href="<?php echo $lrg_src; ?>" style="position: relative; overflow: hidden;">
                                <?php echo $full_src; ?>
                            </a>
                        </div>
                    <?php endforeach; ?>

                    <?php
                    do_action('riode_after_product_gallery');
                    ?>
                </figure>

                <?php do_action('riode_after_wc_gallery_figure'); ?>

            </div>

            <!-- photoswipe -->
            <div id="pbs_photoswipe_cont_<?php echo $pbs_product_id; ?>" class="pswp pswp--supports-fs pswp--css_animation pswp--svg pswp--animated-in pswp--zoom-allowed pswp-- pbs_photoswipe_cont" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="pswp__bg"></div>
                <div class="pswp__scroll-wrap">
                    <div class="pswp__container">
                        <div class="pswp__item"></div>
                        <div class="pswp__item"></div>
                        <div class="pswp__item"></div>
                    </div>
                    <div class="pswp__ui pswp__ui--hidden">
                        <div class="pswp__top-bar">
                            <div class="pswp__counter"></div>
                            <button class="pswp__button pswp__button--close" aria-label="Close (Esc)"></button>
                            <button class="pswp__button pswp__button--share" aria-label="Share"></button>
                            <button class="pswp__button pswp__button--fs" aria-label="Toggle fullscreen"></button>
                            <button class="pswp__button pswp__button--zoom" aria-label="Zoom in/out"></button>
                            <div class="pswp__preloader">
                                <div class="pswp__preloader__icn">
                                    <div class="pswp__preloader__cut">
                                        <div class="pswp__preloader__donut"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                            <div class="pswp__share-tooltip"></div>
                        </div>
                        <button class="pswp__button pswp__button--arrow--left" aria-label="Previous (arrow left)"></button>
                        <button class="pswp__button pswp__button--arrow--right" aria-label="Next (arrow right)"></button>
                        <div class="pswp__caption">
                            <div class="pswp__caption__center"></div>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            // render linked product galleries if default product gallery used
            if ($pbs_is_default_gall) :
                self::pbs_generate_linked_product_gallery_html();
            endif;
        }

        /**
         * Attempts to generate linked product image gallery HTML 
         *
         * @return void
         */
        public static function pbs_generate_linked_product_gallery_html()
        {

            global $pbs_product_id;

            // get linked products
            $linked_prod_data = get_option('plgfymao_all_rulesplgfyplv');

            // get current lang (default en if pll not present)
            $current_lang = function_exists('pll_get_post_language') ? pll_get_post_language($pbs_product_id) : 'en';

            // holds linked product ids
            $linked_ids = [];

            // holds base linked ids
            $linked_ids_raw = '';

            // loop to check for matching grouped products
            if ($linked_prod_data && !empty($linked_prod_data)) :

                foreach ($linked_prod_data as $index => $data) :
                    if (in_array($pbs_product_id, $data['apllied_on_ids'])) :
                        $linked_ids_raw = $data['apllied_on_ids'];
                    endif;
                endforeach;

            endif;

            // loop through $linked_ids_raw and make sure product ids match current language
            foreach ($linked_ids_raw as $l_id) :
                $linked_ids[] = pll_get_post($l_id, $current_lang);
            endforeach;

            // if linked ids present, generate gallery and photoswipe html for each
            if (is_array($linked_ids) && !empty($linked_ids)) :

                foreach ($linked_ids as $l_id) :

                    // retrieve product and gallery image ids
                    $product        = wc_get_product($l_id);
                    $gall_img_ids   = $product->get_gallery_image_ids();
                    $gall_img_ids[] = $product->get_image_id();

                    do_action('riode_single_product_before_image');

                    $columns           = apply_filters('woocommerce_product_thumbnails_columns', 4);
                    $wrapper_classes   = apply_filters(
                        'woocommerce_single_product_image_gallery_classes',
                        array(
                            'woocommerce-product-gallery',
                            'woocommerce-product-gallery--' . ($product->get_image_id() ? 'with-images' : 'without-images'),
                            'woocommerce-product-gallery--columns-' . absint($columns),
                            'images',
                        )
                    );
            ?>
                    <div id="pbs_custom_std_gallery_<?php echo $l_id ?>" class="<?php echo esc_attr(implode(' ', array_map('sanitize_html_class', $wrapper_classes))); ?> pbs_custom_std_gallery" data-columns="<?php echo esc_attr($columns); ?>" style="display: none;">

                        <?php do_action('riode_before_wc_gallery_figure'); ?>

                        <figure class="woocommerce-product-gallery__wrapper product-gallery row pg-custom grid-gallery row cols-lg-2 cols-md-2 cols-sm-1 cols-1">

                            <?php
                            do_action('riode_before_product_gallery');
                            ?>

                            <?php foreach ($gall_img_ids as $img_id) :

                                $lrg_src    = wp_get_attachment_image_src($img_id, 'full')[0];
                                $lrg_width  = wp_get_attachment_image_src($img_id, 'full')[1];
                                $lrg_height = wp_get_attachment_image_src($img_id, 'full')[2];
                                $medium_src = wp_get_attachment_image_src($img_id, 'large')[0];
                                $full_src   = wp_get_attachment_image($img_id, 'full', false, ['data-large_image' => $lrg_src, 'data-large_image_width' => $lrg_width, 'data-large_image_height' => $lrg_height]);

                            ?>

                                <div data-thumb="<?php echo $medium_src; ?>" class="woocommerce-product-gallery__image" style="padding: var(--rio-gutter-md);">
                                    <a href="<?php echo $lrg_src; ?>" style="position: relative; overflow: hidden;">
                                        <?php echo $full_src; ?>
                                    </a>
                                    <button class="product-image-full d-icon-zoom"></button>
                                </div>
                            <?php endforeach; ?>

                            <?php
                            do_action('riode_after_product_gallery');
                            ?>
                        </figure>

                        <?php do_action('riode_after_wc_gallery_figure'); ?>

                    </div>

                    <!-- photoswipe -->
                    <div id="pbs_photoswipe_cont_<?php echo $l_id; ?>" class="pswp pswp--supports-fs pswp--css_animation pswp--svg pswp--animated-in pswp--zoom-allowed pswp--has_mouse pbs_photoswipe_cont" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="pswp__bg"></div>
                        <div class="pswp__scroll-wrap">
                            <div class="pswp__container">
                                <div class="pswp__item"></div>
                                <div class="pswp__item"></div>
                                <div class="pswp__item"></div>
                            </div>
                            <div class="pswp__ui pswp__ui--hidden">
                                <div class="pswp__top-bar">
                                    <div class="pswp__counter"></div>
                                    <button class="pswp__button pswp__button--close" aria-label="Close (Esc)"></button>
                                    <button class="pswp__button pswp__button--share" aria-label="Share"></button>
                                    <button class="pswp__button pswp__button--fs" aria-label="Toggle fullscreen"></button>
                                    <button class="pswp__button pswp__button--zoom" aria-label="Zoom in/out"></button>
                                    <div class="pswp__preloader">
                                        <div class="pswp__preloader__icn">
                                            <div class="pswp__preloader__cut">
                                                <div class="pswp__preloader__donut"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                                    <div class="pswp__share-tooltip"></div>
                                </div>
                                <button class="pswp__button pswp__button--arrow--left" aria-label="Previous (arrow left)"></button>
                                <button class="pswp__button pswp__button--arrow--right" aria-label="Next (arrow right)"></button>
                                <div class="pswp__caption">
                                    <div class="pswp__caption__center"></div>
                                </div>
                            </div>
                        </div>
                    </div>

<?php endforeach;

            endif;
        }
    }

endif;
