<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view');

class RSMembershipViewAddExtra extends JViewLegacy
{
	function display($tpl = null)
	{
		$app = JFactory::getApplication();
		
		// get parameters
		$pathway = $app->getPathway();
		$pathway->addItem(JText::_('COM_RSMEMBERSHIP_RENEW'), '');
		
		// get the user
		$this->user		= JFactory::getUser();

		// get the current layout
		$layout = $this->getLayout();
		if ($layout == 'default')
		{
			$this->payments = RSMembership::getPlugins();
			// get the encoded return url
			$this->return 	= base64_encode(JURI::getInstance());
			$this->data 	= $this->get('data');

			// get the membership
			$this->membership 		 = $this->get('membership');
			$this->fields 	 		 = RSMembershipHelper::getFields(false);
			$this->membership_fields = RSMembershipHelper::getMembershipFields($this->membership->id, false, $this->user->id, true, $this->membership->last_transaction_id);
		}
		elseif ($layout == 'payment') 
		{
			$this->html = $this->get('html');
		}

		// get the extra
		$this->extra 	= $this->get('extra');
		$this->cid 	 	= $this->get('cid');
		$this->params	= clone($app->getParams('com_rsmembership'));
		$this->token	= JHTML::_('form.token');
		$this->currency	= RSMembershipHelper::getConfig('currency');

		parent::display();
	}
}