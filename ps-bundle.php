<?php

/**
 * Plugin Name:       Product Single Bundle - CANLES ONLY [Shortcode]
 * Description:       Replicates/modifies Product Single template via shortcode for use with bundles. Variable products only. <strong>NOTE: RIODE/CANLESS ONLY!</strong>
 * Version:           1.0.6b
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            WC Bessinger
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ps-bundle
 */

defined('ABSPATH') || exit();

add_action('plugins_loaded', function () {

    // constants
    define('PBS_Bundle_Path', plugin_dir_path(__FILE__));
    define('PBS_Bundle_URL', plugin_dir_url(__FILE__));

    // core class admin
    include PBS_Bundle_Path . 'inc/class/admin/class_pp_bundle_admin.php';

    // class insert shortcode @ editor with settings
    include PBS_Bundle_Path . 'inc/class/admin/class_pp_insert_shortcode.php';

    // bundle gallery class
    include PBS_Bundle_Path . 'inc/class/admin/class_pp_bundle_gallery.php';

    // core class front
    include PBS_Bundle_Path . 'inc/class/front/class_pp_bundle.php';
});

/**
 * Make sure selected/default bundle is clicked on load so that items are added to cart
 */
add_action('wp_footer', function () { ?>

    <script>
        jQuery(document).ready(function($) {
            setTimeout(() => {
                $('.bd_active_product').click();
            }, 1500);
        });
    </script>

<?php });
