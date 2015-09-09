<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

class RSMembershipViewFile extends JViewLegacy
{
	protected $form;
	protected $item;

	public function display($tpl = null) 
	{
		// fields
		$this->field	 = $this->get('RSFieldset');

		// get file xml form 
		$this->form  = $this->get('Form');

		// get fieldsets -> used to get the label
		$this->fieldsets = $this->form->getFieldsets();

		// get file data
		$this->item  = $this->get('Item');

		$this->is_file = $this->get('IsFile');

		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar() 
	{
		$id		= JFactory::getApplication()->input->get('cid', '', 'string');
		JToolBarHelper::title(JText::sprintf('COM_RSMEMBERSHIP_FILE_EDIT',$id), 'files');

		JToolBarHelper::apply('file.apply');
		JToolBarHelper::save('file.save');
		JToolBarHelper::cancel('file.cancel');
	}
}