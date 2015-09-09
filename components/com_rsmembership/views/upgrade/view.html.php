<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view');

class RSMembershipViewUpgrade extends JViewLegacy
{
	function display($tpl = null) 
	{
		$app = JFactory::getApplication();

		// get parameters
		$params = clone($app->getParams('com_rsmembership'));

		$pathway = $app->getPathway();
		$pathway->addItem(JText::_('COM_RSMEMBERSHIP_UPGRADE'), '');

		// token
		$token = JHTML::_('form.token');
		
		// get the logged user
		$this->user		= JFactory::getUser();
		
		// the new membership id
		$this->cid 		= $this->get('cid');
		
		// get the current layout
		$layout = $this->getLayout();
		if ($layout == 'default') 
		{
			$this->payments = RSMembership::getPlugins();

			// get the encoded return url
			$this->return 	= base64_encode(JURI::getInstance());

			$this->data 	= $this->get('data');
			
			// get the upgrade
			$this->upgrade = $this->get('upgrade');
			
			// price
			$this->total 	= RSMembershipHelper::getPriceFormat($this->upgrade->price);
			
			$this->fields	= RSMembershipHelper::getFields(true);
			
			$this->fields_validation = RSMembershipHelper::getFieldsValidation($this->upgrade->membership_to_id);
			$this->membership_fields = RSMembershipHelper::getMembershipFields($this->upgrade->membership_to_id, true, $this->user->id, true);

			$this->membershipterms = $this->get('membershipterms');
		}
		elseif ($layout == 'payment') 
		{
			$this->html = $this->get('html');
		}
		
		$this->config 	= RSMembershipHelper::getConfig();
		$this->params 	= $params;
		$this->token	= $token;
		$this->currency = RSMembershipHelper::getConfig('currency');

		parent::display();
	}
}