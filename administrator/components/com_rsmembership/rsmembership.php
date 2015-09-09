<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

$user = JFactory::getUser();
if (!$user->authorise('core.manage', 'com_rsmembership')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}
require_once JPATH_COMPONENT.'/helpers/adapter.php';
require_once JPATH_COMPONENT.'/helpers/rsmembership.php';
require_once JPATH_COMPONENT.'/helpers/version.php';
require_once JPATH_COMPONENT.'/helpers/patches.php';

// Require the base controller
require_once JPATH_COMPONENT.'/controller.php';

JHtml::_('behavior.framework');

RSMembershipHelper::buildHead();

$controller	= JControllerLegacy::getInstance('RSMembership');
$task 		= JFactory::getApplication()->input->get('task');
$controller->execute($task);
$controller->redirect();