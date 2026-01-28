<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Manages support settings
 *
 * Here support settings are defined and managed.
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/includes
 * @author        impleCode
 */
//add_action( 'settings-menu', 'ic_admin_add_import_tab', 99 );

function ic_admin_add_import_tab() {
    ?>
    <a id="csv-settings" class="nav-tab<?php echo $class ?>"
       href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=product-settings&submenu=csv' ) ?>"><?php _e( 'Import / Export', 'ecommerce-product-catalog' ); ?></a>
    <?php
}

add_action( 'general_submenu', 'implecode_custom_csv_menu' );

function implecode_custom_csv_menu() {
    ?>
    <a id="csv-settings" class="element"
       href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=product-settings&submenu=csv' ) ?>"><?php _e( 'Import / Export', 'ecommerce-product-catalog' ); ?></a>
    <?php
}

function implecode_custom_csv_settings_content() {
    ?>
    <?php $submenu = isset( $_GET['submenu'] ) ? $_GET['submenu'] : ''; ?>
    <?php if ( $submenu == 'csv' ) { ?>
        <div class="setting-content submenu csv-tab">
            <script>
                jQuery('.settings-submenu a').removeClass('current');
                jQuery('.settings-submenu a#csv-settings').addClass('current');
            </script>
            <h2><?php
                _e( 'Simple CSV', 'ecommerce-product-catalog' );
                ?>
            </h2>
            <h3><?php _e( 'Simple Export', 'ecommerce-product-catalog' ); ?></h3>
            <?php
            $export = isset( $_GET['export_csv'] ) ? $_GET['export_csv'] : '';
            ic_register_setting( __( 'Export Products', 'ecommerce-product-catalog' ), 'simple-export-button' );
            ic_register_setting( __( 'Import Products', 'ecommerce-product-catalog' ), 'product_csv' );
            if ( $export == 1 ) {
                $url = simple_export_to_csv();
                echo '<a style="display: block; margin-top: 20px;" href="' . $url . '">' . __( "Download CSV", 'ecommerce-product-catalog' ) . '</a>';
            } else {
                ?>
                <a style="display: block; margin-top: 20px;"
                   href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=product-settings&submenu=csv&export_csv=1' ) ?>">
                    <button class="button simple-export-button"><?php _e( "Export all items to CSV file", 'ecommerce-product-catalog' ) ?></button>
                </a>
                <h3><?php _e( 'Simple Import', 'ecommerce-product-catalog' ); ?></h3>
                <?php
                simple_upload_csv_products_file();
                do_action( 'ic_simple_csv_bottom' );
            }
            ?>
        </div>
        <div class="helpers">
        <div class="wrapper"><?php
            main_helper();
            doc_helper( __( 'import', 'ecommerce-product-catalog' ), 'product-import' );
            ?>
        </div></div><?php
    }
}

add_action( 'admin_init', 'ic_simple_csv_provide_admin_file' );

function ic_simple_csv_provide_admin_file() {
    $provide_export        = isset( $_GET['provide_export_csv'] ) ? $_GET['provide_export_csv'] : '';
    $provide_import_sample = isset( $_GET['provide_import_sample'] ) ? $_GET['provide_import_sample'] : '';
    if ( $provide_export == 1 ) {
        ic_simple_csv_provide_export();
    } else if ( $provide_import_sample == 1 ) {
        ic_simple_csv_provide_import_sample();
    }
}


add_action( 'product-settings', 'implecode_custom_csv_settings_content' );

function simple_upload_csv_products_file() {
    $upload_feedback = '';
    if ( isset( $_FILES['product_csv'] ) && ( $_FILES['product_csv']['size'] > 0 ) ) {
        $arr_file_type      = wp_check_filetype( basename( $_FILES['product_csv']['name'] ) );
        $uploaded_file_type = $arr_file_type['ext'];
        $allowed_file_type  = 'csv';
        if ( $uploaded_file_type == $allowed_file_type ) {
            $filepath = ic_simple_import_file_name();
            if ( move_uploaded_file( $_FILES['product_csv']['tmp_name'], $filepath ) ) {
                simple_import_product_from_csv();
            } else {
                $upload_feedback = '<div class="al-box warning">' . __( 'There was a problem with your upload.', 'ecommerce-product-catalog' ) . '</div>';
            }
        } else {
            $upload_feedback = '<div class="al-box warning">' . __( 'Please upload only CSV files.', 'ecommerce-product-catalog' ) . '</div>';
        }
        echo $upload_feedback;
    } else {
        if ( ! empty( $_FILES['product_csv']['error'] ) ) {
            if ( $_FILES['product_csv']['error'] === 1 || $_FILES['product_csv']['error'] === 2 ) {
                implecode_warning( __( "The file could not be uploaded because of your server limit. Please contact the server administrator or decrease the file size.", "ecommerce-product-catalog" ) );
            } else {
                implecode_warning( __( "There was an error while uploading the file to your server.", "ecommerce-product-catalog" ) );
            }
        }
        $url = sample_import_file_url();
        echo '<form method="POST" enctype="multipart/form-data"><input type="file" accept=".csv" name="product_csv" id="product_csv" /><input type="submit" class="button" value="' . esc_attr( __( 'Import Now', 'ecommerce-product-catalog' ) ) . '" /></form>';
        $sep = get_simple_separator();
        if ( $sep === ';' ) {
            $sep_label = __( 'Semicolon', 'ecommerce-product-catalog' );
        } else {
            $sep_label = __( 'Comma', 'ecommerce-product-catalog' );
        }
        echo '<div class="al-box info"><p>' . __( "The CSV fields should be in the following order: Image URL, Name, Price, Categories, Short Description, Long Description.", "ecommerce-product-catalog" ) . '</p><p>' . sprintf( __( "The first row should contain the field names. %s should be used as the CSV separator.", "ecommerce-product-catalog" ), $sep_label ) . '</p><a href="' . $url . '" class="button-primary">' . __( 'Download CSV Template', 'ecommerce-product-catalog' ) . '</a></div>';
    }
}

function simple_import_product_from_csv() {
    $file_path = ic_simple_import_file_name();
    $fp        = simple_prepare_csv_file( 'r', $file_path );
    $product   = array();
    if ( $fp !== false ) {
        $sep      = apply_filters( 'simple_csv_separator', ';' );
        $csv_cols = fgetcsv( $fp, 0, $sep, '"', '\\' );
        if ( isset( $csv_cols[0] ) && $csv_cols[0] == '﻿sep=' ) {
            $csv_cols = fgetcsv( $fp, 0, $sep, '"', '\\' );
        }
        $import_array = simple_prepare_csv_import_array();
        if ( count( $csv_cols ) == count( $import_array ) ) {
            $i     = 0;
            $error = 0;
            while ( ( $data = fgetcsv( $fp, 0, $sep, '"', '\\' ) ) !== false ) {
                $filtered_data = array_filter( $data );
                if ( empty( $data ) || ! is_array( $data ) || ( is_array( $data ) && empty( $filtered_data ) ) || count( $data ) == 1 ) {
                    continue;
                }
                foreach ( $data as $key => $val ) {
                    if ( isset( $import_array[ $key ] ) ) {
                        unset( $data[ $key ] );
                        $new_key          = $import_array[ $key ];
                        $data[ $new_key ] = $val;
                    }
                }

                $product_id = simple_insert_csv_product( $data );
                if ( ! empty( $product_id ) && ! is_wp_error( $product_id ) ) {
                    $i ++;
                } else {
                    $error ++;
                }
            }
            $result = 'success';
            if ( ! empty( $error ) ) {
                $result = 'warning';
            }
            echo '<div class="al-box ' . $result . '">';
            echo '<p>' . sprintf( __( '%s products successfully added to the catalog', 'ecommerce-product-catalog' ), $i ) . '.<p>';
            if ( ! empty( $error ) ) {
                echo '<p>' . sprintf( __( '%s failures occurred. Please check if the file is UTF-8 encoded', 'ecommerce-product-catalog' ), $error ) . '.</p>';
            }
            echo '</div>';
        } else {
            //echo '<div class="al-box warning">';
            //_e( 'Number of fields in database and number of fields in CSV file do not match!', 'ecommerce-product-catalog' );
            $included     = str_replace( array( 'Array', '(', ')', ']', '[' ), array(
                    '',
                    '',
                    '',
                    '',
                    '<br>'
            ), print_r( $csv_cols, true ) );
            $export_array = prepare_sample_import_file();
            $expected     = str_replace( array( 'Array', '(', ')', ']', '[' ), array(
                    '',
                    '',
                    '',
                    '',
                    '<br>'
            ), print_r( array_values( $export_array[1] ), true ) );
            echo '<div class = "al-box warning">';
            echo '<p>' . __( 'Number of product fields and number of fields in CSV file do not match!', 'ecommerce-product-catalog' ) . '</p>';
            echo '<p>' . sprintf( __( 'Columns included in file: %s', 'al-product-csv' ), $included ) . '</p>';
            echo '<p>' . sprintf( __( 'Columns expected in file: %s', 'al-product-csv' ), $expected ) . '</p>';
            echo '<p>' . __( 'Please make sure that only the expected columns exist in the import file and the correct CSV separator is set.', 'ecommerce-product-catalog' ) . '</p>';
            echo '</div>';
            //echo '</div>';
        }
    }
    fclose( $fp );
}

function simple_prepare_csv_file( $type = 'w', $file_path = '' ) {
    if ( version_compare( PHP_VERSION, '8.1.0', '<' ) ) {
        ini_set( 'auto_detect_line_endings', true );
    }
    $fp = fopen( $file_path, $type ) or die( implecode_warning( sprintf( __( 'Permission error. Please check WordPress uploads %sfolder permissions%s.', 'ecommerce-product-catalog' ), '<a href="https://codex.wordpress.org/Changing_File_Permissions">', '</a>' ), 0 ) );

    return $fp;
}

function simple_prepare_csv_import_array() {
    $arr   = array( 'image_url' );
    $arr[] = 'product_name';
    if ( function_exists( 'is_ic_price_enabled' ) && is_ic_price_enabled() ) {
        $arr[] = 'product_price';
    }
    $arr[] = 'product_categories';
    $arr[] = 'product_short_desc';
    $arr[] = 'product_desc';

    return $arr;
}

function simple_insert_csv_product( $data ) {
    $short_description = wp_kses_post( $data['product_short_desc'] );
    $long_description  = wp_kses_post( $data['product_desc'] );
    $post              = array(
            'ID'           => '',
            'post_title'   => $data['product_name'],
            'post_status'  => 'publish',
            'post_type'    => 'al_product',
            'post_excerpt' => $short_description,
            'post_content' => $long_description
    );
    $id                = wp_insert_post( $post );
    if ( ! is_wp_error( $id ) && ! empty( $id ) ) {
        if ( function_exists( 'is_ic_price_enabled' ) && is_ic_price_enabled() && isset( $data['product_price'] ) ) {
            update_post_meta( $id, '_price', ic_price_display::raw_price_format( $data['product_price'] ) );
        }
        //update_post_meta( $id, 'excerpt', $short_description );
        //update_post_meta( $id, 'content', $long_description );
        $image_url = get_product_image_id( $data['image_url'] );
        set_post_thumbnail( $id, $image_url );
        if ( ! empty( $data['product_categories'] ) ) {
            if ( ic_string_contains( $data['product_categories'], ' | ' ) ) {
                $data['product_categories'] = explode( ' | ', $data['product_categories'] );
            }
            wp_set_object_terms( $id, $data['product_categories'], 'al_product-cat' );
        }
        ic_set_time_limit( 30 );
    }

    return $id;
}

function prepare_sample_import_file() {
    $fields                    = array();
    $fields[1]['image_url']    = __( 'Image URL', 'ecommerce-product-catalog' );
    $fields[1]['product_name'] = __( 'Name', 'ecommerce-product-catalog' );
    if ( function_exists( 'is_ic_price_enabled' ) && is_ic_price_enabled() ) {
        $fields[1]['product_price'] = __( 'Price', 'ecommerce-product-catalog' );
    }
    $fields[1]['product_categories'] = __( 'Categories', 'ecommerce-product-catalog' );
    $fields[1]['product_short_desc'] = __( 'Short Description', 'ecommerce-product-catalog' );
    $fields[1]['product_desc']       = __( 'Long Description', 'ecommerce-product-catalog' );

    return array_filter( $fields );
}

function sample_import_file_url() {
    $file_path = ic_simple_import_file_name();
    $fp        = simple_prepare_csv_file( 'w', $file_path );
    $fields    = prepare_sample_import_file();
    fprintf( $fp, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );
    $sep = apply_filters( 'simple_csv_separator', ';' );
    foreach ( $fields as $field ) {
        fputcsv( $fp, $field, $sep, '"', "\\" );
    }
    simple_close_csv_file( $fp );

    return ic_simple_import_template_file_url();
}

function simple_close_csv_file( $fp ) {
    fclose( $fp );
    ini_set( 'auto_detect_line_endings', false );
}

function simple_get_all_exported_products() {
    $args     = array(
            'posts_per_page'   => 1000,
            'orderby'          => 'title',
            'order'            => 'ASC',
            'post_type'        => 'al_product',
            'post_status'      => ic_visible_product_status(),
            'suppress_filters' => true
    );
    $products = get_posts( $args );

    return $products;
}

function simple_prepare_products_to_export() {
    $products                  = simple_get_all_exported_products();
    $fields                    = array();
    $fields[1]['image_url']    = __( 'Image URL', 'ecommerce-product-catalog' );
    $fields[1]['product_name'] = __( 'Name', 'ecommerce-product-catalog' );
    if ( class_exists( 'ic_price_display' ) ) {
        $fields[1]['product_price'] = __( 'Price', 'ecommerce-product-catalog' );
    }
    $fields[1]['product_categories'] = __( 'Categories', 'ecommerce-product-catalog' );
    $fields[1]['product_short_desc'] = __( 'Short Description', 'ecommerce-product-catalog' );
    $fields[1]['product_desc']       = __( 'Long Description', 'ecommerce-product-catalog' );
    $z                               = 2;
    foreach ( $products as $product ) {
        $image      = wp_get_attachment_image_src( get_post_thumbnail_id( $product->ID ), 'full' );
        $desc       = get_product_description( $product->ID );
        $short_desc = get_product_short_description( $product->ID );
        if ( empty( $fields[ $z ] ) || ! is_array( $fields[ $z ] ) ) {
            $fields[ $z ] = array();
        }
        $image_url                    = isset( $image[0] ) ? $image[0] : '';
        $fields[ $z ]['image_url']    = $image_url;
        $fields[ $z ]['product_name'] = $product->post_title;
        if ( class_exists( 'ic_price_display' ) ) {
            $fields[ $z ]['product_price'] = get_post_meta( $product->ID, '_price', true );
        }
        $category_array = get_the_terms( $product->ID, 'al_product-cat' );
        $category       = array();
        if ( ! empty( $category_array ) ) {
            foreach ( $category_array as $p_cat ) {
                $value      = html_entity_decode( $p_cat->name );
                $category[] = $value;
            }
        }
        $fields[ $z ]['product_categories'] = implode( ' | ', $category );
        $fields[ $z ]['product_short_desc'] = $short_desc;
        $fields[ $z ]['product_desc']       = $desc;
        $z ++;
    }

    return array_filter( $fields );
}

function simple_export_to_csv() {
    $file_path = ic_simple_export_file_name();
    $fp        = simple_prepare_csv_file( 'w', $file_path );
    $fields    = simple_prepare_products_to_export();
    fprintf( $fp, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );
    $sep = apply_filters( 'simple_csv_separator', ';' );
    //fwrite( $fp, "sep=" . $sep . "\n" );
    foreach ( $fields as $field ) {
        fputcsv( $fp, $field, $sep, '"', "\\" );
    }
    simple_close_csv_file( $fp );

    return ic_simple_export_file_url();
}

function ic_simple_csv_provide_export() {
    $file_path = ic_simple_export_file_name();
    ic_simple_csv_provide_file( $file_path );
}

function ic_simple_csv_provide_import_sample() {
    $file_path = ic_simple_import_file_name();
    ic_simple_csv_provide_file( $file_path );
}

function ic_simple_csv_provide_file( $file_path ) {
    if ( ! current_user_can( 'read_private_products' ) ) {
        echo implecode_warning( __( "You don't have permission to read the exported file.", 'ecommerce-product-catalog' ) );

        return;
    }
    if ( file_exists( $file_path ) ) {
        header( 'Content-Description: File Transfer' );
        header( 'Content-Type: application/octet-stream' );
        header( 'Content-Disposition: attachment; filename=' . basename( $file_path ) );
        header( 'Expires: 0' );
        header( 'Cache-Control: must-revalidate' );
        header( 'Pragma: public' );
        header( 'Content-Length: ' . filesize( $file_path ) );
        readfile( $file_path );
        exit;
    }
}

function ic_simple_export_file_url() {
    return admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=product-settings&submenu=csv&provide_export_csv=1' );
}

function ic_simple_import_template_file_url() {
    return admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=product-settings&submenu=csv&provide_import_sample=1' );
}

function ic_simple_import_file_name() {
    $csv_temp  = ic_simple_csv_temp_folder();
    $file_name = md5( $csv_temp ) . '-import.csv';

    return $csv_temp . '/' . $file_name;
}

function ic_simple_export_file_name() {
    $csv_temp  = ic_simple_csv_temp_folder();
    $file_name = md5( $csv_temp ) . '-export.csv';

    return $csv_temp . '/' . $file_name;
}

function ic_simple_csv_temp_folder() {
    $csv_temp   = wp_upload_dir( null, false );
    $csv_folder = $csv_temp['basedir'] . '/ic-simple-csv';
    if ( ! file_exists( $csv_folder ) && ! is_dir( $csv_folder ) ) {
        mkdir( $csv_folder );
        $htaccess_data = 'Order deny,allow
Deny from all';
        file_put_contents( $csv_folder . '/.htaccess', $htaccess_data );
        $index_data = '<?php
// Silence is golden.';
        file_put_contents( $csv_folder . '/index.php', $index_data );
    }

    return $csv_folder;
}

add_filter( 'simple_csv_separator', 'get_simple_separator' );

/**
 * Defines simple csv separator
 *
 * @return type
 */
function get_simple_separator() {
    if ( function_exists( 'get_currency_settings' ) ) {
        $product_currency_settings = get_currency_settings();
        if ( $product_currency_settings['dec_sep'] == ',' ) {
            $sep = ';';
        } else {
            $sep = ',';
        }
    } else {
        $sep = ',';
    }

    return $sep;
}

if ( ! function_exists( 'get_product_image_id' ) ) {

    function get_product_image_id( $attachment_url = '' ) {
        global $wpdb;
        $attachment_id = false;
        if ( '' == $attachment_url ) {
            return;
        }
        $cache                   = ic_get_global( 'ic_cat_db_image_id_from_url' );
        $oryginal_attachment_url = $attachment_url;
        if ( empty( $cache ) ) {
            $cache = array();
        } else if ( ! empty( $cache[ $oryginal_attachment_url ] ) ) {
            return intval( $cache[ $oryginal_attachment_url ] );
        }
        $upload_dir_paths = wp_upload_dir( null, false );
        if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {
            $attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );
            $attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );
            $attachment_id  = intval( $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) ) );

            $cache[ $oryginal_attachment_url ] = $attachment_id;
            ic_save_global( 'ic_cat_db_image_id_from_url', $cache );
        }

        return $attachment_id;
    }

}

add_filter( 'upload_mimes', 'ic_csv_mime', 99 );

function ic_csv_mime( $mimes ) {
    if ( empty( $mimes['csv'] ) ) {
        $mimes['csv'] = 'text/csv';
    }

    return $mimes;
}

class IC_EPC_import_post_type {

    public function __construct() {
        add_action( 'ic_simple_csv_bottom', array( $this, 'import_output' ) );
        add_action( 'ic_csv_import_end', array( $this, 'import_output' ), 15 );
    }

    public function post_types_dropdown() {
        $post_types = get_post_types( array( 'public' => true ), 'objects' );
        $options    = '';
        $selected   = isset( $_GET['import_post_type'] ) ? strval( $_GET['import_post_type'] ) : '';
        foreach ( $post_types as $post_type ) {
            if ( ! ic_string_contains( $post_type->name, 'al_product' ) && $post_type->name != 'attachment' ) {
                $options .= '<option value="' . $post_type->name . '" ' . selected( $selected, $post_type->name, 0 ) . '>' . $post_type->label . '</option>';
            }
        }
        if ( ! empty( $options ) ) {
            $drop_down = '<select name="import_post_type">' . $options . '</select>';

            return $drop_down;
        }

        return;
    }

    public function import_output() {
        $this->import_initial_html();
        if ( ! empty( $_GET['import_post_type'] ) ) {
            $this->process_import_post_type();
        }
    }

    public function import_initial_html() {
        $post_types_dropdown = $this->post_types_dropdown();
        if ( ! empty( $post_types_dropdown ) ) {
            echo '<h3>' . __( 'Import from other content', 'ecommerce-product-catalog' ) . '</h3>';
            echo '<form>';
            foreach ( $_GET as $key => $value ) {
                echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '">';
            }
            echo $post_types_dropdown . ' <button type="submit" class="button-secondary">' . __( 'Import', 'ecommerce-product-catalog' ) . '</button>';
            echo '</form>';
        }
    }

    public function process_import_post_type() {
        $post_type = $_GET['import_post_type'];
        if ( ! empty( $post_type ) ) {
            $posts   = get_posts( array( 'posts_per_page' => 1000, 'post_type' => $post_type, 'post_parent' => 0 ) );
            $counter = 0;
            foreach ( $posts as $post ) {
                $original_id     = $post->ID;
                $post->ID        = 0;
                $post->post_type = 'al_product';
                $new_id          = wp_insert_post( $post );
                if ( ! is_wp_error( $new_id ) ) {
                    $this->copy_post_meta( $new_id, $original_id );
                    $this->copy_taxonomies( $new_id, $original_id );
                    $counter ++;
                }
            }
            implecode_success( sprintf( __( '%s successfully imported!', 'ecommerce-product-catalog' ), $counter ) );
        }
    }

    public function copy_post_meta( $target_post_id, $origin_post_id ) {
        if ( ! is_int( $target_post_id ) || ! is_int( $origin_post_id ) ) {
            return;
        }
        $post_meta        = get_post_meta( $origin_post_id );
        $restricted_names = $this->meta_import_restricted_names();
        foreach ( $post_meta as $name => $value ) {
            if ( in_array( $name, $restricted_names ) ) {
                continue;
            }
            if ( $name === '_length' ) {
                $name = '_size_length';
            }
            if ( $name === '_width' ) {
                $name = '_size_width';
            }
            if ( $name === '_height' ) {
                $name = '_size_height';
            }
            if ( is_array( $value ) ) {
                foreach ( $value as $val ) {
                    update_post_meta( $target_post_id, $name, $val );
                }
            } else {
                update_post_meta( $target_post_id, $name, $value );
            }
        }
    }

    public function copy_taxonomies( $target_post_id, $origin_post_id ) {
        $taxonomies   = get_object_taxonomies( get_post_type( $origin_post_id ), 'objects' );
        $valid_tax    = array();
        $priority_tax = array();
        foreach ( $taxonomies as $tax_name => $tax ) {
            if ( empty( $tax->publicly_queryable ) || empty( $tax->public ) || empty( $tax->hierarchical ) ) {
                continue;
            }
            $valid_tax[] = $tax_name;
            if ( ic_string_contains( $tax->label, 'cat' ) || ic_string_contains( $tax->label, 'kat' ) ) {
                $priority_tax[] = $tax_name;
            }
        }
        if ( ! empty( $priority_tax ) ) {
            $valid_tax = $priority_tax;
        }
        if ( ! empty( $valid_tax[0] ) ) {
            $origin_tax = $valid_tax[0];
            $terms      = wp_get_object_terms( $origin_post_id, $origin_tax );
            $term       = array();
            foreach ( $terms as $t ) {
                $term_id       = 0;
                $args          = array(
                        'slug'        => $t->slug,
                        'parent'      => $t->parent,
                        'description' => $t->description
                );
                $existing_term = term_exists( $t->name, 'al_product-cat', $args['parent'] );
                if ( empty( $existing_term ) ) {
                    $inserted = wp_insert_term( $t->name, 'al_product-cat', $args );
                    if ( ! is_wp_error( $inserted ) ) {
                        $existing_term = $inserted;
                    }
                }
                if ( ! empty( $existing_term['term_id'] ) ) {
                    $term_id = intval( $existing_term['term_id'] );
                } else if ( is_int( $existing_term ) ) {
                    $term_id = intval( $existing_term );
                }
                if ( ! empty( $term_id ) && function_exists( 'get_term_meta' ) ) {
                    $meta = get_term_meta( $t->term_id );
                    if ( ! empty( $meta['thumbnail_id'] ) ) {
                        if ( ! empty( $meta['thumbnail_id'][0] ) ) {
                            $image_id = $meta['thumbnail_id'][0];
                        } else {
                            $image_id = $meta['thumbnail_id'];
                        }
                        update_term_meta( $term_id, 'thumbnail_id', intval( $image_id ) );
                    }
                    $term[] = $term_id;
                }
            }
            if ( ! empty( $term ) ) {
                wp_set_object_terms( $target_post_id, $term, 'al_product-cat' );
            }
        }
    }

    public function meta_import_restricted_names() {
        return array( '_wp_page_template', '_edit_last', '_edit_lock' );
    }

}

$ic_epc_import_post_types = new IC_EPC_import_post_type;
