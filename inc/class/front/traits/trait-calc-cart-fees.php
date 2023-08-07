<?php

defined('ABSPATH') ?: exit();

if (!trait_exists('PBS_Calculate_Cart_Fees')) :

    trait PBS_Calculate_Cart_Fees {

        /**
         * Calculates bundle fees and adds said fees to cart
         *
         * @param object $cart
         * @return void
         */
        public static function pbs_calculate_cart_fees($cart) {

            $bundle_id         = null;
            $cart_prod         = [];
            $cart_prod_post_id = [];
            $cart_qty          = 0;
            $subtotal          = 0;

            foreach ($cart->get_cart() as $cart_item) {

                if (isset($cart_item['pbs_bundle_dd']) && isset($cart_item['product_id']) && isset($cart_item['quantity'])) {

                    // get bundle bd id
                    $bundle_id = $cart_item['bundle_id'];

                    // get total qty
                    $cart_qty += $cart_item['quantity'];

                    // get subtotal cart
                    $subtotal += $cart_item['data']->get_price() * $cart_item['quantity'];

                    if (isset($cart_prod[$cart_item['product_id']])) {
                        $cart_prod[$cart_item['product_id']] += $cart_item['quantity'];
                        $cart_prod_post_id[] = $cart_item['bd_prod_post_id'];
                    } else {
                        $cart_prod[$cart_item['product_id']] = $cart_item['quantity'];
                        $cart_prod_post_id[] = $cart_item['bd_prod_post_id'];
                    }
                }
            }

            // current currency and bundle discount data
            $current_curr     = function_exists('alg_get_current_currency_code') ? alg_get_current_currency_code() : get_woocommerce_currency();
            $bundle_selection = get_post_meta($bundle_id, 'product_discount', true);
            $bundle_selection = is_array($bundle_selection) ? $bundle_selection : json_decode($bundle_selection, true);

            // apply discount FREE
            if ($bundle_selection['selValue'] == 'free') {

                file_put_contents(PBS_Bundle_Path . 'free_bun_data.txt', print_r($bundle_selection, true));

                if ($cart_qty >= ($bundle_selection['selValue_free']['quantity'] + $bundle_selection['selValue_free_prod']['quantity'])) {

                    $free_prod = $bundle_selection['selValue_free_prod'];
                    $free_prod_count = (int)$bundle_selection['selValue_free_prod']['quantity'];
                    $paid_prod_count = (int)$bundle_selection['selValue_free']['quantity'];

                    $discount_mp = $free_prod_count / ($paid_prod_count + $free_prod_count);
                    $discount = $subtotal * $discount_mp;

                    if ($discount > 0) {
                        $disc_name = sprintf(__('Buy %s + Get %d FREE', 'woocommerce'), $bundle_selection['selValue_free']['quantity'], $free_prod['quantity']);
                        $cart->add_fee($disc_name, -$discount, true);
                    }
                }
            }

            // apply discount OFF
            if ($bundle_selection['selValue'] == 'off') {
                if ($cart_qty >= $bundle_selection['selValue_off']['quantity']) {
                    $discount = ($subtotal * $bundle_selection['selValue_off']['coupon']) / 100;
                    if ($discount > 0) {
                        $disc_name = sprintf(__('Buy %s + Get %d&#37; Off', 'woocommerce'), $bundle_selection['selValue_off']['quantity'], $bundle_selection['selValue_off']['coupon']);
                        $cart->add_fee($disc_name, -$discount, true);
                    }
                }
            }

            // apply discount Bundle products
            if ($bundle_selection['selValue'] == 'bun') {

                $bun_tt_qty = is_countable($bundle_selection->selValue_bun->post) ? count($bundle_selection->selValue_bun->post) : null;

                if ($cart_qty >= $bun_tt_qty && $bundle_selection['discount_percentage'] > 0) {
                    $discount = ($subtotal * $bundle_selection['discount_percentage']) / 100;
                    if ($discount > 0) {
                        $disc_name = sprintf(__('Bundle discount %d&#37;', 'woocommerce'), $bundle_selection['discount_percentage']);
                        $cart->add_fee($disc_name, -$discount, true);
                    }
                }
            }
        }
    }

endif;
