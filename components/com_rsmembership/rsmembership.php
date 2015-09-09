<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

require_once JPATH_ADMINISTRATOR.'/components/com_rsmembership/helpers/adapter.php';
require_once JPATH_ADMINISTRATOR.'/components/com_rsmembership/helpers/rsmembership.php';
require_once JPATH_ADMINISTRATOR.'/components/com_rsmembership/helpers/version.php';
require_once JPATH_SITE.'/components/com_rsmembership/helpers/route.php';
require_once JPATH_COMPONENT.'/controller.php';

$controller	= JControllerLegacy::getInstance('RSMembership');
$app 		= JFactory::getApplication();
$task 		= $app->input->get('task');

$controller->execute($task);
$controller->redirect();