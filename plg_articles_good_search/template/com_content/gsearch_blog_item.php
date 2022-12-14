<?php

/**
 * @package     Articles Good Search
 *
 * @copyright   Copyright (C) 2017 Joomcar extensions. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;

if(class_exists('FieldsHelper')) {
	$fields = FieldsHelper::getFields('com_content.article', $item, true);
}
else {
	$fields = array();
}
$tmp = new stdClass;
foreach($fields as $field) {
	$name = $field->name;
	$tmp->{$name} = $field;
}
$fields = $tmp;
//you can call some field with $fields->{"name"}->title and $fields->{"name"}->value
//e.g.
//echo $fields->{"test1"}->title . ' - ' .  $fields->{"test1"}->value;

$image_type = $model->module_params->image_type;
$images = json_decode($item->images);			
$ImageIntro = strlen($images->image_intro) > 1 ? 1 : 0;
preg_match('/(<img[^>]+>)/i', $item->introtext, $matches);
$ImageInText = count($matches);

if (JPluginHelper::isEnabled('system', 'imagestab')) {
	$db = JFactory::getDBO();
	$db->setQuery("SELECT COUNT(*) FROM #__content_images_data WHERE article_id = {$item->id}");
	$res = $db->loadResult();
	$ImagesTab = (int)$res;
}

if ($image_type == "intro" || $ImagesTab) {
	$item->introtext = trim(strip_tags($item->introtext, '<h2><h3><a><b>'));
}
if($model->module_params->text_limit) {
	preg_match('/(<img[^>]+>)/i', $item->introtext, $images_text);	
	$item->introtext = trim(strip_tags($item->introtext, '<h2><h3><a><b>'));
	if(extension_loaded('mbstring')) {
		$item->introtext = mb_strimwidth($item->introtext, 0, $model->module_params->text_limit, '...', 'utf-8');
	}
	else {
		$item->introtext = strlen($item->introtext) > $model->module_params->text_limit ? substr($item->introtext, 0, $model->module_params->text_limit) . '...' : $item->introtext;
	}
	if(count($images_text) && 
		($image_type == "text" || ($image_type == "" && !$ImageIntro))
	) {
		if(strpos($images_text[0], '://') === false) {
			$parts = explode('src="', $images_text[0]);
			$images_text[0] = $parts[0] . 'src="' . JURI::root() . $parts[1];
		}
		$item->introtext = $images_text[0] . $item->introtext;
	}
}
$model->execPlugins($item);

// Fix local images src
preg_match_all("/[^'\"\s(]*?(\.jpg|\.jpeg|\.png)/smix", $item->introtext, $matches);
$links = array();
foreach($matches[0] as $link) {
	if(strpos($link, "//") !== false) continue; //external image
	if($link[0] != '/') { // wrong relative link
		$item->introtext = str_replace($link, '/' . $link, $item->introtext);
	}
}

?>

<div class="item<?php echo $item->featured ? ' featured' : ''; ?>" itemprop="blogPost" itemscope itemtype="https://schema.org/BlogPosting">
	<h4 itemprop="name" class="item-title">
		<a href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language)); ?>" itemprop="url">
			<?php echo $item->title; ?>
		</a>
	</h4>
	<?php echo $item->event->afterDisplayTitle; ?>
	<?php echo $item->event->beforeDisplayContent; ?>

	<?php if ($ImageIntro && !$ImagesTab && ($image_type == "intro" || $image_type == "")) { ?>
	<div class="item-image">
		<a href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language)); ?>">
			<img src="<?php echo JURI::root() . htmlspecialchars($images->image_intro, ENT_COMPAT, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($images->image_intro_alt, ENT_COMPAT, 'UTF-8'); ?>" itemprop="thumbnailUrl"/>
		</a>
	</div>
	<?php } ?>
	
	<?php 
	$image_empty = $model->module_params->image_empty;
	if(((!$ImageIntro && $image_type == "intro") || (!$ImageInText && $image_type == "text") || (!$ImageIntro && !$ImageInText && $image_type == "")) && $image_empty != "" && $image_empty != "-1" && !$ImagesTab) { ?>
	<div class="item-image image-empty">
		<a href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language)); ?>">
			<img src="<?php echo JURI::root(); ?>images/<?php echo $image_empty; ?>" itemprop="thumbnailUrl"/>
		</a>
	</div>
	<?php } ?>
	
	<?php if($model->module_params->show_introtext) { ?>
	<div class="item-body">
		<div class="introtext">
			<?php echo $item->introtext; ?>
		</div>
		<div style="clear: both;"></div>
	</div>
	<?php } ?>
	
	<?php if($model->module_params->show_readmore) { ?>
	<div class="item-readmore">
		<a class="btn btn-secondary" href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language)); ?>"><?php echo JText::_('MOD_AGS_ITEM_READMORE'); ?></a>
	</div>
	<?php } ?>
	
	<?php if($model->module_params->show_info) { ?>
	<div class="item-info">
		<ul>
			<li class="createdby hasTooltip" itemprop="author" itemscope="" itemtype="http://schema.org/Person" title="" data-original-title="Written by">
				<i class="icon icon-user"></i>
				<span itemprop="name">
					<?php
						if($item->created_by_alias != "") {
							echo $item->created_by_alias;
						} 
						else {
							echo $model->getAuthorById($item->created_by)->name;
						}
					?>
				</span>
			</li>
			<li class="category-name hasTooltip" title="" data-original-title="Category">
				<i class="icon icon-folder"></i>
				<?php foreach($model->getItemCategories($item) as $category) { ?>
				<a href="<?php echo $category->link; ?>">
					<span itemprop="genre">
						<?php echo $category->title; ?>
					</span>
				</a>				
				<?php } ?>
			</li>
			<?php
			if($item->tags != "") {
				$item->tags = new JHelperTags;
				$item->tags->getItemTags('com_content.article', $item->id);
			?>
			<li class="tags hasTooltip" title="" data-original-title="Tags">
				<i class="icon icon-tags"></i>
				<div style="display: inline-block;">
				<?php echo JLayoutHelper::render('joomla.content.tags', $item->tags->itemTags); ?>
				</div>
			</li>
			<?php } ?>
			<li class="created">
				<i class="icon icon-clock"></i>
				<time datetime="<?php echo $item->created; ?>" itemprop="dateCreated">
					<?php echo JText::_('MOD_AGS_ITEM_CREATED'); ?> 
					<?php 
						setlocale(LC_ALL, JFactory::getLanguage()->getLocale());
						$date_format = explode("::", $model->module_params_native->get('date_format', '%e %b %Y::d M yyyy'))[0];
						if(strpos(PHP_OS, 'WIN') !== false) {
							$date_format = str_replace("%e", "%#d", $date_format);
						}
						$date = strftime($date_format, strtotime($item->created));
						if(function_exists("mb_convert_case")) {
							$date = mb_convert_case($date, MB_CASE_TITLE, 'UTF-8');
						}
						echo $date;
					?>		
				</time>
			</li>
			<li class="hits">
					<i class="icon icon-eye"></i>
					<meta itemprop="interactionCount" content="UserPageVisits:<?php echo $item->hits; ?>">
					<?php echo JText::_('MOD_AGS_ITEM_HITS'); ?> <?php echo $item->hits; ?>
			</li>
		</ul>
	</div>
	<?php } ?>
	
	<?php echo $item->event->afterDisplayContent; ?>
	<div style="clear: both;"></div>
</div>