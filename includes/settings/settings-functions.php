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

function implecode_settings_number($option_label, $option_name, $option_value, $unit, $echo = 1) {
	$return = '<tr>';
		$return .= '<td>'. $option_label .':</td>';
		$return .= '<td><input type="number" class="number_box" name="'. $option_name .'" value="'. $option_value .'" />'. $unit .'</td>';
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

function implecode_upload_image($button_value, $option_name, $option_value, $default_image = null) {
wp_enqueue_media(); ?>
<div class="custom-uploader">
	<input hidden="hidden" type="text" id="default" value="<?php echo $default_image; ?>" />
	<input hidden="hidden" type="text" name="<?php echo $option_name; ?>" id="uploaded_image" value="<?php echo $option_value ?>" />
	<div class="admin-media-image">
		<img class="media-image" name="<?php echo $option_name; ?>_image" src="<?php echo $option_value; ?>" width="100%" height="100%" />
	</div>
	<a href="#" class="button insert-media add_media" name="<?php echo $option_name; ?>_button" id="button_<?php echo $option_name; ?>"><span class="wp-media-buttons-icon"></span> <?php echo $button_value; ?></a>
	<a class="button" id="reset-image-button" name="<?php echo $option_name; ?>_reset" href="#"><?php _e('Reset image', 'al-ecommerce-product-catalog'); ?></a>
</div>
<script>
jQuery(document).ready(function()
{
jQuery('.add_media[name="<?php echo $option_name ?>_button"]').click(function()
{
wp.media.editor.send.attachment = function(props, attachment)
{
jQuery('#uploaded_image[name="<?php echo $option_name ?>"]').val(attachment.url);
jQuery('.media-image[name="<?php echo $option_name ?>_image"]').attr("src", attachment.url);
}

wp.media.editor.open(this);

return false;
});
});

jQuery('#reset-image-button[name="<?php echo $option_name ?>_reset"]').click(function() {
jQuery('#uploaded_image[name="<?php echo $option_name ?>"]').val('');
src = jQuery('#default').val();
jQuery('.media-image[name="<?php echo $option_name ?>_image"]').attr("src", src);
});
</script>
<?php }

function echo_ic_setting($return, $echo = 1) {
if ($echo == 1) {
echo $return;
}
else {
return $return;
}
}