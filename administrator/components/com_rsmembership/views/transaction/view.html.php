<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

class RSMembershipViewTransaction extends JViewLegacy
{
	protected $form;
	protected $item;

	public function display($tpl = null) {
		$this->field = $this->get('RSFieldset'); // field
		$this->tabs	 = $this->get('RSTabs'); 	 // tabs
		$this->form  = $this->get('Form'); 		 // get subscriber xml form

		// get fieldsets -> used to get the label
		$this->fieldsets = $this->form->getFieldsets();

		// get subscriber
		$this->item  		= $this->get('Item');
		$this->temp  		= true;
		$this->cache		= $this->get('Cache');

		$show_edit 		= $this->temp ? false : true;
		$user_id   		= $this->item->user_id;
		$show_required 	= false;
		$transaction_id = $this->temp ? $this->item->transaction : 0;

		
		$this->custom_fields = RSMembershipHelper::getFields($show_edit, $user_id, $show_required, $transaction_id);

		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar() {
		JToolBarHelper::title(JText::_('COM_RSMEMBERSHIP_EDIT_TRANSACTION'));

		JToolBarHelper::cancel('transaction.cancel');
	}
}