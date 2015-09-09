<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipViewMymembership extends JViewLegacy
{
	public function display($tpl = null)
	{
		$app 	 = JFactory::getApplication();
		$pathway = $app->getPathway();
		
		// Set pathway
		$pathway->addItem(JText::_('COM_RSMEMBERSHIP_MEMBERSHIP'), '');

		// Set params
		$this->params = clone($app->getParams('com_rsmembership'));

		if ($terms = $this->get('terms')) {
			$this->terms  = $terms;
			$this->action = $this->escape(JRoute::_(JURI::getInstance(),false));

			parent::display('terms');
		} else {
			$this->cid 				= $this->get('cid');
			$this->membership 		= $this->get('membership');
			$this->membershipterms 	= $this->get('membershipterms');
			$this->boughtextras 	= $this->get('boughtextras');
			$this->extras 			= $this->get('extras');
			$upgrades_array 		= $this->get('upgrades');

			$upgrades = array();
			foreach ( $upgrades_array as $upgrade ) 
				$upgrades[] = JHTML::_('select.option', $upgrade->membership_to_id, $upgrade->name . ' - ' . RSMembershipHelper::getPriceFormat($upgrade->price));

			$has_upgrades = !empty($upgrades);
			$this->has_upgrades = $has_upgrades;

			$lists['upgrades'] = JHTML::_('select.genericlist', $upgrades, 'to_id', 'class="inputbox input-medium"');

			$this->folders 	= $this->get('folders');
			$this->files 	= $this->get('files');
			$this->previous = $this->get('previous');
			$this->from 	= $this->get('from');
			$this->lists	= $lists;

			$Itemid = $app->input->get('Itemid',0, 'int');
			$this->Itemid = '';
			if ( $Itemid > 0 ) 
				$this->Itemid = '&Itemid='.$Itemid;

			$this->currency = RSMembershipHelper::getConfig('currency');
			
			// get the logged user
			$this->user		= JFactory::getUser();
			$this->membership_fields = RSMembershipHelper::getMembershipFields($this->membership->membership_id, false, $this->user->id, true, $this->membership->last_transaction_id);

			parent::display();
		}
	}
}