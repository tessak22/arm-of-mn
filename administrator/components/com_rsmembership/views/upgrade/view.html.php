<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

class RSMembershipViewUpgrade extends JViewLegacy
{
	protected $form;
	protected $item;

	public function display($tpl = null) 
	{
		// fields
		$this->field	 = $this->get('RSFieldset');

		// get upgrade xml form
		$this->form  = $this->get('Form');

		// get fieldsets -> used to get the label
		$this->fieldsets = $this->form->getFieldsets();

		// get upgrade data
		$this->item  = $this->get('Item');

		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar() 
	{
		$id		= JFactory::getApplication()->input->get('id', 0, 'int');

		if ($id) 
			JToolBarHelper::title(JText::sprintf('COM_RSMEMBERSHIP_EDIT_MEMBERSHIP_UPGRADE', $this->escape($this->item->name_from), $this->escape($this->item->name_to) ), 'upgrades');
		else 
			JToolBarHelper::title(JText::_('COM_RSMEMBERSHIP_NEW_MEMBERSHIP_UPGRADE'), 'upgrades');

		JToolBarHelper::apply('upgrade.apply');
		JToolBarHelper::save('upgrade.save');
		JToolBarHelper::save2new('upgrade.save2new');
		JToolBarHelper::cancel('upgrade.cancel');
	}
}