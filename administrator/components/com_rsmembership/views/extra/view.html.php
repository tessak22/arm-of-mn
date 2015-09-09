<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

class RSMembershipViewExtra extends JViewLegacy
{
	protected $form;
	protected $item;

	public function display($tpl = null) 
	{
		// fields
		$this->field	 = $this->get('RSFieldset');

		// get extra xml form
		$this->form  = $this->get('Form');

		// get fieldsets -> used to get the label
		$this->fieldsets = $this->form->getFieldsets();
		
		// get extra
		$this->item  = $this->get('Item');

		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar() 
	{
		$id	= JFactory::getApplication()->input->get('id', 0, 'int');
		if ($id) 
			JToolBarHelper::title(JText::sprintf('COM_RSMEMBERSHIP_EDIT_EXTRA', $this->escape($this->item->name)), 'extras');
		else 
			JToolBarHelper::title(JText::_('COM_RSMEMBERSHIP_NEW_EXTRA'), 'extras');

		JToolBarHelper::apply('extra.apply');
		JToolBarHelper::save('extra.save');
		JToolBarHelper::save2new('extra.save2new');
		JToolBarHelper::cancel('extra.cancel');
	}
}