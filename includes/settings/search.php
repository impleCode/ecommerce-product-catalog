<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/*
 *
 *  @version       1.0.0
 *  @package
 *  @author        impleCode
 *
 */

class ic_settings_search {

    private $search_word = '', $points, $exploded_search_word;

    function __construct() {
        add_action( 'ic_settings_top', array( $this, 'search_html' ) );
        add_action( 'ic_extensions_page_help_top', array( $this, 'search_html' ) );
        add_action( 'wp_ajax_ic_search_docs', array( $this, 'ajax_search_docs' ) );
    }

    function placeholder() {
        if ( isset( $_GET['tab'] ) && $_GET['tab'] === 'help' ) {
            $placeholder = __( 'Describe your issue', 'ecommerce-product-catalog' );
        } else {
            $placeholder = __( 'Search settings & docs', 'ecommerce-product-catalog' );
        }

        return $placeholder;
    }

    function search_html() {
        $search_word         = isset( $_GET['ic-settings-search'] ) ? sanitize_text_field( $_GET['ic-settings-search'] ) : '';
        $class               = '';
        $search_results_html = '';
        $placeholder         = $this->placeholder();
        if ( ! empty( $search_word ) ) {
            if ( ! empty( $search_word ) ) {
                ob_start();
                $this->search_results_html( $search_word );
                $search_results_html = ob_get_clean();
                if ( ! empty( $search_results_html ) ) {
                    $class .= ' with-search-results';
                }
            }
        }
        ?>
        <form class="ic-settings-search<?php echo $class ?>">
            <?php
            foreach ( $_GET as $get_key => $get_value ) {
                if ( $get_key === 'find_option_name' ) {
                    continue;
                }
                ?>
                <input type="hidden" name="<?php echo esc_attr( sanitize_text_field( $get_key ) ) ?>"
                       value="<?php echo esc_attr( sanitize_text_field( $get_value ) ) ?>">
                <?php
            }
            ?>
            <input type="search" name="ic-settings-search" placeholder="<?php echo esc_attr( $placeholder ) ?>"
                   value="<?php echo esc_attr( $search_word ) ?>">
            <input type="submit" class="button-primary" value="<?php _e( 'Search', 'ecommerce-product-catalog' ) ?>">
            <a class="button-secondary" target="_blank"
               href="https://wordpress.org/support/plugin/ecommerce-product-catalog/">Free Support Forum</a>
        </form>
        <?php
        if ( empty( $_GET['find_option_name'] ) ) {
            if ( empty( $_GET['ic-settings-search'] ) ) {
                ?>
                <script>
                    jQuery(document).ready(function () {
                        jQuery('[name="ic-settings-search"]').focus();
                    });
                </script>
                <?php
            }
        }

        echo $search_results_html;
    }

    function search_results_html( $search_word ) {
        if ( empty( $search_word ) ) {
            return;
        }
        $this->search_settings( $search_word );
        ?>
        <div class="ic-settings-search-results">
            <div class='ic-settings-search-results-settings'>
                <?php
                $this->search_results_settings( $search_word );
                $this->search_results_docs( $search_word );
                ?>
            </div>
        </div>
        <?php
        $this->js();
    }

    function search_results_settings( $search_word ) {
        if ( empty( $this->points ) ) {
            ?>
            <div class="ic-settings-search-empty"><?php echo sprintf( __( "Didn't find any adjustment options for '%s'.", 'ecommerce-product-catalog' ), $search_word ) ?></div>
            <?php
            return;
        }
        ?>
        <h2><?php echo sprintf( __( "%s adjustment options found in settings for '%s'", 'ecommerce-product-catalog' ), count( $this->points ), $search_word ) ?>
            :</h2>
        <ul>
            <?php
            $first = reset( $this->points );
            $num   = 0;
            foreach ( $this->points as $option_name => $number ) {
                $label = $this->get_label( $option_name );
                if ( empty( $label ) ) {
                    continue;
                }
                $url = $this->get_url( $option_name );
                if ( empty( $url ) ) {
                    continue;
                }

                $tip        = $this->get_tip( $option_name );
                $additional = '';
                if ( ! empty( $tip ) ) {
                    $tip_html = 'title="' . $tip . '"';
                    if ( ! empty( $tip_html ) ) {
                        $additional .= '<span ' . $tip_html . ' class="dashicons dashicons-editor-help ic_tip"></span>';
                    }
                }

                if ( $first < 5 ) {
                    $class = 'ic-medium-priority';
                } else {
                    $class = 'ic-low-priority';
                }
                if ( $number >= 10 ) {
                    $class = 'ic-high-priority';
                } else if ( $number >= 5 ) {
                    $class = 'ic-medium-priority';
                }
                if ( $num >= 5 ) {
                    $class .= ' ic-settings-hidden-row';
                }
                ?>
                <li class="ic-settings-search-result <?php echo $class ?>"><a
                            href="<?php echo esc_url( $url ) ?>"><?php echo $label ?></a><?php echo $additional ?></li>
                <?php
                $num ++;
            }
            if ( count( $this->points ) > 5 ) {
                ?>
                <li>
                    <div class="button-secondary ic-search-settings-show-button"><?php echo sprintf( __( 'Show %s more related adjustment options', 'ecommerce-product-catalog' ), count( $this->points ) - 5 ) ?></div>
                </li>
                <?php
            }
            ?>
        </ul>
        <script>
            jQuery('.ic-search-settings-show-button').click(function () {
                jQuery('ul li.ic-settings-hidden-row').addClass('show');
                jQuery(this).hide();
            });
        </script>
        <?php
    }

    function search_results_docs( $search_word ) {
        $transient_name   = 'ic-cat-search-docs-' . sanitize_title( $search_word );
        $transient_result = get_site_transient( $transient_name );
        if ( $transient_result !== false ) {
            echo $transient_result;

            return;
        }
        ?>
        <div class="ic-docs-ajax-search-container">
            <div class="ic-docs-search-placeholder"><?php _e( 'Searching also in docs...', 'ecommerce-product-catalog' ) ?></div>
        </div>
        <script>
            var data = {
                'action': 'ic_search_docs',
                'term': "<?php echo $search_word ?>",
                'nonce': ic_catalog.nonce
            };
            jQuery.post(ajaxurl, data, function (response) {
                jQuery(".ic-docs-search-placeholder").replaceWith(response);
            });
        </script>
        <?php
    }

    function ajax_search_docs() {
        if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'ic-ajax-nonce' ) ) {
            wp_die();

            return '';
        }
        $search_word = isset( $_POST['term'] ) ? sanitize_text_field( $_POST['term'] ) : '';
        if ( empty( $search_word ) ) {
            wp_die();

            return '';
        }
        $transient_name   = 'ic-cat-search-docs-' . sanitize_title( $search_word );
        $transient_result = get_site_transient( $transient_name );
        if ( $transient_result !== false ) {
            echo $transient_result;
        }
        $options = array(
                'timeout' => 10, //seconds
        );
        $request = wp_remote_get( 'https://implecode.com/?ic_docs_api_search=' . $search_word, $options );
        if ( is_wp_error( $request ) ) {
            implecode_warning( __( 'An error ocurred while searching. Please try again. ', 'ecommerce-product-catalog' ) );
            wp_die();

            return;
        }
        $body = wp_remote_retrieve_body( $request );
        $data = json_decode( $body, true );
        if ( ! empty( $data ) && is_array( $data ) ) {
            ob_start();
            ?>
            <h2><?php
                echo sprintf( __( "%s documentation pages found for '%s'", 'ecommerce-product-catalog' ), count( $data ), $search_word )
                ?>
                :</h2>
            <ul>
                <?php
                foreach ( $data as $key => $doc ) {
                    if ( empty( $doc['title'] ) || empty( $doc['link'] ) ) {
                        continue;
                    }
                    $additional = '';
                    if ( ! empty( $doc['excerpt'] ) ) {
                        $tip_html = 'title="' . esc_attr( sanitize_textarea_field( $doc['excerpt'] ) ) . '"';
                        if ( ! empty( $tip_html ) ) {
                            $additional .= '<span ' . $tip_html . ' class="dashicons dashicons-editor-help ic_tip"></span>';
                        }
                    }
                    $class = '';
                    if ( $key > 4 ) {
                        $class .= ' ic-docs-hidden-row';
                    }
                    ?>
                    <li class="ic-docs-search-result<?php echo $class ?>"><a
                                href="<?php echo str_replace( '?cam', '#cam', esc_url( add_query_arg( array(
                                        'cam' => 'settings-search',
                                        'key' => 'doc-link'
                                ), $doc['link'] ) ) ) ?>"
                                target="_blank"><?php echo esc_attr( $doc['title'] ) ?></a><?php echo $additional ?>
                    </li>
                    <?php
                }

                if ( count( $data ) > 5 ) {
                    ?>
                    <li>
                        <div class="button-secondary ic-search-docs-show-button"><?php echo sprintf( __( 'Show %s more related docs', 'ecommerce-product-catalog' ), count( $data ) - 5 ) ?></div>
                    </li>
                    <?php
                }
                ?>
            </ul>
            <script>
                jQuery('.ic-search-docs-show-button').click(function () {
                    jQuery('ul li.ic-docs-hidden-row').addClass('show');
                    jQuery(this).hide();
                });
            </script>
            <?php
            $results_hml = ob_get_clean();
            set_site_transient( $transient_name, $results_hml, DAY_IN_SECONDS );
            echo $results_hml;
        } else if ( empty( $data ) ) {
            ob_start();
            ?>
            <div class="ic-docs-search-empty"><?php echo sprintf( __( "Didn't find any docs or tutorials for '%s'.", 'ecommerce-product-catalog' ), $search_word ) ?></div>
            <?php
            $results_html = ob_get_clean();
            echo $results_html;
            set_site_transient( $transient_name, $results_html, DAY_IN_SECONDS );
        }
        wp_die();
    }

    function js() {
        if ( empty( $_GET['find_option_name'] ) ) {
            return;
        }
        $option_name = esc_attr( urldecode( $_GET['find_option_name'] ) );
        ?>
        <script>
            jQuery(document).ready(function () {
                    var container = jQuery('#implecode_settings');
                    var scrollTo = jQuery("[name='<?php echo $option_name ?>']").first();
                    if (scrollTo.length === 0) {
                        scrollTo = jQuery("[name^='<?php echo $option_name ?>[']");
                    }
                    if (scrollTo.length === 0) {
                        scrollTo = jQuery(".<?php echo $option_name ?>");
                    }
                    if (scrollTo.length === 0) {
                        scrollTo = jQuery("[name^='<?php echo $option_name ?>']");
                    }
                    if (scrollTo.is(":hidden")) {
                        var td = scrollTo.parent();
                        scrollTo = td;
                    } else {
                        var td = scrollTo.parent("td");
                    }
                    if (td === undefined || td.length === 0) {
                        td = scrollTo.closest("td.ic_radio_td");
                    }

                    if (td !== undefined) {
                        var tr = td.parent("tr");
                        if (tr.length) {
                            tr.addClass("found-option");
                        }
                        td.addClass("found-option");
                    }
                    // var max = jQuery( "#implecode_settings" ).outerHeight();
                    var calculated = scrollTo.offset().top - container.offset().top + container.scrollTop() - 100;

                    jQuery("body,html").animate(
                        {
                            scrollTop: calculated
                        },
                        800 //speed
                    );
                }
            );


        </script>
        <?php
    }

    function search_settings( $search_word ) {
        if ( empty( $search_word ) ) {
            return;
        }
        $registered_settings = $this->get_settings();
        if ( empty( $registered_settings ) ) {
            return;
        }
        $this->search_word = $search_word;

        foreach ( $registered_settings as $option_name => $setting_args ) {
            if ( ! empty( $setting_args['option_label'] ) ) {
                $this->search( $option_name, $setting_args['option_label'], 10 );
            }
            $option_value = $this->get_value( $option_name );
            if ( ! empty( $option_value ) ) {
                $this->search( $option_name, $option_value, 6 );
            }
            if ( ! empty( $setting_args['option_tip'] ) ) {
                $this->search( $option_name, $setting_args['option_tip'], 5 );
            }
        }
        if ( ! empty( $this->points ) ) {
            arsort( $this->points );
        }
    }

    function search( $option_name, $sentence, $max_points, $do_shortened = true ) {
        if ( empty( $this->search_word ) ) {
            return;
        }
        if ( ic_string_contains( $this->search_word, ' ' ) ) {
            $this->exploded_search_word = explode( ' ', $this->search_word );
        }
        if ( ! isset( $this->points ) ) {
            $this->points = array();
        }
        if ( is_array( $sentence ) ) {
            $sentence = implode( ' ', $sentence );
        }
        if ( ic_string_contains( $sentence, $this->search_word, false ) || $sentence == $this->search_word ) {
            if ( ! isset( $this->points[ $option_name ] ) ) {
                $this->points[ $option_name ] = 0;
            }
            $pos = strpos( $sentence, $this->search_word );
            if ( empty( $pos ) ) {
                $pos = 0.5;
            } else {
                $substring = substr( $sentence, $pos - 1, 1 );
                if ( $substring !== ' ' ) {
                    $pos += 10;
                }
                $pos = $pos / 10;
            }
            $this->points[ $option_name ] += $max_points / $pos;
            if ( $sentence == $this->search_word ) {
                $this->points[ $option_name ] ++;
            }
        } else if ( ! empty( $this->exploded_search_word ) ) {
            foreach ( $this->exploded_search_word as $e_search ) {
                if ( empty( $e_search ) ) {
                    continue;
                }
                if ( ic_string_contains( $sentence, $e_search, false ) ) {
                    if ( ! isset( $this->points[ $option_name ] ) ) {
                        $this->points[ $option_name ] = 0;
                    }
                    $this->points[ $option_name ] ++;
                }
            }
        }
        if ( $do_shortened ) {
            $original_search_word = $this->search_word;
            $this->search_word    = substr( $this->search_word, 0, - 1 );
            $new_max              = $max_points - 3;
            if ( $new_max < 1 ) {
                $new_max = 1;
            }
            $this->search( $option_name, $sentence, $new_max, false );
            $this->search_word = $original_search_word;
        }
    }

    function get_label( $option_name ) {
        $registered_settings = $this->get_settings();
        if ( ! empty( $registered_settings[ $option_name ]['option_label'] ) ) {
            return $registered_settings[ $option_name ]['option_label'];
        }
    }

    function get_tip( $option_name ) {
        $registered_settings = $this->get_settings();
        if ( ! empty( $registered_settings[ $option_name ]['option_tip'] ) ) {
            return $registered_settings[ $option_name ]['option_tip'];
        }
    }

    function get_url( $option_name ) {
        $registered_settings      = $this->get_settings();
        $args['find_option_name'] = $option_name;
        if ( ! empty( $registered_settings[ $option_name ]['tab'] ) ) {
            $args['tab'] = $registered_settings[ $option_name ]['tab'];
        }
        if ( ! empty( $registered_settings[ $option_name ]['submenu'] ) ) {
            $args['submenu'] = $registered_settings[ $option_name ]['submenu'];
        }
        if ( ! empty( $_GET['ic-settings-search'] ) ) {
            $args['ic-settings-search'] = sanitize_text_field( $_GET['ic-settings-search'] );
        }
        $url = add_query_arg( $args, admin_url( 'edit.php?post_type=al_product&page=product-settings.php' ) );

        return $url;
    }

    function get_value( $option_name ) {
        if ( ic_string_contains( $option_name, '[' ) ) {
            $exploded_option_name = explode( '[', $option_name );
            foreach ( $exploded_option_name as $name ) {
                if ( ic_string_contains( $name, ']' ) ) {
                    if ( empty( $option_values ) ) {
                        return '';
                    }
                    $subname = str_replace( ']', '', $name );
                    if ( ! isset( $option_values[ $subname ] ) ) {
                        return '';
                    }
                    $option_value = $option_values[ $subname ];
                } else {
                    $main_name     = $name;
                    $option_values = get_option( sanitize_text_field( $main_name ) );
                }
            }
        } else {
            $option_value = get_option( sanitize_text_field( $option_name ) );
        }

        return $option_value;
    }

    function get_settings() {
        $registered = wp_parse_args( ic_get_registered_settings(), $this->default_settings() );

        return $registered;
    }

    function default_settings() {
        return json_decode( '{"default_product_thumbnail":{"option_label":"Default Image","option_tip":"","tab":"design-settings","submenu":"single-design"},"multi_single_options[enable_product_gallery]":{"option_label":"Enable image","option_tip":"The image will be used only on the listing when unchecked.","tab":"design-settings","submenu":"single-design"},"catalog_lightbox":{"option_label":"Enable lightbox gallery","option_tip":"The image on single page will not be linked when unchecked.","tab":"design-settings","submenu":"single-design"},"catalog_magnifier":{"option_label":"Enable image magnifier","option_tip":"The image on single page will be magnified when pointed with mouse cursor.","tab":"design-settings","submenu":"single-design"},"multi_single_options[enable_product_gallery_only_when_exist]":{"option_label":"Enable image only when inserted","option_tip":"The default image will be used on the listing only when unchecked.","tab":"design-settings","submenu":"single-design"},"multi_single_options[template]":{"option_label":"Select template","option_tip":"","tab":"design-settings","submenu":"single-design"},"design_schemes[icons_display]":{"option_label":"Icons Display","option_tip":"","tab":"design-settings","submenu":"design-schemes"},"design_schemes[icons_display_catalog]":{"option_label":"Hide Catalog Icon","option_tip":"","tab":"design-settings","submenu":"design-schemes"},"design_schemes[icons_display_search]":{"option_label":"Hide Search Icon","option_tip":"","tab":"design-settings","submenu":"design-schemes"},"design_schemes[icons_search]":{"option_label":"Search Icon","option_tip":"","tab":"design-settings","submenu":"design-schemes"},"design_schemes[price-size]":{"option_label":"Price Size","option_tip":"","tab":"design-settings","submenu":"design-schemes"},"design_schemes[price-color]":{"option_label":"Price Color","option_tip":"","tab":"design-settings","submenu":"design-schemes"},"archive_names[all_products]":{"option_label":"Main Listing Title Label","option_tip":null,"tab":"names-settings","submenu":"archive-names"},"archive_names[all_main_categories]":{"option_label":"Categories Header Label","option_tip":null,"tab":"names-settings","submenu":"archive-names"},"archive_names[all_subcategories]":{"option_label":"Subcategories Header Label","option_tip":null,"tab":"names-settings","submenu":"archive-names"},"archive_names[all_prefix]":{"option_label":"Category Prefix Label","option_tip":null,"tab":"names-settings","submenu":"archive-names"},"archive_names[category_products]":{"option_label":"Category Products Header Label","option_tip":null,"tab":"names-settings","submenu":"archive-names"},"archive_names[next_products]":{"option_label":"Next Page Label","option_tip":null,"tab":"names-settings","submenu":"archive-names"},"archive_names[previous_products]":{"option_label":"Previous Page Label","option_tip":null,"tab":"names-settings","submenu":"archive-names"},"archive_names[bread_home]":{"option_label":"Breadcrumbs Home Label","option_tip":null,"tab":"names-settings","submenu":"archive-names"},"single_names[product_price]":{"option_label":"Price Label","option_tip":null,"tab":"names-settings","submenu":"single-names"},"single_names[free]":{"option_label":"Free Product Text","option_tip":null,"tab":"names-settings","submenu":"single-names"},"single_names[after_price]":{"option_label":"After Price Text","option_tip":null,"tab":"names-settings","submenu":"single-names"},"single_names[product_shipping]":{"option_label":"Shipping Label","option_tip":null,"tab":"names-settings","submenu":"single-names"},"single_names[product_sku]":{"option_label":"SKU Label","option_tip":null,"tab":"names-settings","submenu":"single-names"},"single_names[product_mpn]":{"option_label":"MPN Label","option_tip":null,"tab":"names-settings","submenu":"single-names"},"single_names[product_description]":{"option_label":"Description Label","option_tip":null,"tab":"names-settings","submenu":"single-names"},"single_names[product_features]":{"option_label":"Features Label","option_tip":null,"tab":"names-settings","submenu":"single-names"},"single_names[other_categories]":{"option_label":"Another Categories Label","option_tip":null,"tab":"names-settings","submenu":"single-names"},"single_names[return_to_archive]":{"option_label":"Return to Products Label","option_tip":null,"tab":"names-settings","submenu":"single-names"},"product_shipping_options_number":{"option_label":"Number of shipping options","option_tip":"","tab":"shipping-settings","submenu":"shipping"},"product_archive":{"option_label":"Default","option_tip":"","tab":"product-settings","submenu":""},"archive_multiple_settings[shortcode_mode][show_everywhere]":{"option_label":"Show main catalog page content everywhere","option_tip":"Check this if you want to display main catalog page content on every catalog page. For example if you are using page builder on main catalog page to design your catalog.","tab":"product-settings","submenu":""},"archive_multiple_settings[shortcode_mode][force_name]":{"option_label":"Force product name display","option_tip":"On some themes the product name is missing on the product page so you can use this to restore it. Uncheck this if you see duplicated product name on the product page.","tab":"product-settings","submenu":""},"archive_multiple_settings[shortcode_mode][move_breadcrumbs]":{"option_label":"Move breadcrumbs to the top","option_tip":"Breadcrumbs will be displayed before the page title. It may require some additional styling when checked.","tab":"product-settings","submenu":""},"archive_multiple_settings[catalog_singular]":{"option_label":"Catalog Singular Name","option_tip":"Admin panel customisation setting. Change it to what you sell.","tab":"product-settings","submenu":""},"archive_multiple_settings[catalog_plural]":{"option_label":"Catalog Plural Name","option_tip":"Admin panel customisation setting. Change it to what you sell.","tab":"product-settings","submenu":""},"enable_product_listing":{"option_label":"Enable Main Listing Page","option_tip":"Disable and use [show_products] shortcode to display the products.","tab":"product-settings","submenu":""},"archive_multiple_settings[archive_products_limit]":{"option_label":"Listing shows at most","option_tip":"You can also use shortcode with products_limit attribute to set this.","tab":"product-settings","submenu":""},"archive_multiple_settings[product_listing_cats]":{"option_label":"Main listing shows","option_tip":"","tab":"product-settings","submenu":""},"archive_multiple_settings[product_order]":{"option_label":"Default order","option_tip":"This is also the default setting for sorting drop-down.","tab":"product-settings","submenu":""},"archive_multiple_settings[category_top_cats]":{"option_label":"Category Page shows","option_tip":"The main listing can show only products, top level categories and products or only the categories. With the subcategories option selected the products will show only if they are directly assigned to the category. If you want to display the products only on the bottom category level please assign the products only to it (not to all categories in the tree).","tab":"product-settings","submenu":""},"archive_multiple_settings[cat_template]":{"option_label":"Categories Display","option_tip":"Template option will display categories with the same listing theme as products. Link option will show categories as simple URLs without image.","tab":"product-settings","submenu":""},"archive_multiple_settings[cat_image_disabled]":{"option_label":"Disable Image on Category Page","option_tip":"If you disable the image it will be only used for categories listing.","tab":"product-settings","submenu":""},"archive_multiple_settings[related]":{"option_label":"Show Related","option_tip":"The related products or categories will be shown on the bottom of product pages.","tab":"product-settings","submenu":""},"archive_multiple_settings[seo_title]":{"option_label":"Archive SEO Title","option_tip":"Title tag for selected product listing page. If you are using separate SEO plugin you should set it there. E.g. in Yoast SEO look for it in Custom Post Types archive titles section.","tab":"product-settings","submenu":""},"archive_multiple_settings[seo_title_sep]":{"option_label":"Enable SEO title separator","option_tip":"","tab":"product-settings","submenu":""},"archive_multiple_settings[enable_structured_data]":{"option_label":"Enable Structured Data","option_tip":"Enable to show structured data on each single product page. Test it with Google\u2019s Structured Data Testing Tool. You can modify the output with the structured-data.php template file.","tab":"product-settings","submenu":""},"archive_multiple_settings[enable_product_breadcrumbs]":{"option_label":"Enable Catalog Breadcrumbs","option_tip":"Shows a path to the currently displayed product catalog page with URLs to parent pages and correct schema markup for SEO.","tab":"product-settings","submenu":""},"archive_multiple_settings[breadcrumbs_title]":{"option_label":"Main listing breadcrumbs title","option_tip":"The title for main product listing in breadcrumbs.","tab":"product-settings","submenu":""},"product_currency_settings[price_enable]":{"option_label":"Price","option_tip":"Whether to enable or disable price functionality for the catalog.","tab":"product-settings","submenu":""},"product_currency":{"option_label":"Your currency","option_tip":"","tab":"product-settings","submenu":""},"product_currency_settings[custom_symbol]":{"option_label":"Custom Currency Symbol","option_tip":"If you choose custom currency symbol, it will override Your Currency setting and let you use any currency.","tab":"product-settings","submenu":""},"product_currency_settings[price_format]":{"option_label":"Currency position","option_tip":"","tab":"product-settings","submenu":""},"product_currency_settings[price_space]":{"option_label":"Space between currency & price","option_tip":"","tab":"product-settings","submenu":""},"product_currency_settings[th_sep]":{"option_label":"Thousands Separator","option_tip":null,"tab":"product-settings","submenu":""},"product_currency_settings[dec_sep]":{"option_label":"Decimal Separator","option_tip":null,"tab":"product-settings","submenu":""},"archive_multiple_settings[disable_sku]":{"option_label":"Disable SKU","option_tip":"","tab":"product-settings","submenu":""},"archive_multiple_settings[disable_mpn]":{"option_label":"Disable MPN","option_tip":"","tab":"product-settings","submenu":""},"simple-export-button":{"option_label":"Export Products","option_tip":"","tab":"product-settings","submenu":"csv"},"product_csv":{"option_label":"Import Products","option_tip":"","tab":"product-settings","submenu":"csv"},"product_attributes_number":{"option_label":"Number of attributes","option_tip":"","tab":"attributes-settings","submenu":"attributes"},"product_attribute_label":{"option_label":"Attribute name","option_tip":"","tab":"attributes-settings","submenu":"attributes"},"product_attribute":{"option_label":"Attribute value","option_tip":"","tab":"attributes-settings","submenu":"attributes"},"product_attribute_unit":{"option_label":"Attribute Unit","option_tip":"","tab":"attributes-settings","submenu":"attributes"},"ic_attributes_compare[url]":{"option_label":"Comparison Disabled","option_tip":"","tab":"attributes-settings","submenu":"attributes"},"ic_standard_attributes[size_unit]":{"option_label":"Size Unit","option_tip":null,"tab":"attributes-settings","submenu":"attributes"},"ic_standard_attributes[weight_unit]":{"option_label":"Weight Unit","option_tip":null,"tab":"attributes-settings","submenu":"attributes"},"archive_template":{"option_label":"Listing Design","option_tip":"","tab":"design-settings","submenu":"archive-design"},"modern_grid_settings[per-row]":{"option_label":"Per row products (Modern Grid)","option_tip":"","tab":"design-settings","submenu":"archive-design"},"modern_grid_settings[per-row-categories]":{"option_label":"Per row categories (Modern Grid)","option_tip":"","tab":"design-settings","submenu":"archive-design"},"modern_grid_settings[attributes]":{"option_label":"Show Attributes Modern Grid","option_tip":"","tab":"design-settings","submenu":"archive-design"},"classic_list_settings[attributes]":{"option_label":"Show Attributes Classic List","option_tip":"","tab":"design-settings","submenu":"archive-design"},"classic_grid_settings[entries]":{"option_label":"Per row products (Classic Grid)","option_tip":"","tab":"design-settings","submenu":"archive-design"},"classic_grid_settings[per-row-categories]":{"option_label":"Per row categories (Classic Grid)","option_tip":"","tab":"design-settings","submenu":"archive-design"},"classic_grid_settings[attributes]":{"option_label":"Show Attributes Classic Grid","option_tip":"","tab":"design-settings","submenu":"archive-design"}}', true );
    }

}

global $ic_settings_search;
$ic_settings_search = new ic_settings_search;
