<?php

/**
 * Renders correct product size measurements/types based on user location
 */

defined('ABSPATH') ?: exit();

if (!trait_exists('PBS_Regional_Sizes')) :

    trait PBS_Regional_Sizes {

        public static function pbs_regional_sizes() {

            // query current country code and language
            $country_code = isset($_SERVER["HTTP_CF_IPCOUNTRY"]) && $_SERVER["HTTP_CF_IPCOUNTRY"] !== '' ? $_SERVER["HTTP_CF_IPCOUNTRY"] : 'EU';

            // DEBUG
            // $country_code = 'DE';

            // jp size replacement
            $jp_size_replacement = [
                "39-43" => "24.0cm - 27.5cm",
                "43-47" => "28.0cm - 31.0cm",
                "36"    => "22.0cm",
                "37"    => "23.0cm",
                "38"    => "24.0cm",
                "39"    => "24.5cm",
                "40"    => "25.5cm",
                "41"    => "26.5cm",
                "42"    => "27.0cm",
                "43"    => "28.0cm",
                "44"    => "28.5cm",
                "45"    => "29.0cm",
                "46"    => "30.0cm",
                "47"    => "30.5cm",
                "48"    => "31.5cm"
            ];

            // UK/GB/SG/HK size replacement
            $uk_size_replacement = [
                "39-43" => "UK 5 - UK 8.5",
                "43-47" => "UK 9 - UK 12",
                "36"    => "UK 3",
                "37"    => "UK 4",
                "38"    => "UK 5",
                "39"    => "UK 5.5",
                "40"    => "UK 6",
                "41"    => "UK 7",
                "42"    => "UK 7.5",
                "43"    => "UK 8.5",
                "44"    => "UK 9",
                "45"    => "UK 10",
                "46"    => "UK 11",
                "47"    => "UK 11.5",
                "48"    => "UK 12.5"
            ];

            // AU/CA/US/NZ size replacement MEN
            $us_size_replacement_men = [
                "39-43" => "US 6 - US 9.5",
                "43-47" => "US 10 - US 13",
                "36"    => "US 4",
                "37"    => "US 5",
                "38"    => "US 6",
                "39"    => "US 6.5",
                "40"    => "US 7",
                "41"    => "US 8",
                "42"    => "US 8.5",
                "43"    => "US 9.5",
                "44"    => "US 10",
                "45"    => "US 11",
                "46"    => "US 12",
                "47"    => "US 12.5",
                "48"    => "US 13.5"
            ];

            // AU/CA/US/NZ size replacement WOMEN
            $us_size_replacement_women = [
                "39-43" => "US 7 - US 10.5",
                "43-47" => "US 11 - US 14",
                "36"    => "US 5",
                "37"    => "US 6",
                "38"    => "US 7",
                "39"    => "US 7.5",
                "40"    => "US 8",
                "41"    => "US 9",
                "42"    => "US 9.5",
                "43"    => "US 10.5",
                "44"    => "US 11",
                "45"    => "US 12",
                "46"    => "US 13",
                "47"    => "US 13.5",
                "48"    => "US 14.5",
            ];


            // hidden inputs to ref default size setting etc
?>
            <input type="hidden" id="ss_size_desig" value="<?php echo $country_code; ?>">
            <input type="hidden" id="ss_us_i_men" value="<?php echo base64_encode(json_encode($us_size_replacement_men)) ?>">
            <input type="hidden" id="ss_us_i_women" value="<?php echo base64_encode(json_encode($us_size_replacement_women)) ?>">
            <input type="hidden" id="ss_uk_i" value="<?php echo base64_encode(json_encode($uk_size_replacement)) ?>">
            <input type="hidden" id="ss_jp_i" value="<?php echo base64_encode(json_encode($jp_size_replacement)) ?>">

            <!-- js -->
            <script>
                jQuery(document).ready(function($) {

                    // retrieve all size settings
                    var us_s_men = JSON.parse(atob($('#ss_us_i_men').val()));
                    var us_s_women = JSON.parse(atob($('#ss_us_i_women').val()));
                    var uk_s = JSON.parse(atob($('#ss_uk_i').val()));
                    var jp_s = JSON.parse(atob($('#ss_jp_i').val()));

                    // set default/selected sizes
                    var def_setting = $('#ss_size_desig').val();

                    // debug
                    // def_setting = 'JP';

                    // set UK sizes
                    if (def_setting === 'UK') {
                        $('.var_prod_attr').each(function(i, el) {
                            if ($(this).data('attribute_name') === 'attribute_pa_size') {
                                $(el).find('option').each(function(i, el) {
                                    $(el).text(uk_s[$(this).val()]);
                                });
                            }
                        })
                    }

                    // set US sizes
                    if (def_setting === 'US') {
                        $('.var_prod_attr').each(function(i, el) {
                            if ($(this).data('attribute_name') === 'attribute_pa_size' && $(this).find("option:contains('36')").length) {
                                $(el).find('option').each(function(i, el) {
                                    $(el).text(us_s_women[$(this).val()]);
                                });
                            } else if ($(this).data('attribute_name') === 'attribute_pa_size' && !$(this).find("option:contains('36')").length) {
                                $(el).find('option').each(function(i, el) {
                                    $(el).text(us_s_women[$(this).val()]);
                                });
                            }
                        })
                    }

                    // set JP sizes
                    if (def_setting === 'JP') {
                        $('.var_prod_attr').each(function(i, el) {
                            if ($(this).data('attribute_name') === 'attribute_pa_size') {
                                $(el).find('option').each(function(i, el) {
                                    $(el).text(jp_s[$(this).val()]);
                                });
                            }
                        })
                    }

                });
            </script>

<?php
        }
    }

endif;
