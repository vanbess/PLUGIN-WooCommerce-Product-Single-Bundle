<?php

defined('ABSPATH') ?: exit();

if (!trait_exists('PBS_Retrieve_All_Bundle_Data')) :

    trait PBS_Retrieve_All_Bundle_Data {

        /**
         * Queries and returns bundle date for all specified bundle ids
         *
         * @param array $bundle_ids - the bundle ids to retrieve data for
         * @return array $bundle_data or false of no data found
         */
        public static function pbs_retrieve_all_bundle_data($bundle_ids) {

            // setup array $bundle_data to hold all relevant bundle data for all bundles
            $bundle_data = [];

            // loop to extract bundle data and push to $bundle_data array
            foreach ($bundle_ids as $key => $bundle_id) :

                // get correct bundle ID for current language if it exists
                if (function_exists('pll_get_post')) :
                    $bundle_id = pll_get_post($bundle_id, pll_current_language()) ?: $bundle_id;
                endif;

                // retrieve discount data
                $discount_data = get_post_meta($bundle_id, 'product_discount', TRUE);

                // if discount data is not array, we might have to decode
                if (!is_array($discount_data)) :
                    $discount_data = json_decode($discount_data, true);
                endif;

                if (isset($discount_data['selValue'])) :

                    $bundle_data[$key]['type']                  = $discount_data['selValue'];
                    $bundle_data[$key]['bun_id']                = $bundle_id;
                    $bundle_data[$key]['description']           = $discount_data['description'];
                    $bundle_data[$key]['image_package_desktop'] = isset($discount_data['image_package_desktop']) ? $discount_data['image_package_desktop'] : "";
                    $bundle_data[$key]['image_package_mobile']  = isset($discount_data['image_package_mobile']) ? $discount_data['image_package_mobile'] : "";
                    $bundle_data[$key]['image_package_hover']   = isset($discount_data['image_package_hover']) ? $discount_data['image_package_hover'] : "";
                    $bundle_data[$key]['feature_description']   = $discount_data['feature_description'];
                    $bundle_data[$key]['label_item']            = $discount_data['label_item'];
                    $bundle_data[$key]['discount_percentage']   = (isset($discount_data['discount_percentage']) && is_numeric($discount_data['discount_percentage'])) ? $discount_data['discount_percentage'] : 0;
                    $bundle_data[$key]['sell_out_risk']         = isset($discount_data['sell_out_risk']) ? $discount_data['sell_out_risk'] : "";
                    $bundle_data[$key]['popularity']            = isset($discount_data['popularity']) ? $discount_data['popularity'] : "";
                    $bundle_data[$key]['free_shipping']         = isset($discount_data['free_shipping']) ? $discount_data['free_shipping'] : "";
                    $bundle_data[$key]['show_discount_label']   = isset($discount_data['show_discount_label']) ? $discount_data['show_discount_label'] : "";

                    // free type
                    if ($discount_data['selValue'] == 'free') :
                        $bundle_data[$key]['product_name']  = isset($discount_data['product_name']) ? $discount_data['product_name'] : '';
                        $bundle_data[$key]['title_package'] = isset($discount_data['title_package']) ? $discount_data['title_package'] : "";
                        $bundle_data[$key]['id']            = str_replace(' ', '', $discount_data['selValue_free']['post']['id']);
                        $bundle_data[$key]['qty']           = $discount_data['selValue_free']['quantity'];
                        $bundle_data[$key]['id_free']       = $discount_data['selValue_free_prod']['post']['id'];
                        $bundle_data[$key]['qty_free']      = $discount_data['selValue_free_prod']['quantity'];
                        $bundle_data[$key]['custom_price']  = isset($discount_data['custom_price']) ? $discount_data['custom_price'] : '';
                    endif;

                    // off type
                    if ($discount_data['selValue'] == 'off') :
                        $bundle_data[$key]['product_name']  = isset($discount_data['product_name']) ? $discount_data['product_name'] : '';
                        $bundle_data[$key]['title_package'] = isset($discount_data['title_package']) ? $discount_data['title_package'] : "";
                        $bundle_data[$key]['id']            = str_replace(' ', '', $discount_data['selValue_off']['post']['id']);
                        $bundle_data[$key]['qty']           = $discount_data['selValue_off']['quantity'];
                        $bundle_data[$key]['coupon']        = $discount_data['selValue_off']['coupon'];
                        $bundle_data[$key]['custom_price']  = isset($discount_data['custom_price']) ? $discount_data['custom_price'] : '';
                    endif;

                    // bun type
                    if ($discount_data['selValue'] == 'bun') :

                        // get current WC currency and set default rate
                        $curr      = function_exists('alg_get_current_currency_code') ? alg_get_current_currency_code() : get_woocommerce_currency();
                        $curr_rate = 1;

                        // change $curr_rate if currency converter plugin is installed
                        if (function_exists('alg_wc_cs_get_currency_exchange_rate')) :
                            $curr_rate = alg_wc_cs_get_currency_exchange_rate($curr);
                        endif;

                        // continue setup
                        $bundle_data[$key]['title_header']  = isset($discount_data['title_header']) ? $discount_data['title_header'] : "";
                        $bundle_data[$key]['title_package'] = isset($discount_data['title_package_bundle']) ? $discount_data['title_package_bundle'] : "";
                        $bundle_data[$key]['total_price']   = is_numeric($discount_data['selValue_bun']['price_currency'][$curr]) ? $discount_data['selValue_bun']['price_currency'][$curr] : (current($discount_data['selValue_bun']['price_currency']) > 0 ? current($discount_data['selValue_bun']['price_currency']) * $curr_rate : false);

                        // retrieve bundle id => bundle qty combinations
                        foreach ($discount_data['selValue_bun']['post'] as $i => $bun) :
                            $bundle_data[$key]['prod'][$i]['id'] = $bun['id'];
                            $bundle_data[$key]['prod'][$i]['qty'] = $bun['quantity'];
                        endforeach;

                    endif;
                endif;
            endforeach;

            // bail if $bundle_data is empty
            if (empty($bundle_data)) :
                return false;
            else :
                return $bundle_data;
            endif;
        }
    }

endif;
