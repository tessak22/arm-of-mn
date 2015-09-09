<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipViewMembership_Fields extends JViewLegacy
{
	public function display($tpl = null)
	{
		$this->items 		 = $this->get('Items');
		$this->pagination 	 = $this->get('Pagination');
		$this->state	 	 = $this->get('State');
		$this->ordering	 	 = $this->get('Ordering');
		$this->membership_id = $this->get('MembershipID');

		$this->addToolbar();

		$this->filterbar = $this->get('FilterBar');
		$this->sidebar 	 = $this->get('SideBar');
		
		
		parent::display($tpl);
	}

	protected function addToolbar() 
	{
		JToolBarHelper::title(JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_FIELDS'),'membership_fields');

		// add Menu in sidebar
		require_once JPATH_COMPONENT.'/helpers/toolbar.php';
		RSMembershipToolbarHelper::addToolbar('membership_fields');
		
		JToolBarHelper::addNew('membership_field.add');
		JToolBarHelper::editList('membership_field.edit');
		
		JToolBarHelper::spacer();
		JToolbarHelper::publish('membership_fields.publish', 'JTOOLBAR_PUBLISH', true);
		JToolbarHelper::unpublish('membership_fields.unpublish', 'JTOOLBAR_UNPUBLISH', true);

		JToolBarHelper::spacer();
		JToolBarHelper::deleteList('COM_RSMEMBERSHIP_CONFIRM_DELETE','membership_fields.delete');
	}
}