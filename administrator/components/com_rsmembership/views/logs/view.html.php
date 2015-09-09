<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipViewLogs extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $filterbar;
	protected $sidebar;
	protected $isJ30;
	
	function display($tpl=null) {		
		$this->addToolBar();
		
		$this->isJ30		= $this->get('isJ30');
		
		$this->items 		= $this->get('Items');
		$this->pagination 	= $this->get('Pagination');
		$this->state 		= $this->get('State');
		
		$this->filterbar	= $this->get('FilterBar');		
		$this->sidebar 		= $this->get('SideBar');
		$this->dropdown		= $this->get('Dropdown');
		
		$this->userId		= $this->get('userId');
		
		parent::display($tpl);
	}
	
	protected function addToolBar() {
		// set title
		JToolBarHelper::title('RSMembership!', 'rsmembership');
		
		require_once JPATH_COMPONENT.'/helpers/toolbar.php';
		RSMembershipToolbarHelper::addToolbar('subscribers');
		
		JToolBarHelper::deleteList('COM_RSMEMBERSHIP_CONFIRM_DELETE', 'logs.delete');
	}
}