<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class RSMembershipModelTerms extends JModelList
{
	var $message;
	
	function __construct()
	{
		parent::__construct();
	}

	function getTerms()
	{
		$app = JFactory::getApplication();
		$cid = $app->input->get('cid', 0, 'int');
		$row = JTable::getInstance('Term','RSMembershipTable');

		$row->load($cid);

		if ( !$row->published ) 
		{
			JError::raiseWarning(500, JText::_('COM_RSMEMBERSHIP_NO_TERM'));
			$app->redirect(JRoute::_(RSMembershipRoute::Memberships(), false));
		}
		return $row;
	}
}