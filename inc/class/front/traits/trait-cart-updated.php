<?php
defined('ABSPATH') ?: exit();

if (!trait_exists('PBS_Cart_Updated')) :

    trait PBS_Cart_Updated {

        /**
         * Removes discount/fees if cart qtys updated
         *
         * @param bool $cart_updated
         * @return void
         */
        public static function pbs_cart_updated($cart_updated) {

            if ($cart_updated) :

                $cart_qty  = WC()->cart->get_cart_contents_count();
                $bundle_id = null;
                $bd_qty    = 0;

                foreach (WC()->cart->get_cart() as $cart_item) :

                    if (isset($cart_item['pbs_bundle_dd'])) :
                        $bundle_id = $cart_item['bundle_id'];
                        $bd_qty    = $cart_item['quantity'];
                    endif;

                endforeach;

                $bundle_selection = json_decode(get_post_meta($bundle_id, 'product_discount', true));

                if (isset($bundle_selection)) :

                    // free
                    if ($bundle_selection->selValue == 'free') :
                        if ($cart_qty >= ($bundle_selection->selValue_free->quantity + $bundle_selection->selValue_free_prod->quantity)) :
                            return false;
                        endif;
                    endif;

                    // off
                    if ($bundle_selection->selValue == 'off') :
                        if ($bd_qty == $bundle_selection->selValue_off->quantity) :
                            return false;
                        endif;
                    endif;

                    // bundle
                    if ($bundle_selection->selValue == 'bun') :
                        if ($bd_qty == count($bundle_selection->selValue_bun->post)) :
                            return false;
                        endif;
                    endif;

                    // remove action which applies bundle discount to cart
                    remove_action('woocommerce_cart_calculate_fees', [__CLASS__, 'pbs_calculate_cart_fees'], PHP_INT_MAX);

                    // set wc pbs bundle variable to no
                    wc()->session->set('is_pbs_bundle', 'no');

                endif;
            endif;
        }
    }

endif;
