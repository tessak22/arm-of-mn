<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class RSMembershipControllerFile extends JControllerForm
{
	public function __construct() {
		parent::__construct();
	}

	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'cid')
	{
		$append = parent::getRedirectToItemAppend($recordId, $urlVar);

		$cid = JFactory::getApplication()->input->get('cid', '', 'string');
		if ($cid) 
			$append .= '&cid=' . $cid;
			
		return $append;
	}

	protected function getRedirectToListAppend()
	{
		$append = parent::getRedirectToListAppend();
		
		$model  = $this->getModel('file');
		$folder = $model->getFolder();
		if ($folder)
			$append .= '&folder=' . $folder;		

		return $append;
	}

	public function cancel($key = null)
	{
		$model  = $this->getModel('file');
		$folder = $model->getFolder();
		$link 	= 'index.php?option=com_rsmembership&view=files&folder='.$folder;

		$this->setRedirect(JRoute::_($link, false));
		return true;
	}
	
	/**
	 * Logic to save
	 */
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries
		JSession::checkToken() or jexit('Invalid Token');

		// Get the model
		$model  = $this->getModel('file');
		$jinput = JFactory::getApplication()->input;
		
		if (!$model->pathExists())
			$this->setRedirect(JRoute::_('index.php?option=com_rsmembership&view=files', false), JText::_('COM_RSMEMBERSHIP_NOT_FILE'));

		// Save
		$jform  = $jinput->get('jform', array(), 'array');
		$result = $model->save($jform);

		$cid 	= $jinput->get('cid', '', 'string');
		$folder = $model->getFolder();

		$task   = $jinput->get('task', '', 'cmd');

		switch($task)
		{
			case 'apply':
				$link = 'index.php?option=com_rsmembership&task=file.edit&cid='.$cid;
				if ($result)
					$this->setRedirect(JRoute::_($link, false), JText::_('COM_RSMEMBERSHIP_FILE_SAVED_OK'));
				else
					$this->setRedirect(JRoute::_($link, false), JText::_('COM_RSMEMBERSHIP_FILE_SAVED_ERROR'));
			break;
		
			case 'save':
				if (empty($folder))
					$link = 'index.php?option=com_rsmembership&view=files';
				else
					$link = 'index.php?option=com_rsmembership&view=files&folder='.$folder;
				if ($result)
					$this->setRedirect(JRoute::_($link, false), JText::_('COM_RSMEMBERSHIP_FILE_SAVED_OK'));
				else
					$this->setRedirect(JRoute::_($link, false), JText::_('COM_RSMEMBERSHIP_FILE_SAVED_ERROR'));
			break;
		}
	}
	
}