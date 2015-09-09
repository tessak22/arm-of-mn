<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipControllerShare_url extends JControllerLegacy
{

	function addMembershipURL()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit('Invalid Token');
		
		// Get the selected items
		$jform  = JFactory::getApplication()->input->get('jform', array(), 'array');
		$cid 	= $jform['id'];
		
		// Get the model
		$model = $this->getModel('share_url');
		
		$model->addMembershipURL($cid);
		jexit();
	}

	public function addExtraValueURL()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit('Invalid Token');

		// Get the selected items
		$jform  = JFactory::getApplication()->input->get('jform', array(), 'array');
		$cid 	= $jform['id'];

		// Get the model
		$model = $this->getModel('share_url');

		$model->addExtraValueURL($cid);
		jexit();
	}
}