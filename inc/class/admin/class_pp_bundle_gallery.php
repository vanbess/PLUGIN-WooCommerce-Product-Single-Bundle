<?php

defined('ABSPATH') ?: exit();

if (!class_exists('PBS_Bundle_Gallery')) :

    class PBS_Bundle_Gallery extends WC_Meta_Box_Product_Images {

        public function __construct() {

            // add metabox
            add_action('admin_init', array($this, 'add_prod_gallery_metabox'));

            // gallery js
            add_action('admin_footer', array($this, 'gallery_imgs_js'));

            // save gallery image
            add_action('save_post_landing', array($this, 'save_pbs_gall_imgs'), 10, 2);
            add_action('save_post_offer', array($this, 'save_pbs_gall_imgs'), 10, 2);
        }

        /**
         * Add product gallery metabox to Landing and Offers pages
         */
        public function add_prod_gallery_metabox() {
            add_meta_box(
                "pbs_gall_images",
                __('PBS Gallery Images', 'woocommerce'),
                array($this, "output"),
                "landing",
                "side",
                "low"
            );
            add_meta_box(
                "pbs_gall_images",
                __('PBS Gallery Images', 'woocommerce'),
                array($this, "output"),
                "offer",
                "side",
                "low"
            );
        }


        /**
         * Output the metabox
         */
        public static function output($post) { ?>
            <div id="product_images_container">
                <ul class="product_images">
                    <?php
                    // $product_image_gallery = $product_object->get_gallery_image_ids('edit');

                    $bundle_img_gallery  = get_post_meta($post->ID, '_gall_images', true);
                    $attachments         = array_filter($bundle_img_gallery);
                    $update_meta         = false;
                    $updated_gallery_ids = array();

                    if (!empty($attachments)) {
                        foreach ($attachments as $attachment_id) {
                            $attachment = wp_get_attachment_image($attachment_id, 'thumbnail');

                            // if attachment is empty skip.
                            if (empty($attachment)) {
                                $update_meta = true;
                                continue;
                            }
                    ?>
                            <li class="image" data-attachment_id="<?php echo esc_attr($attachment_id); ?>">
                                <?php echo $attachment; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
                                ?>
                                <ul class="actions">
                                    <li>
                                        <a href="#" class="delete tips" data-tip="<?php esc_attr_e('Delete image', 'woocommerce'); ?>">
                                            X
                                        </a>
                                    </li>
                                </ul>

                            </li>
                    <?php

                            // rebuild ids to be saved.
                            $updated_gallery_ids[] = $attachment_id;
                        }

                        // need to update product meta to set new gallery ids
                        if ($update_meta) {
                            update_post_meta($post->ID, '_gall_images', implode(',', $updated_gallery_ids));
                        }
                    }
                    ?>
                </ul>

                <input type="hidden" id="_gall_images" name="_gall_images" value="<?php echo esc_attr(implode(',', $updated_gallery_ids)); ?>" />

            </div>
            <p class="add_product_images hide-if-no-js">
                <a href="#" data-choose="<?php esc_attr_e('Add images to PBS gallery', 'woocommerce'); ?>" data-update="<?php esc_attr_e('Add to PBS gallery', 'woocommerce'); ?>" data-delete="<?php esc_attr_e('Delete image', 'woocommerce'); ?>" data-text="X"><?php esc_html_e('Add PBS gallery images', 'woocommerce'); ?></a>
            </p>

            <style>
                ul.product_images.ui-sortable {
                    position: relative;
                    left: 3px;
                }

                ul.product_images.ui-sortable>li {
                    width: 45%;
                    display: inline-block;
                    margin-right: 4%;
                    position: relative;
                }

                ul.product_images.ui-sortable>li>img {
                    width: 100%;
                    border: 2px solid #ccc;
                    height: auto;
                }

                ul.actions {
                    position: absolute;
                    top: 4px;
                    right: 0;
                    background: #d63638;
                    padding: 2px 8px;
                    border-radius: 50%;
                    box-shadow: 0px 2px 2px #0000002e;
                }

                ul.actions>li {
                    margin-bottom: 0;
                }

                ul.actions>li>a {
                    text-decoration: none;
                    font-size: 11px;
                    color: white;
                }
            </style>
        <?php }

        /**
         * Save gallery images
         *
         * @param int $post_id
         * @param obj $post
         * @return void
         */
        public static function save_pbs_gall_imgs($post_id, $post) {

            $attachment_ids = isset($_POST['_gall_images']) ? array_filter(explode(',', wc_clean($_POST['_gall_images']))) : array();

            if (!empty($attachment_ids)) :
                update_post_meta($post_id, '_gall_images', $attachment_ids);
            else:
                delete_post_meta($post_id, '_gall_images');
            endif;
        }

        /**
         * JS which handles upload/deletion of gallery images
         *
         * @return void
         */
        public static function gallery_imgs_js() { ?>

            <script id="pbs_gallery_imgs_js">
                $ = jQuery;

                // Product gallery file uploads.
                var product_gallery_frame;
                var $image_gallery_ids = $('#_gall_images');
                var $product_images = $('#product_images_container').find(
                    'ul.product_images'
                );

                $('.add_product_images').on('click', 'a', function(event) {
                    var $el = $(this);

                    event.preventDefault();

                    // If the media frame already exists, reopen it.
                    if (product_gallery_frame) {
                        product_gallery_frame.open();
                        return;
                    }

                    // Create the media frame.
                    product_gallery_frame = wp.media.frames.product_gallery = wp.media({
                        // Set the title of the modal.
                        title: $el.data('choose'),
                        button: {
                            text: $el.data('update'),
                        },
                        states: [
                            new wp.media.controller.Library({
                                title: $el.data('choose'),
                                filterable: 'all',
                                multiple: true,
                            }),
                        ],
                    });

                    // When an image is selected, run a callback.
                    product_gallery_frame.on('select', function() {
                        var selection = product_gallery_frame.state().get('selection');
                        var attachment_ids = $image_gallery_ids.val();

                        selection.map(function(attachment) {
                            attachment = attachment.toJSON();

                            if (attachment.id) {
                                attachment_ids = attachment_ids ?
                                    attachment_ids + ',' + attachment.id :
                                    attachment.id;
                                var attachment_image =
                                    attachment.sizes && attachment.sizes.thumbnail ?
                                    attachment.sizes.thumbnail.url :
                                    attachment.url;

                                $product_images.append(
                                    '<li class="image" data-attachment_id="' +
                                    attachment.id +
                                    '"><img src="' +
                                    attachment_image +
                                    '" /><ul class="actions"><li><a href="#" class="delete" title="' +
                                    $el.data('delete') +
                                    '">' +
                                    $el.data('text') +
                                    '</a></li></ul></li>'
                                );
                            }
                        });

                        $image_gallery_ids.val(attachment_ids);
                    });

                    // Finally, open the modal.
                    product_gallery_frame.open();
                });

                // Image ordering.
                $product_images.sortable({
                    items: 'li.image',
                    cursor: 'move',
                    scrollSensitivity: 40,
                    forcePlaceholderSize: true,
                    forceHelperSize: false,
                    helper: 'clone',
                    opacity: 0.65,
                    placeholder: 'wc-metabox-sortable-placeholder',
                    start: function(event, ui) {
                        ui.item.css('background-color', '#f6f6f6');
                    },
                    stop: function(event, ui) {
                        ui.item.removeAttr('style');
                    },
                    update: function() {
                        var attachment_ids = '';

                        $('#product_images_container')
                            .find('ul li.image')
                            .css('cursor', 'default')
                            .each(function() {
                                var attachment_id = $(this).attr('data-attachment_id');
                                attachment_ids = attachment_ids + attachment_id + ',';
                            });

                        $image_gallery_ids.val(attachment_ids);
                    },
                });

                // Remove images.
                $('#product_images_container').on('click', 'a.delete', function() {
                    $(this).closest('li.image').remove();

                    var attachment_ids = '';

                    $('#product_images_container')
                        .find('ul li.image')
                        .css('cursor', 'default')
                        .each(function() {
                            var attachment_id = $(this).attr('data-attachment_id');
                            attachment_ids = attachment_ids + attachment_id + ',';
                        });

                    $image_gallery_ids.val(attachment_ids);

                    // Remove any lingering tooltips.
                    $('#tiptip_holder').removeAttr('style');
                    $('#tiptip_arrow').removeAttr('style');

                    return false;
                });
            </script>

<?php }
    }

endif;

new PBS_Bundle_Gallery;
