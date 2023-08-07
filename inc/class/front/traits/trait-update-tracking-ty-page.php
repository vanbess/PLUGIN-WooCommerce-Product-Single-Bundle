<?php

defined('ABSPATH') ?: exit();

if (!trait_exists('PBS_Update_Tracking_TY_Page')) :

    trait PBS_Update_Tracking_TY_Page {

        /**
         * Update tracking (sales/conversion) on order thank you page
         *
         * @param int $order_id
         * @return void
         */
        public static function pbs_update_tracking_ty_page($order_id) {

            // start session if not started, otherwise we can grab our data from $_SESSION
            // if (!session_id()) :
            //     session_start();
            // endif;

            // if pbs bundle session flag isn't set, bail
            if (!isset($_SESSION['is_pbs_bundle']) || $_SESSION['is_pbs_bundle'] != 1) :
                return;
            endif;

            // retrieve bundle id from session
            $bundle_id = isset($_SESSION['pbs_bundle_id']) ? $_SESSION['pbs_bundle_id'] : null;

            // $bundle_id = 7362;

            // bail if $bundle_id is null
            if (is_null($bundle_id)) :
                return;
            endif;

            // retrieve order object
            $order       = wc_get_order($order_id);

            // calculate bundle revenue (order total minus shipping costs)
            $order_total    = $order->get_total();
            $order_shipping = $order->get_shipping_total();
            $order_revenue  = $order_total - $order_shipping;
            $currency       = $order->get_currency();

            // update conversion qty and revenue
            $count_paid     = get_post_meta($bundle_id, 'count_paid', true);
            $revenue        = get_post_meta($bundle_id, 'revenue', true);
            $new_count_paid = $count_paid ? (int)$count_paid + 1 : 1;
            $new_revenue    = $revenue ? (float)$order_revenue + (float)$revenue : (float)$order_revenue;

            // update relevant post meta
            update_post_meta($bundle_id, 'count_paid', $new_count_paid);
            update_post_meta($bundle_id, 'revenue', $new_revenue);
            update_post_meta($bundle_id, 'order_currency', $currency);

            // destroy session to avoid any potential issues with calculating tracking
            session_destroy();
        }
    }

endif;
