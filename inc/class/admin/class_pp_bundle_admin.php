<?php

/**
 * Bundle admin selection
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('pp_bundle_Admin')) {

    /*
    * pp_bundle_Admin Class
    */
    class pp_bundle_Admin
    {

        /**
         * Constructor
         */
        public function __construct()
        {

            // register admin menu
            add_action('admin_menu', function () {
                add_menu_page(
                    __('PP Bundle Dropdown'),
                    __('PP Bundle Dropdown'),
                    'read',
                    'bundle-dropdown',
                    null,
                    PBS_Bundle_URL . 'images/bd_logo.png',
                    '55'
                );
            });

            // add submenu page for usage instructions
            add_action('admin_menu', function () {
                add_submenu_page(
                    'bundle-dropdown',
                    __('Usage Instructions'),
                    __('Usage Instructions'),
                    'read',
                    'bundle-dropdown-usage',
                    array($this, 'bundle_dropdown_usage')
                );
            });

            // init
            add_action('init', array($this, 'bundle_dd_post_type'));

            // scripts
            add_action('admin_enqueue_scripts', array($this, 'bdd_admin_scripts'));

            // metaboxes
            add_action('admin_init', array($this, 'add_form_meta_boxes'));

            // save bundle data/post
            add_action('save_post', array($this, 'save_bundle_dropdown_fields'));

            // customize bundle admin post columns
            add_filter('manage_bundle_dropdown_posts_columns', array($this, 'set_cpt_column_headers_bundle_dropdown'), 10);
            add_action('manage_bundle_dropdown_posts_custom_column', array($this, 'set_cpt_column_data__bundle_dropdown'), 10, 2);

            // action ajax get product
            add_action('wp_ajax_nopriv_bundle_products', array($this, 'bundle_products'));
            add_action('wp_ajax_bundle_products', array($this, 'bundle_products'));

            // action ajax get html custom product price
            add_action('wp_ajax_nopriv_bd_get_html_custom_product_price', array($this, 'ajax_get_html_custom_product_price'));
            add_action('wp_ajax_bd_get_html_custom_product_price', array($this, 'ajax_get_html_custom_product_price'));
        }

        /**
         * Usage instructions
         */
        public function bundle_dropdown_usage()
        { ?>

            <div id="pp-usage-instructions">

                <h1><?php _e('Per Product Bundle Usage Instructions', 'default'); ?></h1>

                <h3><?php _e('Supported Bundle Types', 'default'); ?></h3>

                <p>
                    <?php _e('There are 2 bundle types to choose from: Buy X Get X Free, and Buy X get X Off. Both have similar basic options: bundle title, desktop bundle image, mobile bundle image and hover image.', 'default'); ?>
                </p>

                <p>
                    <?php _e('For Buy X Get X Free, you need to specify the paid product ID and quantity, and the free quantity applicable to the bundle.', 'default'); ?>
                </p>

                <p>
                    <?php _e('For Buy X Get X Off, you need to specify the paid product ID and quantity, and the percentage discount applicable for the bundle.', 'default'); ?>
                </p>

                <p>
                    <?php _e('Bundles can be added under the Bundle Dropdown menu as needed. Each bundle can have a language assigned to it, of which EN (English) will be default, unless specified otherwise before publication and/or update.', 'default'); ?>
                </p>

                <p>
                    <u>
                        <b>
                            <?php _e('An explanation of what each input does can be accessed by hovering over the question mark icon next to each input label:', 'default'); ?>
                        </b>
                    </u>
                </p>

                <p>
                    <img src="<?php echo PBS_Bundle_URL . 'images/pp_i_5.png'; ?>" alt="">
                </p>

                <p>
                    <?php _e('Please hover over these icons if you\'re unsure about what an input\'s value is used for.', 'default'); ?>
                </p>

                <h3><?php _e('The Bundle Shortcode and its Settings', 'default'); ?></h3>

                <p>
                    <?php _e('The per product bundle is rendered via shortcode. Said shortcode is only supported on Landings and Offers pages.', 'default'); ?>
                </p>

                <p>
                    <u>
                        <b>
                            <?php _e('Once you\'ve setup up bundles for a specific product, you can add the shortcode to and Offer or Landing page using the button provided: ', 'default'); ?>
                        </b>
                    </u>
                </p>

                <p>
                    <img src="<?php echo PBS_Bundle_URL . 'images/pp_i_1.png'; ?>" alt="">
                </p>


                <p>
                    <b>
                        <i>

                            <?php _e('Clicking on the button shown will open a popup where you will be allowed to select the options for the shortcode:', 'default'); ?>
                        </i>
                    </b>
                </p>

                <p>
                    <img src="<?php echo PBS_Bundle_URL . 'images/pp_i_2.png'; ?>" alt="">
                </p>

                <p>
                    <i>
                        <b>
                            <?php _e('Note that you will have to first select a valid product in the <u>Select product</u> field. If this product has any bundles associated with it, said bundles will load and be selectable in the <u>Select product bundles</u> and <u>Select default bundle</u> fields:', 'default'); ?>
                        </b>
                    </i>
                </p>

                <p>
                    <img src="<?php echo PBS_Bundle_URL . 'images/pp_i_3.png'; ?>" alt="">
                </p>

                <p>
                    <b>
                        <i>
                            <?php _e('If you select a product which does not yet have any bundles defined for it, you will see the following:', 'default'); ?>
                        </i>
                    </b>
                </p>

                <p>
                    <img src="<?php echo PBS_Bundle_URL . 'images/pp_i_4.png'; ?>" alt="">
                </p>

                <p>
                    <u>
                        <i>
                            <b>
                                <?php _e('In this case you will need to first publish bundles for this particular product before you will be able to add them to the shortcode.', 'default'); ?>
                            </b>
                        </i>
                    </u>
                </p>

                <p>
                    <b>
                        <i>

                            <?php _e('Lastly, you can use the <u>Select gallery type</u> dropdown to specify whether you want to use the default product image gallery on the front-end, or whether you want to use custom images in the image gallery on the front-end (pay attention to the note!):', 'default'); ?>
                        </i>
                    </b>
                </p>

                <p>
                    <img src="<?php echo PBS_Bundle_URL . 'images/pp_i_6.png'; ?>" alt="">
                </p>

                <p>
                    <b>
                        <i>
                            <?php _e('Once you\'ve defined all your shortcode settings, click on the Insert PP Shortcode button to insert the shortcode into the page:', 'default'); ?>
                        </i>
                    </b>
                </p>

                <p>
                    <img src="<?php echo PBS_Bundle_URL . 'images/pp_i_7.png'; ?>" alt="">
                </p>

                <p>
                    <b>
                        <i>
                            <?php _e('Now you can publish your Landing or Offer page, and the Per Product Bundle will be displayed on the front-end using a modified version of the single product page template (custom product gallery seen below): ', 'default'); ?>
                        </i>
                    </b>
                </p>

                <p>
                    <img src="<?php echo PBS_Bundle_URL . 'images/pp_i_8.png'; ?>" alt="">
                </p>

                <p style="font-size: 20px; font-weight: bold;">
                    <i>
                        <?php _e('~ THE END ~', 'default'); ?>
                    </i>
                </p>
            </div>

            <style>
                div#pp-usage-instructions p {
                    font-size: 15px;
                }

                div#pp-usage-instructions>h1 {
                    background: white;
                    padding: 15px 20px;
                    margin-top: 0;
                    margin-left: -19px;
                    box-shadow: 0px 2px 5px lightgray;
                }

                div#pp-usage-instructions>h3 {
                    text-transform: uppercase;
                    text-decoration: underline;
                    margin-bottom: 30px;
                    margin-top: 30px;
                }
            </style>

            <?php }

        /**
         * Register custom post type
         *
         * @return void
         */
        public function bundle_dd_post_type()
        {

            $args = array(
                'labels' => array(
                    'name'               => 'Bundle Dropdown',
                    'singular_name'      => 'Bundle Dropdown',
                    'add_new'            => 'Add New',
                    'add_new_item'       => 'Add New Bundle Selection',
                    'edit_item'          => 'Edit Bundle Selection',
                    'new_item'           => 'New Bundle Selection',
                    'view_item'          => 'View Bundle Selection',
                    'search_items'       => 'Search Bundle Selection',
                    'not_found'          => 'Nothing Found',
                    'not_found_in_trash' => 'Nothing found in the Trash',
                    'parent_item_colon'  => ''
                ),
                'show_in_menu'       => 'bundle-dropdown',
                'public'             => true,
                'publicly_queryable' => true,
                'show_ui'            => true,
                'query_var'          => true,
                'rewrite'            => true,
                'capability_type'    => 'post',
                'hierarchical'       => false,
                'menu_position'      => 0,
                'supports'           => array('title')
            );

            register_post_type('bundle_dropdown', $args);
        }

        /**
         * Customize post type columns
         *
         * @param array $defaults - default post columns
         * @return array $defaults - updated post columns
         */
        public function set_cpt_column_headers_bundle_dropdown($defaults)
        {

            $defaults['tracking_id']     = __('Tracking ID', 'woocommerce');
            $defaults['product_id']      = __('Product ID(s)', 'woocommerce');
            $defaults['count_view']      = __('Impressions', 'woocommerce');
            $defaults['count_click']     = __('Clicks', 'woocommerce');
            $defaults['count_paid']      = __('Conversions', 'woocommerce');
            $defaults['conversion_rate'] = __('Conversion Rate', 'woocommerce');
            $defaults['revenue']         = __('Revenue', 'woocommerce');

            unset($defaults['date']);

            return $defaults;
        }

        /**
         * Customize post column data output
         *
         * @param string $column_name
         * @param int $post_id
         * @return void
         * @todo add proper tracking
         */
        public function set_cpt_column_data__bundle_dropdown($column_name, $post_id)
        {

            switch ($column_name) {

                    // tracking id
                case 'tracking_id':
                    echo $post_id;
                    break;

                    // product id
                case 'product_id':

                    $bundle_data = get_post_meta($post_id, 'product_discount', true);

                    $bundle_type = $bundle_data['selValue'];

                    // bundle products
                    if ($bundle_type === 'bun') :
                        $bundle_products = $bundle_data["selValue_$bundle_type"]['post'];
                        foreach ($bundle_products as $index => $data_arr) :
                            echo $data_arr['id'] . '<br>';
                        endforeach;

                    // non bundle products
                    else :
                        $product_id = $bundle_data["selValue_$bundle_type"]['post']['id'];
                        echo $product_id;
                    endif;
                    break;

                    // view count
                case 'count_view':
                    $view_count  = get_post_meta($post_id, 'count_view', true);
                    echo $view_count ?: '-';
                    break;

                    // click count
                case 'count_click':
                    $click_count = get_post_meta($post_id, 'count_click', true);
                    echo $click_count ?: '-';
                    break;

                    // paid count
                case 'count_paid':
                    $paid_count  = get_post_meta($post_id, 'count_paid', true);
                    echo $paid_count ?: '-';
                    break;

                    // conversion rate
                case 'conversion_rate':

                    $paid_count  = (int)get_post_meta($post_id, 'count_paid', true);
                    $view_count  = (int)get_post_meta($post_id, 'count_view', true);
                    $click_count = (int)get_post_meta($post_id, 'count_click', true);

                    $conversion_rate = 0;

                    // if (is_numeric($paid_count) && is_numeric($view_count) && is_numeric($click_count)) :
                    $impressions = $view_count + $click_count;
                    $rate        = $paid_count && $view_count ? (($paid_count * 100) / $impressions) : 0;

                    update_post_meta($post_id, 'conversion_rate', $rate);
                    // endif;

                    $conversion_rate = get_post_meta($post_id, 'conversion_rate', true);
                    echo $conversion_rate > 0 ? number_format($conversion_rate, 2, '.', '') . '%' : '-';
                    break;

                    // total revenue
                case 'revenue':

                    // revenue and order currency
                    $revenue        = get_post_meta($post_id, 'revenue', true);
                    $order_currency = get_post_meta($post_id, 'order_currency', true);

                    // if ALG currency converter is installed
                    if ($revenue && $order_currency && function_exists('alg_wc_cs_get_exchange_rate')) :
                        if ($order_currency !== 'USD') :
                            $ext_rate = alg_wc_cs_get_exchange_rate($order_currency, 'USD') ? alg_wc_cs_get_exchange_rate($order_currency, 'USD') : 1;
                            $conv_revenue = $revenue * $ext_rate;
                            echo 'USD ' . number_format($conv_revenue, 2, '.', '');
                        else :
                            echo $order_currency . ' ' . number_format($revenue, 2, '.', '');
                        endif;

                    // if ALG currency converter is not installed
                    elseif ($revenue && $order_currency && !function_exists('alg_wc_cs_get_exchange_rate')) :
                        echo $order_currency . ' ' . number_format($revenue, 2, '.', '');
                    elseif (!$revenue) :
                        echo '-';
                    endif;
                    break;
            }
        }

        /**
         * Enqueue styles
         *
         * @return void
         */
        public function bdd_admin_scripts()
        {

            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('bdd_tinymce', PBS_Bundle_URL . 'inc/class/admin/assets/lib/tinymce/tinymce.min.js', array(), '4.5.1');
            wp_enqueue_style('bdd-admin-css', PBS_Bundle_URL . 'inc/class/admin/assets/css/admin.css', array(), time());
            wp_enqueue_style('bdd-select2', PBS_Bundle_URL . 'inc/class/admin/assets/css/select2.min.css', array(), time());
            wp_enqueue_script('bdd-select2', PBS_Bundle_URL . 'inc/class/admin/assets/js/select2.min.js', array(), '4.1.0-rc.0');
            wp_enqueue_script('bdd-admin-js', PBS_Bundle_URL . 'inc/class/admin/assets/js/admin.js', array(), time());
        }

        /**
         * AJAX action to retrieve WC product data
         *
         * @return void
         */
        public function bundle_products()
        {
            if (isset($_GET['action']) && isset($_GET['product_title']) && $_GET['action'] == 'bundle_products') {
                global $wpdb;
                $posts = $wpdb->prefix . 'posts';
                $title = $_GET['product_title'];
                $db_data['results'] = $wpdb->get_results($wpdb->prepare("SELECT * FROM `$posts` WHERE `post_type`='product'  AND `post_title` LIKE %s", "%$title%"));
                wp_send_json($db_data);
            }
        }

        /**
         * AJAX action to get product custom price HTML
         *
         * @return void
         * @uses get_custom_price_html
         */
        public function ajax_get_html_custom_product_price()
        {

            if (isset($_GET['action']) && isset($_GET['product_id']) && $_GET['action'] == 'bd_get_html_custom_product_price') {

                $prod_id = (int)$_GET['product_id'];
                $html    = $this->get_custom_price_html($prod_id);

                // return data
                echo json_encode(
                    array(
                        'status' => true,
                        'html' => $html
                    )
                );
                exit;
            }
        }

        /**
         * Generate and return product custom price HTML
         *
         * @param int $product_id
         * @param array $data_custom_price
         * @return void
         */
        public function get_custom_price_html($product_id, $data_custom_price = [])
        {

            $product = wc_get_product($product_id);

            // bail if product object not returned
            if (!$product) :
                return false;
            endif;

            // get currencies
            $additional_currencies = $this->bd_getCurrency();

            if (!empty($additional_currencies)) {
                $default_curr = get_option('woocommerce_currency', true);
                $additional_currencies = array_merge([$default_curr], $additional_currencies);
                $additional_currencies = array_unique($additional_currencies);
            } else {
                $all_currencies = get_woocommerce_currencies();
                $all_currencies = array_unique($all_currencies);
            }

            // get currencies rate
            $currencies_rate = [];

            // html custom product price
            $html = '<div class="collapsible custom_price_prod">
                        <span>' . __("Custom product price") . '</span>
                        <span class="i_toggle"></span>
                    </div>
                    <div class="toggle_content custom_price_prod">';

            // get price product variable
            if ($product->is_type('variable')) {

                foreach ($product->get_available_variations() as $value) {

                    $prod_price = get_post_meta($value['variation_id'], '_price', true);

                    // add variation price item html
                    $html .= '<div class="variation_item">
                                <div class="collapsible custom_price_prod">
                                    <span>' . implode(" - ", $value['attributes']) . '</span>
                                    <span class="i_toggle"></span>
                                </div>
                            <div class="toggle_content custom_price_prod">';
                    if (!empty($additional_currencies)) {
                        foreach ($additional_currencies as $currency_code) {

                            // get currencies rate
                            if (!isset($currencies_rate[$currency_code])) {
                                $currencies_rate[$currency_code] = null;
                                if (function_exists('alg_wc_cs_get_currency_exchange_rate')) {
                                    $currencies_rate[$currency_code] = alg_wc_cs_get_currency_exchange_rate($currency_code);
                                }
                            }

                            // get old custom price
                            if (!empty($data_custom_price)) {
                                $old_price = isset($data_custom_price[$value['variation_id']][$currency_code]) ? $data_custom_price[$value['variation_id']][$currency_code] : '';
                            }

                            $html .= '<div class="item_currency">
                                        <div class="item_name">
                                            <label>' . $currency_code . '</label>
                                        </div>
                                        <input type="text" class="input_price" name="custom_price_prod[' . $value['variation_id'] . '][' . $currency_code . ']" value="' . $old_price . '" data-value="' . $prod_price * $currencies_rate[$currency_code] . '">
                                    </div>';
                        }
                    } else {
                        foreach ($all_currencies as $key => $currency_code) {

                            // get currencies rate
                            if (!isset($currencies_rate[$key])) {
                                $currencies_rate[$key] = null;
                                if (function_exists('alg_wc_cs_get_currency_exchange_rate')) {
                                    $currencies_rate[$key] = alg_wc_cs_get_currency_exchange_rate($key);
                                }
                            }

                            // get old custom price
                            if (isset($data_custom_price)) {
                                $old_price = isset($data_custom_price[$value['variation_id']][$key]) ? $data_custom_price[$value['variation_id']][$key] : '';
                            }

                            $html .= '<div class="item_currency">
                                        <label>' . $key . '</label>
                                        <input type="text" name="custom_price_prod[' . $value['variation_id'] . '][' . $key . ']" value="' . $old_price . '" data-value="' . $prod_price * $currencies_rate[$currency_code] . '">
                                    </div>';
                        }
                    }

                    $html .= '</div>
                </div>';
                }
            }

            // single product
            else {
                // get price product
                $prod_price = $product->get_price();

                if (!empty($additional_currencies)) {
                    foreach ($additional_currencies as $currency_code) {
                        // get currencies rate
                        if (!isset($currencies_rate[$currency_code])) {
                            $currencies_rate[$currency_code] = null;
                            if (function_exists('alg_wc_cs_get_currency_exchange_rate')) {
                                $currencies_rate[$currency_code] = alg_wc_cs_get_currency_exchange_rate($currency_code);
                            }
                        }

                        // get old custom price
                        if (isset($data_custom_price)) {
                            $old_price = isset($data_custom_price[$product_id][$currency_code]) ? $data_custom_price[$product_id][$currency_code] : '';
                        }

                        $html .= '<div class="item_currency">
                                    <div class="item_name">
                                        <label>' . $currency_code . '</label>
                                    </div>
                                    <input type="text" class="input_price" name="custom_price_prod[' . $product_id . '][' . $currency_code . ']" value="' . $old_price . '" data-value="' . $prod_price * $currencies_rate[$currency_code] . '">
                                </div>';
                    }
                } else {
                    foreach ($all_currencies as $key => $currency_code) {
                        // get currencies rate
                        if (!isset($currencies_rate[$key])) {
                            $currencies_rate[$key] = null;
                            if (function_exists('alg_wc_cs_get_currency_exchange_rate')) {
                                $currencies_rate[$key] = alg_wc_cs_get_currency_exchange_rate($key);
                            }
                        }

                        // get old custom price
                        if (isset($data_custom_price)) {
                            $old_price = isset($data_custom_price[$product_id][$key]) ? $data_custom_price[$product_id][$key] : '';
                        }

                        $html .= '<div class="item_currency">
                                    <label>' . $key . '</label>
                                    <input type="text" name="custom_price_prod[' . $product_id . '][' . $key . ']" value="' . $old_price . '" data-value="' . $prod_price * $currencies_rate[$currency_code] . '">
                                </div>';
                    }
                }
            }
            // end html
            $html .= '</div>';

            return $html;
        }

        /**
         * Add metabox to bundle dropdown CPT
         *
         * @return void
         */
        public function add_form_meta_boxes()
        {
            add_meta_box(
                "bd_bundle_dropdown_meta",
                __('Bundle Selection', 'woocommerce'),
                array($this, "add_bundle_dropdown_meta_box"),
                "bundle_dropdown",
                "normal",
                "low"
            );
        }

        /**
         * Bundle dropdown CPT metabox
         *
         * @return void
         */
        public function add_bundle_dropdown_meta_box()
        {

            global $post;

            // get data bundle selection
            $db_data = get_post_meta($post->ID, 'product_discount', true);

            // form edit
            if ($db_data) {
                if (!is_array($db_data)) {
                    $db_data = json_decode($db_data, true);
                }
                $selValue = $db_data['selValue'] ?? 'free';
            ?>
                <!-- load select bundle type -->
                <select name="selValue" class="select_type">
                    <option <?php ($selValue == 'free') ? print_r('selected') : '' ?> value="free"><?= __('Buy X Get X Free') ?></option>
                    <option <?php ($selValue == 'off') ? print_r('selected') : '' ?> value="off"><?= __('Buy X Get X % Off') ?></option>
                    <!-- <option <?php ($selValue == 'bun') ? print_r('selected') : '' ?> value="bun"><?= __('Bundled Product') ?></option> -->
                </select>

                <button type="button" class="button product product_add_bun <?php echo (($selValue == 'bun') ? 'activetype_button' : '') ?>"><?= __('ADD One More') ?></button>
                <?php
                /**
                 * edit option buy x get y free
                 */
                $data                  = [];

                $data['title']               = isset($db_data['title_package']) ? $db_data['title_package'] : '';
                $data['image_desk']          = isset($db_data['image_package_desktop']) ? $db_data['image_package_desktop'] : '';
                $data['image_mobile']        = isset($db_data['image_package_mobile']) ? $db_data['image_package_mobile'] : '';
                $data['hover_image']         = isset($db_data['image_package_hover']) ? $db_data['image_package_hover'] : '';
                $data['description']         = isset($db_data['feature_description']) ? $db_data['feature_description'] : '';
                $data['label']               = isset($db_data['label_item']) ? $db_data['label_item'] : '';
                $data['discount_percentage'] = isset($db_data['discount_percentage']) ? $db_data['discount_percentage'] : '';
                $data['sell_out_risk']       = isset($db_data['sell_out_risk']) ? $db_data['sell_out_risk'] : '';
                $data['popularity']          = isset($db_data['popularity']) ? $db_data['popularity'] : '';
                $data['free_shipping']       = isset($db_data['free_shipping']) ? $db_data['free_shipping'] : false;

                // // buy x get x free
                if ($selValue == 'free') {
                    $data['product_name']             = isset($db_data['product_name']) ? $db_data['product_name'] : '';
                    $data['free']                     = isset($db_data['selValue_free']['post']) ? $db_data['selValue_free']['post'] : ['id' => '', 'text' => 'title'];
                    $data['free_qty']                 = isset($db_data['selValue_free']['quantity']) ? $db_data['selValue_free']['quantity'] : '';
                    $data['free_prod']                = isset($db_data['selValue_free_prod']['post']) ? $db_data['selValue_free_prod']['post'] : ['id' => '', 'text' => 'title'];
                    $data['free_prod_qty']            = isset($db_data['selValue_free_prod']['quantity']) ? $db_data['selValue_free_prod']['quantity'] : '';
                    $data['custom_price']             = isset($db_data['custom_price']) ? $db_data['custom_price'] : '';
                    $data['free_show_discount_label'] = isset($db_data['show_discount_label']) ? $db_data['show_discount_label'] : false;

                    // option buy x get x free *** main option
                    echo $this->renderBuyXgetXFree($data, true);

                    // buy x get y%
                    echo $this->renderBuyXgetYOff();
                }

                // /*
                // ** edit option buy x get y%
                // */
                if ($selValue == 'off') {
                    $data['product_name']            = isset($db_data['product_name']) ? $db_data['product_name'] : '';
                    $data['off']                     = isset($db_data['selValue_off']['post']) ? $db_data['selValue_off']['post'] : ['id' => '', 'text' => 'title'];
                    $data['off_qty']                 = isset($db_data['selValue_off']['quantity']) ? $db_data['selValue_off']['quantity'] : '';
                    $data['off_coupon']               = isset($db_data['selValue_off']['coupon']) ? $db_data['selValue_off']['coupon'] : '';
                    $data['custom_price']            = isset($db_data['custom_price']) ? $db_data['custom_price'] : '';
                    $data['off_show_discount_label'] = isset($db_data['show_discount_label']) ? $db_data['show_discount_label'] : false;

                    // option buy x get x free
                    echo $this->renderBuyXgetXFree();

                    // buy x get y% *** main option
                    echo $this->renderBuyXgetYOff($data, true);
                }
            }
            // form create
            else {

                ?>
                <!-- load select bundle type -->
                <select name="selValue" class="select_type">
                    <option value="free"><?= __('Buy X Get X Free') ?></option>
                    <option value="off"><?= __('Buy X Get X % Off') ?></option>
                </select>
                <button type="button" class="button product product_add_bun"><?= __('ADD One More') ?></button>

            <?php
                // option buy x get x free
                echo $this->renderBuyXgetXFree(null, true);

                // buy x get y%
                echo $this->renderBuyXgetYOff();
            }
        }

        // function save bundle selection form
        public function save_bundle_dropdown_fields($post_id)
        {
            global $post;

            if (!$post || $post->post_type != 'bundle_dropdown' || $post_id != $post->ID) {
                return;
            }

            // save option buy x get x free
            if ($_POST['selValue'] == 'free') {

                $data_arr['selValue']              = $_POST['selValue'];
                $data_arr['title_package']         = $_POST['title_package_free'];
                $data_arr['image_package_desktop'] = $_POST['free_image_desk'];
                $data_arr['image_package_mobile']  = $_POST['free_image_mobile'];
                $data_arr['image_package_hover']   = $_POST['free_hover_image'];
                $data_arr['product_name']          = $_POST['free_product_name'];

                $value = explode('/%%/', $_POST['selValue_free']);

                if (isset($value[0]) && isset($value[1])) {
                    $_POST['selValue_free'] = ['id' => $value[0], 'title' => preg_replace('/[^a-zA-Z0-9_ -]/s', '', $value[1])];
                }
                $data_arr['selValue_free'] = ['post' => $_POST['selValue_free'], 'quantity' => $_POST['quantity_main_free']];

                $data_arr['selValue_free_prod'] = ['post' => $_POST['selValue_free'], 'quantity' => $_POST['quantity_free_free']];

                //get feature desc _POST
                $desc = isset($_POST['feature_free_desc']) ? array_filter($_POST['feature_free_desc']) : '';
                $data_arr['feature_description'] = $desc;

                // show discout label
                $data_arr['show_discount_label'] = ($_POST['free_show_discount_label'] == true) ?: false;

                // sell out risk
                $data_arr['sell_out_risk'] = $_POST['free_sell_out_risk'] ?: '';

                // popularity
                $data_arr['popularity'] = $_POST['free_popularity'] ?: '';

                // free shipping
                $data_arr['free_shipping'] = ($_POST['free_shipping'] == true) ?: false;

                // custom product price
                $custom_price = [];
                if ($_POST['selValue_free'] && $_POST['custom_price_prod']) {
                    foreach ($_POST['custom_price_prod'] as $post_id => $values) {
                        foreach ($values as $curr => $price) {
                            if ($price) {
                                $custom_price[$post_id][$curr] = $price;
                            }
                        }
                    }
                }
                $data_arr['custom_price'] = $custom_price;
            }
            // save option buy x get x%
            elseif ($_POST['selValue'] == 'off') {

                $data_arr['selValue']              = $_POST['selValue'];
                $data_arr['title_package']         = $_POST['title_package_off'];
                $data_arr['image_package_desktop'] = $_POST['off_image_desk'];
                $data_arr['image_package_mobile']  = $_POST['off_image_mobile'];
                $data_arr['image_package_hover']   = $_POST['off_hover_image'];
                $data_arr['product_name']          = $_POST['off_product_name'];

                $value = explode('/%%/', $_POST['selValue_off']);

                if (isset($value[0]) && isset($value[1])) {
                    $_POST['selValue_off'] = ['id' => $value[0], 'title' => preg_replace('/[^a-zA-Z0-9_ -]/s', '', $value[1])];
                }
                $data_arr['selValue_off'] = ['post' => $_POST['selValue_off'], 'quantity' => $_POST['quantity_main_off'], 'coupon' => $_POST['quantity_coupon_off']];
                $desc = isset($_POST['feature_off_desc']) ? array_filter($_POST['feature_off_desc']) : '';
                $data_arr['feature_description'] = $desc;

                // custom product price
                $custom_price = [];
                if ($_POST['selValue_off'] && $_POST['custom_price_prod']) {
                    foreach ($_POST['custom_price_prod'] as $post_id => $values) {
                        foreach ($values as $curr => $price) {
                            if ($price) {
                                $custom_price[$post_id][$curr] = $price;
                            }
                        }
                    }
                }
                $data_arr['custom_price'] = $custom_price;
            }
            // save option buy bundle products
            elseif ($_POST['selValue'] == 'bun') {

                $data_arr['selValue']              = 'bun';
                $data_arr['title_header']          = $_POST['title_bundle_header'];
                $data_arr['title_package_bundle']  = $_POST['title_package_bundle'];
                $data_arr['image_package_desktop'] = $_POST['bundle_image_desk'];
                $data_arr['image_package_mobile']  = $_POST['bundle_image_mobile'];
                $data_arr['image_package_hover']   = $_POST['bundle_hover_image'];

                // $total_price = 0;
                foreach ($_POST['selValue_bundle'] as $key => $value) {
                    $value = explode('/%%/', $value);
                    $new_arr = '';
                    if (isset($value[0]) && isset($value[1])) {
                        $new_arr = ['id' => $value[0], 'title' => preg_replace('/[^a-zA-Z0-9_ -]/s', '', $value[1]), 'quantity' => $_POST['bundle_quantity'][$key]  ?: 1];
                    }
                    $_POST['selValue_bundle'][$key] = $new_arr;
                }

                // $data_arr['selValue_bun'] = ['post' => $_POST['selValue_bundle'], 'price' => $_POST['bundle_price'], 'coupon' => round($coupon_discount, 2), 'default_currency' => get_woocommerce_currency()];
                $data_arr['selValue_bun'] = ['post' => $_POST['selValue_bundle'], 'price_currency' => $_POST['bun_price_currency']];
                $desc = array_filter($_POST['feature_bundle_desc']);
                $data_arr['feature_description'] = $desc;

                // discount percentage
                $data_arr['discount_percentage'] = floatval($_POST['bun_discount_percentage']) ?: '';

                // show discout label
                $data_arr['show_discount_label'] = ($_POST['bun_show_discount_label'] == true) ?: false;

                // sell out risk
                $data_arr['sell_out_risk'] = $_POST['bun_sell_out_risk'] ?: '';

                // popularity
                $data_arr['popularity'] = $_POST['bun_popularity'] ?: '';

                // free shipping
                $data_arr['free_shipping'] = ($_POST['free_shipping'] == true) ?: false;

                //get label items _POST
                $label_name = array_filter($_POST['name_label_bundle']);
                $label_color = array_filter($_POST['color_label_bundle']);
                $data_arr['label_item'] = array_map(function ($name, $color) {
                    return array(
                        'name' => $name,
                        'color' => $color
                    );
                }, $label_name, $label_color);
            }

            if ($data_arr) {
                update_post_meta($post->ID, 'product_discount', $data_arr);
            }
        }

        /**
         * Renders Buy X Get X Free
         *
         * @param array $data - bundle data
         * @param boolean $active - bundle active state
         * @return void
         */
        public function renderBuyXgetXFree($data = null, $active = false)
        {
            ?>
            <!-- option buy x get x free -->
            <div class='product product_free <?= $active ? 'activetype' : '' ?>'>
                <table class="form-table">
                    <tbody>

                        <!-- description -->
                        <tr valign="top">
                            <th style="width:30%" scope="row" class="titledesc">
                                <label for="title_package_free">Title:
                                    <span class="pp-bundle-info" title="A custom title for the bundle (optional). Displayed directly below the bundle image. 50 characters max.">?</span>
                                </label>
                            </th>
                            <td class="forminp forminp-text">
                                <input name='title_package_free' type='text' class='title_main' value="<?= $data['title'] ?>" maxlength="50" style="width:400px;">
                            </td>
                        </tr>

                        <!-- desktop image -->
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="upload_image">Desktop image:
                                    <span class="pp-bundle-info" title="The image displayed on desktop devices when viewing this bundle (laptops, notepads and desktop computers)">?</span>
                                </label>
                            </th>
                            <td class="forminp forminp-text">
                                <label for='upload_image'>
                                    <input class='upload_image' type='text' name='free_image_desk' value="<?= $data['image_desk'] ?>" placeholder="Image URL or click to upload" style="width:400px;" />
                                    <input class='button upload_image_button' type='button' value='Upload Image' />
                                </label>
                            </td>
                        </tr>

                        <!-- mobile image -->
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="upload_image">Mobile image:
                                    <span class="pp-bundle-info" title="The image displayed on mobile devices when viewing this bundle (mobile phones and tablets)">?</span>
                                </label>
                            </th>
                            <td class="forminp forminp-text">
                                <label for='upload_image'>
                                    <input class='upload_image' type='text' name='free_image_mobile' value="<?= $data['image_mobile'] ?>" placeholder="Image URL or click to upload" style="width:400px;" />
                                    <input class='button upload_image_button' type='button' value='Upload Image' />
                                </label>
                            </td>
                        </tr>

                        <!-- hover image -->
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="hover_image">Hover image:
                                    <span class="pp-bundle-info" title="The image displayed when the user hovers over the bundle in desktop view">?</span>
                                </label>
                            </th>
                            <td class="forminp forminp-text">
                                <label for='hover_image'>
                                    <input class='upload_image' type='text' name='free_hover_image' value="<?= is_array($data) ? $data['hover_image'] : '' ?>" placeholder="Image URL or click to upload" style="width:400px;" />
                                    <input class='button upload_image_button' type='button' value='Upload Image' />
                                </label>
                            </td>
                        </tr>

                        <!-- main product -->
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="selectpicker">Paid Product:
                                    <span class="pp-bundle-info" title="The paid product for this bundle">?</span>
                                </label>
                            </th>
                            <td class="forminp forminp-text">
                                <select name='selValue_free' class='selectpicker' style="width: 400px;">
                                    <?php if (isset($data['free']["id"])) { ?>
                                        <option value="<?= ($data['free']["id"] . '/%%/' . $data['free']["title"]) ?>"> <?= ($data['free']["id"] . ': ' . $data['free']["title"]) ?> </option>
                                    <?php } else { ?>
                                        <option value=""></option>
                                    <?php } ?>
                                </select>
                                <label class="label_inline">
                                    <b>
                                        Quantity:
                                        <span class="pp-bundle-info" title="The paid product quantity for this bundle">?</span>
                                    </b>
                                </label>
                                <input name='quantity_main_free' type='number' class="small-text" value="<?= $data['free_qty'] ? $data['free_qty'] : 1 ?>">
                            </td>
                        </tr>

                        <!-- free product -->
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label>Free Product Qty:</label>
                                <span class="pp-bundle-info" title="The free product quantity for this bundle">?</span>
                            </th>
                            <td class="forminp forminp-text">
                                <input name='quantity_free_free' type='number' step="1" min="1" class='small-text' value="<?= $data['free_prod_qty'] ? $data['free_prod_qty'] : 1 ?>" style="width:50px;">
                            </td>
                        </tr>

                        <!-- custom price -->
                        <!-- <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label>Custom price</label>
                            </th>
                            <td class="forminp forminp-text">
                                <div class="custom_prod_price">
                                    <?php
                                    if (isset($data['free']['id'])) {
                                        echo $this->get_custom_price_html($data['free']['id'], $data['custom_price']);
                                    }
                                    ?>
                                </div>
                            </td>
                        </tr> -->
                    </tbody>
                </table>
            </div>
            <!-- end buy x get x free -->

            <style>
                span.pp-bundle-info {
                    border: 1px solid #ccc;
                    width: 18px;
                    display: inline-block;
                    text-align: center;
                    border-radius: 50%;
                    color: #666;
                }
            </style>

        <?php
        }

        /**
         * Renders Buy X Get X Off
         *
         * @param array $data - bundle data
         * @param boolean $active - bundle active state
         * @return void
         */
        public function renderBuyXgetYOff($data = null, $active = false)
        {
        ?>
            <!-- buy x get y% -->
            <div class='product product_off <?= $active ? 'activetype' : '' ?>'>
                <table class="form-table">
                    <tbody>

                        <!-- description -->
                        <tr valign="top">
                            <th style="width:30%" scope="row" class="titledesc">
                                <label for="title_package_off">Title:
                                    <span class="pp-bundle-info" title="A custom title for the bundle (optional). Displayed directly below the bundle image. 50 characters max.">?</span>
                                </label>
                            </th>
                            <td class="forminp forminp-text">
                                <input name='title_package_off' type='text' class='title_main' value="<?= $data['title'] ?>" maxlength="50" style="width:400px;">
                            </td>
                        </tr>

                        <!-- desktop image -->
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="upload_image">Desktop image:
                                    <span class="pp-bundle-info" title="The image displayed on desktop devices when viewing this bundle (laptops, notepads and desktop computers)">?</span>
                                </label>
                            </th>
                            <td class="forminp forminp-text">
                                <label for='upload_image'>
                                    <input class='upload_image' type='text' name='off_image_desk' value="<?= $data['image_desk'] ?>" placeholder="Image URL or click to upload" style="width:400px;" />
                                    <input class='button upload_image_button' type='button' value='Upload Image' />
                                </label>
                            </td>
                        </tr>

                        <!-- mobile image -->
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="upload_image">Mobile image:
                                    <span class="pp-bundle-info" title="The image displayed on mobile devices when viewing this bundle (mobile phones and tablets)">?</span>
                                </label>
                            </th>
                            <td class="forminp forminp-text">
                                <label for='upload_image'>
                                    <input class='upload_image' type='text' name='off_image_mobile' value="<?= $data['image_mobile'] ?>" placeholder="Image URL or click to upload" style="width:400px;" />
                                    <input class='button upload_image_button' type='button' value='Upload Image' />
                                </label>
                            </td>
                        </tr>

                        <!-- hover image -->
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="hover_image">Hover image:
                                    <span class="pp-bundle-info" title="The image displayed when the user hovers over the bundle in desktop view">?</span>
                                </label>
                            </th>
                            <td class="forminp forminp-text">
                                <label for='hover_image'>
                                    <input class='upload_image' type='text' name='off_hover_image' value="<?= is_array($data) ? $data['hover_image'] : '' ?>" placeholder="Image URL or click to upload" style="width:400px;" />
                                    <input class='button upload_image_button' type='button' value='Upload Image' />
                                </label>
                            </td>
                        </tr>

                        <!-- product -->
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="selectpicker">Product:
                                    <span class="pp-bundle-info" title="The product to which the discount will be applied">?</span>
                                </label>
                            </th>
                            <td class="forminp forminp-text">
                                <select name='selValue_off' class='selectpicker' style="width: 400px;">
                                    <?php if (isset($data['off']["id"])) { ?>
                                        <option value="<?php echo ($data['off']["id"] . '/%%/' . $data['off']["title"]) ?>"> <?php echo ($data['off']["id"] . ': ' . $data['off']["title"]) ?> </option>
                                    <?php } else { ?>
                                        <option value=""></option>
                                    <?php } ?>
                                </select>
                                <label class="label_inline">
                                    <b>Quantity:
                                        <span class="pp-bundle-info" title="The product quantity">?</span>
                                    </b>
                                </label>
                                <input name='quantity_main_off' type='number' class="small-text" value="<?= $data['off_qty'] ? $data['off_qty'] : 1 ?>">
                            </td>
                        </tr>

                        <!-- custom price -->
                        <!-- <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label>Custom price
                                <span class="pp-bundle-info" title="The image displayed when the user hovers over the bundle in desktop view">?</span>
                                </label>
                            </th>
                            <td class="forminp forminp-text">
                                <div class="custom_prod_price">
                                    <?php
                                    if (isset($data['off']['id'])) {
                                        echo $this->get_custom_price_html($data['off']['id'], $data['custom_price']);
                                    } else { ?>
                                        <span><?php _e('Can be defined after publishing this bundle.', 'default'); ?></span>
                                    <?php  }
                                    ?>
                                </div>
                            </td>
                        </tr> -->

                        <!-- coupon -->
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label>Discount:
                                    <span class="pp-bundle-info" title="The discount amount which should be applied to this bundle">?</span>
                                </label>
                            </th>
                            <td class="forminp forminp-text">
                                <input name='quantity_coupon_off' type='number' value="<?= $data['off_coupon'] ?>" style="width:50px;"> %
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
            <!-- end buy x get y% -->

            <style>
                span.pp-bundle-info {
                    border: 1px solid #ccc;
                    width: 18px;
                    display: inline-block;
                    text-align: center;
                    border-radius: 50%;
                    color: #666;
                }
            </style>
        <?php
        }

        /**
         * Query and return all enabled currencies
         *
         * @return array $additional_currencies
         */
        public function bd_getCurrency()
        {

            $additional_currencies = [];
            $total_number          = min(get_option('alg_currency_switcher_total_number', 2), apply_filters('alg_wc_currency_switcher_plugin_option', 2));

            for ($i = 1; $i <= $total_number; $i++) {
                if ('yes' === get_option('alg_currency_switcher_currency_enabled_' . $i, 'yes')) {
                    $additional_currencies[] = get_option('alg_currency_switcher_currency_' . $i);
                }
            }
            return $additional_currencies;
        }

        /**
         * Instructions for the user
         * 
         * @return void
         */
        public function bundle_dropdown_instructions()
        { ?>

            <div id="pp-bundle-instructions">
                <h1><?php _e('Usage Instructions & Notes', 'default'); ?></h1>

                <!-- section: basic breakdown of bundle options -->
                <div class="pp-bundle-section">
                    <h2><?php _e('Basic Breakdown of Bundle Options:', 'default'); ?></h2>

                    <p>
                        <?php _e('<u><b><i>IMPORTANT NOTE: </i></b></u> Only bundles of the same product is supported at this stage, so make sure that you use the same product when creating and using bundles on a particular Offer or Landing page. Mixing products is <u>not</u> supported at this stage and will very likely cause errors.', 'default'); ?>
                    </p>

                    <p><?php _e('The bundle options found under the PP Bundle Drowpdown menu are broken down into 2 types:', 'default'); ?></p>

                    <ol>
                        <li><b><?php _e('Buy X Get X Free', 'default'); ?></b></li>
                        <li><b><?php _e('Buy X Get X% Off', 'default'); ?></b></li>
                    </ol>

                    <p><?php _e('Each section has the same basic options available, main difference being that one allows you to specify a coupon discount amount (Buy X Get X% Off) while the other allows you to specify paid and free products.', 'default'); ?></p>

                    <h3><?php _e('Shared bundle settings: ', 'default'); ?></h3>

                    <ol>
                        <li><b>Description:</b> used to add a title to the bundle. Keep it short and sweet!</li>
                        <li><b>Desktop image:</b> the image disaplayed when the page is visited from a laptop or desktop computer.</li>
                        <li><b>Mobile image:</b> the image displayed when the page is visited from a mobile phone or most tablets.</li>
                        <li><b>Hover image:</b> the image displayed when a user hovers over the bundle in desktop view.</li>
                    </ol>

                    <h3><?php _e('Buy X Get X Free bundle:', 'default'); ?></h3>

                    <ol>
                        <li><b>Main Product:</b> the product to which this bundle applies, along with the paid quantity as defined in the <b>Quantity</b> input.</li>
                        <li><b>Free Product (Quantity):</b> the free product quantity (based on main product previously specified) which the customer will get on adding this bundle to cart.</li>
                    </ol>


                </div>




    <?php }
    }

    // init action class
    new pp_bundle_Admin();
}
