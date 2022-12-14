<?php

/**
 * @package     Articles Good Search
 *
 * @copyright   Copyright (C) 2017 Joomcar extensions. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;

$customSorting = explode("\r\n", $model->module_params->ordering_fields);
$sortingFields = array();
foreach($customSorting as $field) {
	$tmp = new stdClass;
	$aField = explode(":", $field);
	$tmp->id = $aField[1];
	$flt = explode('{', $field, 2);
	if(!empty($flt[1]) && $flt[1] != '') {
		$extra_params = json_decode('{' . $flt[1]);
		$tmp->name = $extra_params->name;
	}
	if(count($aField) == 1) { // generic fields
		$sortingFields[] = $field;
	}
	else { // custom fields
		$sortingFields[] = $tmp;
	}
}
?>

<style>
	.gsearch-sorting { position: relative; }
	.gsearch-sorting select { width: auto !important; min-width: 120px; display: inline-block !important; border: 1px solid #ccc; background-color: #fff; }
	.gsearch-sorting .chzn-container { width: auto !important; min-width: 120px; display: inline-block !important; }
	.gsearch-sorting .chzn-container a { height: auto !important; }
	.ordering.direction.asc { transform: rotate(180deg); margin-top: 5px; }
	.ordering.direction {
		float: right;
		margin: 8px 0 0 6px;
		width: 0px;
		height: 0px;
		display: block;
		border-left: 7px solid transparent;
		border-right: 7px solid transparent;
		border-top: 14px solid #999;
		cursor: pointer;
	}
	.ordering.direction:after {
		content: " ";
		width: 0px;
		height: 0px;
		margin: -13px 0 0 -5px;
		display: block;
		border-left: 5px solid transparent;
		border-right: 5px solid transparent;
		border-top: 12px solid #ccc;
	}
</style>

<select class="inputbox select ordering">
	<option value=""><?php echo JText::_('MOD_AGS_SORTING_SELECT'); ?></option>
	<?php if(count($sortingFields)) { ?>
		<?php foreach($sortingFields as $field) { ?>
			<?php if($field->id) { ?>
				<option value="field<?php echo $field->id; ?>"><?php echo urldecode($field->name); ?></option>
			<?php } else {
				switch($field) {
					case "title" : ?>
						<option value="title"><?php echo JText::_('MOD_AGS_SORTING_TITLE'); ?></option>
				<?php	break;
					case "alias" : ?>
						<option value="alias"><?php echo JText::_('MOD_AGS_SORTING_ALIAS'); ?></option>
				<?php	break;
					case "created" : ?>
						<option value="created"><?php echo JText::_('MOD_AGS_SORTING_DATE'); ?></option>
				<?php	break;
					case "publish_up" : ?>
						<option value="publish_up"><?php echo JText::_('MOD_AGS_SORTING_DATE_PUBLISHING'); ?></option>
				<?php	break;
					case "category" : ?>
						<option value="category"><?php echo JText::_('MOD_AGS_SORTING_CATEGORY'); ?></option>
				<?php	break;
					case "hits" : ?>
						<option value="hits"><?php echo JText::_('MOD_AGS_SORTING_POPULAR'); ?></option>
				<?php	break;
					case "featured" : ?>
						<option value="featured"><?php echo JText::_('MOD_AGS_SORTING_FEATURED'); ?></option>
				<?php	break;
					case "rand" : ?>
						<option value="rand"><?php echo JText::_('MOD_AGS_SORTING_RANDOM'); ?></option>
				<?php	break;
					case "ordering" : ?>
						<option value="ordering"><?php echo JText::_('MOD_AGS_SORTING_ORDERING'); ?></option>
				<?php
				}
			} ?>
		<?php } ?>
	<?php } ?>
</select>
<div class="ordering direction <?php echo (JRequest::getVar("orderto") ? JRequest::getVar("orderto") : $model->module_params->ordering_default_dir); ?>"></div>

<script>
	jQuery(document).ready(function($) {
		var query = window.location.search;
		if(query.indexOf("orderby") != -1) {
			var orderby_active = window.location.search.split("orderby=")[1];
				orderby_active = orderby_active.split("&")[0];
			$("select.ordering").val(orderby_active).trigger("liszt:updated");	
		}
 		
		$("select.ordering").on("change", function() {
			var query = window.location.search;
			if(query.indexOf("orderby") != -1) {
				var current = query.split("orderby=")[1];
				current = current.split("&")[0];
				if($(this).val() == "") {
					query = query.replace("&orderby=" + current, "");
				}
				else {
					query = query.replace("orderby=" + current, "orderby=" + $(this).val());
				}
			}
			else {
				query += "&orderby=" + $(this).val();
			}
			window.location.search = query;
		});
		
		$("div.direction").on("click", function() {
			var query = window.location.search;
			if(query.indexOf("orderto") != -1) {
				var current = query.split("orderto=")[1];
				current = current.split("&")[0];
				if(current == "asc") {
					query = query.replace("orderto=" + current, "orderto=desc");
				}
				else {
					query = query.replace("orderto=" + current, "orderto=asc");
				}
			}
			else {
				query += "&orderto=asc";
			}
			window.location.search = query;
		});
	});
</script>