<?php

defined('ABSPATH') ?: exit();

if (!trait_exists('PBS_Generate_Return_Bun_Total')) :

    trait PBS_Generate_Return_Bun_Total {

        /**
         * WP Ajax function to generate and return bundle totals
         *
         * @return void
         */
        public static function pbs_generate_return_bun_total() {

            check_ajax_referer('pbs get updated product bundle total');

            print_r($_POST);

            wp_die();
        }

        /**
         * JS to handle associated AJAX request
         *
         * @return void
         */
        public static function pbs_generate_return_bun_total_js() { ?>

            <script id="pbs_gen_return_pbs_bundle_total">
                $ = jQuery;

                $(document).ready(function() {

                    // swatches on mousedown
                    $('.tooltipplugify, .wcvaswatchlabel').on('mousedown', function(e) {

                        var swatch = $(e);
                        var parent = swatch.parents('.c_prod_item');
                        var swatch_ids_attribs = {};

                        swatch.removeClass('imgclasssmallactive')
                        
                        $('.c_prod_item').each(function (i, bundle) {
                            
                            if($(bundle).is(':visible')){

                                $(bundle).find('.tooltipplugify, .wcvaswatchlabel').each(function(swatch){
if ($(swatch).hasClass()) {
    
}
                                });

                                $(bundle).find('.var_prod_attr').each(function(vdd){

                                });
                            }
                        });

                       

                    });

                    // attributes dropdown on change
                    $('.var_prod_attr').on('change', function(e) {

                        var vdd = $(e);

                        console.log(vdd.parents('.c_prod_item'));

                    });
                });
            </script>

<?php }
    }

endif;
