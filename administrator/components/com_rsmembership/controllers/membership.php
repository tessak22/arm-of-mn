<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class RSMembershipControllerMembership extends JControllerForm
{
	public function __construct() {
		parent::__construct();
		
		// Shared Folders
		$this->registerTask('orderup', 	'foldersmove');
		$this->registerTask('orderdown', 'foldersmove');
		$this->registerTask('saveorder', 'folderssaveorder');
		$this->registerTask('folderspublish', 	'folderschangestatus');
		$this->registerTask('foldersunpublish', 'folderschangestatus');
		
		// Attachment Tasks (Subscriber Emails)
		$this->registerTask('attachmentspublish', 'attachmentschangestatus');
		$this->registerTask('attachmentsunpublish', 'attachmentschangestatus');
	}

	// 
	/**
	 * Folder Tasks
	 */

	// Folder - Save Ordering
	public function foldersSaveOrder()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit('Invalid Token');
		$db 	= JFactory::getDBO();
		$model 	= $this->getModel('Membership', 'RSMembershipModel');
		$row 	= $model->getTable('MembershipShared','RSMembershipTable');
		$jinput = JFactory::getApplication()->input;

		// Get the selected items
		$cid = $jinput->get('cid_folders', array(), 'array');

		// Get the ordering
		$order = $jinput->get('order', array(), 'array');

		// Force array elements to be integers
		JArrayHelper::toInteger($cid, array(0));
		JArrayHelper::toInteger($order, array(0));

		// Load each element of the array
		for ($i=0;$i<count($cid);$i++)
		{
			// Load the item
			$row->load($cid[$i]);

			// Set the new ordering only if different
			if ($row->ordering != $order[$i])
			{	
				$row->ordering = $order[$i];
				if (!$row->store()) 
				{
					$this->setError($db->getErrorMsg());
					return false;
				}
			}
		}

		// Redirect
		$tabposition = $jinput->get('tabposition', 0, 'int');
		if (!empty($row->membership_id))
			$this->setRedirect(JRoute::_('index.php?option=com_rsmembership&task=membership.edit&id='.$row->membership_id.'&tabposition='.$tabposition, false), JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_FILES_ORDERED'));
		else
			$this->setRedirect(JRoute::_('index.php?option=com_rsmembership&view=memberships', false));
	}

	/*
	Joomla 3.0 Saving order function for shared content
	*/
	public function saveOrderAjax()
	{
		$return = true;
		$db 	= JFactory::getDBO();
		$model 	= $this->getModel('Membership', 'RSMembershipModel');
		$row 	= $model->getTable('MembershipShared','RSMembershipTable');
		$jinput = JFactory::getApplication()->input;
		$pks 	= $jinput->get('cid_folders', array(), 'array');
		$order 	= $jinput->get('order', array(), 'array');

		// Sanitize the input
		JArrayHelper::toInteger($pks);
		JArrayHelper::toInteger($order);

		// Save the ordering
		for ($i=0;$i<count($pks);$i++)
		{
			// Load the item
			$row->load($pks[$i]);

			// Set the new ordering only if different
			if ($row->ordering != $order[$i])
			{	
				$row->ordering = $order[$i];
				if (!$row->store()) 
				{
					$this->setError($db->getErrorMsg());
					$return = false;
				}
			}
		}

		if ($return)
			echo "1";

		// Close the application
		JFactory::getApplication()->close();
	}

	// Folder - Move Up/Down
	public function foldersMove() 
	{
		// Check for request forgeries
		JSession::checkToken() or jexit('Invalid Token');
		$db 	= JFactory::getDBO();
		$jinput = JFactory::getApplication()->input;
		$model 	= $this->getModel('Membership', 'RSMembershipModel');
		$row 	= $model->getTable('MembershipShared','RSMembershipTable');

		// Get the selected items
		$cid = $jinput->get('cid_folders', array(), 'array');

		// Get the task
		$task = $jinput->get('task', '', 'cmd');
		
		// Force array elements to be integers
		JArrayHelper::toInteger($cid, array(0));
		
		// Set the direction to move
		$direction = $task == 'orderup' ? -1 : 1;
		
		// Can move only one element
		if (is_array($cid))	$cid = $cid[0];
		
		// Load row
		if (!$row->load($cid)) 
		{
			$this->setError($db->getErrorMsg());
			return false;
		}
		
		// Move
		$row->move($direction);

		$tabposition = $jinput->get('tabposition', 0, 'int');
		// Redirect
		if (!empty($row->membership_id))
			$this->setRedirect(JRoute::_('index.php?option=com_rsmembership&task=membership.edit&id='.$row->membership_id.'&tabposition='.$tabposition, false));
		else
			$this->setRedirect(JRoute::_('index.php?option=com_rsmembership&view=memberships', false));
	}

	// Folder - Publish/Unpublish
	function foldersChangestatus()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit('Invalid Token');
		$jinput = JFactory::getApplication()->input;
		$model 	= $this->getModel('Membership', 'RSMembershipModel');
		$cid 	= $jinput->get('cid_folders', array(), 'array');
		$task 	= $jinput->get('task', '', 'cmd');

		// Force array elements to be integers
		JArrayHelper::toInteger($cid, array());

		$msg = '';

		// No items are selected
		if (!is_array($cid) || count($cid) < 1)
			JError::raiseWarning(500, JText::_('SELECT ITEM PUBLISH'));
		// Try to publish the item
		else
		{
			$value = $task == 'folderspublish' ? 1 : 0;

			if (!$model->foldersPublish($cid, $value))
				JError::raiseError(500, $model->getError());

			$total = count($cid);
			if ($value) 
				$msg = JText::sprintf('COM_RSMEMBERSHIP_MEMBERSHIP_FILES_PUBLISHED', $total);
			else 
				$msg = JText::sprintf('COM_RSMEMBERSHIP_MEMBERSHIP_FILES_UNPUBLISHED', $total);

			// Clean the cache, if any
			$cache = JFactory::getCache('com_rsmembership');
			$cache->clean();
		}

		// Get the table instance
		$row 	= $model->getTable('MembershipShared','RSMembershipTable');
		$row->load($cid[0]);

		// Redirect
		$tabposition = $jinput->get('tabposition', 0, 'int');
		if (!empty($row->membership_id))
			$this->setRedirect(JRoute::_('index.php?option=com_rsmembership&task=membership.edit&id='.$row->membership_id.'&tabposition='.$tabposition, false), $msg);
		else
			$this->setRedirect(JRoute::_('index.php?option=com_rsmembership&view=memberships', false));
	}
	
	// Folder - Remove
	public function foldersRemove()
	{
		// Check for request forgeries
		JSession::checkToken('get') or jexit('Invalid Token');
		$jinput = JFactory::getApplication()->input;

		// Get the model
		$model 	= $this->getModel('Membership', 'RSMembershipModel');

		// Get the selected items
		$cid = $jinput->get('cid_folders', array(), 'array');

		// Force array elements to be integers
		JArrayHelper::toInteger($cid, array(0));

		$msg = '';

		// No items are selected
		if (!is_array($cid) || count($cid) < 1)
			JError::raiseWarning(500, JText::_('SELECT ITEM DELETE'));
		// Try to remove the item
		else
		{
			$row 	= $model->getTable('MembershipShared','RSMembershipTable');
			$row->load($cid[0]);

			if (!empty($row->membership_id))
			{
				$model->foldersRemove($cid);

				$total 	= count($cid);
				$msg 	= JText::sprintf('COM_RSMEMBERSHIP_MEMBERSHIP_FILES_DELETED', $total);

				// Clean the cache, if any
				$cache = JFactory::getCache('com_rsmembership');
				$cache->clean();
			}
		}

		$tabposition 		= $jinput->get('tabposition', 0, 'int');
		$tabposition_link 	= '&tabposition='.$tabposition;

		// Redirect
		if (!empty($row->membership_id))
			$this->setRedirect(JRoute::_('index.php?option=com_rsmembership&task=membership.edit&id='.$row->membership_id.$tabposition_link, false), $msg);
		else
			$this->setRedirect(JRoute::_('index.php?option=com_rsmembership&view=memberships', false));
	}

	/**
	 * Attachment Tasks
	 */

	// Attachment - Publish/Unpublish
	function attachmentsChangestatus()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit('Invalid Token');

		$jinput = JFactory::getApplication()->input;

		// Get the model
		$model 	= $this->getModel('Membership', 'RSMembershipModel');
		
		// Get the selected items
		$cid 	= $jinput->get('cid_attachments', array(), 'array');

		// Get the task
		$task = $jinput->get('task', '', 'cmd');

		// Force array elements to be integers
		JArrayHelper::toInteger($cid, array());

		$msg = '';
		// No items are selected
		if (!is_array($cid) || count($cid) < 1)
			JError::raiseWarning(500, JText::_('SELECT ITEM PUBLISH'));
		// Try to publish the item
		else
		{
			$value = $task == 'attachmentspublish' ? 1 : 0;
			if (!$model->attachmentsPublish($cid, $value))
				JError::raiseError(500, $model->getError());

			$total = count($cid);
			if ($value)
				$msg = JText::plural('COM_RSMEMBERSHIP_MEMBERSHIP_ATTACHMENTS_PUBLISHED', $total);
			else
				$msg = JText::plural('COM_RSMEMBERSHIP_MEMBERSHIP_ATTACHMENTS_UNPUBLISHED', $total);

			// Clean the cache, if any
			$cache = JFactory::getCache('com_rsmembership');
			$cache->clean();
		}

		// Get the table instance
		$row = $model->getTable('MembershipAttachment','RSMembershipTable');
		$row->load($cid[0]);

		// Redirect
		$tabposition = $jinput->get('tabposition', 0, 'int');
		if (!empty($row->membership_id))
			$this->setRedirect(JRoute::_('index.php?option=com_rsmembership&task=membership.edit&id='.$row->membership_id.'&tabposition='.$tabposition, false), $msg);
		else
			$this->setRedirect(JRoute::_('index.php?option=com_rsmembership&view=memberships', false));
	}

	// Attachment - Remove
	function attachmentsRemove()
	{
		// Check for request forgeries
		JSession::checkToken('get') or jexit('Invalid Token');

		$jinput = JFactory::getApplication()->input;

		// Get the model
		$model 	= $this->getModel('Membership', 'RSMembershipModel');

		// Get the selected items
		$cid = $jinput->get('cid_attachments', array(), 'array');

		// Force array elements to be integers
		JArrayHelper::toInteger($cid, array(0));

		$msg = '';

		// No items are selected
		if (!is_array($cid) || count($cid) < 1)
			JError::raiseWarning(500, JText::_('SELECT ITEM DELETE'));
		// Try to remove the item
		else
		{
			$row = $model->getTable('MembershipAttachment','RSMembershipTable');
			$row->load($cid[0]);
			
			if (!empty($row->membership_id))
			{
				$model->attachmentsRemove($cid);
			
				$total = count($cid);
				$msg = JText::sprintf('COM_RSMEMBERSHIP_MEMBERSHIP_ATTACHMENTS_DELETED', $total);
			
				// Clean the cache, if any
				$cache = JFactory::getCache('com_rsmembership');
				$cache->clean();
			}
		}

		$tabposition = $jinput->get('tabposition', 0, 'int');
		// Redirect
		if (!empty($row->membership_id))
			$this->setRedirect(JRoute::_('index.php?option=com_rsmembership&task=membership.edit&id='.$row->membership_id.'&tabposition='.$tabposition, false), $msg);
		else
			$this->setRedirect(JRoute::_('index.php?option=com_rsmembership&view=memberships', false));
	}
}