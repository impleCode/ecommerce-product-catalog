<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages support settings
 *
 * Here support settings are defined and managed.
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog/includes
 * @author 		Norbert Dreszer
 */
function implecode_custom_csv_menu() {
	?>
	<a id="csv-settings" class="element" href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=product-settings&submenu=csv' ) ?>"><?php _e( 'Simple CSV', 'ecommerce-product-catalog' ); ?></a>
	<?php
}

add_action( 'general_submenu', 'implecode_custom_csv_menu' );

function implecode_custom_csv_settings_content() {
	?>
	<?php $submenu = isset( $_GET[ 'submenu' ] ) ? $_GET[ 'submenu' ] : ''; ?>
	<?php if ( $submenu == 'csv' ) { ?>
		<div class="setting-content submenu csv-tab">
			<script>
		        jQuery( '.settings-submenu a' ).removeClass( 'current' );
		        jQuery( '.settings-submenu a#csv-settings' ).addClass( 'current' );
			</script>
			<h2><?php
				_e( 'Simple CSV', 'ecommerce-product-catalog' );
				?>
			</h2>
			<h3><?php _e( 'Simple Export', 'ecommerce-product-catalog' ); ?></h3>
			<?php
			$export = isset( $_GET[ 'export_csv' ] ) ? $_GET[ 'export_csv' ] : '';
			if ( $export == 1 ) {
				$url = simple_export_to_csv();
				echo '<a style="display: block; margin-top: 20px;" href="' . $url . '">' . __( "Download CSV", 'ecommerce-product-catalog' ) . '</a>';
			} else {
				?>
				<a style="display: block; margin-top: 20px;" href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=product-settings&submenu=csv&export_csv=1' ) ?>"><button class="button" ><?php _e( "Export all items to CSV file", 'ecommerce-product-catalog' ) ?></button></a>
				<h3><?php _e( 'Simple Import', 'ecommerce-product-catalog' ); ?></h3><?php simple_upload_csv_products_file(); ?>
			<?php } ?>
		</div>
		<div class="helpers"><div class="wrapper"><?php
				main_helper();
				doc_helper( __( 'import', 'ecommerce-product-catalog' ), 'product-import' );
				?>
			</div></div><?php
	}
}

add_action( 'product-settings', 'implecode_custom_csv_settings_content' );

function simple_upload_csv_products_file() {
	$upload_feedback = '';
	if ( isset( $_FILES[ 'product_csv' ] ) && ($_FILES[ 'product_csv' ][ 'size' ] > 0) ) {
		$arr_file_type		 = wp_check_filetype( basename( $_FILES[ 'product_csv' ][ 'name' ] ) );
		$uploaded_file_type	 = $arr_file_type[ 'ext' ];
		$allowed_file_type	 = 'csv';
		if ( $uploaded_file_type == $allowed_file_type ) {
			$wp_uploads_dir	 = wp_upload_dir();
			$filepath		 = $wp_uploads_dir[ 'basedir' ] . '/simple-products.csv';
			if ( move_uploaded_file( $_FILES[ 'product_csv' ][ 'tmp_name' ], $filepath ) ) {
				simple_import_product_from_csv();
			} else {
				$upload_feedback = '<div class="al-box warning">' . __( 'There was a problem with your upload.', 'ecommerce-product-catalog' ) . '</div>';
			}
		} else {
			$upload_feedback = '<div class="al-box warning">' . __( 'Please upload only CSV files.', 'ecommerce-product-catalog' ) . '</div>';
		}
		echo $upload_feedback;
	} else {
		$url = sample_import_file_url();
		echo '<form method="POST" enctype="multipart/form-data"><input type="file" accept=".csv" name="product_csv" id="product_csv" /><input type="submit" class="button" value="' . __( 'Import Now', 'ecommerce-product-catalog' ) . '" /></form>';
		$sep = get_simple_separator();
		if ( $sep === ';' ) {
			$sep_label = __( 'Semicolon', 'ecommerce-product-catalog' );
		} else {
			$sep_label = __( 'Comma', 'ecommerce-product-catalog' );
		}
		echo '<div class="al-box info"><p>' . __( "The CSV fields should be in following order: Image URL, Name, Price, Categories, Short Description, Long Description.", "ecommerce-product-catalog" ) . '</p><p>' . sprintf( __( "The first row should contain the field names. %s should be used as the CSV separator.", "ecommerce-product-catalog" ), $sep_label ) . '</p><a href="' . $url . '" class="button-primary">' . __( 'Download CSV Template', 'ecommerce-product-catalog' ) . '</a></div>';
	}
}

function simple_import_product_from_csv() {
	$fp		 = simple_prepare_csv_file( 'r' );
	$product = array();
	if ( $fp !== false ) {
		$sep		 = apply_filters( 'simple_csv_separator', ';' );
		$csv_cols	 = fgetcsv( $fp, 0, $sep, '"' );
		if ( isset( $csv_cols[ 0 ] ) && $csv_cols[ 0 ] == '﻿sep=' ) {
			$csv_cols = fgetcsv( $fp, 0, $sep, '"' );
		}
		$import_array = simple_prepare_csv_import_array();
		if ( count( $csv_cols ) == count( $import_array ) ) {
			$i		 = 0;
			$error	 = 0;
			while ( ($data	 = fgetcsv( $fp, 0, $sep, '"' )) !== FALSE ) {
				$filtered_data = array_filter( $data );
				if ( empty( $data ) || !is_array( $data ) || (is_array( $data ) && empty( $filtered_data ) ) || count( $data ) == 1 ) {
					continue;
				}
				foreach ( $data as $key => $val ) {
					unset( $data[ $key ] );
					$new_key			 = $import_array[ $key ];
					$data[ $new_key ]	 = $val;
				}
				$product_id = simple_insert_csv_product( $data );
				if ( !empty( $product_id ) && !is_wp_error( $product_id ) ) {
					$i++;
				} else {
					$error++;
				}
			}
			$result = 'success';
			if ( !empty( $error ) ) {
				$result = 'warning';
			}
			echo '<div class="al-box ' . $result . '">';
			echo '<p>' . sprintf( __( '%s products successfully added to the catalog', 'ecommerce-product-catalog' ), $i ) . '.<p>';
			if ( !empty( $error ) ) {
				echo '<p>' . sprintf( __( '%s failures occurred. Please check if the file is UTF-8 encoded', 'ecommerce-product-catalog' ), $error ) . '.</p>';
			}
			echo '</div>';
		} else {
			//echo '<div class="al-box warning">';
			//_e( 'Number of fields in database and number of fields in CSV file do not match!', 'ecommerce-product-catalog' );
			$included		 = str_replace( array( 'Array', '(', ')', ']', '[' ), array( '', '', '', '', '<br>' ), print_r( $csv_cols, true ) );
			$export_array	 = prepare_sample_import_file();
			$expected		 = str_replace( array( 'Array', '(', ')', ']', '[' ), array( '', '', '', '', '<br>' ), print_r( array_values( $export_array[ 1 ] ), true ) );
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

function simple_prepare_csv_file( $type = 'w' ) {
	$csv_temp	 = wp_upload_dir();
	ini_set( 'auto_detect_line_endings', true );
	$fp			 = fopen( $csv_temp[ 'basedir' ] . '/simple-products.csv', $type ) or die( implecode_warning( sprintf( __( 'Permission error. Please check WordPress uploads %sfolder permissions%s.', 'ecommerce-product-catalog' ), '<a href="https://codex.wordpress.org/Changing_File_Permissions">', '</a>' ), 0 ) );
	return $fp;
}

function simple_prepare_csv_import_array() {
	$arr = array( 'image_url', 'product_name', 'product_price', 'product_categories', 'product_short_desc', 'product_desc' );
	return $arr;
}

function simple_insert_csv_product( $data ) {
	$short_description	 = wp_kses_post( $data[ 'product_short_desc' ] );
	$long_description	 = wp_kses_post( $data[ 'product_desc' ] );
	$post				 = array(
		'ID'			 => '',
		'post_title'	 => $data[ 'product_name' ],
		'post_status'	 => 'publish',
		'post_type'		 => 'al_product',
		'post_excerpt'	 => $short_description,
		'post_content'	 => $long_description
	);
	$id					 = wp_insert_post( $post );
	if ( !is_wp_error( $id ) && !empty( $id ) ) {
		update_post_meta( $id, '_price', $data[ 'product_price' ] );
		update_post_meta( $id, 'excerpt', $short_description );
		update_post_meta( $id, 'content', $long_description );
		$image_url = get_product_image_id( $data[ 'image_url' ] );
		set_post_thumbnail( $id, $image_url );
		wp_set_object_terms( $id, $data[ 'product_categories' ], 'al_product-cat' );
		set_time_limit( 30 );
	}
	return $id;
}

function prepare_sample_import_file() {
	$fields								 = array();
	$fields[ 1 ][ 'image_url' ]			 = __( 'Image URL', 'ecommerce-product-catalog' );
	$fields[ 1 ][ 'product_name' ]		 = __( 'Name', 'ecommerce-product-catalog' );
	$fields[ 1 ][ 'product_price' ]		 = __( 'Price', 'ecommerce-product-catalog' );
	$fields[ 1 ][ 'product_categories' ] = __( 'Categories', 'ecommerce-product-catalog' );
	$fields[ 1 ][ 'product_short_desc' ] = __( 'Short Description', 'ecommerce-product-catalog' );
	$fields[ 1 ][ 'product_desc' ]		 = __( 'Long Description', 'ecommerce-product-catalog' );
	return array_filter( $fields );
}

function sample_import_file_url() {
	$fp		 = simple_prepare_csv_file();
	$fields	 = prepare_sample_import_file();
	fprintf( $fp, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );
	$sep	 = apply_filters( 'simple_csv_separator', ';' );
	foreach ( $fields as $field ) {
		fputcsv( $fp, $field, $sep, '"' );
	}
	simple_close_csv_file( $fp );
	$csv_temp = wp_upload_dir();
	return $csv_temp[ 'baseurl' ] . '/simple-products.csv';
}

function simple_close_csv_file( $fp ) {
	fclose( $fp );
	ini_set( 'auto_detect_line_endings', false );
}

function simple_get_all_exported_products() {
	$args		 = array(
		'posts_per_page'	 => 1000,
		'orderby'			 => 'title',
		'order'				 => 'ASC',
		'post_type'			 => 'al_product',
		'post_status'		 => 'publish',
		'suppress_filters'	 => true );
	$products	 = get_posts( $args );
	return $products;
}

function simple_prepare_products_to_export() {
	$products							 = simple_get_all_exported_products();
	$fields								 = array();
	$fields[ 1 ][ 'image_url' ]			 = __( 'Image URL', 'ecommerce-product-catalog' );
	$fields[ 1 ][ 'product_name' ]		 = __( 'Name', 'ecommerce-product-catalog' );
	$fields[ 1 ][ 'product_price' ]		 = __( 'Price', 'ecommerce-product-catalog' );
	$fields[ 1 ][ 'product_categories' ] = __( 'Categories', 'ecommerce-product-catalog' );
	$fields[ 1 ][ 'product_short_desc' ] = __( 'Short Description', 'ecommerce-product-catalog' );
	$fields[ 1 ][ 'product_desc' ]		 = __( 'Long Description', 'ecommerce-product-catalog' );
	$z									 = 2;
	foreach ( $products as $product ) {
		$image							 = wp_get_attachment_image_src( get_post_thumbnail_id( $product->ID ), 'full' );
		$desc							 = get_product_description( $product->ID );
		$short_desc						 = get_product_short_description( $product->ID );
		$fields[ $z ][ 'image_url' ]	 = $image[ 0 ];
		$fields[ $z ][ 'product_name' ]	 = $product->post_title;
		$fields[ $z ][ 'product_price' ] = get_post_meta( $product->ID, '_price', true );
		$category_array					 = get_the_terms( $product->ID, 'al_product-cat' );
		$category						 = array();
		if ( !empty( $category_array ) ) {
			foreach ( $category_array as $p_cat ) {
				$value		 = html_entity_decode( $p_cat->name );
				$category[]	 = $value;
			}
		}
		$fields[ $z ][ 'product_categories' ]	 = implode( ' | ', $category );
		$fields[ $z ][ 'product_short_desc' ]	 = $short_desc;
		$fields[ $z ][ 'product_desc' ]			 = $desc;
		$z++;
	}
	return array_filter( $fields );
}

function simple_export_to_csv() {
	$fp		 = simple_prepare_csv_file();
	$fields	 = simple_prepare_products_to_export();
	fprintf( $fp, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );
	$sep	 = apply_filters( 'simple_csv_separator', ';' );
	fwrite( $fp, "sep=" . $sep . "\n" );
	foreach ( $fields as $field ) {
		fputcsv( $fp, $field, $sep, '"' );
	}
	simple_close_csv_file( $fp );
	$csv_temp = wp_upload_dir();
	return $csv_temp[ 'baseurl' ] . '/simple-products.csv';
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
		if ( $product_currency_settings[ 'dec_sep' ] == ',' ) {
			$sep = ';';
		} else {
			$sep = ',';
		}
	} else {
		$sep = ',';
	}
	return $sep;
}
