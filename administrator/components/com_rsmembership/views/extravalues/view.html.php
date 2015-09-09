<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipViewExtraValues extends JViewLegacy
{
	public function display($tpl = null)
	{
		$this->items 		= $this->get('Items');
		$this->pagination 	= $this->get('Pagination');
		$this->state	 	= $this->get('State');

		$this->addToolbar();

		$this->filterbar = $this->get('FilterBar');
		$this->sidebar 	 = $this->get('SideBar');
		$this->ordering	 = $this->get('Ordering');

		parent::display($tpl);
	}

	protected function addToolbar() 
	{
		$extra_id 	 = JFactory::getApplication()->input->get('extra_id', 0, 'int');
		$extra_model = JModelLegacy::getInstance('Extra', 'RSMembershipModel');
		$extra		 = $extra_model->getItem($extra_id);

		JToolBarHelper::title(JText::sprintf('COM_RSMEMBERSHIP_MEMBERSHIP_EXTRA_VALUES', $extra->name), 'extras');

		// add Menu in sidebar
		require_once JPATH_COMPONENT.'/helpers/toolbar.php';
		RSMembershipToolbarHelper::addToolbar('extras');
		
		JToolBarHelper::addNew('extravalue.add');
		JToolBarHelper::editList('extravalue.edit');
		
		JToolBarHelper::spacer();
		JToolbarHelper::publish('extravalues.publish', 'JTOOLBAR_PUBLISH', true);
		JToolbarHelper::unpublish('extravalues.unpublish', 'JTOOLBAR_UNPUBLISH', true);

		JToolBarHelper::spacer();
		JToolBarHelper::deleteList('COM_RSMEMBERSHIP_CONFIRM_DELETE','extravalues.delete');
		
		JToolBarHelper::back('Back', "index.php?option=com_rsmembership&task=extra.edit&id=".$extra_id);
	}
}