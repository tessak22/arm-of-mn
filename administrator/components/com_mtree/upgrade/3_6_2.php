<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2015-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class mUpgrade_3_6_2 extends mUpgrade
{
	function upgrade() {
		$database = JFactory::getDBO();

		// Update assets table lft & rgt value so that it does not remove Root when Mosets Tree is uninstalled.
		$database->setQuery( "UPDATE `#__assets` SET lft = 9999, rgt = 9999 WHERE name = 'com_mtree' AND lft = 0 AND rgt = 0 LIMIT 1" );
		$database->execute();

		updateVersion(3,6,2);
		$this->updated = true;
		return true;
	}
}
?>
