<?php

// exit if file is called directly
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('PP_Insert_Shortcode')) :

    class PP_Insert_Shortcode
    {

        public function __construct()
        {

            // media button to insert shortcode
            add_action('media_buttons', array($this, 'pp_add_media_button'), 15);

            // js and css to admin footer
            add_action('admin_footer', array($this, 'pp_admin_footer'));
        }

        /**
         * Add media button to insert shortcode
         * 
         * @since 1.0.6
         * 
         * @return void
         */
        public function pp_add_media_button()
        {
            // if pll_get_current_language is defined, get current language, else default to english
            $curr_lang = function_exists('pll_current_language') ? pll_current_language() : 'en';

            // get all product posts with language set to current language, returning ids only
            $args = array(
                'post_type'      => 'product',
                'posts_per_page' => -1,
                'lang'           => $curr_lang,
                'fields'         => 'ids'
            );

            // get posts
            $products = get_posts($args);

            // product names array
            $product_names = array();

            // if get_posts does not return an empty array, loop through returned product ids, get product name and id, and add to array
            if ($products = get_posts($args)) {
                foreach ($products as $pid) {
                    $product_names[] = array(
                        'label' => get_the_title($pid) . ' (ID: ' . $pid . ')',
                        'value' => $pid
                    );
                }
            }

            // get all bundle_dropdown post types for current language
            $args = array(
                'post_type'      => 'bundle_dropdown',
                'posts_per_page' => -1,
                'lang'           => $curr_lang,
                'fields'         => 'ids'
            );

            // get posts
            $bundles = get_posts($args);

            // product_id => bundle_ids array
            $bundle_ids = array();

            // if bundles, get post meta for each bundle (product_discount) and extract main product id, then add to product_id => bundle_ids array
            foreach ($bundles as $bundle_id) :

                // get bundle product discount
                $bundle_data = get_post_meta($bundle_id, 'product_discount', true);

                // get bundle type
                $bundle_type = $bundle_data['selValue'];

                // get bundle product id
                $product_id = $bundle_data["selValue_$bundle_type"]['post']['id'];

                // if product id is not empty, add to bundle_ids array
                if (!empty($product_id)) {
                    $bundle_ids[$product_id][] = [
                        'label' => get_the_title($bundle_id) . ' (ID: ' . $bundle_id . ')',
                        'value'   => $bundle_id
                    ];
                }

            endforeach;

            // parse bundle_ids array into json and add to hidden input
            $bundle_ids_json = json_encode($bundle_ids);
            echo "<input type='hidden' id='pp_bundle_ids_json' value='$bundle_ids_json'>";

            // if product names empty, bail
            if (empty($product_names)) {
                return;
            } ?>

            <!-- button -->
            <button onclick="showPopup()" class="thickbox button">
                <?php _e('<b style="font-weight: bold;">[PP]</b> Insert PP Bundle Shortcode', 'default'); ?>
            </button>

            <!-- popup overlay -->
            <div id="pp_bundle_popup_overlay" style="display:none;"></div>

            <!-- // popup -->
            <div id="pp_bundle_shortcode" style="display:none;">

                <h2 style="position: relative;"><?php _e('Per Product Bundle Shortcode Settings', 'default'); ?><span onclick="ppClose()">x</span></h2>

                <p><i><?php _e('Specify settings for PP shortcode and click "Insert PP Shortcode" button when done. <br><br><b><i><u style="color: red;">IMPORTANT:</u></i></b> If the product you select has PP bundles associated with it, these will be loaded, else the message <b>"No bundles for this product"</b> will be shown for <b>Select product bundles</b> and <b>Select default bundle</b> inputs.', 'default'); ?></i></p>

                <table class="form-table">

                    <!-- Main bundle product ID -->
                    <tr>
                        <th scope="row"><label for="pp_bundle_product_id"><?php _e('Select product:', 'default'); ?></label></th>
                        <td>
                            <select id="pp_bundle_sproduct_id">
                                <option value=""></option>
                                <?php foreach ($product_names as $product_name) { ?>
                                    <option value="<?php echo $product_name['value']; ?>"><?php echo $product_name['label']; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>

                    <!-- bundles to add to shortcode -->
                    <tr>
                        <th scope="row">
                            <label for="pp_bundle_ids"><?php _e('Select product bundles:', 'default'); ?></label>
                        </th>
                        <td id="komnou1">
                            <select id="pp_bundle_ids" style="width: 100%;">
                                <option value=""><?php _e('Select product first', 'default'); ?></option>
                            </select>
                        </td>
                    </tr>

                    <!-- default bundle id select -->
                    <tr>
                        <th scope="row">
                            <label for="pp_default_bundle"><?php _e('Select default bundle:', 'default'); ?>
                            </label>
                        </th>
                        <td id="komnou2">
                            <select id="pp_default_bundle" style="width: 100%;">
                                <option value=""><?php _e('Select product first', 'default'); ?></option>
                            </select>
                        </td>
                    </tr>

                    <!-- default or custom gallery -->
                    <tr>
                        <th scope="row">
                            <label for="pp_bundle_gallery"><?php _e('Select gallery type:', 'default'); ?>
                            </label>
                        </th>
                        <td>
                            <select id="pp_bundle_gallery" style="width: 100%;">
                                <option value="true"><?php _e('Default Gallery', 'default'); ?></option>
                                <option value="false"><?php _e('Custom Gallery', 'default'); ?></option>
                            </select>

                            <!-- note -->
                            <p class="description"><?php _e('<b>Note:</b> if you choose Custom Gallery, make sure that you add custom gallery images in the <b>PBS Gallery Images</b> metabox located in the sidebar, else the default product image gallery will be used instead.', 'default'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <!-- insert -->
                        <td colspan="2"><a href="#" class="button-primary" id="pp_bundle_shortcode_insert">Insert PP Shortcode</a></td>
                    </tr>
                </table>
            </div>

            <?php }

        /**
         * Add js and css to admin footer
         * 
         * @since 1.0.6
         * 
         * @return void
         */
        public function pp_admin_footer()
        {

            // if is 'offer' or 'landing' post type edit screen
            if (get_post_type() == 'offer' || get_post_type() == 'landing') { ?>

                <script type="text/javascript">
                    $ = jQuery.noConflict();

                    // select2 on product select
                    $('#pp_bundle_sproduct_id').select2({
                        placeholder: '<?php _e('Select product', 'default'); ?>',
                        width: '100%'
                    });

                    // show pp bundle shortcode popup
                    function showPopup() {

                        event.preventDefault();

                        // return false;
                        $('#pp_bundle_popup_overlay, #pp_bundle_shortcode').show();
                    }

                    // close pp bundle shortcode popup
                    function ppClose() {
                        $('#pp_bundle_popup_overlay, #pp_bundle_shortcode').hide();
                    }

                    // hide on overlay click
                    $(document).on("click", '#pp_bundle_popup_overlay', function() {
                        $('#pp_bundle_popup_overlay, #pp_bundle_shortcode').hide();
                    });

                    $(document).ready(function() {

                        // on product select change
                        $(document).on("change", '#pp_bundle_sproduct_id', function() {

                            // get selected product id
                            var product_id = $(this).val();

                            // get bundle ids json
                            var bundle_ids_json = $("#pp_bundle_ids_json").val();

                            // parse bundle ids json
                            var bundle_ids = JSON.parse(bundle_ids_json);

                            // get bundle select dropdowns
                            var pp_bundle_ids = $("#pp_bundle_ids"),
                                pp_default_bundle = $("#pp_default_bundle");

                            // if product is in bundle_ids array, get bundle labels and values for selected product id
                            if (bundle_ids[product_id]) {

                                // get bundles for selected product id
                                var bundles = bundle_ids[product_id];

                                // if bundles, build select2 dropdown
                                if (bundles) {

                                    // empty
                                    pp_bundle_ids.empty();
                                    pp_default_bundle.empty();

                                    // Create a new option element
                                    bundles.forEach(function(bundle) {

                                        // create option
                                        var option = '<option value="' + bundle.value + '">' + bundle.label + '</option>';

                                        // Append the option to the select element
                                        $('#pp_bundle_ids').append(option);
                                        $('#pp_default_bundle').append(option);
                                    });

                                    // select2 on bundle select
                                    $('#pp_bundle_ids').select2({
                                        multiple: true,
                                        width: '100%',
                                        maximumSelectionLength: 3,
                                        language: {
                                            maximumSelected: function() {
                                                return "You can select a maximum of 3 bundles";
                                            }
                                        }
                                    });

                                    // select2 on default bundle select
                                    $('#pp_default_bundle').select2({
                                        width: '100%'
                                    });

                                    // update select2 dropdowns
                                    pp_bundle_ids.trigger('change');
                                    pp_default_bundle.trigger('change');

                                }

                            } else {

                                // console.log('no bundles for this product');

                                // destroy select2 if active on dropdowns
                                if (pp_bundle_ids.hasClass('select2-hidden-accessible')) {
                                    pp_bundle_ids.select2('destroy');

                                    // remove attribute multiple
                                    pp_bundle_ids.removeAttr('multiple');
                                }

                                if (pp_default_bundle.hasClass('select2-hidden-accessible')) {
                                    pp_default_bundle.select2('destroy');
                                }

                                // empty
                                pp_bundle_ids.empty().append('<option value=""><?php _e('No bundles for this product', 'default'); ?></option>');
                                pp_default_bundle.empty().append('<option value=""><?php _e('No bundles for this product', 'default'); ?></option>');

                            }

                        });

                        // insert pp shortcode on click
                        $('#pp_bundle_shortcode_insert').on("click", function() {

                            // if not all fields have values
                            if (!$('#pp_bundle_sproduct_id').val() || !$('#pp_bundle_ids').val() || !$('#pp_default_bundle').val()) {
                                alert('<?php _e('Please select all fields', 'default'); ?>');
                                return false;
                            }

                            // get selected product id
                            var product_id = $('#pp_bundle_sproduct_id').val();

                            // get selected bundle ids
                            var bundle_ids = $('#pp_bundle_ids').val();

                            // get selected default bundle id
                            var default_bundle_id = $('#pp_default_bundle').val();

                            // get selected gallery type
                            var gallery_type = $('#pp_bundle_gallery').val();

                            // get shortcode
                            var shortcode = '[pp_bundle product_id=\"' + product_id + '\" bundle_ids=\"' + bundle_ids + '" default=\"' + default_bundle_id + '\" gallery=\"' + gallery_type + '\"]';

                            // set shortcode in content field
                            $('#content').val(shortcode);

                            // if tinyMCE is active
                            if (typeof tinyMCE != "undefined") {

                                // Get the TinyMCE instance
                                var editor = tinyMCE.get('content');

                                // if editor
                                if (editor) {

                                    // Update the TinyMCE editor with the new content
                                    editor.setContent(shortcode);
                                }
                            }

                            // close popup
                            ppClose();

                        });

                    });
                </script>

                <style>
                    div#pp_bundle_popup_overlay {
                        position: fixed;
                        width: 100vw;
                        height: 100vh;
                        background: #000000a1;
                        left: 0;
                        top: 0;
                        z-index: 100;
                    }

                    div#pp_bundle_shortcode {
                        position: absolute;
                        background: white;
                        padding: 20px;
                        border-radius: 5px;
                        z-index: 101;
                        min-width: 360px;
                        width: 600px;
                        left: 29%;
                        top: -10vh;
                    }

                    div#pp_bundle_shortcode>h2 {
                        background: #efefef;
                        margin-right: -20px;
                        margin-left: -20px;
                        margin-top: -20px;
                        padding-left: 20px;
                        border-top-right-radius: 5px;
                        border-top-left-radius: 5px;
                        font-size: 16px;
                        box-shadow: 0px 2px 2px #eee;
                    }

                    a#pp_bundle_shortcode_insert {
                        width: 100%;
                        text-align: center;
                    }

                    div#pp_bundle_shortcode>p {
                        font-size: 14px;
                        border-bottom: 1px dashed #ddd;
                        padding-bottom: 20px;
                        margin-bottom: 0;
                    }

                    .select2-container--default .select2-search--inline .select2-search__field {
                        position: relative;
                        bottom: 13px;
                        left: 9px;
                        min-width: 300px;
                    }

                    div#pp_bundle_shortcode>h2>span {
                        position: absolute;
                        right: 15px;
                        height: 23px;
                        width: 23px;
                        text-align: center;
                        border: 1px solid #999;
                        border-radius: 50%;
                        cursor: pointer;
                        line-height: 1.2;
                    }

                    .select2-container--default .select2-selection--multiple .select2-selection__choice__display {
                        padding-left: 20px;
                    }
                </style>

<?php }
        }
    }

    new PP_Insert_Shortcode();

endif;
?>