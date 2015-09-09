<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

class RSMembershipViewTerm extends JViewLegacy
{
	protected $form;
	protected $item;

	public function display($tpl = null) 
	{
		// fields
		$this->field	 = $this->get('RSFieldset');

		// get term xml form
		$this->form  = $this->get('Form');

		// get fieldsets -> used to get the label
		$this->fieldsets = $this->form->getFieldsets();
		
		// get term data
		$this->item  = $this->get('Item');

		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar() 
	{
		$id		= JFactory::getApplication()->input->get('id', 0, 'int');

		if ($id) 
			JToolBarHelper::title(JText::sprintf('COM_RSMEMBERSHIP_EDIT_TERM', $this->escape($this->item->name) ), 'terms');
		else 
			JToolBarHelper::title(JText::_('COM_RSMEMBERSHIP_NEW_TERM'), 'terms');

		JToolBarHelper::apply('term.apply');
		JToolBarHelper::save('term.save');
		JToolBarHelper::save2new('term.save2new');
		JToolBarHelper::cancel('term.cancel');
	}
}