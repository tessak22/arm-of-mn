<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2014-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class mUpgrade_3_5_11 extends mUpgrade
{
    function upgrade() {
        $database = JFactory::getDBO();

        // Adds config for fe_num_of_related
        $database->setQuery(
            'INSERT INTO `#__mt_config` (`varname`, `groupname`, `value`, `default`, `configcode`, `ordering`, `displayed`, `overridable_by_category`) '
            . ' VALUES '
            . ' ( \'fe_num_of_related\',  \'listing\',  \'\',  \'50\',  \'text\',  \'6800\',  \'0\',  \'0\');'
        );
        $database->execute();

        updateVersion(3,5,11);
        $this->updated = true;
        return true;
    }
}
?>
