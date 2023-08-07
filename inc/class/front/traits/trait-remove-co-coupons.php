<?php

defined('ABSPATH') ?: exit();

if (!trait_exists('PBS_Remove_CO_Coupons')) :

    trait PBS_Remove_CO_Coupons {

        /**
         * Queries and removes all applied coupon codes from cart if product bundle single present in cart
         *
         * @return void
         */
        public static function pbs_remove_co_coupons($cart) {

            if (is_admin() && !defined('DOING_AJAX')) :
                return;
            endif;

            if(wc()->session->get('is_pbs_bundle') === 'yes'):
                foreach (WC()->cart->get_applied_coupons() as $index => $code) :
                    wc()->cart->remove_coupon($code);
                endforeach;
            endif;

        }

        /**
         * Remove coupon messages if product bundle in cart
         *
         * @param string $msg
         * @param string $msg_code
         * @param obj $something
         * @return void
         */
        public static function pbs_remove_co_coupon_notices() {

            if (is_checkout() || is_cart()) :

                // remove any applied coupon messages
                if (wc()->session->get('is_pbs_bundle') === 'yes') : ?>
                    <style>
                        .woocommerce-notices-wrapper,
                        div#cart_coupon_box,
                        .woocommerce-form-coupon-toggle,
                        .woocommerce-NoticeGroup {
                            display: none !important;
                        }

                        tr.fee>th {
                            font-weight: 600;
                        }

                        tr.fee>td {
                            font-weight: 600;
                            text-align: right;
                        }
                    </style>
<?php endif;

            endif;
        }
    }

endif;
