<?php

defined('ABSPATH') ?: exit();

if (!trait_exists('PBS_Update_Clicks')) :

    trait PBS_Update_Clicks {

        /**
         * Function hooked to wp_footer to update clicks on offers/landing pages only
         *
         * @return void
         */
        public static function pbs_update_clicks() {

            global $pbs_post_id;
            $ptype = get_post_type($pbs_post_id);

            // update clicks
            if ($ptype === 'offer' || $ptype === 'landing') :

                // create nonce for ajax
                $nonce = wp_create_nonce('pbs update clicks');

                // create session nonce
                $session_nonce = wp_create_nonce('pbs bundle id to session');

                // setup ajax url
                $ajax_url = admin_url('admin-ajax.php');
?>

                <script id="pbs_register_clicks">
                    $ = jQuery;

                    $(document).ready(function() {

                        // bundle items on click
                        $('.bd_item_div').on('click', function() {

                            var bundle_id = $(this).data('bundle_id');

                            var data = {
                                '_ajax_nonce': '<?php echo $nonce; ?>',
                                'action': 'pbs_update_clicks_ajax',
                                'bundle_id': bundle_id
                            }

                            $.post('<?php echo $ajax_url ?>', data, function(response) {
                                // debug
                                // console.log(response);
                            });

                        });

                        // add/remove bundle products to tracking session if checked
                        $('.bd_item_div').on('change click', function() {

                            var data = {
                                '_ajax_nonce': '<?= $session_nonce ?>',
                                'action': 'pbs_add_products_to_session',
                                'bundle_id': $(this).data('bundle_id'),
                                'bundle_to_session': true
                            }

                            $.post('<?= $ajax_url ?>', data, function(response) {
                                // debug
                                // console.log(response);
                            });

                        });
                    });
                </script>

<?php

            endif;
        }

        /**
         * Hooked AJAX function to update clicks
         *
         * @return void
         */
        public static function pbs_update_clicks_ajax() {

            check_ajax_referer('pbs update clicks');

            // *********************
            // UPDATE BUNDLE CLICKS
            // *********************

            // retrieve bundle id
            $bundle_id = $_POST['bundle_id'];

            // query bundle_selection posts
            $posts = new WP_Query([
                'post_type' => 'bundle_dropdown',
                'p'         => (int)$bundle_id
            ]);

            // update clicks as needed
            if ($posts->have_posts()) :

                while ($posts->have_posts()) : $posts->the_post();

                    // retrieve current clicks
                    $curr_clicks = get_post_meta(get_the_ID(), 'count_click', true);

                    // if current clicks, increment and update
                    if ($curr_clicks) :
                        $new_clicks = (int)$curr_clicks += 1;
                        update_post_meta(get_the_ID(), 'count_click', $new_clicks);

                    // if no current clicks, insert initial click
                    else :
                        update_post_meta(get_the_ID(), 'count_click', 1);
                    endif;

                endwhile;
                wp_reset_postdata();
            endif;

            wp_die();
        }

        /**
         * AJAX to add bundle products to session data for ref on order thank you page
         *
         * @return void
         */
        public static function pbs_add_products_to_session() {

            check_ajax_referer('pbs bundle id to session');

            // UNCOMMENT TO DEBUG
            // print_r($_POST);
            // wp_die();

            // add bundle products to session
            // if (!session_id()) :
            //     session_start();
            // endif;

            if (isset($_POST['bundle_id'])) :
                $_SESSION['pbs_bundle_id'] = $_POST['bundle_id'];
            else :
                $_SESSION['pbs_bundle_id'] = null;
            endif;

            wp_send_json($_SESSION);

            wp_die();
        }
    }

endif;
