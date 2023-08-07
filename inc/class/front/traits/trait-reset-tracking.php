<?php

defined('ABSPATH') ?: exit();

if (!trait_exists('PBS_Reset_Tracking')) :

    trait PBS_Reset_Tracking {

        /**
         * Add optiong to bundle_dropdown bulk actions for resetting tracking
         *
         * @param array $bulk_actions
         * @return void
         */
        public static function pbs_reset_tracking_bulk_action($bulk_actions) {
            $bulk_actions['reset-tracking'] = __('Reset Tracking', 'mwc');
            return $bulk_actions;
        }

        /**
         * Handle reset tracking bulk action submission
         *
         * @param string $redirect_url
         * @param string $action
         * @param array $post_ids
         * @return void
         */
        public static function pbs_handle_reset_tracking_bulk_action($redirect_url, $action, $post_ids) {
            if ($action == 'reset-tracking') {
                foreach ($post_ids as $post_id) {

                    // reset all tracking meta
                    delete_post_meta($post_id, 'view');
                    delete_post_meta($post_id, 'click');
                    delete_post_meta($post_id, 'count_paid');
                    delete_post_meta($post_id, 'conversion_rate');
                    delete_post_meta($post_id, 'revenue');
                }
                $redirect_url = add_query_arg('reset-tracking', count($post_ids), $redirect_url);
            }
            return $redirect_url;
        }
    }

endif;
