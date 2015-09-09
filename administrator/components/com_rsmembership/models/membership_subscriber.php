<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class RSMembershipModelMembership_Subscriber extends JModelAdmin
{
	protected $text_prefix = 'COM_RSMEMBERSHIP';

	public function getTable($type = 'Membership_Subscriber', $prefix = 'RSMembershipTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_rsmembership.membership_subscriber', 'membership_subscriber', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form)) 
			return false;

		return $form;
	}

	public function getItem($pk = null)
	{
		static $cache = array();
		if (!isset($cache[$pk])) {
			$item = parent::getItem($pk);
			
			if (isset($item->extras)) {
				$item->extras = explode(',', $item->extras);
			}
			
			if (empty($item->user_id)) {
				$item->user_id = JFactory::getApplication()->input->get('user_id', 0, 'int');
			}
			
			if (empty($item->currency)) {
				$item->currency = RSMembershipHelper::getConfig('currency');
			}
			
			@list($date, $time) = explode(' ', $item->membership_end, 2);
			if ($item->membership_end == '0000-00-00 00:00:00' || $date == '1970-01-01' || $date == '1969-12-31') {
				$item->unlimited = 1;
			}
			

			$membership_info	= array();
			$hasTransaction = (int) $item->last_transaction_id;
			if ($item->membership_id && $hasTransaction) {
				if ($membership_fields = RSMembership::getCustomMembershipFields($item->membership_id)) {
					// get the trasaction
					$transaction = JTable::getInstance('Transaction', 'RSMembershipTable');
					$transaction->load($item->last_transaction_id);
					$user_data = $transaction->user_data ? (object) unserialize($transaction->user_data) : (object) array();
					
					$selected = isset($user_data->membership_fields) ? $user_data->membership_fields : array();
					foreach ($membership_fields as $field) {
						$membership_info[] = RSMembershipHelper::showCustomField($field, $selected, true, true, 'membership');
					}
					
				}
			}
			$item->membership_info = $membership_info;
			
			$cache[$pk] = $item;
		}
		return $cache[$pk];
	}

	protected function loadFormData() 
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_rsmembership.edit.subscriber_membership.data', array());

		if (empty($data))  
			$data = $this->getItem();
			
		if ($data->membership_end == JFactory::getDbo()->getNullDate()) {
			$data->unlimited = 1;
		}
		return $data;
	}

	public function getRSFieldset() 
	{
		require_once JPATH_COMPONENT.'/helpers/adapters/fieldset.php';

		$fieldset = new RSFieldset();
		return $fieldset;
	}

	public function remove($cids) {
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$cids 	= implode(',', $cids);
		
		$query->delete()
			  ->from($db->qn('#__rsmembership_membership_subscribers'))
			  ->where($db->qn('id').' IN ('.$cids.')');
		$db->setQuery($query);
		return $db->execute();
	}
	
	public function checkMembershipFields($verifyFields, $last_transaction_id, $membership_id) {
		if (count($verifyFields)) {
			require_once JPATH_ADMINISTRATOR.'/components/com_rsmembership/helpers/validation.php';
			
			$membership_fields 	= RSMembership::getCustomMembershipFields($membership_id);
			if (count($membership_fields)) {
				$fields  = $membership_fields;
				foreach ($fields as $field) {
					if (($field->required && empty($verifyFields[$field->name])) ||
						($field->rule && is_callable('RSMembershipValidation', $field->rule) && !call_user_func(array('RSMembershipValidation', $field->rule), $verifyFields[$field->name]))) {
						$message = JText::_($field->validation);
						if (empty($message)) {
							$message = JText::sprintf('COM_RSMEMBERSHIP_VALIDATION_DEFAULT_ERROR', JText::_($field->label));
						}
						return $message;
					}
				}
			}
		}
		
		return '';
	}
	
	public function save($data) {	
		$membership_fields = JRequest::getVar('rsm_membership_fields', array(), 'post', 'array');
		$last_transaction_id = JRequest::getVar('last_transaction_id', '', 'post');
		
		if (count($membership_fields)) {
			$transaction = JTable::getInstance('Transaction', 'RSMembershipTable');
			$transaction->load($last_transaction_id);
			$user_data = $transaction->user_data ? (object) unserialize($transaction->user_data) : (object) array();
			$user_data->membership_fields = $membership_fields;
			$transaction->user_data =serialize($user_data);
			
			if ($transaction->check()) {
				$transaction->store();
			}
		}
		
		if (!isset($data['extras'])) {
			$data['extras'] = '';
		} else {
			$data['extras'] = implode(',', $data['extras']);
		}
		
		// Updating ? Make sure we check the status
		if ($data['id']) {
			$current = $this->getTable();
			$current->load($data['id']);
			
			if ($current->status == MEMBERSHIP_STATUS_ACTIVE && $data['status'] != MEMBERSHIP_STATUS_ACTIVE) {
				$data['notified'] = 0;
			}
		}
		
		// Handle dates
		$offset = JFactory::getApplication()->getCfg('offset');
		$data['membership_start'] = JFactory::getDate($data['membership_start'], $offset)->toSql();
		
		if (isset($data['unlimited'])) {
			$data['membership_end'] = JFactory::getDbo()->getNullDate();
			unset($data['unlimited']);
		} else {
			$data['membership_end'] = JFactory::getDate($data['membership_end'], $offset)->toSql();
		}
		
		$result = parent::save($data);
		// Save was successful
		if ($result) {
			$membership = JTable::getInstance('Membership', 'RSMembershipTable');
			$membership->load($data['membership_id']);
			
			if ($data['status'] == MEMBERSHIP_STATUS_ACTIVE) {
				if ($membership->gid_enable) {
					RSMembership::updateGid($data['user_id'], $membership->gid_subscribe, true);
				}
				if ($membership->disable_expired_account) {
					RSMembership::enableUser($data['user_id']);
				}
			} elseif ($data['status'] == MEMBERSHIP_STATUS_EXPIRED || $data['status'] == MEMBERSHIP_STATUS_CANCELLED) {
				if ($membership->gid_enable) {
					RSMembership::updateGid($data['user_id'], $membership->gid_expire, false, 'remove');
				}
				if ($membership->disable_expired_account) {
					RSMembership::disableUser($data['user_id']);
				}
			}
		}
		
		return $result;
	}
	
	public function getEndDate($membership_id, $membership_start) {
		$format		= 'Y-m-d H:i:s';
		$membership = JTable::getInstance('Membership', 'RSMembershipTable');
		$offset 	= JFactory::getApplication()->getCfg('offset');
		if ($membership->load($membership_id)) {
			// Trial settings
			if ($membership->use_trial_period) {
				$period 	 = $membership->trial_period;
				$period_type = $membership->trial_period_type;
			} else {
				$period 	 = $membership->period;
				$period_type = $membership->period_type;
			}

			// Fixed expiry
			if ($membership->fixed_expiry) {
				$date = RSMembershipHelper::calculateFixedDate($membership->fixed_day, $membership->fixed_month, $membership->fixed_year, JFactory::getDate($membership_start));
				$membership_end = JHtml::_('date', $date->toSql(), $format);
			} elseif ($period) { // Expire in a period of time
				$date = JFactory::getDate($membership_start, $offset);
				$date->modify(RSMembership::getDateString($period, $period_type));
				$membership_end = JHtml::_('date', $date->toSql(), $format);
			} else { // No expiry
				$membership_end = JFactory::getDbo()->getNullDate();
			}
			
			return $membership_end;
		}
	}
	
	public function getPrices() {
		$db 	= JFactory::getDbo();
		$query 	= $db->getQuery(true);
		$return = array(
			'memberships' => array(),
			'extras'	  => array()
		);
		
		// Get membership prices
		$query->select($db->qn('id'))
			  ->select($db->qn('price'))
			  ->from($db->qn('#__rsmembership_memberships'));
		
		$memberships = $db->setQuery($query)
						  ->loadObjectList();
		foreach ($memberships as $membership) {
			$return['memberships'][$membership->id] = $membership->price;
		}
		
		// Get extra prices
		$query->clear();
		$query->select($db->qn('id'))
			  ->select($db->qn('price'))
			  ->from($db->qn('#__rsmembership_extra_values'));
			  
		$extras = $db->setQuery($query)
					 ->loadObjectList();
		foreach ($extras as $extra) {
			$return['extras'][$extra->id] = $extra->price;
		}
		
		return $return;
	}
	
	public function sendNotification($pks) {
		
		require_once JPATH_ADMINISTRATOR.'/components/com_rsmembership/helpers/rsmembership.php';
		// Get custom fields
		$db 	= JFactory::getDBO();
		$table = $this->getTable();
		$query	= $db->getQuery(true);
		$pks = (array) $pks;
		
		foreach ($pks as $i => $pk) {
			if ($table->load($pk)) {
				// Load specific membership
				$query->select('*')
					->from($db->qn('#__rsmembership_memberships'))
					->where('('.$db->qn('user_email_from_addr').' != '.$db->q('').' OR '.$db->qn('user_email_use_global').' = '.$db->q(1).')')
					->where($db->qn('published').' = '.$db->q(1))
					->where($db->qn('id').' = '.$db->q($table->membership_id));
				$db->setQuery($query);
				$memberships = $db->loadObjectList();
				
				$sent = RSMembership::sendNotifications($memberships,array($pk),true);
				$query->clear();
				return $sent;
			}
		}
		
		$this->cleanCache();

		return true;
	}
	
	public function getRSTabs()
	{
		require_once JPATH_COMPONENT.'/helpers/adapters/tabs.php';

		$tabs = new RSTabs('com-rsmembership-transaction');
		return $tabs;
	}
}