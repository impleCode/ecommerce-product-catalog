<?php 
/**
 * Manages product functions folder
 *
 * Here all plugin functions folder is defined and managed.
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog/includes/settings
 * @author 		Norbert Dreszer
 */
 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
 
function implecode_settings_radio($option_label, $option_name, $option_value, $elements = array(), $echo = 1) {
	$return = '<tr>';
		$return .= '<td>'. $option_label .':</td>';
		$return .= '<td>';
		foreach ($elements as $key => $element) { 
		$return .= '<input type="radio" class="number_box" name="'. $option_name .'" value="'. $key .'"'. checked( $key, $option_value, 0).'/>'. $element;
		} 
		$return .= '</td>';
	$return .= '</tr>';
return echo_ic_setting($return, $echo);
}

function implecode_settings_dropdown($option_label, $option_name, $option_value, $elements = array(), $echo = 1) {
	$return = '<tr>';
		$return .= '<td>'. $option_label .':</td>';
		$return .= '<td>';
		$return .= '<select name="'.$option_name.'">';
		foreach ($elements as $key => $element) { 
		$return .= '<option value="'.$key.'" '.selected($key,$option_value,0).'>'.$element.'</option>';
		} 
		$return .= '</select>';
		$return .= '</td>';
	$return .= '</tr>';
return echo_ic_setting($return, $echo);
}

function implecode_settings_checkbox($option_label, $option_name, $option_enabled, $echo = 1) {
	$return = '<tr>';
		$return .='<td>'.$option_label.':</td>';
		$return .='<td><input type="checkbox" name="'. $option_name.'" value="1"'. checked( 1, $option_enabled, 0) .'/></td>';
	$return .='</tr>';
return echo_ic_setting($return, $echo);
}

function implecode_settings_text($option_label, $option_name, $option_value, $required = null, $echo = 1, $class = null) { 
if ($required != '') {
	$regired_field = 'required="required"';
	$star = '<span class="star"> *</span>';
}
else {
	$regired_field = '';
	$star = '';
} 
	$return = '<tr>';
		$return .= '<td>'. $option_label.$star .':</td>';
		$return .= '<td><input '. $regired_field .' class="'.$class.'" type="text" name="'. $option_name .'" value="'. $option_value .'" /></td>';
	$return .= '</tr>';
return echo_ic_setting($return, $echo);
}

function implecode_settings_number($option_label, $option_name, $option_value, $unit, $echo = 1, $step = 1) {
	$return = '<tr>';
		$return .= '<td>'. $option_label .':</td>';
		$return .= '<td><input type="number" step="'.$step.'" class="number_box" name="'. $option_name .'" value="'. $option_value .'" />'. $unit .'</td>';
	$return .= '</tr>';
return echo_ic_setting($return, $echo);
}

function implecode_settings_textarea($option_label, $option_name, $option_value, $echo = 1) {
	$return = '<tr>';
		$return .= '<td>'. $option_label .':</td>';
		$return .= '<td><textarea name="'. $option_name .'">'. $option_value .'</textarea></td>';
	$return .= '</tr>';
return echo_ic_setting($return, $echo);
}

function implecode_upload_image($button_value, $option_name, $option_value, $default_image = null, $upload_image_id = 'url', $echo = 1) {
wp_enqueue_media(); 
$option_value = !empty($option_value) ? $option_value : $default_image;
$image_src = $option_value;
if ( $upload_image_id != 'url') {
$upload_image_id = 'id';
$image_src = wp_get_attachment_image_src($option_value);
$image_src = $image_src[0];
} 
$content = '<div class="custom-uploader">';
$content .= '<input hidden="hidden" type="text" id="default" value="'. $default_image .'" />';
$content .= '<input hidden="hidden" type="text" name="'. $option_name.'" id="uploaded_image" value="'. $option_value .'" />';
//if ($image_src != '') {
$content .= '<div class="admin-media-image">';
$content .= '<img class="media-image" name="'. $option_name.'_image" src="'. $image_src.'" width="100%" height="100%" />';
$content .= '</div>';
//}
$content .= '<a href="#" class="button insert-media add_media" name="'. $option_name.'_button" id="button_'. $option_name.'"><span class="wp-media-buttons-icon"></span> '. $button_value.'</a>';
$content .= '<a class="button" id="reset-image-button" name="'. $option_name.'_reset" href="#">'. __('Reset image', 'al-ecommerce-product-catalog').'</a>';
$content .= '</div>
';
$content .= '<script>jQuery(document).ready(function() {
';
$content .= 'jQuery(".add_media[name=\"'. $option_name .'_button\"]").click(function() {
';
$content .= 'wp.media.editor.send.attachment = function(props, attachment) {
';
$content .= 'jQuery("#uploaded_image[name=\"'. $option_name .'\"]").val(attachment.'. $upload_image_id .
');';
$content .= 'jQuery(".media-image[name=\"'. $option_name .'_image\"]").attr("src", attachment.url);
';
$content .= '} 
wp.media.editor.open(this); 
return false; 
}); 
});
';
$content .= 'jQuery("#reset-image-button[name=\"'. $option_name .'_reset\"]").click(function() {
';
$content .= 'jQuery("#uploaded_image[name=\"'. $option_name .'\"]").val("");
';
$content .= 'src = jQuery("#default").val();
';
$content .= 'jQuery(".media-image[name=\"'. $option_name .'_image\"]").attr("src", src);
';
$content .= '}); </script>';
return echo_ic_setting($content, $echo);
} 

function echo_ic_setting($return, $echo = 1) {
if ($echo == 1) {
echo $return;
}
else {
return $return;
}
}

function implecode_warning($text, $echo = 1) {
return echo_ic_setting('<div class="al-box warning">'.$text.'</div>', $echo);
}

function implecode_info($text, $echo = 1, $p = 1) {
$return = '<div class="al-box info">';
if ($p == 1) {
$return .= '<p>'.$text.'</p>';
}
else {
$return .= $text;
}
$return .= '</div>';
return echo_ic_setting($return, $echo);
}

function implecode_success($text, $echo = 1) {
return echo_ic_setting('<div class="al-box success"><p>'.$text.'</p></div>', $echo);
}