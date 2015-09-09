<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2015-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class mUpgrade_3_6_3 extends mUpgrade
{
	function upgrade() {
		$database = JFactory::getDBO();

		// Adds new config: use_captcha_recommend
		$database->setQuery(
			'INSERT INTO `#__mt_config` (`varname`, `groupname`, `value`, `default`, `configcode`, `ordering`, `displayed`, `overridable_by_category`) '
			. ' VALUES '
			. ' ( \'use_captcha_recommend\',  \'captcha\',  \'0\',  \'0\',  \'yesno\',  \'5000\',  \'1\',  \'0\'); '
		);
		$database->execute();

		updateVersion(3,6,3);
		$this->updated = true;
		return true;
	}
}

