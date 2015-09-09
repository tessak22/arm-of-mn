<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipViewSales_Report extends JViewLegacy
{
	public function display($tpl = null)
	{
		if ($this->getLayout() == 'log') 
			$this->log		 = $this->get('log');
		else 
		{
			$this->items 		= $this->get('Items');
			$this->pagination 	= $this->get('Pagination');
			$this->state	 	= $this->get('State');
			$this->total		= $this->get('TotalIncome');
			
			$this->addToolbar();

			$this->filterbar = $this->get('FilterBar');
			$this->sidebar 	 = $this->get('SideBar');

		}

		parent::display($tpl);
	}
	
	protected function addToolbar() 
	{
		JToolBarHelper::title(JText::_('COM_RSMEMBERSHIP_TRANSACTIONS'),'sales_report');

		// add Menu in sidebar
		require_once JPATH_COMPONENT.'/helpers/toolbar.php';
		RSMembershipToolbarHelper::addToolbar('sales_report');
	}
}