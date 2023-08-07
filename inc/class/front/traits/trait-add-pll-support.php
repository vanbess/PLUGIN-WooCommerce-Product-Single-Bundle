<?php

defined('ABSPATH') ?: exit();

if (!trait_exists('PBS_Add_PLL_Support')) :

    trait PBS_Add_PLL_Support {

        public static function pbs_add_pll_support() {

            if (defined('POLYLANG')) :

                // get Polylang options/settings
                $polylang_opts = get_option('polylang');

                // add bundle_dropdown tracking cpt support
                if (!in_array('bundle_dropdown', $polylang_opts['post_types'])) :
                    array_push($polylang_opts['post_types'], 'bundle_dropdown');
                endif;

                // update Polylang options/settings
                update_option('polylang', $polylang_opts);

            endif;
        }
    }

endif;
