<?php 
/**
 * @package     Articles Good Search
 *
 * @copyright   Copyright (C) 2017 Joomcar extensions. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_SITE . "/plugins/system/plg_articles_good_search/models/com_content/model.php");

JHtml::_('bootstrap.framework');
$document = JFactory::getDocument();

$path = JModuleHelper::getLayoutPath('mod_articles_good_search', $params->get('module_template', $params->get('module_template', 'Default') . '') . '/template');
if(strpos($path, "modules/") !== false) {
	$path = explode("modules/", $path);
	$path = explode("/template.php", $path[1]);
	$path = JURI::root(true) . '/modules/' . $path[0] . '/assets/filter.css';
}
if(strpos($path, "templates/") !== false) {
	$path = explode("templates/", $path);
	$path = explode("/template.php", $path[1]);
	$path = JURI::root(true) . '/templates/' . $path[0] . '/assets/filter.css';
}
$document->addStylesheet($path);
$document->addStyleSheet(JURI::root(true) . '/media/jui/css/icomoon.css');

$cols = $params->get('filters_cols');
if($cols == 'auto') unset($cols); //responsive view

$date_format = explode("::", $params->get("date_format", "%e %b %Y::d M yyyy"))[0];
$date_format_text = explode("::", $params->get("date_format"))[1];
$lang = JFactory::getLanguage()->getTag();
$lang = explode("-", $lang)[0];
$curr_locale = JFactory::getLanguage()->getLocale();
setlocale(LC_ALL, $curr_locale);

?>

<style>
	.filter_loading<?php echo $module->id; ?> {
		display: none;
		z-index: 10000;
		position: fixed;
		top: 0px;
		left: 0px;
		width: 100%;
		height: 100%;
		text-align: center;
		padding-top: 18%;
	}
	.filter_loading<?php echo $module->id; ?> img { max-width: 130px; margin: 0 auto; }
</style>

<script type="text/javascript">
	<?php require(JModuleHelper::getLayoutPath('mod_articles_good_search', $params->get('module_template', 'Default') . '/elements/basic_scripts')); ?>
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.12/css/bootstrap-select.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.12/js/bootstrap-select.min.js"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" />	
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<?php if($lang && $lang != 'en') { 
	if($lang == "zh") {
		$lang = "zh-CN";
	}	
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.<?php echo $lang; ?>.min.js"></script>
<?php } ?>

<script type="text/javascript">
	jQuery(document).ready(function($) {
		$("#GSearch<?php echo $module->id; ?> select").each(function() {
			if($(this).attr("multiple") == "multiple") {
				$(this).find("option:eq(0)").attr("disabled", "disabled");
			}
			$(this).selectpicker({
				//actionsBox: true,
				//selectAllText: "Select All",
				//deselectAllText: "Clear",
				title: $(this).find("option:eq(0)").text(),
				liveSearch: "true",
				iconBase: "icon",
				tickIcon: "icon-checkmark",
			});
		});
		$("#GSearch<?php echo $module->id; ?>").find(".datepicker").each(function(k, el) {
			$(el).datepicker({
				autoclose: true,
				format: '<?php echo $date_format_text; ?>',
				<?php if($lang && $lang != 'en') { ?>
				language: "<?php echo $lang; ?>",
				<?php } ?>
			}).on('changeDate', function(e) {
				prepareDates<?php echo $module->id; ?>(el);
			});
			setTimeout(function() {
				$(el).datepicker('show');
				$(el).datepicker('hide'); // fix single number empty space issue;
			}, 200);
		});
	});
	function prepareDates<?php echo $module->id; ?>(el) {
		$ = jQuery.noConflict();
		el = $(el);
		var date = el.datepicker('getDate');
		var date_val = '';
		if(date) {
			date_val = date.getFullYear() + '-' + ('0' + (date.getMonth()+1)).slice(-2) + '-' + ('0' + date.getDate()).slice(-2);
		}
		if(el.hasClass("single")) {
			var el_val = el.parent().find('input[type=hidden]');
		}
		if(el.hasClass("from")) {
			var el_val = el.parent().find('input[type=hidden].from');
			if(el.datepicker('getDate') > el.parent().find(".datepicker.to").datepicker('getDate')) {
				el.parent().find(".datepicker.to").datepicker('setStartDate', el.datepicker('getDate')); // adjust date-to select
			}
		}
		if(el.hasClass("to")) {
			var el_val = el.parent().find('input[type=hidden].to');
		}
		el_val.val(date_val);
	}
</script>

<div id="GSearch<?php echo $module->id; ?>" class="GSearchBlock <?php echo $params->get('moduleclass_sfx'); ?>">

	<?php if($params->get('descr') != "") : ?>
	<p><?php echo $params->get('descr'); ?></p>
	<?php endif; ?>
	
	<form action="#gsearch-results" name="GSearch<?php echo $module->id; ?>" method="get">		
	  <div class="gsearch-table<?php if($cols) echo " columns-{$cols}"; ?>">

<?php for($filters_counter = 0; $filters_counter < count($filters); $filters_counter++) { 
		$field = $filters[$filters_counter];
?>
		<div class="gsearch-cell gsearch-cell<?php echo $filters_counter; ?>"<?php if($cols) echo ' style="width: '. str_replace(",", ".", (100/$cols - 2)) .'%;"'?>>
		
		<?php	
			switch($field->type) {				
				case 'keyword' :			
					require(JModuleHelper::getLayoutPath('mod_articles_good_search', $params->get('module_template', 'Default') . '/fields/basic/keyword'));
				break;	
				
				case 'title_select' :			
					require(JModuleHelper::getLayoutPath('mod_articles_good_search', $params->get('module_template', 'Default') . '/fields/basic/title_select'));
				break;		
				
				case 'tag' :
					$tags = (array)$helper->getTags($params);
					require(JModuleHelper::getLayoutPath('mod_articles_good_search', $params->get('module_template', 'Default') . '/fields/basic/tag'));
				break;
				
				case 'tag_cloud' :
					$tags = (array)$helper->getTags($params);
					require(JModuleHelper::getLayoutPath('mod_articles_good_search', $params->get('module_template', 'Default') . '/fields/basic/tag_cloud'));
				break;
				
				case 'category' :
					require(JModuleHelper::getLayoutPath('mod_articles_good_search', $params->get('module_template', 'Default') . '/fields/basic/category'));
				break;			
				
				case 'author' :	
					$authors = (array)$helper->getAuthors($params);
					require(JModuleHelper::getLayoutPath('mod_articles_good_search', $params->get('module_template', 'Default') . '/fields/basic/author'));
				break;
				
				case 'date' :
					require(JModuleHelper::getLayoutPath('mod_articles_good_search', $params->get('module_template', 'Default') . '/fields/basic/date'));
				break;
				
				//custom fields
				case 'text' :
				case 'textarea' :
				case 'url' :
				case 'editor' :
					require(JModuleHelper::getLayoutPath('mod_articles_good_search', $params->get('module_template', 'Default') . '/fields/custom_fields/text'));
				break;
				
				case 'text_range' :
					require(JModuleHelper::getLayoutPath('mod_articles_good_search', $params->get('module_template', 'Default') . '/fields/custom_fields/text_range'));
				break;
				
				case 'select' :
					require(JModuleHelper::getLayoutPath('mod_articles_good_search', $params->get('module_template', 'Default') . '/fields/custom_fields/select'));
				break;
				
				case 'list' :
				case 'multi' :
					require(JModuleHelper::getLayoutPath('mod_articles_good_search', $params->get('module_template', 'Default') . '/fields/custom_fields/multi'));
				break;
				
				case 'checkboxes' :
					require(JModuleHelper::getLayoutPath('mod_articles_good_search', $params->get('module_template', 'Default') . '/fields/custom_fields/checkboxes'));
				break;
				
				case 'radio' :
					require(JModuleHelper::getLayoutPath('mod_articles_good_search', $params->get('module_template', 'Default') . '/fields/custom_fields/radio'));
				break;
				
				case 'integer' :
				case 'slider-range' :
					require(JModuleHelper::getLayoutPath('mod_articles_good_search', $params->get('module_template', 'Default') . '/fields/custom_fields/slider'));
				break;
				
				case 'calendar' :
					require(JModuleHelper::getLayoutPath('mod_articles_good_search', $params->get('module_template', 'Default') . '/fields/custom_fields/calendar'));
				break;
				
				case 'calendar_range' :
					require(JModuleHelper::getLayoutPath('mod_articles_good_search', $params->get('module_template', 'Default') . '/fields/custom_fields/calendar_range'));
				break;
				
				case 'custom_select' :
					require(JModuleHelper::getLayoutPath('mod_articles_good_search', $params->get('module_template', 'Default') . '/fields/custom_fields/custom_select'));
				break;
				
				//added for compatibility with radical multifield
				case 'radicalmultifield' : 
						$extra_params = json_decode($field->extra_params);
						$sub_field = $extra_params->selected;
						if(!$sub_field) {
							echo "Select radicalmultifield in the module parameters";
							continue;
						}
						$field_type = $extra_params->type ? $extra_params->type : "text";
						require(JModuleHelper::getLayoutPath('mod_articles_good_search', $params->get('module_template', 'Default') . '/fields/custom_fields/radicalmultifield/'.$field_type));
				break;
				
				//added for compatibility with repeatable field
				case 'repeatable' : 
						$extra_params = json_decode($field->extra_params);
						$sub_field = $extra_params->selected;
						if(!$sub_field) {
							echo "Select repeatable field in the module parameters";
							continue;
						}
						$field_type = $extra_params->type ? $extra_params->type : "text";
						require(JModuleHelper::getLayoutPath('mod_articles_good_search', $params->get('module_template', 'Default') . '/fields/custom_fields/repeatable/'.$field_type));
				break;
				
			}
		?>
		</div>
		<?php
		if($cols && ($filters_counter + 1) % $cols == 0 && ($filters_counter + 1) != count($filters)) {
			echo '<div class="clear" style="clear: both;"></div>';
		}
	}
		?>
		
			<div class="gsearch-buttons">
				<input type="submit" value="<?php echo JText::_('MOD_AGS_BUTTON_SEARCH_TEXT'); ?>" class="btn btn-primary button submit <?php echo $moduleclass_sfx; ?>" />	
				<?php
				if($params->get("clear_btn_show", 1)) {
					require(JModuleHelper::getLayoutPath('mod_articles_good_search', $params->get('module_template', 'Default') . '/elements/clear_btn')); 
				}
				?>
			</div>
		
		   <div class="clear" style="clear: both;"></div>
		</div><!--//gsearch-table-->
	
		<input type="hidden" name="gsearch" value="1" />
		<input type="hidden" name="moduleId" value="<?php echo $module->id; ?>" />
		<?php if($params->get("Itemid")) { ?>
		<input type="hidden" name="Itemid" value="<?php echo $params->get("Itemid"); ?>" />
		<?php } ?>		
		
		<input type="hidden" name="orderby" value="<?php echo JRequest::getVar("orderby"); ?>" />
		<input type="hidden" name="orderto" value="<?php echo JRequest::getVar("orderto"); ?>" />

		<?php if((JRequest::getVar("option") == "com_j2store"
					&& JRequest::getVar("view") == "producttags"
					&& JRequest::getVar("tag") != "")
					|| JRequest::getVar("j2store_tag")
			) {
			//j2store automatically restriction by tag
			$tagId = JFactory::getDBO()->setQuery("SELECT id FROM #__tags WHERE alias = '". JRequest::getVar("tag") ."'")->loadResult();
		?>
		<input type="hidden" name="j2store_tag" value="<?php echo JRequest::getVar("j2store_tag", $tagId); ?>" /> 
		<?php
			}
		?>

		<?php if($params->get("filter_match") == "All") { ?>
		<input type="hidden" name="match" value="all" />
		<?php } ?>
		
		<div style="clear:both;"></div>
	</form>
	
	<?php if($params->get("acounter")) { ?>
		<?php require(JModuleHelper::getLayoutPath('mod_articles_good_search', $params->get('module_template', 'Default') . '/elements/acounter')); ?>
	<?php } ?>
	
	<?php if($params->get("dynobox")) { ?>
		<?php require(JModuleHelper::getLayoutPath('mod_articles_good_search', $params->get('module_template', 'Default') . '/elements/dynobox')); ?>
	<?php } ?>

	<?php if($params->get("field_connection") != "" && 
			 $params->get("field_connection") != "FieldLabel->FieldLabel2->FieldLabel3") { ?>
		<?php require(JModuleHelper::getLayoutPath('mod_articles_good_search', $params->get('module_template', 'Default') . '/elements/connected_single')); ?>
	<?php } ?>

	<?php if($params->get("search_history")) { ?>
		<?php require(JModuleHelper::getLayoutPath('mod_articles_good_search', $params->get('module_template', 'Default') . '/elements/search_history')); ?>
	<?php } ?>
	
	<?php if($params->get("search_stats")) { ?>
		<?php require(JModuleHelper::getLayoutPath('mod_articles_good_search', $params->get('module_template', 'Default') . '/elements/search_stats')); ?>
	<?php } ?>
	
	<?php if($params->get("search_type") == "ajax") { ?>
		<?php require(JModuleHelper::getLayoutPath('mod_articles_good_search', $params->get('module_template', 'Default') . '/elements/ajax_search')); ?>
	<?php } ?>
</div><!--//gsearch-box-->