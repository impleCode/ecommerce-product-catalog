<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Manages attributes settings
 *
 * Here attributes settings are defined and managed.
 *
 * @version        1.1.4
 * @package        ecommerce-product-catalog/functions
 * @author        impleCode
 */
add_action( 'settings-menu', 'attributes_menu', 20 );

/**
 * Shows attributes menu tab
 *
 */
function attributes_menu() {
    if ( current_user_can( 'manage_product_settings' ) ) {
        ?>
        <a id="attributes-settings" class="nav-tab"
           href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=attributes-settings&submenu=attributes' ) ?>"><?php _e( 'Attributes', 'ecommerce-product-catalog' ); ?></a>
        <?php
    }
}

// add_action('general_submenu','attributes_menu'); // UNCOMMENT TO INSERT IN FIRST TAB and change url above

function attributes_settings() {
    register_setting( 'product_attributes', 'product_attributes_number' );
    register_setting( 'product_attributes', 'al_display_attributes' );
    register_setting( 'product_attributes', 'product_attribute' );
    register_setting( 'product_attributes', 'product_attribute_label' );
    register_setting( 'product_attributes', 'product_attribute_unit' );
    register_setting( 'product_attributes', 'ic_standard_attributes' );
}

add_action( 'product-settings-list', 'attributes_settings' );

function attributes_settings_content( $url = '', $label = '' ) {
    $submenu = isset( $_GET['submenu'] ) ? $_GET['submenu'] : '';
    if ( empty( $url ) ) {
        $url = admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=attributes-settings&submenu=attributes' );
    }
    if ( empty( $label ) ) {
        $label = __( 'Attributes Settings', 'ecommerce-product-catalog' );
    }
    ?>
    <div class="attributes-product-settings settings-wrapper" style="clear:both;">
    <div class="settings-submenu">
        <h3>
            <a id="attributes-settings" class="element current"
               href="<?php echo $url ?>"><?php echo $label ?></a>
            <?php do_action( 'attributes_submenu' ); ?>
        </h3>
    </div>
    <?php if ( $submenu == 'attributes' ) { ?>
        <div class="setting-content submenu">
            <script>
                jQuery('.settings-submenu a').removeClass('current');
                jQuery('.settings-submenu a#attributes-settings').addClass('current');
            </script>
            <h2><?php _e( 'Attributes Settings', 'ecommerce-product-catalog' ); ?></h2>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'product_attributes' );
                $attributes_count = product_attributes_number();
                if ( function_exists( 'ic_register_setting' ) ) {
                    ic_register_setting( __( 'Number of attributes', 'ecommerce-product-catalog' ), 'product_attributes_number' );
                }
                ?>
                <h3><?php _e( 'Attributes options', 'ecommerce-product-catalog' ); ?></h3>
                <table>
                    <tr>
                        <td colspan="2"><?php _e( 'Number of attributes', 'ecommerce-product-catalog' ) ?> <input
                                    size="30" type="number" step="1" min="0" name="product_attributes_number"
                                    id="admin-number-field" value="<?php echo $attributes_count; ?>"/><input
                                    type="submit" class="button"
                                    value="<?php _e( 'Update', 'ecommerce-product-catalog' ); ?>"/>
                        </td>
                    </tr>
                </table>
                <?php
                if ( $attributes_count > 0 ) {
                    ?>
                    <div class="al-box info">
                        <p><?php _e( "If you fill out the fields below, the system will automatically pre-fill the fields on product pages, so you don't have to fill them every time you add product.", 'ecommerce-product-catalog' ) ?></p>
                        <p><?php _e( "When every product in your catalogue is different, you can leave all or a part of these fields empty.", 'ecommerce-product-catalog' ) ?></p>
                        <p><?php _e( 'You can change these default values for every item.', 'ecommerce-product-catalog' ); ?></p>
                    </div>
                    <div class="settings-table-container" style="overflow-x: scroll;">
                        <table class="wp-list-table widefat product-settings-table dragable">
                            <thead>
                            <tr>
                                <th class="title"></th>
                                <th class="title"><b><?php _e( 'Attribute name', 'ecommerce-product-catalog' ); ?></b>
                                </th>
                                <th></th>
                                <th class="title"><b><?php _e( 'Attribute value', 'ecommerce-product-catalog' ); ?></b>
                                </th>
                                <th class="title"><b><?php _e( 'Unit', 'ecommerce-product-catalog' ); ?></b></th>
                                <?php
                                if ( function_exists( 'ic_register_setting' ) ) {
                                    ic_register_setting( __( 'Attribute name', 'ecommerce-product-catalog' ), 'product_attribute_label' );
                                    ic_register_setting( __( 'Attribute value', 'ecommerce-product-catalog' ), 'product_attribute' );
                                    ic_register_setting( __( 'Attribute Unit', 'ecommerce-product-catalog' ), 'product_attribute_unit' );
                                }
                                do_action( 'product_attributes_settings_table_th' );
                                ?>
                                <th class="dragger"></th>
                            </tr>
                            </thead>
                            <tbody><?php
                            $attribute       = get_default_product_attribute_value();
                            $attribute_label = get_default_product_attribute_label();
                            $attribute_unit  = get_default_product_attribute_unit();
                            ic_attributes_settings_rows( $attribute, $attribute_label, $attribute_unit );
                            ?>
                            </tbody>
                        </table>
                    </div>
                    <?php
                    do_action( 'attributes-settings' );
                } else {
                    ?>
                    <table>
                        <tr>
                            <td colspan="2">
                                <div class="al-box warning"><?php _e( 'Attributes disabled. To enable set minimum 1 attribute.', 'ecommerce-product-catalog' ); ?></div>
                            </td>
                        </tr>
                    </table>
                    <?php
                }
                ?>
                <h3><?php _e( 'Standard Attributes', 'ecommerce-product-catalog' ); ?></h3>
                <table>
                    <?php
                    $standard_settings = ic_attributes_standard_settings();
                    implecode_settings_dropdown( __( 'Size Unit', 'ecommerce-product-catalog' ), 'ic_standard_attributes[size_unit]', $standard_settings['size_unit'], ic_available_size_units() );
                    implecode_settings_dropdown( __( 'Weight Unit', 'ecommerce-product-catalog' ), 'ic_standard_attributes[weight_unit]', $standard_settings['weight_unit'], ic_available_weight_units() );
                    ?>
                </table>
                <?php do_action( 'ic_catalog_standard_attributes_settings', $standard_settings ); ?>
                <p class="submit">
                    <input type="submit" class="button-primary"
                           value="<?php _e( 'Save changes', 'ecommerce-product-catalog' ); ?>"/>
                </p>
            </form>
        </div>
        <div class="helpers">
            <div class="wrapper"><?php
                main_helper();
                doc_helper( __( 'attributes', 'ecommerce-product-catalog' ), 'product-attributes' )
                ?>
            </div>
        </div>
    <?php }
    do_action( 'product-attributes' ); ?>
    </div><?php
}

function ic_attributes_settings_rows( $attribute, $attribute_label, $attribute_unit, $names = null, $max_num = null ) {
    if ( $max_num === null ) {
        $max_num = product_attributes_number();
    }
    if ( $names === null ) {
        $names['label'] = 'product_attribute_label';
        $names['value'] = 'product_attribute';
        $names['unit']  = 'product_attribute_unit';
    }
    for ( $i = 1; $i <= $max_num; $i ++ ) {
        $attribute_label[ $i ] = isset( $attribute_label[ $i ] ) ? $attribute_label[ $i ] : '';
        $attribute[ $i ]       = isset( $attribute[ $i ] ) ? $attribute[ $i ] : '';
        $attribute_unit[ $i ]  = isset( $attribute_unit[ $i ] ) ? $attribute_unit[ $i ] : '';
        ?>
        <tr>
            <td class="lp-column lp<?php echo $i ?>"><?php echo $i ?>.</td>
            <td class="product-attribute-label-column"><input class="product-attribute-label" type="text"
                                                              data-base_name="<?php echo $names['label'] ?>"
                                                              name="<?php echo $names['label'] ?>[<?php echo $i ?>]"
                                                              value="<?php echo esc_html( $attribute_label[ $i ] ) ?>"/>
            </td>
            <td class="lp-column">:</td>
            <td><input class="product-attribute" type="text" data-base_name="<?php echo $names['value'] ?>"
                       name="<?php echo $names['value'] ?>[<?php echo $i ?>]"
                       value="<?php echo esc_html( $attribute[ $i ] ) ?>"/></td>
            <td><input id="admin-number-field" class="product-attribute-unit" type="text"
                       data-base_name="<?php echo $names['unit'] ?>"
                       name="<?php echo $names['unit'] ?>[<?php echo $i ?>]"
                       value="<?php echo esc_html( $attribute_unit[ $i ] ) ?>"/></td>
            <?php do_action( 'product_attributes_settings_table_td', $i, $names, $attribute_label[ $i ], $attribute[ $i ], $attribute_unit[ $i ] ); ?>
            <td class="dragger"></td>
        </tr> <?php
    }
}

add_action( 'general_settings', 'attributes_settings_content' );

/**
 * Returns the number of defined product attributes
 *
 * @return int
 */
function product_attributes_number() {
    $number = ic_get_global( 'product_attributes_number' );
    if ( ! $number ) {
        $number = get_option( 'product_attributes_number', 3 );
        ic_save_global( 'product_attributes_number', $number );
    }

    return intval( $number );
}

/**
 * Returns default product attribute label defined in product settings
 *
 * @param int $i
 *
 * @return string
 */
function get_default_product_attribute_label( $i = null ) {
    $attribute_label = apply_filters( 'ic_product_attribute_label_option', get_option( 'product_attribute_label' ) );
    if ( ! is_array( $attribute_label ) ) {
        $attribute_label = array();
    }
    if ( $i === null ) {
        return $attribute_label;
    }
    $attribute_label[ $i ] = isset( $attribute_label[ $i ] ) ? $attribute_label[ $i ] : '';

    return $attribute_label[ $i ];
}

/**
 * Returns default product attribute value defined in product settings
 *
 * @param int $i
 *
 * @return string
 */
function get_default_product_attribute_value( $i = null ) {
    $attribute_value = get_option( 'product_attribute' );
    if ( ! is_array( $attribute_value ) ) {
        $attribute_value = array();
    }
    if ( $i === null ) {
        return $attribute_value;
    }
    $attribute_value[ $i ] = isset( $attribute_value[ $i ] ) ? $attribute_value[ $i ] : '';

    return $attribute_value[ $i ];
}

/**
 * Returns default product attribute unit defined in product settings
 *
 * @param int $i
 *
 * @return string
 */
function get_default_product_attribute_unit( $i = null ) {
    $attribute_unit = get_option( 'product_attribute_unit' );
    if ( ! is_array( $attribute_unit ) ) {
        $attribute_unit = array();
    }
    if ( $i === null ) {
        return $attribute_unit;
    }
    $attribute_unit[ $i ] = isset( $attribute_unit[ $i ] ) ? $attribute_unit[ $i ] : '';

    return apply_filters( 'ic_default_product_attribute_unit', $attribute_unit[ $i ], $i );
}

add_action( 'modern_grid_additional_settings', 'ic_listing_attributes_settings', 10, 2 );
add_action( 'classic_list_additional_settings', 'ic_listing_attributes_settings', 10, 2 );
add_action( 'classic_grid_additional_settings', 'ic_listing_attributes_settings', 10, 2 );

function ic_listing_attributes_settings( $listing_settings, $listing_name ) {
    $option_name = $listing_name . '_settings[attributes]';
    ic_register_setting( __( 'Show Attributes', 'ecommerce-product-catalog' ) . ' ' . ucwords( str_replace( '_', ' ', $listing_name ) ), $option_name );
    ?>
    <input title="<?php _e( 'Use this only with short attributes labels and values, e.g. Color: Red', 'ecommerce-product-catalog' ) ?>"
           type="checkbox" name="<?php echo $option_name ?>"
           value="1"<?php checked( 1, isset( $listing_settings['attributes'] ) ? $listing_settings['attributes'] : '' ); ?>> <?php _e( 'Show Attributes', 'ecommerce-product-catalog' ); ?>
    <br><?php
}

function ic_attributes_standard_settings() {
    $settings = get_option( 'ic_standard_attributes' );
    if ( empty( $settings ) ) {
        $settings = array();
    }
    $settings['weight_unit'] = ! empty( $settings['weight_unit'] ) ? $settings['weight_unit'] : 'kg';
    $settings['size_unit']   = ! empty( $settings['size_unit'] ) ? $settings['size_unit'] : 'cm';

    return $settings;
}

function ic_available_weight_units() {
    $units = array(
            'disable' => __( 'Disable Weight', 'ecommerce-product-catalog' ),
            'kg'      => 'kg',
            'g'       => 'g',
            'lbs'     => 'lbs',
            'oz'      => 'oz'
    );

    return $units;
}

function ic_available_size_units() {
    $units = array(
            'disable' => __( 'Disable Size', 'ecommerce-product-catalog' ),
            'm'       => 'm',
            'cm'      => 'cm',
            'mm'      => 'mm',
            'in'      => 'in',
            'yd'      => 'yd',
            'ft'      => 'ft'
    );

    return $units;
}

function ic_attributes_get_size_unit() {
    $settings = ic_attributes_standard_settings();
    if ( ! empty( $settings['size_unit'] ) && $settings['size_unit'] !== 'disable' ) {
        return apply_filters( 'ic_size_display_unit', $settings['size_unit'] );
    }

    return '';
}

function ic_attributes_get_weight_unit() {
    $settings = ic_attributes_standard_settings();
    if ( ! empty( $settings['weight_unit'] ) && $settings['weight_unit'] !== 'disable' ) {
        return apply_filters( 'ic_weight_display_unit', $settings['weight_unit'] );
    }

    return '';
}

function ic_attributes_get_weight_label() {
    $single_names = get_single_names();

    return $single_names['weight'];
}

function ic_attributes_get_size_label() {
    $single_names = get_single_names();

    return $single_names['size'];
}

function ic_attributes_get_height_label() {
    $single_names = get_single_names();

    return $single_names['height'];
}

function ic_attributes_get_width_label() {
    $single_names = get_single_names();

    return $single_names['width'];
}

function ic_attributes_get_length_label() {
    $single_names = get_single_names();

    return $single_names['length'];
}

add_filter( 'ic_default_single_names', 'ic_attributes_standard_labels' );

function ic_attributes_standard_labels( $single_names ) {
    remove_filter( 'ic_default_single_names', 'ic_attributes_standard_labels' );
    $true_single_names = get_single_names();
    add_filter( 'ic_default_single_names', 'ic_attributes_standard_labels' );
    $single_names['product_size']   = $true_single_names['size'] . ':';
    $single_names['product_weight'] = $true_single_names['weight'] . ':';

    return $single_names;
}

add_filter( 'pre_update_option_product_attribute_label', 'ic_product_attribute_label_update', 10, 2 );

function ic_product_attribute_label_update( $new_value, $old_value ) {
    if ( empty( $old_value ) || ! is_array( $new_value ) ) {
        return $new_value;
    }
    ic_clear_empty_attributes();
    $all_labels = get_all_attribute_labels();
    foreach ( $old_value as $i => $old ) {
        if ( ! isset( $new_value[ $i ] ) || in_array( $old, $new_value ) || ( ! empty( $new_value[ $i ] ) && $old === $new_value[ $i ] ) ) {
            continue;
        } else {
            $new = strval( $new_value[ $i ] );
        }
        if ( empty( $new ) || in_array( $new, $all_labels ) ) {
            continue;
        }
        if ( ! in_array( $old, $all_labels ) ) {
            continue;
        }
        $attribute_id = intval( ic_get_attribute_id( $old ) );
        if ( empty( $attribute_id ) ) {
            continue;
        }
        wp_update_term( $attribute_id, 'al_product-attributes', array(
                'parent' => 0,
                'name'   => $new,
                'slug'   => $new
        ) );
    }

    return $new_value;
}
