<?php

defined('ABSPATH') ?: exit();

if (!trait_exists('PBS_Update_Impressions_Action_Scheduler')) :

    trait PBS_Update_Impressions_Action_Scheduler {

        /**
         * Schedules and runs impressions update via Action Scheduler
         *
         * @return void
         */
        public static function pbs_update_impressions_action_scheduler() {

            // schedule AS action
            add_action('init', function () {
                if (function_exists('as_next_scheduled_action') && false === as_next_scheduled_action('pbs_update_upsell_tracking_impressions')) {
                    as_schedule_recurring_action(strtotime('now'), 300, 'pbs_update_upsell_tracking_impressions');
                }
            });

            // AS function to run to update impressions
            add_action('pbs_update_upsell_tracking_impressions', function () {

                // retrieve mwco bundle impressions
                $pbs_bundle_sell_imps = maybe_unserialize(get_transient('pbs_bundle_impressions'));

                // *****************************************
                // update product single bundle impressions
                // *****************************************
                if ($pbs_bundle_sell_imps) :

                    foreach ($pbs_bundle_sell_imps as $bundle_id => $imps) :

                        // get current impressions
                        $curr_imps = get_post_meta($bundle_id, 'count_view', true);

                        // calc new impressions
                        $new_imps = $curr_imps ? (int)$curr_imps + (int)$imps : (int)$imps;

                        // update impressions
                        update_post_meta($bundle_id, 'count_view', $new_imps);

                    endforeach;

                    // delete cached impressions
                    delete_transient('pbs_bundle_impressions');

                endif;
            });
        }
    }

endif;
