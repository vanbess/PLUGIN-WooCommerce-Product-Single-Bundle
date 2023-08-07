# Product Single Bundle

Plugin which renders product single bundle. 

Only works on custom pages Landings and Offers, thus requires SBWC Custom Pages plugin to be installed. 

Does not render bundles on product single, but uses WC product single shortcode instead, with various overrides. 

- Supports Products Linked by Variations for WooCommerce.
- Supports Currency Switcher for WooCommerce Pro.

Bundles to be created under Bundle Dropdown menu in WP backend. Full conversion and impression tracking included.

Custom post type used for bundles (bundle_dropdown) supports Polylang, however it is likely that Polylang support for the CPT has to be enabled in backend (Languages -> Settings -> Custom post types & Taxonomies).

Polylang strings translations also have to manually be added in order for translation to work fully (Languages -> Strings translations) as Polylang does not auto translate strings, contrary to popular belief.