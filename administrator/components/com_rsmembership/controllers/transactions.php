<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipControllerTransactions extends JControllerAdmin
{
	protected $text_prefix = 'COM_RSMEMBERSHIP';

	public function __construct($config = array()) 
	{
		parent::__construct($config);
	}
	
	public function getModel($name = 'Transaction', $prefix = 'RSMembershipModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Logic to remove
	 */
	public function remove()
	{
		// Check for request forgeries
		JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

		// Get the model
		$model 	= $this->getModel('transaction');

		$app	= JFactory::getApplication();
		// Get the selected items
		$cid = $app->input->get('cid', array(0), 'array');

		// Force array elements to be integers
		JArrayHelper::toInteger( $cid, array(0) );

		$msg = '';
		
		// No items are selected
		if (!is_array($cid) || count($cid) < 1)
			JError::raiseWarning(500, JText::_('SELECT ITEM DELETE'));
		// Try to remove the item
		else
		{
			$model->delete($cid);

			$total = count($cid);
			$msg = JText::sprintf('COM_RSMEMBERSHIP_TRANSACTIONS_DELETED', $total);

			// Clean the cache, if any
			$cache = JFactory::getCache('com_rsmembership');
			$cache->clean();
		}

		// Redirect
		$tabposition 	= $app->input->get('tabposition', 0, 'int');
		$user_id 		= $app->input->get('user_id', 0, 'int');
		if ($user_id > 0) 
			$this->setRedirect(JRoute::_('index.php?option=com_rsmembership&view=subscriber&layout=edit&user_id='.$user_id.'&tabposition='.$tabposition, false), $msg);
		else
			$this->setRedirect(JRoute::_('index.php?option=com_rsmembership&view=transactions', false), $msg);
	}

	public function approve() {
		// Check for request forgeries
		JSession::checkToken() or jexit('Invalid Token');
		
		// Get the selected items
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');
		
		// Force array elements to be integers
		JArrayHelper::toInteger($cid, array(0));
		
		$msg = '';
		
		// No items are selected
		if (!is_array($cid) || count($cid) < 1) {
			JError::raiseWarning(500, JText::_('JERROR_NO_ITEMS_SELECTED'));
		} else {
			$user 		= JFactory::getUser();
			$user_id 	= $user->get('username');
			$total 		= 0;
			foreach ($cid as $id) {
				RSMembership::saveTransactionLog('Manually approved by '.$user_id, $id);
				if (RSMembership::approve($id)) {
					$total++;
				}
			}

			$msg = JText::sprintf('COM_RSMEMBERSHIP_TRANSACTIONS_APPROVED', $total);

			// Clean the cache, if any
			$cache = JFactory::getCache('com_rsmembership');
			$cache->clean();
		}

		$this->setRedirect(JRoute::_('index.php?option=com_rsmembership&view=transactions', false), $msg);
	}
	
	public function deny() {
		// Check for request forgeries
		JSession::checkToken() or jexit('Invalid Token');

		// Get the selected items
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');

		// Force array elements to be integers
		JArrayHelper::toInteger($cid, array(0));

		$msg = '';

		// No items are selected
		if (!is_array($cid) || count($cid) < 1) {
			JError::raiseWarning(500, JText::_('JERROR_NO_ITEMS_SELECTED'));
		} else {
			$user 		= JFactory::getUser();
			$user_id 	= $user->get('username');
			$total		= 0;
			foreach ($cid as $id) {
				RSMembership::saveTransactionLog('Manually denied by '.$user_id, $id);
				if (RSMembership::deny($id)) {
					$total++;
				}
			}

			$msg = JText::sprintf('COM_RSMEMBERSHIP_TRANSACTIONS_DENIED', $total);

			// Clean the cache, if any
			$cache = JFactory::getCache('com_rsmembership');
			$cache->clean();
		}
		
		$this->setRedirect(JRoute::_('index.php?option=com_rsmembership&view=transactions', false), $msg);
	}
}