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
 
function implecode_settings_radio($option_label, $option_name, $option_value, $elements = array()) { ?>
	<tr>
		<td><?php echo $option_label; ?>:</td>
		<td><?php foreach ($elements as $key => $element) { ?>
		<input type="radio" class="number_box" name="<?php echo $option_name;?>" value="<?php echo $key ?>"<?php checked( $key == $option_value ); ?>/> <?php echo $element;
		} ?></td>
	</tr>
<?php }

function implecode_settings_checkbox($option_label, $option_name, $option_enabled) { ?>
	<tr>
		<td>
			<?php echo $option_label; ?>:
		</td>
		<td>
			<input type="checkbox" name="<?php echo $option_name;?>" value="1"<?php checked( 1, $option_enabled ); ?> />
		</td>
	</tr>
<?php
}

function implecode_settings_text($option_label, $option_name, $option_value) { ?>
	<tr>
		<td><?php echo $option_label; ?>:</td>
		<td><input type="text" name="<?php echo $option_name;?>" value="<?php echo $option_value; ?>" /></td>
	</tr>
<?php }

function implecode_settings_number($option_label, $option_name, $option_value, $unit) { ?>
	<tr>
		<td><?php echo $option_label; ?>:</td>
		<td><input type="number" class="number_box" name="<?php echo $option_name;?>" value="<?php echo $option_value; ?>" /><?php echo $unit; ?></td>
	</tr>
<?php }

function implecode_settings_textarea($option_label, $option_name, $option_value) { ?>
	<tr>
		<td><?php echo $option_label; ?>:</td>
		<td><textarea name="<?php echo $option_name;?>"><?php echo $option_value; ?></textarea></td>
	</tr>
<?php }

function implecode_upload_image($button_value, $option_name, $option_value, $default_image = null) {
wp_enqueue_media(); ?>
<div class="custom-uploader">
	<input hidden="hidden" type="text" id="default" value="<?php echo $default_image; ?>" />
	<input hidden="hidden" type="text" name="<?php echo $option_name; ?>" id="uploaded_image" value="<?php echo $option_value ?>" />
	<div class="admin-media-image">
		<img class="media-image" src="<?php echo $option_value; ?>" width="100%" height="100%" />
	</div>
	<a href="#" class="button insert-media add_media" name="<?php echo $option_name; ?>_button" id="button_<?php echo $option_name; ?>"><span class="wp-media-buttons-icon"></span> <?php echo $button_value; ?></a>
	<a class="button" id="reset-image-button" href="#"><?php _e('Reset image', 'al-ecommerce-product-catalog'); ?></a>
</div>
<script>
jQuery(document).ready(function()
{
jQuery('.add_media').click(function()
{
wp.media.editor.send.attachment = function(props, attachment)
{
jQuery('#uploaded_image').val(attachment.url);
jQuery('.media-image').attr("src", attachment.url);
}

wp.media.editor.open(this);

return false;
});
});

jQuery('#reset-image-button').click(function() {
jQuery('#uploaded_image').val('');
src = jQuery('#default').val();
jQuery('.media-image').attr("src", src);
});
</script>
<?php }