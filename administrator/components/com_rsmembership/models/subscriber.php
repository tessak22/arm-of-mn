<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class RSMembershipModelSubscriber extends JModelAdmin
{
	protected $text_prefix = 'COM_RSMEMBERSHIP';

	public function getTable($type = 'Subscriber', $prefix = 'RSMembershipTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_rsmembership.subscriber', 'subscriber', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
			return false;

		return $form;
	}

	protected function loadFormData() 
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_rsmembership.edit.category.data', array());

		if (empty($data)) 
			$data = $this->getItem();

		return $data;
	}

	public function save($data)
	{
		$jinput		= JFactory::getApplication()->input;
		$user_id 	= $jinput->get('id', 0, 'int');
		$user_data 	= $jinput->get('u', array(), 'array');
		$fields 	= $jinput->get('rsm_fields', array(), 'post');
		
		$ourtable = $this->getTable();
		
		RSMembership::createUserData((int) $user_id, $fields);

		$user = JFactory::getUser($user_id);
		$user->bind($user_data);		
		$user->save();

		$this->_id = $user->get('id');

		return true;
	}

	public function getItem($pk = null)
	{		
		$id = $pk ? $pk : $this->getState($this->getName().'.id');
		
		// $item = parent::getItem($id);
		// if transaction is made and user is not created
		if ($temp = $this->getTempId()) {
			$transaction = JTable::getInstance('Transaction', 'RSMembershipTable');
			$transaction->load($temp);
			$data = $transaction->user_data ? (object) unserialize($transaction->user_data) : (object) array();

			$user = (object) array(
				'id' 		=> 0,
				'username' 	=> isset($data->username) ? $data->username : JText::_('COM_RSMEMBERSHIP_SUBSCRIBERNAME_EMPTY'),
				'name' 		=> isset($data->name) ? $data->name : '',
				'email' 	=> $transaction->user_email
			);
		} else {
			$user = JFactory::getUser($id);
		}

		$item = (object) array(
			'user_id' 	=> $user->id,
			'username' 	=> $user->username,
			'email' 	=> $user->email,
			'name' 		=> $user->name,
			'memberships' 	=> array(),
			'transactions' 	=> array(),
			'logs' 			=> array()
		);

		$db = JFactory::getDbo();
		
		// get user's memberships
		if ($user->id) {
			$query = $db->getQuery(true);
			$query->select('u.*')
				  ->select($db->qn('m.name'))
				  ->from($db->qn('#__rsmembership_membership_subscribers','u'))
				  ->join('left', $db->qn('#__rsmembership_memberships', 'm').' ON '.$db->qn('u.membership_id').' = '.$db->qn('m.id'))
				  ->where($db->qn('user_id').' = '.$db->q($id))->order($db->qn('u.membership_start').' DESC');
			$db->setQuery($query);
			$item->memberships = $db->loadObjectList();
		}

		// get transactions
		if ($user->id) {
			$query = $db->getQuery(true);
			$query->select('*')
				  ->from($db->qn('#__rsmembership_transactions'))
				  ->where($db->qn('user_id').' = '.$db->q($id))
				  ->order($db->qn('date').' DESC');
			$db->setQuery($query);
			$item->transactions = $db->loadObjectList();
		} else {
			$item->transactions = array($transaction);
		}

		// get logs
		if ($user->id) {
			$query = $db->getQuery(true);
			$query->select('*')
				  ->from($db->qn('#__rsmembership_logs'))
				  ->where($db->qn('user_id').' = '.$db->q($id))
				  ->order($db->qn('date').' DESC');
			$db->setQuery($query, 0, 50);
			$item->logs = $db->loadObjectList();
		}
		
		return $item;
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

		$tabs = new RSTabs('com-rsmembership-subscriber');
		return $tabs;
	}

	function getMembership()
	{
		$app = JFactory::getApplication();
		$cid = $app->input->get('cid', 0, 'int');
		
		$row = JTable::getInstance('Membership_Subscriber','RSMembershipTable');
		$row->load($cid);

		$user_id = $app->input->get('user_id', 0, 'int');
		if ($user_id > 0) 
			$row->user_id = $user_id;

		$row->user = JFactory::getUser($row->user_id);

		if ($row->id == 0) 
		{
			$now = RSMembershipHelper::getCurrentDate();

			$row->membership_start = $now;
			$row->membership_end   = $now;
		}

		if (!empty($row->extras))
		{
			$row->extras = explode(',', $row->extras);
			$row->noextra = false;
		}
		else
			$row->noextra = true;
		
		return $row;
	}

	function getTransactions()
	{
		$id = $this->getState($this->getName().'.id');

		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		
		$query->select('*')->from($db->qn('#__rsmembership_transactions'))->where($db->qn('user_id').' = '.$db->q($id))->order($db->qn('date').' DESC');
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	function getCache()
	{
		return RSMembershipHelper::getCache();
	}
	
	function getPeriods()
	{
		$return = array();
		foreach ($this->_memberships as $membership)
		{
			$tmp = new stdClass();
			$tmp->membership_id = $membership->id;
			
			if ($membership->use_trial_period)
			{
				$membership->period = $membership->trial_period;
				$membership->period_type = $membership->trial_period_type;
			}
			
			$offset = 0;
			if ($membership->period > 0)
				switch ($membership->period_type)
				{
					case 'h': $offset = $membership->period * 3600; break;
					case 'd': $offset = $membership->period * 86400; break;
					case 'm': $offset = $membership->period * 86400 * 30; break;
					case 'y': $offset = $membership->period * 86400 * 30 * 12; break;
				}
			$tmp->offset = $offset;
			
			$return[] = $tmp;
		}
		
		return $return;
	}
	
	function getTempId() 
	{
		return JFactory::getApplication()->input->get('temp_id', 0, 'int');
	}

}