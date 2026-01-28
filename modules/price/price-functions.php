<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Manages product attributes
 *
 * Here all product attributes are defined and managed.
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/includes
 * @author        impleCode
 */
class ic_price_display {

    function __construct() {
        add_filter( 'product_price', array( __CLASS__, 'raw_price_format' ), 5 );
        add_filter( 'unfiltered_product_price', array( __CLASS__, 'raw_price_format' ), 5 );
        add_filter( 'price_format', array( __CLASS__, 'after_price_text' ) );
        add_action( 'example_price', array( __CLASS__, 'example_price' ) );
        add_action( 'product_details', array( __CLASS__, 'show_price' ), 7, 0 );
        add_action( 'archive_price', array( __CLASS__, 'show_archive_price' ), 10, 1 );
        add_filter( 'archive_price_filter', array( __CLASS__, 'set_archive_price' ), 10, 2 );

        add_filter( 'product-class', array( __CLASS__, 'price_class' ), 10, 2 );
    }

    /**
     * Transforms number to the price format
     *
     * @param float $price_value The number to be price formatted
     * @param int $clear Set to 1 to skip price_format filter and allow $format variable
     * @param int $format Set to 0 to return not formatted price
     * @param int $raw Set to 0 to return formatted price without currency
     *
     * @return string|float
     */
    static function price_format( $price_value, $clear = 0, $format = 1, $raw = 0, $free_label = true ) {
        if ( $price_value === null || $price_value === '' ) {
            return '';
        } else if ( empty( $price_value ) && $free_label ) {
            $single_names = get_single_names();

            return $single_names['free'];
        }
        $set             = get_currency_settings();
        $raw_price_value = floatval( $price_value );
        $decimals        = ! empty( $set['dec_sep'] ) ? ic_price_display::decimals() : 0;
        $price_value     = number_format( $raw_price_value, $decimals, $set['dec_sep'], $set['th_sep'] );
        $space           = ' ';
        if ( $set['price_space'] == 'off' ) {
            $space = '';
        }
        $formatted = $price_value . $space . self::product_currency();
        if ( $set['price_format'] == 'before' ) {
            $formatted = self::product_currency() . $space . $price_value;
        }
        if ( $clear == 0 ) {
            return apply_filters( 'price_format', $formatted, $price_value );
        } else if ( $format == 1 ) {
            return $formatted;
        } else if ( $raw == 1 ) {
            return $raw_price_value;
        } else {
            return $price_value;
        }
    }

    /**
     * Transforms price for internal use
     *
     * @param int|string $price_value
     *
     * @return int
     */
    static function raw_price_format( $price_value ) {
        if ( empty( $price_value ) ) {
            return $price_value;
        }
        $set = get_currency_settings();
        if ( strval( floatval( $price_value ) ) === $price_value ) {
            return floatval( $price_value );
        }

        $th_symbol = addslashes( $set['th_sep'] );
        if ( ! empty( $set['dec_sep'] ) && $set['dec_sep'] != '.' ) {
            $dec_symbol      = addslashes( $set['dec_sep'] );
            $raw_price_value = str_replace( array( $th_symbol, $dec_symbol ), array( "", '.' ), $price_value );
        } else {
            $raw_price_value = str_replace( $th_symbol, "", $price_value );
        }

        return floatval( $raw_price_value );
    }

    /**
     * Handles after price text
     *
     * @param string $price
     *
     * @return string
     */
    static function after_price_text( $price ) {
        if ( function_exists( 'is_ic_product_page' ) && is_ic_product_page() ) {
            $labels = get_single_names();
            if ( ! empty( $labels['after_price'] ) ) {
                $price .= ' <span class="after-price">' . $labels['after_price'] . '</span>';
            }
        }

        return $price;
    }

    static function example_price() {
        echo '2500.00 EUR';
    }

    /**
     * Shows price on product page
     *
     * @param type $post
     * @param type $single_names
     */
    static function show_price( $product_id = false ) {
        ic_show_template_file( 'product-page/product-price.php', AL_BASE_TEMPLATES_PATH, $product_id );
    }

    /**
     * Returns price table for product page
     *
     * @param type $product_id
     * @param type $single_names
     *
     * @return type
     */
    static function get_product_price_table( $product_id ) {
        ob_start();
        self::show_price( $product_id );

        return ob_get_clean();
    }

    /**
     * Returns product price
     *
     * @param int $product_id
     * @param string $unfiltered Assign any value to return the original price (without any modifications)
     *
     * @return string
     */
    static function product_price( $product_id, $unfiltered = null ) {
        if ( empty( $unfiltered ) ) {
            $price_value = apply_filters( 'product_price', get_post_meta( $product_id, "_price", true ), $product_id );
        } else {
            $price_value = apply_filters( 'unfiltered_product_price', get_post_meta( $product_id, "_price", true ), $product_id );
        }
        $price_value = ( is_ic_price_enabled() ) ? $price_value : '';

        return $price_value;
    }

    /**
     * 3 letter product currency format
     *
     * @return type
     */
    static function product_currency_letters( $filtered = true ) {
        return get_product_currency_code( $filtered );
    }

    /**
     * Returns product currency
     *
     * @return string
     */
    static function product_currency() {
        $product_currency = self::product_currency_letters();
        /*
          $product_currency_settings	 = get_option( 'product_currency_settings', array(
          'custom_symbol'	 => '$',
          'price_format'	 => 'before',
          'price_space'	 => 'off',
          'price_enable'	 => 'on',
          ) );
         *
         */
        $product_currency_settings = get_currency_settings();
        if ( ! empty( $product_currency_settings['custom_symbol'] ) ) {
            $currency = $product_currency_settings['custom_symbol'];
        } else {
            $currency = $product_currency;
        }

        return apply_filters( 'ic_product_currency', $currency );
    }

    /* Archive Functions */

    /**
     * Shows product listing price
     *
     * @param type $post
     */
    static function show_archive_price( $post ) {
        $price_value = self::product_price( $post->ID );
        if ( ! empty( $price_value ) ) {
            ?>
            <div class="product-price <?php design_schemes( 'color' ); ?>">
                <?php echo self::price_format( $price_value ) ?>
            </div>
            <?php
        }
    }

    /**
     * Sets product listing price
     *
     * @param type $archive_price
     * @param type $post
     *
     * @return string
     */
    static function set_archive_price( $archive_price, $post ) {
        if ( ! isset( $post->ID ) ) {
            return $archive_price;
        }
        $price_value = self::product_price( $post->ID );
        if ( $price_value !== '' ) {
            $archive_price = '<span class="product-price ' . design_schemes( '', 0 ) . '">';
            $archive_price .= apply_filters( 'ic_set_archive_price', self::price_format( $price_value ), $post->ID );
            $archive_price .= '</span>';
        }

        return $archive_price;
    }

    static function price_class( $class, $product_id ) {
        $price_value = self::product_price( $product_id );
        if ( empty( $price_value ) ) {
            $class .= ' no-price';
        } else {
            $class .= ' priced';
        }

        return $class;
    }

    static function decimals() {
        $decimals = intval( apply_filters( 'ic_epc_decimals', 2 ) );
        if ( $decimals < 0 ) {
            $decimals = 0;
        }

        return $decimals;
    }

}

$ic_price_display = new ic_price_display;
