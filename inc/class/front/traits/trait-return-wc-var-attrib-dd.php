<?php
defined('ABSPATH') ?: exit();

if (!trait_exists('PBS_Return_WC_Variation_Attrib_Dropdown')) :

    trait PBS_Return_WC_Variation_Attrib_Dropdown {

        /**
         * Build and return WC variation attribute options dropdown
         *
         * @param array $args
         * @return void
         */
        public static function pbs_return_wc_variation_attrib_dropdown($args = array()) {

            $args = wp_parse_args(apply_filters('woocommerce_dropdown_variation_attribute_options_args', $args), array(
                'options'          => false,
                'attribute'        => false,
                'product'          => false,
                'selected'         => false,
                'n_item'           => false,
                'img_variations'   => '',
                'name'             => '',
                'id'               => '',
                'class'            => '',
                'show_option_none' => __('Choose an option', 'woocommerce'),
            ));

            $options               = $args['options'];
            $product               = $args['product'];
            $attribute             = $args['attribute'];
            $name                  = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title($attribute);
            $id                    = $args['id'] ? $args['id'] : sanitize_title($attribute);
            $class                 = $args['class'];

            // retrieve ALG pricing for current currency, for each variation/child and push to array for ref on dd select
            $current_curr = function_exists('alg_get_current_currency_code') ? alg_get_current_currency_code() : get_woocommerce_currency();
            $default_curr = get_option('woocommerce_currency');

            // uncomment to debug
            // $current_curr = 'SEK';

            $ex_rate              = get_option("alg_currency_switcher_exchange_rate_USD_$current_curr") ? get_option("alg_currency_switcher_exchange_rate_USD_$current_curr") : 1;
            $children             = !empty($product->get_children()) ? $product->get_children() : false;
            $alg_currency_pricing = [];

            if ($children !== false) :
                foreach ($children as $vid) :
                    $product_price = get_post_meta($vid, '_regular_price', true) ? get_post_meta($vid, '_regular_price', true) : get_post_meta($vid, '_price', true);
                    if ($current_curr === $default_curr) :
                        $alg_currency_pricing[$vid] = $product_price;
                    else :
                        $alg_currency_pricing[$vid] = get_post_meta($vid, '_alg_currency_switcher_per_product_regular_price_' . $current_curr, true) ? (float)get_post_meta($vid, '_alg_currency_switcher_per_product_regular_price_' . $current_curr, true) : (float)$product_price * (float)$ex_rate;
                    endif;
                endforeach;
            endif;

            if (empty($options) && !empty($product) && !empty($attribute)) {
                $attributes = $product->get_variation_attributes();
                $options    = $attributes[$attribute];
            }

            $html  = '<select data-def-currency="' . get_option('woocommerce_currency') . '" data-currency="' . $current_curr . '" data-ex-rate="' . $ex_rate . '" data-alg-pricing="' . base64_encode(json_encode($alg_currency_pricing)) . '" class="' . esc_attr($class) . ' bd_product_attribute sel_product_' . esc_attr($id) . '" name="i_variation_' . esc_attr($name) . '" data-attribute_name="attribute_' . esc_attr(sanitize_title($attribute)) . '" data-item="' . $args['n_item'] . '">';

            if (!empty($options)) {
                if ($product && taxonomy_exists($attribute)) {
                    // Get terms if this is a taxonomy - ordered. We need the names too.
                    $terms = wc_get_product_terms($product->get_id(), $attribute, array(
                        'fields' => 'all',
                    ));

                    foreach ($terms as $i => $term) {
                        if (in_array($term->slug, $options, true)) {
                            $html .= '<option data-item="' . $args['n_item'] . '" data-img="' . $args['img_variations'][$i] . '" value="' . esc_attr($term->slug) . '" ' . selected(sanitize_title($args['selected']), $term->slug, false) . '>' . esc_html(apply_filters('woocommerce_variation_option_name', $term->name)) . '</option>';
                        }
                    }
                } else {
                    foreach ($options as $option) {
                        // This handles < 2.4.0 bw compatibility where text attributes were not sanitized.
                        $selected = sanitize_title($args['selected']) === $args['selected'] ? selected($args['selected'], sanitize_title($option), false) : selected($args['selected'], $option, false);
                        $html    .= '<option data-item="' . $args['n_item'] . '" value="' . esc_attr($option) . '" ' . $selected . '>' . esc_html(apply_filters('woocommerce_variation_option_name', $option)) . '</option>';
                    }
                }
            }

            $html .= '</select>';

            return apply_filters('woocommerce_dropdown_variation_attribute_options_html', $html, $args); // WPCS: XSS ok.
        }
    }

endif;
