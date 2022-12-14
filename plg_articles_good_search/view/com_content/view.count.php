<?php

/**
 * @package     Articles Good Search
 *
 * @copyright   Copyright (C) 2017 Joomcar extensions. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;

class ArticlesViewGoodSearch extends JViewCategory {
	function display($search_type = "com_content") {	
		require_once(JPATH_SITE . "/plugins/system/plg_articles_good_search/models/com_content/model.php");
		$model = new ArticlesModelGoodSearch;
		echo $model->total_items;
		die;
	}
}

?>