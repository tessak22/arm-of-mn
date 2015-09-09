<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipViewFiles extends JViewLegacy
{
	public function display($tpl = null)
	{
		$this->addToolbar();
		
		$params = new stdClass();
		$jinput		= JFactory::getApplication()->input;

		$this->files 	= $this->get('files');
		$this->folders  = $this->get('folders');
		$this->elements = $this->get('elements');
		$this->current	= $this->get('current');
		$this->previous = $this->get('previous');
		$this->link 	= 'index.php?option=com_rsmembership&view=files';
		$this->task		= $jinput->get('task', '', 'cmd');
		
		$this->function = '';

		if ($this->task == 'addfolder' || $this->task == 'addfile')
		{
			$params->show_upload  = 0;
			$params->show_new_dir = 0;
			$params->show_edit 	  = 0;
			
			$this->membership_id = $jinput->get('membership_id', 0, 'int');
			if (!empty($this->membership_id)) 
				$this->link .= '&membership_id='.$this->membership_id;
			
			$this->extra_value_id = $jinput->get('extra_value_id', 0, 'int');
			if (!empty($this->extra_value_id))
				$this->link .= '&extra_value_id='.$this->extra_value_id;

			$this->link .= '&tmpl=component';

			$this->function = $jinput->get('function', '', 'string');

			$this->link .= '&function='.$this->function;
			
			if ($email_type = $jinput->get('email_type', '', 'cmd'))
			{
				$this->link 	 .= '&email_type='.$email_type;
				$this->email_type = $email_type;
			}
			
			$this->start = 0;
			if ($this->task == 'addfolder')
			{
				$params->show_folders = 1;
				$params->show_files = 0;
				$this->start = 0;
				$this->count = count($this->folders);
			}
			if ($this->task == 'addfile')
			{
				$params->show_folders = 0;
				$params->show_files = 1;
				$this->start = count($this->folders);
				$this->count = $this->start + count($this->files);
			}

			$this->link .= '&task='.$this->task;
			
			$params->show_add = 1;
		}
		else
		{
			$params->show_upload = 1;
			$this->canUpload = $this->get('canUpload');

			$params->show_new_dir = 1;
			$params->show_edit = 1;

			$params->show_folders = 1;
			$params->show_files = 1;
			$this->count = count($this->files) + count($this->folders);

			$params->show_add = 0;
		}
			$this->params = $params;

		
		
		$this->tmpl = $jinput->get('tmpl', '', 'cmd');

		$this->filterbar = $this->get('FilterBar');
		$this->sidebar 	 = $this->get('SideBar');

		parent::display($tpl);
	}

	protected function addToolbar() {
		JToolBarHelper::title(JText::_('COM_RSMEMBERSHIP_FILES'), 'files');

		// add Menu in sidebar
		require_once JPATH_COMPONENT.'/helpers/toolbar.php';
		RSMembershipToolbarHelper::addToolbar('files');

		JToolBarHelper::editList('file.edit');
		JToolBarHelper::spacer();
		JToolBarHelper::deleteList('COM_RSMEMBERSHIP_CONFIRM_DELETE', 'files.delete');
	}
}