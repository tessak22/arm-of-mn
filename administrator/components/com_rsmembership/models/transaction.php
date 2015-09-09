<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class RSMembershipModelTransaction extends JModelAdmin
{
	protected $text_prefix = 'COM_RSMEMBERSHIP';

	public function getTable($type = 'Transaction', $prefix = 'RSMembershipTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_rsmembership.transaction', 'transaction', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
			return false;

		return $form;
	}
	
	
	public function getItem($pk = null)
	{		
		$id = $pk ? $pk : $this->getState($this->getName().'.id');
		
		// $item = parent::getItem($id);
		// if transaction is made and user is not created
		$transaction = JTable::getInstance('Transaction', 'RSMembershipTable');
		$transaction->load($id);
		
		/// get the user data
		$data = $transaction->user_data ? (object) unserialize($transaction->user_data) : (object) array();
		
		if (!$transaction->user_id) {
			$user = (object) array(
				'id' 		=> 0,
				'username' 	=> (isset($data->username) && !empty($data->username)) ? $data->username : JText::_('COM_RSMEMBERSHIP_SUBSCRIBERNAME_EMPTY'),
				'name' 		=> isset($data->name) ? $data->name : '',
				'email' 	=> $transaction->user_email
			);
		} else {
			$user = JFactory::getUser($transaction->user_id);
		}
		
		$params 			= RSMembershipHelper::parseParams($transaction->params);
		$membership_id 		= 0;
		if (isset($params['membership_id'])) $membership_id = $params['membership_id'];
		if (isset($params['to_id'])) 		 $membership_id = $params['to_id'];

		$membership_info	= array();
		if ($membership_id) {
			if ($membership_fields = RSMembership::getCustomMembershipFields($membership_id)) {
				$selected = isset($data->membership_fields) ? $data->membership_fields : array();
				foreach ($membership_fields as $field) {
					$membership_info[] = RSMembershipHelper::showCustomField($field, $selected, false, false, 'membership');
				}
			}
		}
		
		$item = (object) array(
			'user_id' 			=> $user->id,
			'username' 			=> $user->username,
			'email' 			=> $user->email,
			'name' 				=> $user->name,
			'transaction' 		=> $id,
			'transaction_data' 	=> $transaction,
			'membership_info'  	=> $membership_info
		);
		
		
		return $item; 
	}
	
	function getCache()
	{
		return RSMembershipHelper::getCache();
	}
	
	public function getRSFieldset() 
	{
		require_once JPATH_COMPONENT.'/helpers/adapters/fieldset.php';

		$fieldset = new RSFieldset();
		return $fieldset;
	}

	public function getRSTabs()
	{
		require_once JPATH_COMPONENT.'/helpers/adapters/tabs.php';

		$tabs = new RSTabs('com-rsmembership-transaction');
		return $tabs;
	}
}