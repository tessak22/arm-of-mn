<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2015-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class mUpgrade_3_6_1 extends mUpgrade
{
	function upgrade() {
		$database = JFactory::getDBO();

		// Adds missing configs: prevent_rate_own_listing, prevent_review_own_listing
		$database->setQuery(
			'INSERT INTO `#__mt_config` (`varname`, `groupname`, `value`, `default`, `configcode`, `ordering`, `displayed`, `overridable_by_category`) '
			. ' VALUES '
			. ' ( \'prevent_rate_own_listing\',  \'ratingreview\',  \'1\',  \'1\',  \'yesno\',  \'1450\',  \'1\',  \'1\'), '
			. ' ( \'prevent_review_own_listing\',  \'ratingreview\',  \'1\',  \'1\',  \'yesno\',  \'2750\',  \'1\',  \'1\'); '
		);
		$database->execute();

		// Rename #__mt_customfields access_level to view_access_level
		$database->setQuery(
			'ALTER TABLE `#__mt_customfields` CHANGE `access_level` `view_access_level` INT(11)  NOT NULL  DEFAULT \'1\';'
		);
		$database->execute();

		// Add edit_access_level to #__mt_customfields
		$database->setQuery(
			'ALTER TABLE `#__mt_customfields` ADD `edit_access_level` INT(11)  NOT NULL  DEFAULT \'1\'  AFTER `view_access_level`;'
		);
		$database->execute();

		// Adds LinkedIn social sharing button
		$database->setQuery(
			'INSERT IGNORE INTO `#__mt_config` (`varname`, `groupname`, `value`, `default`, `configcode`, `ordering`, `displayed`, `overridable_by_category`) '
			. ' VALUES '
			// Sharing tab
			. ' (\'show_share_with_linkedin\', \'sharing\', \'1\', \'1\', \'yesno\', 600, 1, 1); '
		);
		$database->execute();

		updateVersion(3,6,1);
		$this->updated = true;
		return true;
	}
}
?>
