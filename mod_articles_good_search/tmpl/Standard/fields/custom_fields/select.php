<?php
/**
 * @package     Articles Good Search
 *
 * @copyright   Copyright (C) 2017 Joomcar extensions. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

$active = JRequest::getVar("field".$field->id, array());
$field_params = json_decode($field->instance->fieldparams);
$values = $field_params->options ? $field_params->options : array();
if($field->instance->type == "integer") {
	$values = Array();
	$range = range($field_params->first, $field_params->last, $field_params->step);
	foreach($range as $val) {
		$tmp = new stdClass;
		$tmp->value = $val;
		$tmp->name = $val;
		$values[] = $tmp;
	}
}
if(!$values) {
	//try to get a values from text (autofill)
	$values = Array();
	$text_values = $helper->getFieldValuesFromText($field->id, "text", $module->id);
	foreach($text_values as $val) {
		$tmp = new stdClass;
		$tmp->value = explode("::", $val)[0];
		$tmp->name = explode("::", $val)[1] != "" ? explode("::", $val)[1] : $val;
		$values[] = $tmp;
	}
}
?>

<div class="gsearch-field-select custom-field">	
	<h3>
		<?php echo JText::_("{$field->instance->label}"); ?>
	</h3>
	<select class="inputbox" name="field<?php echo $field->id; ?>[]">
		<option class="empty" value=""><?php echo JText::_("{$field->instance->label}"); ?></option>
		<?php foreach($values as $val) { ?>
			<option 
				value="<?php echo $val->value; ?>"
				<?php if($active && in_array($val->value, $active)) { ?> 
				selected="selected"
				<?php } ?>
			>
				<?php 
					echo $val->name; 
				?>
			</option>
		<?php } ?>
	</select>
</div>