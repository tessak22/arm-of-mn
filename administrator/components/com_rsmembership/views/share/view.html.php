<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipViewShare extends JViewLegacy
{
	function display($tpl = null)
	{
		$jinput 		= JFactory::getApplication()->input;
		$membership_id 	= $jinput->get('membership_id',  0, 'input');
		$extra_value_id = $jinput->get('extra_value_id', 0, 'int');

		if (!empty($membership_id))
		{
			$this->id 		= $membership_id;
			$this->what		= 'membership_id';
			$this->function = 'addmembershipshared';
		}
		else
		{
			$this->id 		= $extra_value_id;
			$this->what		= 'extra_value_id';
			$this->function = 'addextravaluefolders';
		}

		$this->pluginShareTypes = $this->get('pluginShareTypes'); 
		$this->state			= $this->get('State');
		$this->filter_word 		= $this->state->get('com_rsmembership.share.filter.search'); 

		$layout = JFactory::getApplication()->input->get('layout', '', 'cmd');
		switch ($layout) 
		{
			case 'plugin':
				$this->headers 		= $this->get('headers');
				$this->items 		= $this->get('Items');
				$this->pagination 	= $this->get('Pagination');
				$this->sortColumn 	= $this->get('sortColumn');
				$this->sortOrder 	= $this->get('sortOrder');
				$this->share_type 	= $this->get('shareType');
			break;

			case 'article':
				$this->items 		= $this->get('Items');
				$this->pagination 	= $this->get('pagination');
				$this->sortColumn 	= $jinput->get('filter_order', 'ordering', 'string');
				$this->sortOrder 	= $jinput->get('filter_order_Dir','ASC', 'string');
			break;

			case 'category':
				$this->items 		= $this->get('Items');
				$this->pagination 	= $this->get('pagination');
				$this->sortColumn 	= $jinput->get('filter_order', 'title', 'string');
				$this->sortOrder  	= $jinput->get('filter_order_Dir','ASC', 'string');
			break;

			case 'module':
				$this->has_patches 	= RSMembershipPatchesHelper::checkPatches($layout);
				$this->items 		= $this->get('Items');
				$this->pagination 	= $this->get('pagination');
				$this->sortColumn 	= $jinput->get('filter_order', 'client_id, position, ordering', 'string'); 
				$this->sortOrder	= $jinput->get('filter_order_Dir','ASC', 'string');
			break;

			case 'menu':
				$this->has_patches	= RSMembershipPatchesHelper::checkPatches($layout);
				$this->items 		= $this->get('Items');
				$this->pagination 	= $this->get('pagination');
				$this->sortColumn	= $jinput->get('filter_order', 'menutype, ordering', 'string');
				$this->sortOrder	= $jinput->get('filter_order_Dir','ASC', 'string');
			break;

			case 'url':
				$share_url_model = JModelLegacy::getInstance('Share_url','RSMembershipModel');
				// fields adapter
				$this->field	 = $share_url_model->getRSFieldset();
				// get share_url xml form
				$this->form  	 = $share_url_model->getForm();
				// get share_url fieldsets
				$this->fieldsets = $this->form->getFieldsets();
				// load share_url data
				$this->item  	 = $share_url_model->getItem();
			break;
		}

		parent::display($tpl);
	}
}