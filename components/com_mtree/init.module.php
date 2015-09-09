<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2011-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die;

require_once( JPATH_ROOT . '/components/com_mtree/mtree.tools.php' );
require( JPATH_ROOT.'/components/com_mtree/init.php');
require_once( JPATH_ROOT.'/components/com_mtree/modulehelper.php');

$moduleHelper = new MTModuleHelper;

$moduleHelper->setMtConf($mtconf);
if( isset($params) )
{
	$moduleHelper->setParams($params);
}
?>