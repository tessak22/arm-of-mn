<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2015-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class mUpgrade_3_6_0 extends mUpgrade
{
	function upgrade() {
		$database = JFactory::getDBO();

		// Removes user access config, now that we use Joomla core ACL.
		$database->setQuery(
			'DELETE FROM #__mt_config WHERE varname IN (\'user_addcategory\',\'user_addlisting\',\'user_contact\',\'user_recommend\',\'user_report\',\'user_report_review\',\'user_rating\',\'user_review\');'
		);
		$database->execute();

		// Adds additional configs.
		$database->setQuery(
			'INSERT IGNORE INTO `#__mt_config` (`varname`, `groupname`, `value`, `default`, `configcode`, `ordering`, `displayed`, `overridable_by_category`) '
			. ' VALUES '
			. ' ( \'note_permission\',  \'main\',  \'\',  \'\',  \'note\',  \'11100\',  \'1\',  \'0\'), '
			. ' ( \'link_to_configure_permission\',  \'main\',  \'\',  \'\',  \'label\',  \'11110\',  \'1\',  \'0\'), '
			. ' ( \'listing_details_access_level\', \'listing\', \'1\', \'1\', \'access_level\', \'0\', \'1\', \'1\'), '
			. ' (\'category_view_access_level\', \'main\', \'\', \'1\', \'access_level\', 450, 1, 1), '

			// Schema Type
			. ' (\'schema_type\', \'main\', \'\', \'Thing\', \'text\', 460, 0, 1), '

			// Font awesome
			. ' (\'load_font_awesome\', \'core\', \'1\', \'1\', \'yesno\', 0, 0, 0), '

			// Sharing tab
			. ' (\'show_share_with_email\', \'sharing\', \'1\', \'1\', \'yesno\', 100, 1, 1), '
			. ' (\'show_share_with_facebook\', \'sharing\', \'1\', \'1\', \'yesno\', 200, 1, 1), '
			. ' (\'show_share_with_twitter\', \'sharing\', \'1\', \'1\', \'yesno\', 300, 1, 1), '
			. ' (\'show_share_with_pinterest\', \'sharing\', \'1\', \'1\', \'yesno\', 400, 1, 1), '
			. ' (\'show_share_with_googleplus\', \'sharing\', \'1\', \'1\', \'yesno\', 500, 1, 1), '

			. ' (\'note_facebook_like\', \'sharing\', \'\', \'\', \'note\', 1000, 1, 1), '
			. ' (\'use_facebook_like\', \'sharing\', \'1\', \'1\', \'yesno\', 1010, 1, 1), '

			. ' (\'note_pinterest_on_hover_pin\', \'sharing\', \'\', \'\', \'note\', \'1100\', \'1\', \'1\'), '
			. ' (\'use_pinterest_on_hover_pin\', \'sharing\', \'1\', \'1\', \'yesno\', \'1110\', \'1\', \'1\'), '

			. ' (\'note_twitter_card\', \'sharing\', \'\', \'\', \'note\', \'1200\', \'1\', \'0\'), '
			. ' (\'use_twitter_card\', \'sharing\', \'1\', \'1\', \'yesno\', \'1210\', \'1\', \'0\'), '
			. ' (\'twitter_site\', \'sharing\', \'\', \'\', \'text\', \'1220\', \'1\', \'0\'), '
			. ' (\'twitter_card_type\', \'sharing\', \'summary\', \'summary\', \'text\', \'1230\', \'1\', \'0\'); '

		);
		$database->execute();

		// Adds new config group for 'sharing'
		$database->setQuery(
			'INSERT IGNORE INTO `#__mt_configgroup` (`groupname`, `ordering`, `displayed`, `overridable_by_category`) '
			. ' VALUES (\'sharing\', \'660\', \'1\', \'1\'); '
		);
		$database->execute();

		// Add new column for 'access_level' in #__mt_customfields
		$database->setQuery(
			'ALTER TABLE #__mt_customfields ADD access_level INT NOT NULL DEFAULT \'1\' AFTER published;'
		);
		$database->execute();

		// Add 4 new core fields: date, year, contactperson & mobile
		$database->setQuery(
			'ALTER TABLE #__mt_links ADD mobile VARCHAR(255) NOT NULL DEFAULT \'\' AFTER telephone;'
		);
		$database->execute();

		$database->setQuery(
			'ALTER TABLE #__mt_links ADD contactperson VARCHAR(255) NOT NULL DEFAULT \'\' AFTER postcode;'
		);
		$database->execute();

		$database->setQuery(
			'ALTER TABLE #__mt_links ADD `date` DATE NOT NULL AFTER website;'
		);
		$database->execute();

		$database->setQuery(
			'ALTER TABLE #__mt_links ADD `year` INT(11) NOT NULL AFTER website;'
		);
		$database->execute();

		$database->setQuery(
			'INSERT IGNORE INTO `#__mt_customfields` (`field_type`, `caption`, `alias`, `default_value`, `size`, `field_elements`, `prefix_text_mod`, `suffix_text_mod`, `prefix_text_display`, `suffix_text_display`, `placeholder_text`, `cat_id`, `ordering`, `hidden`, `required_field`, `published`, `access_level`, `hide_caption`, `advanced_search`, `simple_search`, `tag_search`, `filter_search`, `details_view`, `summary_view`, `search_caption`, `params`, `iscore`) '
			. ' VALUES '
			. " ('corecontactperson', 'Contact Person', 'contact-person', '', 0, '', '', '', '', '', '', 0, 9, 0, 0, 0, 1, 0, 0, 0, 0, 0, 1, 1, '', '', 1), "
			. " ('coremobile', 'Mobile', 'mobile', '', 0, '', '', '', '', '', '', 0, 9, 0, 0, 0, 1, 0, 0, 0, 0, 0, 1, 1, '', '', 1), "
			. " ('coredate', 'Date', 'date', '', 0, '', '', '', '', '', '', 0, 9, 0, 0, 0, 1, 0, 0, 0, 0, 0, 1, 1, '', '', 1), "
			. " ('coreyear', 'Year Established', 'year', '', 0, '', '', '', '', '', '', 0, 9, 0, 0, 0, 1, 0, 0, 0, 0, 0, 1, 1, '', '', 1);"
		);
		$database->execute();

		// Add default permission to allow Registered and Administrator groups to create listings etc.
		$database->setQuery(
			"INSERT IGNORE INTO `#__assets` (`parent_id`, `lft`, `rgt`, `level`, `name`, `title`, `rules`) "
			. " VALUES "
			. " (1, 0, 0, 1, 'com_mtree', 'com_mtree', '{\"core.admin\":[],\"core.manage\":[],\"mtree.listing.create\":{\"6\":1,\"2\":1,\"8\":1},\"mtree.category.create\":{\"6\":1,\"2\":1,\"8\":1},\"mtree.listing.rate\":{\"6\":1,\"2\":1,\"8\":1},\"mtree.listing.review\":{\"6\":1,\"2\":1,\"8\":1},\"mtree.listing.contact\":{\"1\":1,\"6\":1,\"2\":1,\"8\":1},\"mtree.listing.recommend\":{\"1\":1,\"6\":1,\"2\":1,\"8\":1},\"mtree.listing.report\":{\"1\":1,\"6\":1,\"2\":1,\"8\":1},\"mtree.listing.report_review\":{\"6\":1,\"2\":1,\"8\":1},\"mtree.listing.claim\":{\"6\":1,\"2\":1,\"8\":1}}') "
			. " ON DUPLICATE KEY UPDATE rules = '{\"core.admin\":[],\"core.manage\":[],\"mtree.listing.create\":{\"6\":1,\"2\":1,\"8\":1},\"mtree.category.create\":{\"6\":1,\"2\":1,\"8\":1},\"mtree.listing.rate\":{\"6\":1,\"2\":1,\"8\":1},\"mtree.listing.review\":{\"6\":1,\"2\":1,\"8\":1},\"mtree.listing.contact\":{\"1\":1,\"6\":1,\"2\":1,\"8\":1},\"mtree.listing.recommend\":{\"1\":1,\"6\":1,\"2\":1,\"8\":1},\"mtree.listing.report\":{\"1\":1,\"6\":1,\"2\":1,\"8\":1},\"mtree.listing.report_review\":{\"6\":1,\"2\":1,\"8\":1},\"mtree.listing.claim\":{\"6\":1,\"2\":1,\"8\":1}}';"
		);
		$database->execute();

		// Rename year fieldtype to myear
		$database->setQuery(
			"UPDATE `#__mt_fieldtypes` SET `field_type` = 'myear' WHERE `field_type` = 'year'"
		);
		$database->execute();

		$database->setQuery(
			"UPDATE `#__mt_customfields` SET `field_type` = 'myear' WHERE `field_type` = 'year';"
		);
		$database->execute();

		updateVersion(3,6,0);
		$this->updated = true;
		return true;
	}
}
?>
