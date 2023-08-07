<?php

defined('ABSPATH') ?: exit();

if (!trait_exists('PBS_Render_Bundles')) :

    /**
     * Include required traits
     */
    require_once __DIR__ . '/trait-return-linked-prod-dd.php';
    require_once __DIR__ . '/trait-return-wc-var-attrib-dd.php';
    require_once __DIR__ . '/trait-return-opc-var-dd.php';
    require_once __DIR__ . '/trait-retrieve-all-bundle-data.php';
    require_once __DIR__ . '/trait-render-size-chart.php';
    require_once __DIR__ . '/trait-regional-sizes.php';

    trait PBS_Render_Bundles
    {

        // use traits
        use PBS_Return_Linked_Product_Dropdown,
            PBS_Return_WC_Variation_Attrib_Dropdown,
            PBS_Retrieve_All_Bundle_Data,
            PBS_Return_OPC_Variations_Dropdown,
            PBS_Render_Size_Chart,
            PBS_Regional_Sizes;

        /**
         * Handles impressions tracking via transient
         *
         * @param array $bundle_ids
         * @return void
         */
        public static function pbs_impressions_tracking($bundle_ids)
        {

            // retrieve current impressions cache
            $curr_impressions = get_transient('pbs_bundle_impressions');

            // if impressions exist
            if ($curr_impressions) :

                // setup new impressions
                $new_impressions = [];

                // update impressions
                foreach ($bundle_ids as $bundle_id) :
                    if (key_exists($bundle_id, $curr_impressions)) :
                        $new_impressions[$bundle_id] = (int)$curr_impressions[$bundle_id] + 1;
                    endif;
                endforeach;

                set_transient('pbs_bundle_impressions', $new_impressions);

            // if impressions do not exist
            else :

                // setup initial impressions array
                $impressions = [];

                // push impressions
                foreach ($bundle_ids as $index => $bundle_id) :
                    $impressions[$bundle_id] = 1;
                endforeach;

                set_transient('pbs_bundle_impressions', $impressions);

            endif;
        }

        /**
         * Render bundle data shortcode
         *
         * @return void
         */
        public static function pbs_render_bundles()
        {

            global $pbs_bundle_ids, $woocommerce, $pbs_default_id;

            // debug
            // echo '<pre>';
            // print_r(wc()->session);
            // echo '</pre>';

            // retrieve bundle ids
            $bundle_ids = explode(',', $pbs_bundle_ids);

            // retrieve all bundle data
            $bundle_data = self::pbs_retrieve_all_bundle_data($bundle_ids);

            // bail if $bundle_data === false
            if (!$bundle_data) :
                return;
            endif;

            // impressions tracking
            self::pbs_impressions_tracking($bundle_ids);

            // get current currency
            $current_curr = function_exists('alg_get_current_currency_code') ? alg_get_current_currency_code() : get_woocommerce_currency();

            // uncomment to debug specific currency conversion
            // $current_curr = 'SEK';
            // echo $current_curr . '<br>';

            // get default currency
            $default_currency = get_option('woocommerce_currency');

            // uncomment to debug
            // echo $default_currency . '<br>';

            // get alg exchange rate
            $ex_rate = get_option("alg_currency_switcher_exchange_rate_USD_$current_curr") ? get_option("alg_currency_switcher_exchange_rate_USD_$current_curr") : 1;

            // uncomment to debug
            // echo $ex_rate;

            // assign $bundle_data tot $package_product_ids
            $package_product_ids = $bundle_data; ?>

            <div class="bd_items_div bd_package_items_div i_clearfix bd_select_wrap row" id="bd_checkout">

                <div class="col list bd_select_ul">

                    <div class="bd_items_div_inner_row row">

                        <?php

                        $p_i = 0;

                        // create array variations data
                        $var_data = [];

                        // create array variation custom price
                        $variation_price = [];

                        // loop
                        foreach ($package_product_ids as $opt_i => $prod) :

                            $i_title                = '';
                            $cus_bundle_total_price = 0;

                            /**
                             * Setup titles, product ids, calculate prices et al
                             */

                            //  retrieve correct product id
                            if (in_array($prod['type'], ['free', 'off'])) :
                                $p_id = (int)$prod['id'];
                            endif;

                            if ($prod['type'] == 'bun') :
                                $p_id = (int)$prod['prod'][0]['id'];
                            endif;

                            $product = wc_get_product($p_id);

                            // get product price regular
                            $product_price = get_post_meta($p_id, '_regular_price', true) ? get_post_meta($p_id, '_regular_price', true) : get_post_meta($p_id, '_price', true);

                            // if not default currency, retrieve converted price
                            // if ($current_curr !== $default_currency) :

                            $alg_price = get_post_meta($p_id, '_alg_currency_switcher_per_product_regular_price_' . $current_curr, true);

                            if ($alg_price && $alg_price !== '') :
                                $product_price = $alg_price;
                            else :
                                $product_price = $product_price * $ex_rate;
                            endif;
                            // endif;

                            // bd product option has custom price
                            if ($prod['custom_price'] && current($prod['custom_price'])[$current_curr]) :
                                $product_price      = (float)current($prod['custom_price'])[$current_curr];
                            endif;

                            // get bd price variation
                            if ($product->is_type('variable')) :

                                foreach ($product->get_available_variations() as $key => $var_data) :

                                    $product_price = get_post_meta($var_data['variation_id'], '_regular_price', true) ? get_post_meta($var_data['variation_id'], '_regular_price', true) : get_post_meta($var_data['variation_id'], '_price', true);

                                    $variation_price[trim($prod['bun_id'])][$var_data['variation_id']]['variation_id'] = $var_data['variation_id'];

                                    // if ($prod['custom_price'] && isset($prod['custom_price'][$var_data['variation_id']][$current_curr])) :
                                    //     $variation_price[trim($prod['bun_id'])][$var_data['variation_id']]['price'] = $prod['custom_price'][$var_data['variation_id']][$current_curr];
                                    // else :

                                    // if ($current_curr !== $default_currency) :

                                    $alg_price = get_post_meta($var_data['variation_id'], '_alg_currency_switcher_per_product_regular_price_' . $current_curr, true);

                                    if ($alg_price && $alg_price !== '') :
                                        $variation_price[trim($prod['bun_id'])][$var_data['variation_id']]['price'] = $alg_price;
                                        $product_price = $alg_price;
                                    else :
                                        $variation_price[trim($prod['bun_id'])][$var_data['variation_id']]['price'] = $product_price * $ex_rate;
                                        $product_price = $product_price * $ex_rate;
                                    endif;
                                // else :
                                //     $variation_price[trim($prod['bun_id'])][$var_data['variation_id']]['price'] = $var_data['display_regular_price'];
                                // endif;

                                // endif;

                                endforeach;

                            // $product_price = 500;

                            endif;

                            // type free
                            if ($prod['type'] == 'free') :

                                // bundle title
                                if ((int)$prod['qty_free'] == 0) :
                                    $i_title = sprintf(__('Buy %s', 'woocommerce'), (int)$prod['qty']);
                                else :
                                    $i_title = sprintf(__('Buy %s + Get %d FREE'), (int)$prod['qty'], (int)$prod['qty_free']);
                                endif;

                                // pricing
                                $i_total_qty    = (int)$prod['qty'] + (int)$prod['qty_free'];
                                $i_price        = ((float)$product_price * (int)$prod['qty']) / $i_total_qty;
                                $i_price_total  = $i_price * $i_total_qty;
                                $price_discount = ((float)$product_price * $i_total_qty) - $i_price_total;
                                $i_coupon       = ((int)$prod['qty_free'] * 100) / $i_total_qty;

                                // js input data package
                                $js_discount_type  = 'free';
                                $js_discount_qty   = (int)$prod['qty_free'];
                                $js_discount_value = (int)$prod['id_free'];

                            endif;

                            // type off
                            if ($prod['type'] == 'off') :

                                // bundle title
                                $i_title = sprintf(__('Buy %s + Get %d&#37;'), (int)$prod['qty'], (float)$prod['coupon']) . ' ' . __('Off');

                                // pricing
                                $i_total_qty    = (int)$prod['qty'];
                                $i_tt           = (float)$product_price * $prod['qty'];
                                $i_coupon       = (float)$prod['coupon'];
                                $i_price        = ((float)$product_price - ((float)$product_price * (float)$i_coupon / 100));
                                $i_price_total  = $i_price * (int)$prod['qty'];
                                $price_discount = $i_tt - $i_price_total;

                                // js input data package
                                $js_discount_type  = 'percentage';
                                $js_discount_qty   = 1;
                                $js_discount_value = (float)$prod['coupon'];

                            endif;

                            // type bun
                            if ($prod['type'] == 'bun') :

                                // bundle title
                                $i_title = $prod['title_header'] ?: __('Bundle option');

                                // pricing
                                $i_total_qty    = count($prod['prod']);
                                $i_price        = (float)$prod['total_price'];

                                // js input data package
                                $js_discount_type  = 'percentage';
                                $js_discount_qty   = 1;
                                $js_discount_value = (float)$prod['discount_percentage'];
                                $sum_price_regular = 0;

                                foreach ($prod['prod'] as $i => $i_prod) :

                                    // retrieve prod price
                                    $product_price = get_post_meta($i_prod['id'], '_regular_price', true) ? get_post_meta($i_prod['id'], '_regular_price', true) : get_post_meta($i_prod['id'], '_price', true) * $i_prod['qty'];

                                    // if not default currency, retrieve converted price, or calculate converted price
                                    // if ($current_curr !== $default_currency) :

                                    // get alg price
                                    $alg_price = get_post_meta($i_prod['id'], '_alg_currency_switcher_per_product_regular_price_' . $current_curr, true);

                                    if ($alg_price && $alg_price !== '') :
                                        $product_price = $alg_price;
                                    else :
                                        $product_price = $product_price * $ex_rate;
                                    endif;

                                    // get total regular price if not default currency
                                    $sum_price_regular += $product_price;
                                // else :
                                //     $sum_price_regular += $product_price;
                                // endif;

                                endforeach;

                                // discount percent
                                $i_coupon = (float)$prod['discount_percentage'];

                                // get price total bundle
                                if ($i_price) :
                                    // $sum_price_regular      = $i_price;
                                    $cus_bundle_total_price = $i_price;
                                endif;

                                $subtotal_bundle = $sum_price_regular;

                                // apply discount percentage
                                if ($prod['discount_percentage'] > 0) :
                                    $subtotal_bundle -= ($subtotal_bundle * $i_coupon / 100);
                                endif;

                                $price_discount = $sum_price_regular - $subtotal_bundle;

                            endif;

                            // free and off product bundles
                            if ($prod['type'] == 'free' || $prod['type'] == 'off') : ?>

                                <div class="col col-lg-4 col-md-4 col-sm-4 col-xs-4 item-selection col-hover-focus bd_item_div bd_item_div_<?php echo trim($prod['bun_id']) ?> bd_c_package_option <?= ($pbs_default_id == $prod['bun_id']) ? 'bd_selected_default_opt bd_active_product' : '' ?>" data-type="<?php echo trim($prod['type']) ?>" data-bundle_id="<?php echo trim($prod['bun_id']) ?>" data-coupon="<?= round((float)$i_coupon, 0) ?>">
                                <?php else : ?>

                                    <li class="col col-lg-4 col-md-4 col-sm-4 col-xs-4 item-selection col-hover-focus bd_item_div bd_item_div_<?php echo trim($prod['bun_id']) ?> bd_c_package_option <?= ($pbs_default_id == $prod['bun_id']) ? 'bd_selected_default_opt bd_active_product' : '' ?>" data-type="<?php echo trim($prod['type']) ?>" data-bundle_id="<?php echo trim($prod['bun_id']) ?>" data-coupon="<?= round((float)$i_coupon, 0) ?>">

                                    <?php endif; ?>

                                    <!-- js input hidden data package -->
                                    <input type="hidden" class="js-input-discount_package" data-type="<?php echo $js_discount_type ?>" data-qty="<?php echo $js_discount_qty ?>" value="<?php echo $js_discount_value ?>">
                                    <input type="hidden" data-per-item-price="<?php echo $product_price; ?>" class="js-input-cus_bundle_total_price" value="<?php echo $cus_bundle_total_price ?>">

                                    <!-- results -->
                                    <input type="hidden" class="js-input-price_package" value="">
                                    <input type="hidden" class="js-input-price_summary" value="">

                                    <!-- package pricing data/overview -->
                                    <?php self::pbs_bundle_pricing_overview($prod, $product, $i_coupon, $i_title, $p_id, $opt_i, $subtotal_bundle, $price_discount, $sum_price_regular, $i_price_total, $product_price, $i_price, $i_total_qty, $current_curr) ?>

                                </div>

                            <?php
                            $p_i++;
                        endforeach;
                            ?>
                    </div>
                    <?php foreach ($package_product_ids as $opt_i => $prod) :

                        //  retrieve correct product id
                        if (in_array($prod['type'], ['free', 'off'])) :
                            $p_id = (int)$prod['id'];
                        endif;

                        if ($prod['type'] == 'bun') :
                            $p_id = (int)$prod['prod'][0]['id'];
                        endif;

                        // type free
                        if ($prod['type'] == 'free') :
                            // coupon
                            $i_total_qty    = (int)$prod['qty'] + (int)$prod['qty_free'];
                            $i_coupon        = ((int)$prod['qty_free'] * 100) / $i_total_qty;
                        endif;

                        // type off
                        if ($prod['type'] == 'off') :
                            $i_coupon        = (float)$prod['coupon'];
                        endif;

                        // type bun
                        if ($prod['type'] == 'bun') :
                            // discount percent
                            $i_coupon = (float)$prod['discount_percentage'];

                        endif; ?>

                        <!-- variations row -->
                        <div class="row bd_variations_row">

                            <!-- Variation dropdowns (free/paid & standard/linked) -->
                            <div id="bd_product_variations_<?php echo trim($prod['bun_id']); ?>" style="display: none;" class="bd_product_variations info_products_checkout <?= (($prod['type'] == 'free' || $prod['type'] == 'off') && $product->is_type('variable')) ? 'is_variable' : '' ?>" data-coupon="<?php echo $i_coupon; ?>" data-bundle_id="<?php echo trim($prod['bun_id']); ?>" data-bundle="<?php echo base64_encode(json_encode($prod)); ?>">

                                <!-- render paid variations dd/linked html -->
                                <?php self::pbs_render_paid_variations($prod, $p_id, $product, $current_curr); ?>

                                <!-- variations free products -->
                                <?php if ($prod['type'] == 'free' && isset($prod['qty_free']) && $prod['qty_free'] > 0) : ?>

                                    <!-- render free variation dd/linked html -->
                                    <?php self::pbs_render_free_variations($p_id, $product, $prod, $current_curr); ?>

                                <?php endif; ?>

                            </div>
                            <!-- end product variations form -->
                        </div>
                    <?php endforeach; ?>

                </div>
            </div>

        <?php

            // setup package JSON data
            self::pbs_setup_pkg_json_data($var_data, $variation_price);

            // change linked variation product image on swatch label click
            self::pbs_wcva_on_click();
        }

        /**
         * Renders free variations dropdown/linked HTML
         *
         * @param int $p_id
         * @param obj $product
         * @param arr $prod
         * @return void
         */
        public static function pbs_render_free_variations($p_id, $product, $prod, $current_curr)
        { ?>

            <h5 class="title_form"><?= __('Select Free Product') ?>:</h5>

            <table class="product_variations_table">
                <tbody>

                    <?php for ($i = 0; $i < $prod['qty_free']; $i++) : ?>

                        <!-- c_prod_item -->
                        <tr class="c_prod_item free-item" data-id="<?php echo ($p_id) ?>" <?= (!$product->is_type('variable')) ? 'hidden' : '' ?>>

                            <?php if ($product->is_type('variable')) : ?>

                                <!-- variation img -->
                                <td class="variation_img">
                                    <?php $pobj = wc_get_product($p_id); ?>
                                    <img class="bd_variation_img" src="<?= wp_get_attachment_image_src($pobj->get_image_id())[0] ?>">
                                </td>

                                <!-- variation selectors -->
                                <td class="variation_selectors">
                                    <?php

                                    // show variations linked by variations
                                    echo self::pbs_return_linked_product_dropdown([
                                        'product_id'        => $p_id,
                                        'class'             => 'var_prod_attr bundle_dropdown_attr',
                                    ], $var_data, $prod, $current_curr);

                                    $prod_variations = $product->get_variation_attributes();

                                    foreach ($prod_variations as $attribute_name => $options) :
                                        $default_opt = '';
                                        try {
                                            $default_opt =  $product->get_default_attributes()[$attribute_name];
                                        } catch (\Throwable $th) {
                                        }
                                    ?>

                                        <div class="variation_item">
                                            <p class="variation_name"><?= wc_attribute_label($attribute_name) ?>: </p>

                                            <!-- load dropdown variations -->
                                            <?php

                                            echo self::pbs_return_opc_variations_dropdown([
                                                'product_id'     => $p_id,
                                                'options'        => $options,
                                                'attribute_name' => $attribute_name,
                                                'default_option' => $default_opt,
                                                'var_data'       => $var_data[$p_id],
                                                'class'          => 'var_prod_attr bundle_dropdown_attr',
                                            ]);
                                            ?>

                                        </div>
                                    <?php endforeach; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php
                    endfor; ?>
                </tbody>
            </table>

        <?php }

        /**
         * Render paid variations dropdown/linked products
         *
         * @param arr $prod
         * @param int $p_id
         * @param obj $product
         * @param string $current_curr
         * @return void
         */
        public static function pbs_render_paid_variations($prod, $p_id, $product, $current_curr)
        { ?>

            <h5 class="title_form"><?= __('Select Product') ?>:</h5>

            <table class="product_variations_table">
                <tbody>
                    <?php
                    //package selection variations free and off
                    if ($prod['type'] == 'free' || $prod['type'] == 'off') :

                        // get variation images product
                        if (!isset($var_data[$p_id]) && $product->is_type('variable')) :

                            $var_arr = [];

                            foreach ($product->get_available_variations() as $key => $var_data) :
                                array_push($var_arr, [
                                    'id'         => (int)$var_data['variation_id'],
                                    'price'      => (float)$prod['custom_price'][$var_data['variation_id']][$current_curr],
                                    'attributes' => $var_data['attributes'],
                                    'image'      => $var_data['image']['url']
                                ]);
                            endforeach;

                            $var_data[$p_id] = $var_arr;
                        endif;

                        for ($i = 0; $i < $prod['qty']; $i++) : ?>

                            <!-- c_prod_item -->
                            <tr class="c_prod_item" data-id="<?php echo ($p_id) ?>" <?= (!$product->is_type('variable')) ? 'hidden' : '' ?>>

                                <?php if ($product->is_type('variable')) : ?>

                                    <!-- variation img -->
                                    <td class="variation_img">
                                        <?php $pobj = wc_get_product($p_id); ?>
                                        <img class="bd_variation_img" src="<?= wp_get_attachment_image_src($pobj->get_image_id())[0] ?>">
                                    </td>

                                    <!-- variation selectors -->
                                    <td class="variation_selectors">
                                        <?php

                                        // show variations linked by variations
                                        echo self::pbs_return_linked_product_dropdown([
                                            'product_id'        => $p_id,
                                            'class'             => 'var_prod_attr bundle_dropdown_attr',
                                        ], $var_data, $prod, $current_curr);

                                        $prod_variations = $product->get_variation_attributes();

                                        foreach ($prod_variations as $attribute_name => $options) :
                                            $default_opt = '';
                                            try {
                                                $default_opt =  $product->get_default_attributes()[$attribute_name];
                                            } catch (\Throwable $th) {
                                            }
                                        ?>

                                            <div class="variation_item">
                                                <p class="variation_name"><?= wc_attribute_label($attribute_name) ?>: </p>

                                                <!-- load dropdown variations -->
                                                <?php
                                                echo self::pbs_return_opc_variations_dropdown([
                                                    'product_id'     => $p_id,
                                                    'options'        => $options,
                                                    'attribute_name' => $attribute_name,
                                                    'default_option' => $default_opt,
                                                    'var_data'       => $var_data[$p_id],
                                                    'class'          => 'var_prod_attr bundle_dropdown_attr',
                                                ]);
                                                ?>

                                            </div>
                                        <?php endforeach; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                            <?php
                        endfor;
                    endif;

                    // if bundle is type "bundle"
                    if ($prod['type'] == 'bun') :

                        //package selection bundle
                        $_index = 1;

                        foreach ($prod['prod'] as $i => $i_prod) :

                            $p_id = (int)$i_prod['id'];
                            $b_product = wc_get_product($p_id);

                            // get variation images product
                            if (!isset($var_data[$p_id]) && $b_product->is_type('variable')) {

                                $var_arr = [];

                                foreach ($b_product->get_available_variations() as $key => $var_data) {
                                    array_push($var_arr, [
                                        'id'         => (int)$var_data['variation_id'],
                                        'price'      => (float)$prod['custom_price'][$var_data['variation_id']][$current_curr],
                                        'attributes' => $var_data['attributes'],
                                        'image'      => $var_data['image']['url']
                                    ]);
                                }
                                $var_data[$p_id] = $var_arr;
                            }

                            for ($i = 1; $i <= $i_prod['qty']; $i++) : ?>

                                <!-- c_prod_item -->
                                <tr class="c_prod_item" data-id="<?php echo ($p_id) ?>" <?= (!$b_product->is_type('variable')) ? 'hidden' : '' ?>>

                                    <?php if ($b_product->is_type('variable')) : ?>

                                        <!-- variation img -->
                                        <td class="variation_img">
                                            <?php $pobj = wc_get_product($p_id); ?>
                                            <img id="prod_image" class="bd_variation_img" src="<?= wp_get_attachment_image_src($pobj->get_image_id())[0] ?>">
                                        </td>

                                        <!-- variation selectors -->
                                        <td class="variation_selectors">

                                            <?php

                                            // show variations linked by variations
                                            echo self::pbs_return_linked_product_dropdown([
                                                'product_id'        => $p_id,
                                                'class'             => 'var_prod_attr bundle_dropdown_attr',
                                            ], $var_data, $prod, $current_curr);

                                            $prod_variations = $b_product->get_variation_attributes();

                                            foreach ($prod_variations as $attribute_name => $options) :
                                                $default_opt = '';
                                                try {
                                                    $default_opt =  $b_product->get_default_attributes()[$attribute_name];
                                                } catch (\Throwable $th) {
                                                    $default_opt = '';
                                                }
                                            ?>
                                                <div class="variation_item">
                                                    <p class="variation_name mb-0 mr-2"><?= wc_attribute_label($attribute_name) ?>: </p>

                                                    <!-- load dropdown variations -->
                                                    <?php
                                                    echo self::pbs_return_opc_variations_dropdown([
                                                        'product_id'     => $p_id,
                                                        'options'        => $options,
                                                        'attribute_name' => $attribute_name,
                                                        'default_option' => $default_opt,
                                                        'var_data'       => $var_data[$p_id],
                                                        'class'          => 'var_prod_attr bundle_dropdown_attr',
                                                    ]);
                                                    ?>

                                                </div>
                                            <?php endforeach; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endfor; ?>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>

        <?php }

        /**
         * Renders bundle overview (pricing etc)
         *
         * @param arr $prod
         * @param obj $product
         * @param float $i_coupon
         * @param string $i_title
         * @param int $p_id
         * @param int $opt_i
         * @param float $subtotal_bundle
         * @param float $price_discount
         * @param float $sum_price_regular
         * @param float $i_price_total
         * @param float $product_regular_price
         * @param float $product_sale_price
         * @param float $i_price
         * @param int $i_total_qty
         * @return void
         */
        public static function pbs_bundle_pricing_overview($prod, $product, $i_coupon, $i_title, $p_id, $opt_i, $subtotal_bundle, $price_discount, $sum_price_regular, $i_price_total, $product_price, $i_price, $i_total_qty, $current_curr)
        { ?>

            <div class="col-inner box-shadow-2 box-shadow-3-hover box-item">

                <div class="bd_item_infos_div bd_collapser_inner i_row i_clearfix">

                    <div class="bd_c_package_content">

                        <!-- bundle image -->
                        <div class="bd_c_package_image">

                            <?php if (wp_is_mobile() && $prod['image_package_mobile']) : ?>
                                <img data-has-hover-img="<?php echo isset($prod['image_package_hover']) ? true : false ?>" src="<?php echo $prod['image_package_mobile'] ?>" class="pbs_orig_img attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="">
                                <img style="display: none;" src="<?php echo $prod['image_package_hover'] ?>" class="pbs_hover_img attachment-woocommerce_thumbnail size-woocommerce_thumbnail" style="display:none;">
                            <?php elseif ($prod['image_package_desktop']) : ?>
                                <img data-has-hover-img="<?php echo isset($prod['image_package_hover']) ? true : false ?>" src="<?php echo $prod['image_package_desktop'] ?>" class="pbs_orig_img attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="">
                                <img src="<?php echo $prod['image_package_hover'] ?>" class="pbs_hover_img attachment-woocommerce_thumbnail size-woocommerce_thumbnail" style="display:none;">
                            <?php else :
                                echo $product->get_image("woocommerce_thumbnail");
                            endif;

                            // show discout label
                            if ($prod['show_discount_label']) : ?>
                                <span class="show_discount_label"><?php echo (sprintf(__('%s&#37; OFF'), round($i_coupon, 0))) ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- bundle title -->
                        <div class="bd_c_package_title d-block">

                            <div class="pi-1"><?php echo $i_title ?></div>

                            <?php if ($prod['type'] == 'free' || $prod['type'] == 'off') : ?>
                                <input type="checkbox" name="bd_selected_package_product" data-product_id="<?php echo $p_id ?>" data-index="<?php echo ($opt_i) ?>" value="<?php echo $p_id ?>" class="d-none bd_selected_package_product product_id">
                            <?php else : ?>
                                <input type="checkbox" name="bd_selected_package_product" data-product_id="<?php echo $p_id ?>" data-index="<?php echo ($opt_i) ?>" value="<?php echo $p_id ?>" class="d-none bd_selected_package_product product_id">
                            <?php endif; ?>

                            <span class="show_discount_label"><?php echo (sprintf(__('%s&#37; OFF'), round((float)$i_coupon, 0))) ?></span>
                        </div>

                        <div class="bd_c_package_info text-center">

                            <!-- standard bundle -->
                            <?php if ($prod['type'] == 'bun') : ?>

                                <!-- per item price -->
                                <div class="pi-price-pricing">
                                    <div class="pi-price-each pl-lg-1">
                                        <span class="js-label-price_total">
                                            <?php echo wc_price($product_price, ['ex_tax_label' => false, 'currency' => $current_curr]); ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- total price -->
                                <div class="pi-price-total">
                                    <span class="js-label-price_total"><?php echo wc_price($i_price_total); ?></span>
                                </div>

                                <!-- get prices bundle -->
                                <input type="hidden" class="bd_bundle_price_hidden" data-label="<?= $i_title ?>" value="<?= $subtotal_bundle ?>">
                                <input type="hidden" class="bd_bundle_price_regular_hidden" data-label="<?= __('Old Price') ?>" value="<?= $subtotal_bundle + ($subtotal_bundle * ($i_coupon / 100));  ?>">
                                <input type="hidden" class="bd_bundle_product_qty_hidden" value="<?= $i_total_qty ?>">

                                <!-- free/off bundle -->
                            <?php else : ?>

                                <!-- per item price -->
                                <div class="pi-price-pricing">
                                    <div class="pi-price-each pl-lg-1">
                                        <span><?php echo wc_price($price_discount, ['ex_tax_label' => false, 'currency' => $current_curr]); ?></span>
                                        <span class="pi-price-each-txt">/ <?php echo __('each'); ?></span>
                                    </div>
                                </div>

                                <!-- total price -->
                                <div class="pi-price-total">
                                    <span><?php echo wc_price($i_price_total, ['ex_tax_label' => false, 'currency' => $current_curr]); ?></span>
                                </div>

                                <!-- get prices bundle -->
                                <input type="hidden" class="bd_bundle_price_hidden" data-label="<?= $i_title ?>" value="<?= $i_price_total ?>">
                                <input type="hidden" class="bd_bundle_price_regular_hidden" data-label="<?= __('Old Price') ?>" value="<?= $product_price * $i_total_qty ?>" data-test="test 125">
                                <input type="hidden" class="bd_bundle_price_old_hidden" data-label="<?= __('Old Price') ?>" value="<?= $product_price ?>">
                                <input type="hidden" class="bd_bundle_product_qty_hidden" value="<?= $i_total_qty ?>">

                            <?php endif; ?>
                        </div> <!-- end bd_c_package_info -->
                    </div> <!-- end bd_c_package_content -->

                </div> <!-- end bd_item_infos_div -->
            </div>

            <?php //self::pbs_regional_sizes(); 
            ?>

            <?php //self::pbs_pkg_variations_js(); 
            ?>

        <?php }

        /**
         * Setup package JSON data
         *
         * @param arr $var_data
         * @param arr $variation_price
         * @return void
         */
        public static function pbs_setup_pkg_json_data($var_data, $variation_price)
        { ?>
            <script>
                var opc_variation_data = <?= json_encode($var_data) ?>;
                var bd_variation_price = <?= json_encode($variation_price) ?>;
            </script>

        <?php }

        /**
         * Handles variation selector related JS (show/hide/update prices/etc)
         *
         * @return void
         */
        public static function pbs_pkg_variations_js()
        {

            global $pbs_is_default_gall;

        ?>
            <script id="pbs_pkg_variations_js" data-is-default-gall="<?php echo $pbs_is_default_gall ? 'yes' : 'no'; ?>">
                $ = jQuery;

                /**
                 * Determine stock availability on swatch click
                 */
                $('.wcvaswatchlabel, .imgclasssmall, .imgclasssmallactive').click(function(e) {

                    e.preventDefault();

                    // hide current gallery and show linked product gallery if default gallery being used
                    if ($('#pbs_pkg_variations_js').attr('data-is-default-gall') === 'yes') {

                        var linked_prod_id = $(this).attr('data-linked_id');

                        // DEBUG
                        // console.log(linked_prod_id);

                        $('.pbs_custom_std_gallery').hide();
                        $('#pbs_custom_std_gallery_' + linked_prod_id).show();

                    }

                    // remove any out of stock messages which might previously have been present
                    $('#pbs_out_of_stock_msg').remove();

                    var variation_stock_data = JSON.parse(atob($(this).data('vars')));
                    var current_attribs = {};
                    var is_in_stock;
                    var variation_id;
                    var stock_msg;
                    var availability_class;

                    // retrieve current attribs
                    $(this).parents('.c_prod_item').find('.var_prod_attr').each(function(i, e) {
                        var this_val = $(this).val();
                        var this_attrib = $(this).data('attribute_name');
                        current_attribs[this_attrib] = this_val;
                    });

                    // find match between current and prod attribs and return stock status
                    $.each(variation_stock_data, function(ind, value) {
                        if (JSON.stringify(current_attribs) === JSON.stringify(value.attributes)) {
                            is_in_stock = value.is_in_stock;
                            variation_id = value.variation_id;
                            stock_msg = value.availability.availability;
                            availability_class = value.availability.class;
                        }
                    });

                    // DEBUG
                    // console.log('variation stock data: ');
                    // console.log(variation_stock_data);
                    // console.log(is_in_stock);
                    // console.log('current attribs: ');
                    // console.log(current_attribs);
                    // console.log(variation_id);
                    // console.log(stock_msg);
                    // console.log(is_in_stock);
                    // console.log(availability_class);

                    // if not in stock, display error message
                    if (!is_in_stock || availability_class === 'available-on-backorder') {
                        $(this).parents('.variation_selectors').append('<span id="pbs_out_of_stock_msg">' + stock_msg + '</span>');

                        // add extra class if backorder message
                        if (availability_class === 'available-on-backorder') {
                            $('#pbs_out_of_stock_msg').addClass('is_backorder_msg');
                        }

                    } else {
                        $('#pbs_out_of_stock_msg').remove();
                    }

                    // if is not in stock, disable by now button, else enable
                    if (!is_in_stock) {
                        $('#pbs_bundle_atc').addClass('pbs_atc_disabled');
                    } else {
                        $('#pbs_bundle_atc').removeClass('pbs_atc_disabled');
                    }
                });

                /**
                 * Show/hide variations containers & change to hover image on page load/bundle select/bundle hover
                 */
                $('.bd_item_div').each(function(index, container) {

                    var bd_cont = $(this);

                    /**
                     * bundle div on mousedown/click
                     */
                    bd_cont.on('click mousedown mouseover mouseout', function(e) {

                        // DEBUG
                        // console.log(e);

                        // on click or mousedown
                        if (e.type === 'click' || e.type === 'mousedown') {

                            $('.bd_item_div').removeClass('bd_active_product bd_selected_default_opt');
                            $('.bd_item_div').find('.col-inner').removeClass('bd_active');
                            bd_cont.addClass('bd_active_product');
                            bd_cont.find('.col-inner').addClass('bd_active');

                            $('.bd_product_variations').hide();
                            $('#bd_product_variations_' + bd_cont.data('bundle_id')).show();

                            // reset hover images for all bd containers
                            if ($(document).find('.pbs_hover_img').attr('src') !== '') {
                                $(document).find('.pbs_hover_img').hide();
                                $(document).find('.pbs_orig_img').show();
                            }

                            // show hover image
                            if (bd_cont.find('.pbs_hover_img').attr('src') !== '') {
                                bd_cont.find('.pbs_hover_img').show();
                                bd_cont.find('.pbs_orig_img').hide();
                            }
                        }

                        // on mouseover and class not active
                        if (e.type === 'mouseover' && !bd_cont.hasClass('bd_active_product') && bd_cont.find('.pbs_hover_img').attr('src') !== '') {
                            bd_cont.find('.pbs_hover_img').show();
                            bd_cont.find('.pbs_orig_img').hide();
                        }

                        // on mouseout and class not active
                        if (e.type === 'mouseout' && !bd_cont.hasClass('bd_active_product') && bd_cont.find('.pbs_hover_img').attr('src') !== '') {
                            bd_cont.find('.pbs_hover_img').hide();
                            bd_cont.find('.pbs_orig_img').show();
                        }

                    });

                });

                /**
                 * Load correct bundle price on page load
                 */
                $('.var_prod_attr').each(function(index, element) {
                    // element == this

                    var top_parent = $(this).parents('.bd_product_variations');
                    var reg_price_total = 0;
                    var coupon = $(this).parents('.bd_product_variations').data('coupon');
                    var bundle_id = $(this).parents('.bd_product_variations').data('bundle_id');
                    var prod_qty = top_parent.find('.var_prod_attr').length;
                    var alg_pricing = JSON.parse(atob($(this).data('alg-pricing')));
                    var current_curr = $(this).data('currency');
                    var curr_sym = $(this).data('curr-symbol');
                    var ex_rate = $(this).data('ex-rate');

                    top_parent.find('.var_prod_attr').each(function(ind, elem) {

                        var var_data = JSON.parse(atob($(this).data('variations')));
                        var this_val = $(this).val();

                        $.each(var_data, function(index, value) {

                            try {
                                if (this_val === value.attributes.attribute_pa_size) {
                                    reg_price_total += parseFloat(alg_pricing[value.variation_id]);
                                }
                            } catch (error) {
                                console.log(error);
                            }
                        });

                    });

                    // calculate and format bundle and per item prices
                    var new_bundle_price = parseFloat((reg_price_total * ((100 - parseFloat(coupon)) / 100).toFixed(2)));
                    var per_item_price = parseFloat((new_bundle_price / prod_qty).toFixed(2));

                    // update above prices in DOM
                    $('.bd_item_div_' + bundle_id).find('.pi-price-each > span > span > bdi').empty().append(curr_sym + per_item_price.toFixed(2));
                    $('.bd_item_div_' + bundle_id).find('.pi-price-total > span > span > bdi').empty().append(curr_sym + new_bundle_price.toFixed(2));

                });

                /**
                 * Update bundle price on variation dropdown change
                 */
                $('.var_prod_attr').change(function(e) {

                    e.preventDefault();

                    // hide and stock related messages
                    $('#pbs_out_of_stock_msg').remove();

                    // var target_id = $(this).parents;

                    var variation_stock_data = JSON.parse(atob($(this).parents('.c_prod_item').find('.wcvaswatchlabel.selected, .imgclasssmall.selected').data('vars')));
                    var current_attribs = {};
                    var is_in_stock;
                    var variation_id;
                    var stock_msg;
                    var availability_class;

                    // retrieve current attribs
                    $(this).parents('.c_prod_item').find('.var_prod_attr').each(function(i, e) {
                        var this_val = $(this).val();
                        var this_attrib = $(this).data('attribute_name');
                        current_attribs[this_attrib] = this_val;
                    });

                    // find match between current and prod attribs and return stock status
                    $.each(variation_stock_data, function(ind, value) {
                        if (JSON.stringify(current_attribs) === JSON.stringify(value.attributes)) {
                            is_in_stock = value.is_in_stock;
                            variation_id = value.variation_id;
                            stock_msg = value.availability.availability;
                            availability_class = value.availability.class;
                        }
                    });

                    // DEBUG
                    // console.log('variation stock data: ');
                    // console.log(variation_stock_data);
                    // console.log('current attribs: ');
                    // console.log(current_attribs);
                    // console.log(variation_id);
                    // console.log(stock_msg);
                    // console.log(is_in_stock);
                    // console.log(availability_class);

                    // if not in stock, display error message
                    if (!is_in_stock || availability_class === 'available-on-backorder') {

                        $(this).parents('.variation_selectors').append('<span id="pbs_out_of_stock_msg">' + stock_msg + '</span>');

                        // add extra class if backorder message
                        if (availability_class === 'available-on-backorder') {
                            $('#pbs_out_of_stock_msg').addClass('is_backorder_msg');
                        }

                    } else {
                        $('#pbs_out_of_stock_msg').remove();
                    }

                    // if is not in stock, disable by now button, else enable
                    if (!is_in_stock) {
                        $('#pbs_bundle_atc').addClass('pbs_atc_disabled');
                    } else {
                        $('#pbs_bundle_atc').removeClass('pbs_atc_disabled');
                    }

                    var top_parent = $(this).parents('.bd_product_variations');
                    var reg_price_total = 0;
                    var coupon = $(this).parents('.bd_product_variations').data('coupon');
                    var bundle_id = $(this).parents('.bd_product_variations').data('bundle_id');
                    var prod_qty = top_parent.find('.var_prod_attr').length;
                    var alg_pricing = JSON.parse(atob($(this).data('alg-pricing')));
                    var current_curr = $(this).data('currency');
                    var curr_sym = $(this).data('curr-symbol');
                    var ex_rate = $(this).data('ex-rate');

                    // uncomment to debug
                    // console.log(alg_pricing);

                    top_parent.find('.var_prod_attr').each(function(ind, elem) {

                        var var_data = JSON.parse(atob($(this).data('variations')));

                        // console.log(var_data);

                        var this_val = $(this).val();

                        $.each(var_data, function(index, value) {
                            if (this_val === value.attributes.attribute_pa_size) {
                                reg_price_total += parseFloat(alg_pricing[value.variation_id]);
                            }
                        });

                    });

                    // calculate and format bundle and per item prices
                    var new_bundle_price = parseFloat((reg_price_total * ((100 - parseFloat(coupon)) / 100).toFixed(2)));
                    var per_item_price = parseFloat((new_bundle_price / prod_qty).toFixed(2));

                    // update above prices in DOM
                    $('.bd_item_div_' + bundle_id).find('.pi-price-each > span > span > bdi').empty().append(curr_sym + per_item_price.toFixed(2));
                    $('.bd_item_div_' + bundle_id).find('.pi-price-total > span > span > bdi').empty().append(curr_sym + new_bundle_price.toFixed(2));

                });

                /**
                 * Show default variations dropdowns on page load
                 */
                $('.bd_product_variations').each(function(index, element) {

                    element = $(this);

                    // get bundle id
                    var bun_id = element.data('bundle_id');

                    // get parent
                    var parent = $(document).find('.bd_item_div_' + bun_id);

                    // show variations container if default product set
                    if (parent.hasClass('bd_selected_default_opt')) {
                        $('#bd_product_variations_' + bun_id).show();
                    }

                });
            </script>
        <?php }

        /**
         * Change variable product image src on swatch label click
         *
         * @return void
         */
        public static function pbs_wcva_on_click()
        { ?>

            <script>
                $ = jQuery;

                $('.wcvaswatchlabel').click(function() {
                    $(this).parents('.c_prod_item').find('.bd_variation_img').attr('src', $(this).attr('img-src'));
                });

                $('.tooltipplugify').click(function() {
                    $(this).parent().find('.wcvaswatchlabel').removeClass('selected');
                    $(this).parent().find('.tooltipplugify').removeClass('selected');
                    $(this).addClass('selected');
                    $(this).parents('.c_prod_item').find('.bd_variation_img').attr('src', $(this).attr('img-src'));
                });
            </script>

<?php }
    }
endif;
