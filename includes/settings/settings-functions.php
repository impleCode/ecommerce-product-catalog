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