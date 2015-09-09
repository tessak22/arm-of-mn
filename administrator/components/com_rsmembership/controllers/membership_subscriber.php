<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class RSMembershipControllerMembership_Subscriber extends JControllerForm
{
	public function __construct() 
	{
		parent::__construct();
		$this->registerTask('publish', 'publish');
		$this->registerTask('unpublish', 'publish');
	}

	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$append = parent::getRedirectToItemAppend($recordId, $urlVar);

		$user_id = JFactory::getApplication()->input->get('user_id', 0, 'int');
		if ($user_id) 
			$append .= '&user_id=' . $user_id;
		
		$tmpl = JFactory::getApplication()->input->get('tmpl', '');
		if ($tmpl == 'component') {
			$append .= '&tmpl=component';
		}

		return $append;
	}
	
	public function save($key = null, $urlVar = null) {
		
		$membership_fields = JRequest::getVar('rsm_membership_fields', array(), 'post', 'array');
		$last_transaction_id = JRequest::getVar('last_transaction_id', '', 'post');
		$data  = JRequest::getVar('jform', array(), 'post', 'array');

		$model = $this->getModel();
		
		$checkMembershipFields = $model->checkMembershipFields($membership_fields,$last_transaction_id, $data['membership_id']);
		if ($checkMembershipFields!= '') {
			JError::raiseWarning(500, $checkMembershipFields);
			$this->setRedirect(
					JRoute::_(
						'index.php?option=com_rsmembership&task=membership_subscriber.edit&id='.$data['id']. $this->getRedirectToItemAppend(), false
					)
				);
			return false;
		}
		
		parent::save($key, $urlVar);
	}

	public function remove()
	{
		// Check for request forgeries
		JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();
		// Get the model
		$model = $this->getModel();

		// Get the selected items
		$cid = $app->input->get('cids', array(), 'array');

		// Force array elements to be integers
		JArrayHelper::toInteger($cid, array(0));

		$msg = '';

		// No items are selected
		if (!is_array($cid) || count($cid) < 1)
			JError::raiseWarning(500, JText::_($this->text_prefix . '_NO_ITEM_SELECTED'));
		// Try to remove the item
		else
		{
			$model->remove($cid);

			$total 	= count($cid);
			$msg 	= JText::sprintf('COM_RSMEMBERSHIP_MEMBERSHIPS_DELETED', $total);

			// Clean the cache, if any
			$cache = JFactory::getCache('com_rsmembership');
			$cache->clean();
		}

		// Redirect
		$user_id 	 = $app->input->get('user_id',0,'int');
		$tabposition = $app->input->get('tabposition', 0, 'int');
		$this->setRedirect(JRoute::_('index.php?option=com_rsmembership&view=subscriber&layout=edit&user_id='.$user_id.'&tabposition='.$tabposition, false), $msg);
	}

	public function publish() {
		// Check for request forgeries
		JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));
		
		$input			= JFactory::getApplication()->input;
		$pks	 		= $input->get('cids', array(), 'array');
		$user_id 		= $input->get('user_id', 0, 'int');
		$tabposition 	= $input->get('tabposition', 0, 'int');

		$task	 = $this->getTask();
		$model   = $this->getModel();

		$publish_memberships = ( $task == 'unpublish' ? $model->publish($pks, 0) : $model->publish($pks, 1) );

		if ( $publish_memberships ) 
			$msg = JText::plural('COM_RSMEMBERSHIP_N_ITEMS_'.strtoupper($task).'ED', count($pks));

		$this->setRedirect(JRoute::_('index.php?option=com_rsmembership&view=subscriber&layout=edit&user_id='.$user_id.'&tabposition='.$tabposition, false), $msg);
	}
}