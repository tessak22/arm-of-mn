<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipViewPayments extends JViewLegacy
{
	public function display($tpl = null)
	{
		$this->items 		= $this->get('Items'); // only wirepayments		
		$this->payments		= $this->get('Payments'); // wirepayments + payment plugins
		$this->pagination 	= $this->get('Pagination');
		$this->state	 	= $this->get('State');
		$this->limitations	= $this->get('Limitations');

		$this->addToolbar();

		$this->filterbar = $this->get('FilterBar');
		$this->sidebar 	 = $this->get('SideBar');
		$this->ordering	 = $this->get('Ordering');
		
		
		parent::display($tpl);
	}

	protected function addToolbar() 
	{
		JToolBarHelper::title(JText::_('COM_RSMEMBERSHIP_PAYMENT_INTEGRATIONS'),'payments');

		// add Menu in sidebar
		require_once JPATH_COMPONENT.'/helpers/toolbar.php';
		RSMembershipToolbarHelper::addToolbar('payments');
		
		JToolBarHelper::addNew('payment.add');
		JToolBarHelper::editList('payment.edit');
		
		JToolBarHelper::spacer();
		JToolbarHelper::publish('payments.publish', 'JTOOLBAR_PUBLISH', true);
		JToolbarHelper::unpublish('payments.unpublish', 'JTOOLBAR_UNPUBLISH', true);

		JToolBarHelper::spacer();
		JToolBarHelper::deleteList('COM_RSMEMBERSHIP_CONFIRM_DELETE','payments.delete');
	}
	
	public function getTranslation($text) {
		JFactory::getLanguage()->load('plg_system_rsmembershipwire', JPATH_ADMINISTRATOR);
		$lang = JFactory::getLanguage();
		$key  = str_replace(' ', '_', $text);
		if ($lang->hasKey($key)) {
			return JText::_($key);
		} else {
			return $text;
		}
	}
}