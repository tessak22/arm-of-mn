<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipControllerShare extends JControllerAdmin
{
	public function __construct() 
	{
		parent::__construct();
	}
	
	public function addMembershipSharedContent()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit('Invalid Token');
		
		// Get the selected items
		$cids = JFactory::getApplication()->input->get('cid', array(), 'array');
		
		// Get the model
		$model = $this->getModel('share');

		$model->addItems($cids, 'membership', JFactory::getApplication()->input->get('share_type', '', 'string'));
		jexit();
	}

	public function addExtraValueSharedContent()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit('Invalid Token');
		
		// Get the selected items
		$cids = JFactory::getApplication()->input->get('cid', array(), 'array');
		
		// Get the model
		$model = $this->getModel('share');
		
		$model->addItems($cids, 'extra_value', JFactory::getApplication()->input->get('share_type', '', 'string'));
		jexit();
	}
	
	public function addMembershipArticles() 
	{
		// Check for request forgeries
		JSession::checkToken() or jexit('Invalid Token');
		
		// Get the selected items
		$cids = JFactory::getApplication()->input->get('cid', array(), 'array');

		// Get the model
		$model = $this->getModel('share');

		$model->addItems($cids, 'membership', 'article');
		jexit();
	}
	
	public function addExtraValueArticles() 
	{
		// Check for request forgeries
		JSession::checkToken() or jexit('Invalid Token');
		
		// Get the selected items
		$cids = JFactory::getApplication()->input->get('cid', array(), 'array');
		
		// Get the model
		$model = $this->getModel('share');

		$model->addItems($cids, 'extra_value', 'article');
		jexit();
	}

	public function addMembershipCategories() 
	{
		// Check for request forgeries
		JSession::checkToken() or jexit('Invalid Token');
		
		// Get the selected items
		$cids = JFactory::getApplication()->input->get('cid', array(), 'array');
		
		// Get the model
		$model = $this->getModel('share');
		
		$model->addItems($cids, 'membership', 'category');
		jexit();
	}
	
	public function addExtraValueCategories()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit('Invalid Token');
		
		// Get the selected items
		$cids = JFactory::getApplication()->input->get('cid', array(), 'array');

		// Get the model
		$model = $this->getModel('share');
		
		$model->addItems($cids, 'extra_value', 'category');
		jexit();
	}

	public function addMembershipModules()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit('Invalid Token');
		
		// Get the selected items
		$cids = JFactory::getApplication()->input->get('cid', array(), 'array');
		JArrayHelper::toInteger($cids);
		
		// Get the model
		$model = $this->getModel('share');
		
		$model->addItems($cids, 'membership', 'module');
		jexit();
	}
	
	public function addExtraValueModules()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit('Invalid Token');

		// Get the selected items
		$cids = JFactory::getApplication()->input->get('cid', array(), 'array');
		JArrayHelper::toInteger($cids);

		// Get the model
		$model = $this->getModel('share');
		
		$model->addItems($cids, 'extra_value', 'module');
		jexit();
	}
	
	public function addMembershipMenus()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit('Invalid Token');
		
		// Get the selected items
		$cids = JFactory::getApplication()->input->get('cid', array(), 'array');
		JArrayHelper::toInteger($cids);
		
		// Get the model
		$model = $this->getModel('share');
		
		$model->addItems($cids, 'membership', 'menu');
		jexit();
	}
	
	public function addExtraValueMenus()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit('Invalid Token');
		
		// Get the selected items
		$cids = JFactory::getApplication()->input->get('cid', array(), 'array');
		JArrayHelper::toInteger($cids);
		
		// Get the model
		$model = $this->getModel('share');
		
		$model->addItems($cids, 'extra_value', 'menu');
		jexit();
	}
}