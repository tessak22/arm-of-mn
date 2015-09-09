<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipControllerFiles extends JControllerAdmin
{
	public function __construct() {
		parent::__construct();
		$this->registerTask('apply', 'save');
	}

	public function delete() {
		// Check for request forgeries
		JSession::checkToken() or jexit('Invalid Token');

		// Get the model
		$model = $this->getModel('files');

		// Get the selected items
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');
		$msg = '';
		
		if (is_array($cid) && count($cid)) {
			$model->remove($cid);
			
			$total 	= count($cid);
			$msg 	= JText::sprintf('COM_RSMEMBERSHIP_FILES_DELETED', $total);
			
			// Clean the cache, if any
			$cache = JFactory::getCache('com_rsmembership');
			$cache->clean();
		}
		
		// Redirect
		$this->setRedirect('index.php?option=com_rsmembership&view=files&folder='.urlencode($model->getCurrent()), $msg);
	}
	
	public function upload()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit('Invalid Token');

		// Get the model
		$model = $this->getModel('files');
		
		$folder = $model->getCurrent();
		$result = $model->upload();

		if ($result)
			$this->setRedirect(JRoute::_('index.php?option=com_rsmembership&view=files&folder='.$folder, false), JText::_('COM_RSMEMBERSHIP_UPLOADED'));
		else
			$this->setRedirect(JRoute::_('index.php?option=com_rsmembership&view=files&folder='.$folder, false), JText::_('COM_RSMEMBERSHIP_NOT_UPLOADED'), 'error');
	}
	
	public function newdir()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit('Invalid Token');
		
		// Get the model
		$model 	= $this->getModel('files');

		$dir 	= JFactory::getApplication()->input->get('dirname', '', 'string');
		$folder = $model->getCurrent();
		
		if (strlen($dir) > 0)
			$result = $model->newdir($dir);
		else
			$result = false;
		
		if ($result)
			$this->setRedirect(JRoute::_('index.php?option=com_rsmembership&view=files&folder='.$folder, false), JText::_('COM_RSMEMBERSHIP_DIRECTORY_CREATED'));
		else
			$this->setRedirect(JRoute::_('index.php?option=com_rsmembership&view=files&folder='.$folder, false), JText::_('COM_RSMEMBERSHIP_DIRECTORY_NOT_CREATED'));
			
	}
	
	public function addmembershipshared()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit('Invalid Token');

		// Get the selected items
		$cids = JFactory::getApplication()->input->get('cid', array(), 'array');

		// Get the model
		$model = $this->getModel('files');

		$model->addmembershipfolders($cids);
	}
	
	public function addsubscriberfiles()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit('Invalid Token');

		// Get the selected items
		$cids = JFactory::getApplication()->input->get('cid', array(), 'array');

		// Get the model
		$model = $this->getModel('files');
		
		$model->addsubscriberfiles($cids);
	}

	public function addextravaluefolders()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit('Invalid Token');
		
		// Get the selected items
		$cids = JFactory::getApplication()->input->get('cid', array(), 'array');
		
		// Get the model
		$model = $this->getModel('files');
		
		$model->addextravaluefolders($cids);
	}
}
