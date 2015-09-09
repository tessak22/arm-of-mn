<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

require_once dirname(__FILE__).'/config.php';
require_once dirname(__FILE__).'/defines.php';
require_once dirname(__FILE__).'/helper.php';
require_once dirname(__FILE__).'/validation.php';

class RSMembership
{
	protected $_plugins = array();

	public static function getSharedContentPlugins()
	{
		jimport('joomla.plugin.helper');

		static $instances;

		if (!is_array($instances))
		{
			$instances 	= array();
			$dispatcher = JDispatcher::getInstance();

			// Get plugins		
			JPluginHelper::importPlugin('rsmembership');
			$plugins = JPluginHelper::getPlugin('rsmembership');

			foreach($plugins as $plugin)
			{
				JPluginHelper::importPlugin('rsmembership', $plugin->name, false);

				$className = 'plgRSMembership'.$plugin->name;
				if(class_exists($className))
					$instances[] = new $className($dispatcher, (array)$plugin);
			}
		}

		return $instances;
	}
	
	public static function saveTransactionLog($log, $id, $append=true)
	{
		if (!$log || !$id)
			return false;
			
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		
		if (!is_array($log))
			$log = array($log);
		
		foreach ($log as $i => $item)
			$log[$i] = JHTML::date('now').' '.$item;
		
		$log = implode("\n", $log);
		
		if ($append)
			$query->update($db->qn('#__rsmembership_transactions'))->set($db->qn('response_log').' = CONCAT('.$db->qn('response_log').', '.$db->q("\n".$log).')')->where( $db->qn('id').' = '.$db->q($id) );
		else
			$query->update($db->qn('#__rsmembership_transactions'))->set($db->qn('response_log').' = '.$db->q($log))->where( $db->qn('id').' = '.$db->q($id) );

		$db->setQuery($query);

		return $db->execute();
	}

	public static function addPlugin($name, $filename)
	{
		$instance = RSMembership::getInstance();
		
		$instance->_plugins[$filename] = $name;
	}
	
	public static function getPlugins()
	{
		$instance = RSMembership::getInstance();
		
		return $instance->_plugins;
	}
	
	public static function getPlugin($name)
	{
		$instance = RSMembership::getInstance();
		
		if (!empty($instance->_plugins[$name]))
			return $instance->_plugins[$name];
		else
			return false;
	}
	
	public static function processPluginResult($array)
	{
		if (is_array($array))
		{
			foreach ($array as $item)
				if ($item !== false)
					return $item;
		}
		else
			return $array;
	}
	
	public static function &getInstance()
	{
		static $instance;

		if (!is_object($instance))
			$instance = new RSMembership();

		return $instance;
	}
	
	public static function getMembershipData($id) {
		$membership = JTable::getInstance('Membership','RSMembershipTable');
		$membership->load($id);
		
		return $membership;
	}
	
	public static function finalize($transaction_id)
	{
		$app 		= JFactory::getApplication();
		$option 	= 'com_rsmembership';
		$db 		= JFactory::getDBO();
		$query		= $db->getQuery(true);

		// get transaction details
		$transaction = JTable::getInstance('Transaction','RSMembershipTable');
		$transaction->load($transaction_id);
		
		if (!$transaction->params)
			return false;
		
		// get user details
		$user_data = unserialize($transaction->user_data);
		$user_email = $transaction->user_email;
		
		// get membership details
		$params = RSMembershipHelper::parseParams($transaction->params);
		
		$membership = JTable::getInstance('Membership','RSMembershipTable');
		
		switch ($transaction->type)
		{
			case 'new':
				$transaction->membership_id = $params['membership_id'];
				$membership->load($transaction->membership_id);
				
				$message = $membership->user_email_new_text;
				$subject = $membership->user_email_new_subject;
				
				$admin_message = $membership->admin_email_new_text;
				$admin_subject = $membership->admin_email_new_subject;
				
				$email_type = 'user_email_new';
			break;
			
			case 'upgrade':
				$transaction->membership_id = $params['to_id'];
				$membership->load($transaction->membership_id);
				
				$message = $membership->user_email_upgrade_text;
				$subject = $membership->user_email_upgrade_subject;
				
				$admin_message = $membership->admin_email_upgrade_text;
				$admin_subject = $membership->admin_email_upgrade_subject;
				
				$email_type = 'user_email_upgrade';
			break;
			
			case 'addextra':
				$transaction->membership_id = $params['membership_id'];
				$membership->load($transaction->membership_id);
				
				$message = $membership->user_email_addextra_text;
				$subject = $membership->user_email_addextra_subject;

				$admin_message = $membership->admin_email_addextra_text;
				$admin_subject = $membership->admin_email_addextra_subject;

				$email_type = 'user_email_addextra';
			break;

			case 'renew':
				$transaction->membership_id = $params['membership_id'];

				$membership->load($transaction->membership_id);
				$subject = $membership->user_email_renew_subject;
				$message = $membership->user_email_renew_text;
				
				$admin_subject = $membership->admin_email_renew_subject;
				$admin_message = $membership->admin_email_renew_text;

				$email_type = 'user_email_renew';
			break;
		}
		
		$extras = '';
		if (!empty($params['extras']))
		{
			$extras = RSMembershipHelper::getExtrasNames($params['extras']);
		}

		$placeholders = array(
			'{membership}' 		=> $membership->name, 
			'{extras}' 			=> $extras, 
			'{email}' 			=> $user_email, 
			'{name}' 			=> $user_data->name,
			'{username}' 		=> (isset($user_data->username) ? $user_data->username : ''),
			'{continue}' 		=> '<input class="button" type="button" onclick="location.href=\''.(!empty($membership->redirect) ? $membership->redirect : JRoute::_('index.php?option=com_rsmembership')).'\'" value="'.JText::_('COM_RSMEMBERSHIP_CONTINUE').'" />', 
			'{price}' 			=> RSMembershipHelper::getPriceFormat($transaction->price), 
			'{coupon}' 			=> $transaction->coupon, 
			'{payment}' 		=> $transaction->gateway, 
			'{transaction_id}' 	=> $transaction->id
		);
		
		if ($transaction->type == 'upgrade') {
			$placeholders['{membership_from}'] = RSMembership::getMembershipData($params['from_id'])->name;
		}

		$fields 			= RSMembership::getCustomFields();
		$membership_fields  = RSMembership::getCustomMembershipFields($transaction->membership_id);
		$all_fields = array_merge($fields,$membership_fields);

		foreach ($all_fields as $field)
		{
			$name 	= $field->name;
			$object = (isset($user_data->fields[$name]) ? 'fields' : 'membership_fields');
			if ( isset($user_data->fields[$name]) || isset($user_data->membership_fields[$name])) 
				$placeholders['{'.$name.'}'] = is_array($user_data->{$object}[$name]) ? implode("\n", $user_data->{$object}[$name]) : $user_data->{$object}[$name];
			else
				$placeholders['{'.$name.'}'] = '';
		}

		$replace = array_keys($placeholders);
		$with 	 = array_values($placeholders);
		
		$jconfig = JFactory::getConfig();
		$membership->user_email_from_addr = $membership->user_email_use_global ? $jconfig->get('mailfrom') : $membership->user_email_from_addr;
		$membership->user_email_from 	  = $membership->user_email_use_global ? $jconfig->get('fromname') : $membership->user_email_from;

		// start sending emails
		// user emails
		if ( !empty($membership->user_email_from_addr) ) 
		{
			$message 	= str_replace($replace, $with, $message);
			// from address
			$from 		= $membership->user_email_from_addr;
			// from name
			$fromName 	= $membership->user_email_from;
			// recipient
			$recipient 	= $user_email; // user email
			// subject
			$subject 	= str_replace($replace, $with, $subject);
			// body
			$body 		= $message;
			// mode
			$mode 		= $membership->user_email_mode; 
			// cc
			$cc 		= null;
			// bcc
			$bcc 		= null;
			// attachments
			$query->clear();
			$query
				->select($db->qn('path'))
				->from($db->qn('#__rsmembership_membership_attachments'))
				->where($db->qn('membership_id').' = '.$db->q($transaction->membership_id))
				->where($db->qn('email_type').' = '.$db->q($email_type))
				->where($db->qn('published').' = '.$db->q('1'))
				->order($db->qn('ordering').' ASC');
			$db->setQuery($query);
			$attachment = $db->loadColumn();
			// reply to
			$replyto = $from;
			// reply to name
			$replytoname = $fromName;
			// send to user
			if ($subject != '')
				RSMembershipHelper::sendMail($from, $fromName, $recipient, $subject, $body, $mode, $cc, $bcc, $attachment, $replyto, $replytoname);
		}
		
		// admin emails
		if (!empty($membership->admin_email_to_addr) && !empty($admin_subject))
		{
			$message = $admin_message;
			$message = str_replace($replace, $with, $message);
			// from address
			$from = (trim($membership->admin_email_from_addr) != '' ? $membership->admin_email_from_addr : $user_email);
			// from name
			$fromName = $user_data->name;
			// recipient
			$recipient = $membership->admin_email_to_addr;
			// subject
			$subject = str_replace($replace, $with, $admin_subject);
			// body
			$body = $message;
			// mode
			$mode = $membership->admin_email_mode;
			// cc
			$cc = null;
			// bcc
			$bcc = null;
			// attachments
			$attachment = null;
			// reply to
			$replyto = $from;
			// reply to name
			$replytoname = $fromName;
			// send to admin
			if ($subject != '')
				RSMembershipHelper::sendMail($from, $fromName, $recipient, $subject, $body, $mode, $cc, $bcc, $attachment, $replyto, $replytoname);
		}
		
		// run php code -Custom PHP Code-
		eval($membership->custom_code);
		
		$session = JFactory::getSession();
		// set the action
		$session->set($option.'.subscribe.action', $membership->action);
		
		// show thank you message
		$thankyou = str_replace($replace, $with, $membership->thankyou);
		$session->set($option.'.subscribe.thankyou', $thankyou);

		// show url
		$redirect = str_replace($replace, $with, $membership->redirect);
		$session->set($option.'.subscribe.redirect', $redirect);
	}
	
	public static function getDateString($period, $type) {
		if ($type == 'h') {
			$unit = 'hour';
		} elseif ($type == 'd') {
			$unit = 'day';
		} elseif ($type == 'm') {
			$unit = 'month';
		} elseif ($type == 'y') {
			$unit = 'year';
		}
		
		// If plural, add 's'
		if ($period > 1) {
			$unit .= 's';
		}
		
		return "+$period $unit";
	}
	
	public static function approve($transaction_id, $force=false)
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_rsmembership/tables');
		
		$app 	= JFactory::getApplication();
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);		
		$date 	= JFactory::getDate();

		// Load the transaction
		$query->select($db->qn('id'))
			  ->from($db->qn('#__rsmembership_transactions'))
			  ->where($db->qn('id').'='.$db->q($transaction_id));
		
		if (!$force) {
			$query->where($db->qn('status').' != '.$db->q('completed'));
		}
		
		$db->setQuery($query);
		if ($id = $db->loadResult()) {
			$transaction = JTable::getInstance('Transaction', 'RSMembershipTable');
			$transaction->load($id);
		} else {
			return false;
		}

		$params 	= RSMembershipHelper::parseParams($transaction->params);
		$user_data 	= !empty($transaction->user_data) ? (object) unserialize($transaction->user_data) : (object) array();
		// Handle user creation
		$user_id 	= $transaction->user_id;
		if (!RSMembershipHelper::getConfig('create_user_instantly') && !$user_id) {
			$user_id = RSMembership::createUser($transaction->user_email, $user_data);
			// Update the transaction with the newly created user ID
			if ($user_id != $transaction->user_id) {
				$updateTransaction = JTable::getInstance('Transaction', 'RSMembershipTable');
				$updateTransaction->save(array(
					'id' 		=> $transaction->id,
					'user_id' 	=> $user_id
				));
			}
		}
		// Update user data
		if ($transaction->user_id && is_object($user_data) && isset($user_data->fields)) {
			RSMembership::createUserData($user_id, $user_data->fields);
		}
		
		// Create the subscriber
		$row = JTable::getInstance('Membership_Subscriber', 'RSMembershipTable');
		$row->bind(array(
			'published' => 1,
			'user_id'   => $user_id,
			'price'	    => $transaction->price,
			'currency'  => $transaction->currency
		));

		// Set some defaults
		$idev_enabled 		 = RSMembershipHelper::getConfig('idev_enable');
		$idev_track_renewals = RSMembershipHelper::getConfig('idev_track_renewals');
		$update_gid    = false;
		$update_user   = false;
		$update_idev   = false;
		$update_rsmail = false;
		
		switch ($transaction->type)
		{
			case 'new':
				$membership_id = $params['membership_id'];

				// Check if this membership still exists
				$membership = JTable::getInstance('Membership', 'RSMembershipTable');
				if (!$membership->load($membership_id)) {
					JError::raiseWarning(500, JText::_('COM_RSMEMBERSHIP_COULD_NOT_APPROVE_TRANSACTION'));
					return false;
				}
				
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
					$date = RSMembershipHelper::calculateFixedDate($membership->fixed_day, $membership->fixed_month, $membership->fixed_year);
					$membership_end = $date->toSql();
				} elseif ($period) { // Expire in a period of time
					$date = JFactory::getDate();
					$date->modify(self::getDateString($period, $period_type));
					$membership_end = $date->toSql();
				} else { // No expiry
					$membership_end = $db->getNullDate();
				}

				$extras = !empty($params['extras']) ? implode(',', $params['extras']) : '';
				
				$row->bind(array(
					'membership_id' 		=> $membership->id,
					'extras'				=> $extras,
					'membership_start'		=> JFactory::getDate()->toSql(),
					'membership_end'		=> $membership_end,
					'status'				=> 0,
					'from_transaction_id' 	=> $transaction->id,
					'last_transaction_id' 	=> $transaction->id
				));
				$row->store();
			
				// Take care of integrations
				if ($membership->gid_enable) {
					$update_gid = true;
				}
				if ($membership->disable_expired_account) {
					$update_user = true;
				}
				if ($idev_enabled) {
					$update_idev = true;
				}
				$update_rsmail = $membership->id;

				// Some values used later on
				$membership_start 	= $row->membership_start;
				$membership_end   	= $row->membership_end;
				$return 			= $row->id;
			break;
			
			case 'renew':
				$membership_id = $params['membership_id'];

				// Check if this membership still exists
				$membership = JTable::getInstance('Membership', 'RSMembershipTable');
				if (!$membership->load($membership_id)) {
					JError::raiseWarning(500, JText::_('COM_RSMEMBERSHIP_COULD_NOT_APPROVE_TRANSACTION'));
					return false;
				}
				
				// Verify if the subscription to be renewed still exists
				$current = JTable::getInstance('Membership_Subscriber', 'RSMembershipTable');
				if (!$current->load($params['id'])) {
					JError::raiseWarning(500, JText::_('COM_RSMEMBERSHIP_COULD_NOT_APPROVE_TRANSACTION'));
					return false;
				}
				$expired = $current->status != MEMBERSHIP_STATUS_ACTIVE; // 0 means active, any other value means it's not active
				
				$period 	 = $membership->period;
				$period_type = $membership->period_type;
				
				// Renew when not expired ?
				if (!$expired) {
					// Start the membership when the current one ends
					$membership_start = $current->membership_end;
				} else {
					// Start today
					$membership_start = JFactory::getDate()->toSql();
				}
				
				// Fixed expiry
				if ($membership->fixed_expiry) {
					$date = JFactory::getDate(JHtml::_('date', $membership_start, 'Y-m-d H:i:s'));
					$date = RSMembershipHelper::calculateFixedDate($membership->fixed_day, $membership->fixed_month, $membership->fixed_year, $date);
					$membership_end = $date->toSql();
				} elseif ($period) { // Expire in a period of time
					// If it's not expired, add the period after the membership ends
					if (!$expired) {
						$date = JFactory::getDate($current->membership_end);
					} else {
						$date = JFactory::getDate();
					}
					
					$date->modify(self::getDateString($period, $period_type));
					$membership_end = $date->toSql();
				} else { // No expiry
					$membership_end = $db->getNullDate();
				}
				
				// Update the current subscription with the new values
				$current->save(array(
					'id' 					=> $current->id,
					'membership_start' 		=> $membership_start,
					'membership_end'		=> $membership_end,
					'price'					=> $transaction->price,
					'currency'				=> $transaction->currency,
					'status'				=> 0,
					'notified'				=> $db->getNullDate(),
					'last_transaction_id' 	=> $transaction->id
				));
				
				// Take care of integrations
				if ($membership->gid_enable) {
					$update_gid = true;
				}
				if ($membership->disable_expired_account) {
					$update_user = true;
				}
				if ($idev_enabled && $idev_track_renewals) {
					$update_idev = true;
				}
					
				// Some values used later on
				// $membership_start && $membership_end already computed
				$return = $params['id'];
			break;
			
			case 'addextra':
				// Verify if the subscription still exists
				$current = JTable::getInstance('Membership_Subscriber', 'RSMembershipTable');
				if (!$current->load($params['id'])) {
					JError::raiseWarning(500, JText::_('COM_RSMEMBERSHIP_COULD_NOT_APPROVE_TRANSACTION'));
					return false;
				}
				
				// Check if this membership still exists
				$membership = JTable::getInstance('Membership', 'RSMembershipTable');
				if (!$membership->load($current->membership_id)) {
					JError::raiseWarning(500, JText::_('COM_RSMEMBERSHIP_COULD_NOT_APPROVE_TRANSACTION'));
					return false;
				}

				if (empty($current->extras)) {
					$extras = $params['extras'];
				} else {
					$extras = explode(',', $current->extras);
					$extras = array_merge($extras, $params['extras']);
				}
				
				$extras = implode(',', $extras);
				
				// Update the subscription so that it contains the newly added extras
				$current->save(array(
					'id' 		=> $current->id,
					'extras' 	=> $extras
				));

				// Some values used later on
				$membership_start = $current->membership_start;
				$membership_end   = $current->membership_end;
				$return 		  = $params['id'];
			break;

			case 'upgrade':
				// Get the upgraded membership
				$membership = JTable::getInstance('Membership', 'RSMembershipTable');
				if (!$membership->load($params['to_id'])) {
					JError::raiseWarning(500, JText::_('COM_RSMEMBERSHIP_COULD_NOT_APPROVE_TRANSACTION'));
					return false;
				}

				// Get the current subscription
				$current = JTable::getInstance('Membership_Subscriber', 'RSMembershipTable');
				if (!$current->load($params['id'])) {
					JError::raiseWarning(500, JText::_('COM_RSMEMBERSHIP_COULD_NOT_APPROVE_TRANSACTION'));
					return false;
				}
				
				// Get the old membership
				$old_membership = JTable::getInstance('Membership', 'RSMembershipTable');
				if (!$old_membership->load($current->membership_id)) {
					JError::raiseWarning(500, JText::_('COM_RSMEMBERSHIP_COULD_NOT_APPROVE_TRANSACTION'));
					return false;
				}
				
				$data = array(
					'id' 					=> $params['id'],
					'membership_id' 		=> $membership->id,
					'last_transaction_id' 	=> $transaction->id
				);
				
				// Get the upgrade price difference & update the price in the subscription
				$upgrade = JTable::getInstance('Upgrade', 'RSMembershipTable');
				if ($upgrade->load(array(
						'membership_from_id' => $old_membership->id,
						'membership_to_id' 	 => $membership->id,
						'published'			 => 1
					))) {
					$data['price'] = $current->price + $upgrade->price;
				}

				$period 	 = $membership->period;
				$period_type = $membership->period_type;
				
				// Fixed expiry
				if ($membership->fixed_expiry) {
					$date = RSMembershipHelper::calculateFixedDate($membership->fixed_day, $membership->fixed_month, $membership->fixed_year);
					$membership_end = $date->toSql();
					
					// Update status & reset notification
					if ($date->toUnix() > JFactory::getDate()->toUnix()) {
						$data['status']   = 0;
						$data['notified'] = $db->getNullDate();
					}
				} elseif ($period) { // Expire in a period of time
					$date = JFactory::getDate();
					$date->modify(self::getDateString($period, $period_type));
					$membership_end = $date->toSql();
					
					// Update status & reset notification
					if ($date->toUnix() > JFactory::getDate()->toUnix()) {
						$data['status']   = 0;
						$data['notified'] = $db->getNullDate();
					}
				} else { // No expiry
					$membership_end   = $db->getNullDate();
					
					// Update status & reset notification
					$data['status']   = 0;
					$data['notified'] = $db->getNullDate();
				}
				// Set the newly calculated end date
				$data['membership_end'] = $membership_end;
				
				// Update the current subscription with the new values
				$current->save($data);

				// Take care of integrations
				if ($membership->gid_enable) { 
					$update_gid = true;
				}
				if ($membership->disable_expired_account) {
					$update_user = true;
				}
				$update_rsmail = $membership->id;

				// Some values used later on
				// $membership_end already computed
				$membership_start 	= $current->membership_start;
				$return 			= $params['id'];
			break;
		}
		
		// Update the groups
		if ($update_gid) {
			// workaround...
			$theuser = JUser::getInstance($row->user_id);
			unset($theuser->password);
			
			RSMembership::updateGid($row->user_id, $membership->gid_subscribe, true);
		}
		
		// Enable the user
		if ($update_user) {
			RSMembership::enableUser($row->user_id);
		}

		// Set the transaction to 'completed'
		$updateTransaction = JTable::getInstance('Transaction', 'RSMembershipTable');
		$updateTransaction->save(array(
			'id' 		=> $transaction->id,
			'status' 	=> 'completed'
		));

		$user_email  = $transaction->user_email;
		$start_date  = RSMembershipHelper::showDate($membership_start);
		$end_date 	 = $membership_end == $db->getNullDate() ? JText::_('COM_RSMEMBERSHIP_UNLIMITED') : RSMembershipHelper::showDate($membership_end);
		
		$placeholders = array(
			'{membership}' 		 => $membership->name,
			'{price}'			 => RSMembershipHelper::getPriceFormat($transaction->price),
			'{extras}'			 => !empty($extras) ? RSMembershipHelper::getExtrasNames($extras) : '',
			'{email}'	   		 => $user_email,
			'{username}'   		 => (isset($user_data->username) ? $user_data->username : ''),
			'{name}'   	   		 => (isset($user_data->name) ? $user_data->name : ''),
			'{membership_start}' => $start_date,
			'{membership_end}'	 => $end_date,
			'{transaction_id}'	 => $transaction->id,
			'{transaction_hash}' => $transaction->hash
		);
		
		if ($transaction->type == 'upgrade') {
			$placeholders['{membership_from}'] = RSMembership::getMembershipData($params['from_id'])->name;
		}

		// Get all published fields so we can grab the values
		$membership_id_fields = ($transaction->type != 'upgrade' ? $params['membership_id'] : $params['to_id']);
		$fields 			= RSMembership::getCustomFields();
		$membership_fields  = RSMembership::getCustomMembershipFields($membership_id_fields);
		$all_fields = array_merge($fields,$membership_fields);

		foreach ($all_fields as $field)
		{
			$name 	= $field->name;
			$object = (isset($user_data->fields[$name]) ? 'fields' : 'membership_fields');
			if ( isset($user_data->fields[$name]) || isset($user_data->membership_fields[$name])) 
				$placeholders['{'.$name.'}'] = is_array($user_data->{$object}[$name]) ? implode("\n", $user_data->{$object}[$name]) : $user_data->{$object}[$name];
			else
				$placeholders['{'.$name.'}'] = '';
		}


		$replace = array_keys($placeholders);
		$with 	 = array_values($placeholders);

		if ($update_rsmail) {
			$app->triggerEvent('rsm_onSaveRegistration',array(array('membership' => $update_rsmail, 'email' => $user_email, 'data' => $user_data, 'userid' => $row->user_id)));
		}

		$userEmail = $adminEmail = array(
			'from' 		  => '',
			'fromName' 	  => '',
			'recipient'   => '',
			'subject' 	  => '',
			'body' 		  => '',
			'mode' 		  => '',
			'cc' 		  => '',
			'bcc' 		  => '',
			'attachments' => '',
			'replyto' 	  => '',
			'replytoname' => ''
		);

		$membership->user_email_from_addr = $membership->user_email_use_global ? JFactory::getConfig()->get('mailfrom') : $membership->user_email_from_addr;
		$membership->user_email_from 	  = $membership->user_email_use_global ? JFactory::getConfig()->get('fromname') : $membership->user_email_from;

		// start sending emails
		// user emails
		if ($membership->user_email_from_addr && $membership->user_email_approved_subject) {
			// attachments
			$query = $db->getQuery(true);
			$query->select($db->qn('path'))
				  ->from($db->qn('#__rsmembership_membership_attachments'))
				  ->where($db->qn('membership_id').' = '.$db->q($membership->id))
				  ->where($db->qn('email_type').' = '.$db->q('user_email_approved'))
				  ->where($db->qn('published').' = '.$db->q(1))
				  ->order($db->qn('ordering').' ASC');
			$db->setQuery($query);
			
			$userEmail = array(
				'from' 			=> $membership->user_email_from_addr,
				'fromName' 		=> $membership->user_email_from,
				'recipient'	 	=> $user_email, // user email
				'subject' 		=> str_replace($replace, $with, $membership->user_email_approved_subject),
				'body' 			=> str_replace($replace, $with, $membership->user_email_approved_text),
				'mode' 			=> $membership->user_email_mode,
				'cc'	 		=> null,
				'bcc' 			=> null,
				'attachments' 	=> $db->loadColumn(),
				'replyto' 		=> $membership->user_email_from_addr,
				'replytoname' 	=> $membership->user_email_from
			);
		}
		
		// admin emails
		if ($membership->admin_email_to_addr && $membership->admin_email_approved_subject) {
			$adminEmail = array(
				'from' 			=> (trim($membership->admin_email_from_addr) != '' ? $membership->admin_email_from_addr : $user_email),
				'fromName' 		=> isset($user_data->name) ? $user_data->name : $user_email,
				'recipient'	 	=> $membership->admin_email_to_addr,
				'subject' 		=> str_replace($replace, $with, $membership->admin_email_approved_subject),
				'body' 			=> str_replace($replace, $with, $membership->admin_email_approved_text),
				'mode' 			=> $membership->admin_email_mode,
				'cc'	 		=> null,
				'bcc' 			=> null,
				'attachments' 	=> null,
				'replyto' 		=> $user_email,
				'replytoname' 	=> isset($user_data->name) ? $user_data->name : $user_email
			);
		}

		// run php code -Custom PHP code (Accepted Transaction)-
		if ($membership->custom_code_transaction) {
			eval($membership->custom_code_transaction);
		}
		
		// send to user
		if ($membership->user_email_from_addr && $membership->user_email_approved_subject) {
			RSMembershipHelper::sendMail($userEmail['from'], $userEmail['fromName'], $userEmail['recipient'], $userEmail['subject'], $userEmail['body'], $userEmail['mode'], $userEmail['cc'], $userEmail['bcc'], $userEmail['attachments'], $userEmail['replyto'], $userEmail['replytoname']);
		}
		
		// send to admin
		if ($membership->admin_email_to_addr && $membership->admin_email_approved_subject) {
			RSMembershipHelper::sendMail($adminEmail['from'], $adminEmail['fromName'], $adminEmail['recipient'], $adminEmail['subject'], $adminEmail['body'], $adminEmail['mode'], $adminEmail['cc'], $adminEmail['bcc'], $adminEmail['attachments'], $adminEmail['replyto'], $adminEmail['replytoname']);
		}
		
		// process stock
		if ($membership->stock > 0) {
			$membershipUpdate = JTable::getInstance('Membership', 'RSMembershipTable');
			$membershipUpdate->save(array(
				'id' 	=> $membership->id,
				// decrease stock
				// or set it to unavailable (-1 instead of 0, which actually means unlimited)
				'stock' => $membership->stock > 1 ? ($membership->stock - 1) : -1
			));
		}
		
		if ($update_idev) {
			RSMembership::updateIdev(array(
				'idev_saleamt'  => $transaction->price,
				'idev_ordernum' => $transaction->id,
				'ip_address' 	=> $transaction->ip
			));
		}
		
		// should return the newly created/updated membership id
		return $return;
	}

	public static function deny($transaction_id, $force=false)
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_rsmembership/tables');
		
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		
		// Load the transaction
		$query->select($db->qn('id'))
			  ->from($db->qn('#__rsmembership_transactions'))
			  ->where($db->qn('id').'='.$db->q($transaction_id));
		
		if (!$force) {
			$query->where($db->qn('status').' != '.$db->q('denied'));
		}
		
		$db->setQuery($query);
		if ($id = $db->loadResult()) {
			$transaction = JTable::getInstance('Transaction', 'RSMembershipTable');
			$transaction->load($id);
		} else {
			return false;
		}		

		// Set the transaction to 'denied'
		$updateTransaction = JTable::getInstance('Transaction', 'RSMembershipTable');
		$updateTransaction->save(array(
			'id' 		=> $transaction->id,
			'status' 	=> 'denied'
		));

		$params 		= RSMembershipHelper::parseParams($transaction->params);
		$membership_id 	= false;
		switch ($transaction->type)
		{
			case 'renew':
			case 'new':
				if (!empty($params['membership_id']))
					$membership_id = $params['membership_id'];
			break;
			
			case 'upgrade':
				if (!empty($params['to_id']))
					$membership_id = $params['to_id'];
			break;
			
			case 'addextra':
				if (!empty($params['id']))
				{
					$query->clear();
					$query->select($db->qn('membership_id'))
						  ->from($db->qn('#__rsmembership_membership_subscribers'))
						  ->where($db->qn('id').' = '.$db->q((int) $params['id']));
					$db->setQuery($query);
					$membership_id = $db->loadResult();
				}
			break;
		}
		
		// start sending emails
		if ($membership_id)
		{
			$query->clear();
			$query->select('*')
				  ->from($db->qn('#__rsmembership_memberships'))
				  ->where($db->qn('id').' = '.$db->q((int) $membership_id).' AND ( '.$db->qn('user_email_denied_subject').' != '.$db->q('').' OR '.$db->qn('admin_email_denied_subject').' != '.$db->q('').' )');
			$db->setQuery($query);

			if ($membership = $db->loadObject())
			{
				$jconfig = JFactory::getConfig();
				$membership->user_email_from_addr = $membership->user_email_use_global ? $jconfig->get('mailfrom') : $membership->user_email_from_addr;
				$membership->user_email_from 	  = $membership->user_email_use_global ? $jconfig->get('fromname') : $membership->user_email_from;

				$userEmail  = array('from' => '', 'fromName' => '', 'recipient' => '', 'subject' => '', 'body' => '', 'mode' => '', 'cc' => '', 'bcc' => '', 'attachments' => '', 'replyto' => '', 'replytoname' => '');
				$adminEmail = array('from' => '', 'fromName' => '', 'recipient' => '', 'subject' => '', 'body' => '', 'mode' => '', 'cc' => '', 'bcc' => '', 'attachments' => '', 'replyto' => '', 'replytoname' => '');

				// placeholders
				$user_data 	= unserialize($transaction->user_data);
				$user_email = $transaction->user_email;
				$replacements = array(
					'{membership}' 		=> $membership->name,
					'{email}'			=> $user_email,
					'{name}'			=> $user_data->name,
					'{username}'		=> (isset($user_data->username) ? $user_data->username : ''),
					'{price}' 			=> RSMembershipHelper::getPriceFormat($transaction->price),
					'{coupon}' 			=> $transaction->coupon,
					'{payment}' 		=> $transaction->gateway, 
					'{transaction_id}' 	=> $transaction->id
				);
				$replace 	= array_keys($replacements);
				$with 		= array_values($replacements);
				
				$fields 			= RSMembership::getCustomFields();
				$membership_fields  = RSMembership::getCustomMembershipFields($membership_id);
				$all_fields 		= array_merge($fields,$membership_fields);

				foreach ($all_fields as $field)
				{
					$name 	= $field->name;
					$replace[] = '{'.$name.'}';
					$object = (isset($user_data->fields[$name]) ? 'fields' : 'membership_fields');
					if ( isset($user_data->fields[$name]) || isset($user_data->membership_fields[$name])) 
						$with[] = is_array($user_data->{$object}[$name]) ? implode("\n", $user_data->{$object}[$name]) : $user_data->{$object}[$name];
					else
						$with[] = '';
				}
				
				// user emails
				if (!empty($membership->user_email_from_addr) && $membership->user_email_denied_subject != '')
				{
					// start sending emails
					// from address
					$userEmail['from'] = $membership->user_email_from_addr;
					// from name
					$userEmail['fromName'] = $membership->user_email_from;
					// recipient
					$userEmail['recipient'] = $user_email; // user email
					// subject
					$userEmail['subject'] = str_replace($replace, $with, $membership->user_email_denied_subject);
					// body
					$userEmail['body'] = str_replace($replace, $with, $membership->user_email_denied_text);
					// mode
					$userEmail['mode'] = $membership->user_email_mode; 
					// cc
					$userEmail['cc'] = null;
					// bcc
					$userEmail['bcc'] = null;
					// attachments
					$userEmail['attachments'] = null;
					// reply to
					$userEmail['replyto'] = $userEmail['from'];
					// reply to name
					$userEmail['replytoname'] = $userEmail['fromName'];
				}
				
				// admin emails
				if (!empty($membership->admin_email_to_addr) && $membership->admin_email_denied_subject != '')
				{
					// from address
					$adminEmail['from'] = (trim($membership->admin_email_from_addr) != '' ? $membership->admin_email_from_addr : $user_email);
					// from name
					$adminEmail['fromName'] = $user_data->name;
					// recipient
					$adminEmail['recipient'] = $membership->admin_email_to_addr;
					// subject
					$adminEmail['subject'] = str_replace($replace, $with, $membership->admin_email_denied_subject);
					// body
					$adminEmail['body'] = str_replace($replace, $with, $membership->admin_email_denied_text);
					// mode
					$adminEmail['mode'] = $membership->admin_email_mode;
					// cc
					$adminEmail['cc'] = null;
					// bcc
					$adminEmail['bcc'] = null;
					// attachments
					$adminEmail['attachments'] = null;
					// reply to
					$adminEmail['replyto'] = $adminEmail['from'];
					// reply to name
					$adminEmail['replytoname'] = $adminEmail['fromName'];
				}
				
				// send to user
				if (!empty($membership->user_email_from_addr) && $membership->user_email_denied_subject != '')
					RSMembershipHelper::sendMail($userEmail['from'], $userEmail['fromName'], $userEmail['recipient'], $userEmail['subject'], $userEmail['body'], $userEmail['mode'], $userEmail['cc'], $userEmail['bcc'], $userEmail['attachments'], $userEmail['replyto'], $userEmail['replytoname']);
				
				// send to admin
				if (!empty($membership->admin_email_to_addr) && !empty($membership->admin_email_denied_subject))
					RSMembershipHelper::sendMail($adminEmail['from'], $adminEmail['fromName'], $adminEmail['recipient'], $adminEmail['subject'], $adminEmail['body'], $adminEmail['mode'], $adminEmail['cc'], $adminEmail['bcc'], $adminEmail['attachments'], $adminEmail['replyto'], $adminEmail['replytoname']);
			}
		}
		
		return true;
	}
	
	public static function checkUser($email) {
		static $cache = array();
		
		$email = strtolower(trim($email));
		if (!isset($cache[$email])) {
			$db 	= JFactory::getDBO();
			$query	= $db->getQuery(true);
			
			$query->select($db->qn('id'))
				  ->from($db->qn('#__users'))
				  ->where($db->qn('email').' = '.$db->q($email));
			$db->setQuery($query);
			$cache[$email] = $db->loadResult();
		}
		
		return $cache[$email];
	}
	
	public static function createUser($email, $data)
	{
		if (empty($email)) return false;
		
		$email = strtolower(trim($email));
		
		$lang = JFactory::getLanguage();
		$lang->load('com_user', JPATH_SITE, null, true);
		$lang->load('com_user', JPATH_ADMINISTRATOR, null, true);
		$lang->load('com_users', JPATH_ADMINISTRATOR, null, true);
		$lang->load('com_rsmembership', JPATH_SITE);
		
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		
		if ($user_id = RSMembership::checkUser($email))
		{
			$user 	  = JFactory::getUser($user_id);
			$password = JText::_('COM_RSMEMBERSHIP_HIDDEN_PASSWORD_TEXT');
			RSMembership::sendUserEmail($user, $password, $data->fields, false);
			
			return $user_id;
		}

		jimport('joomla.user.helper');
		// Get required system objects
		$user = clone(JFactory::getUser(0));
		
		if (!RSMembershipHelper::getConfig('full_email_username')) {
			@list($username, $domain) = explode('@', $email);
		}
		else {
			$username = $email;
		}
		
		if (RSMembershipHelper::getConfig('choose_username') && !empty($data->username))
			$username = $data->username;
		
		
		$query->clear();
		$query->select($db->qn('id'))->from($db->qn('#__users'))->where($db->qn('username').' LIKE '.$db->q($username));
		$db->setQuery($query, 0, 1);
		if (preg_match( "#[<>\"'%;()&]#i", $username) || strlen(utf8_decode($username )) < 2)
		{
			$username = JFilterOutput::stringURLSafe($data->name);
			if (strlen($username) < 2) 
				$username = str_pad($username, 2, mt_rand(0,9));
		}
		
		while ($db->loadResult())
		{
			$username .= mt_rand(0,9);
			
			$query->clear();
			$query->select($db->qn('id'))->from($db->qn('#__users'))->where($db->qn('username').' LIKE '.$db->q($username));
			$db->setQuery($query, 0, 1);
		}
		
		// Bind the post array to the user object
		$post = array();
		$post['name'] = $data->name;
		if (trim($post['name']) == '')
			$post['name'] = $email;
		$post['email'] = $email;
		$post['username'] = $username;
		$post['password']  = JUserHelper::genRandomPassword(8);
		$original = $post['password'];
		$post['password2'] = $post['password'];

		if (!$user->bind($post, 'usertype'))
			JError::raiseError(500, $user->getError());

		// Set some initial user values
		$user->set('id', 0);

		$usersConfig = JComponentHelper::getParams('com_users');
		$user->set('groups', array($usersConfig->get('new_usertype', 2)));

		$date = JFactory::getDate();
		$user->set('registerDate', $date->toSql());

		// If user activation is turned on, we need to set the activation information
		$useractivation = $usersConfig->get('useractivation');
		if ($useractivation == 1 || $useractivation == 2)
		{
			$user->set('activation', JApplication::getHash($post['password']));
			$user->set('block', '1');
		}
		$user->set('lastvisitDate', '0000-00-00 00:00:00');

		// If there was an error with registration, set the message
		if (!$user->save())
		{
			return false;
			JError::raiseWarning('', JText::_($user->getError()));
		}
		
		// Send registration confirmation mail
		$password = $original;
		// Disallow control chars in the email
		$password = preg_replace('/[\x00-\x1F\x7F]/', '', $password);
		
		if (RSMembershipHelper::getConfig('choose_password') && !empty($data->password))
		{
			$query->clear();
			$query->update($db->qn('#__users'))->set($db->qn('password').' = '.$db->q($data->password))->where($db->qn('id').' = '.$db->q($user->get('id')));
			$db->setQuery($query);
			$db->execute();

			$password = JText::_('COM_RSMEMBERSHIP_HIDDEN_PASSWORD_TEXT');
		}

		RSMembership::sendUserEmail($user, $password, $data->fields);
		RSMembership::createUserData($user->get('id'), $data->fields);

		return $user->get('id');
	}

	public static function sendUserEmail(&$user, $password, $fields, $new_user=true)
	{
		$app = JFactory::getApplication();
		$lang = JFactory::getLanguage();
		$lang->load('com_rsmembership', JPATH_SITE);

		$db 		= JFactory::getDBO();
		$query		= $db->getQuery(true);

		$name 		= $user->get('name');
		$email 		= $user->get('email');
		$username 	= $user->get('username');

		$usersConfig 	= JComponentHelper::getParams('com_users');
		$sitename 		= $app->getCfg('sitename');
		$useractivation = $usersConfig->get('useractivation');
		$mailfrom 		= $app->getCfg('mailfrom');
		$fromname 		= $app->getCfg('fromname');
		$siteURL		= JURI::base();
		if (JPATH_BASE == JPATH_ADMINISTRATOR && strpos($siteURL, '/administrator') !== false)
			$siteURL = substr($siteURL, 0, -14);

		$subject = JText::sprintf('COM_RSMEMBERSHIP_NEW_EMAIL_SUBJECT', $name, $sitename);
		$subject = html_entity_decode($subject, ENT_QUOTES);

		if (($useractivation == 1 || $useractivation == 2) && $new_user)
		{
			$activation_url = '<a href="'.$siteURL.'index.php?option=com_users&task=registration.activate&token='.$user->get('activation').'">'.$siteURL.'index.php?option=com_users&task=registration.activate&token='.$user->get('activation').'</a>';

			$message = JText::sprintf('COM_RSMEMBERSHIP_NEW_EMAIL_ACTIVATE', $name, $sitename, $activation_url, $siteURL, $username, $password);
		}
		else
			$message = JText::sprintf('COM_RSMEMBERSHIP_NEW_EMAIL', $name, $sitename, $siteURL, $username, $password);

		$replace 	= array();
		$with 		= array();
		if ($fields)
			foreach ($fields as $field => $value)
			{
				$replace[] = '{'.$field.'}';
				$with[] = is_array($value) ? implode(",", $value) : $value;
			}
		$message = str_replace($replace, $with, $message);
		$message = html_entity_decode($message, ENT_QUOTES);

		// get all admin users
		$query->select($db->qn('name').', '.$db->qn('email').', '.$db->qn('sendEmail'))->from($db->qn('#__users'))->where($db->qn('sendEmail').' = '.$db->q('1'));
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		// Send email to user
		RSMembershipHelper::sendMail($mailfrom, $fromname, $email, $subject, $message, true);

		$lang->load('com_users', JPATH_SITE);
		if (($usersConfig->get('useractivation') < 2) && ($usersConfig->get('mail_to_admin') == 1)) {
			foreach( $rows as $row )
			{
				$data 				= $user->getProperties();
				$config 			= JFactory::getConfig();
				$data['fromname']	= $config->get('fromname');
				$data['mailfrom']	= $config->get('mailfrom');
				$data['sitename']	= $config->get('sitename');
				$data['siteurl']	= JURI::root();
				
				$emailSubject 	= JText::sprintf('COM_USERS_EMAIL_ACCOUNT_DETAILS', $data['name'], $data['sitename']);
				$emailBodyAdmin = JText::sprintf('COM_USERS_EMAIL_REGISTERED_NOTIFICATION_TO_ADMIN_BODY', $data['name'], $data['username'], $data['siteurl']);
			
				RSMembershipHelper::sendMail($data['mailfrom'], $data['fromname'], $row->email, $emailSubject, $emailBodyAdmin);
			}
		}
	}

	public static function createUserData($user_id, $post)
	{
		$db 	 = JFactory::getDBO();
		$query	 = $db->getQuery(true);
		$user_id = (int) $user_id;

		// check if user_id exits
		$query->select('user_id')->from($db->qn('#__rsmembership_subscribers'))->where($db->qn('user_id').' = '.$db->q($user_id));
		$db->setQuery($query);

		if ( !$db->loadResult() ) {
			$query->clear();
			$query->insert($db->qn('#__rsmembership_subscribers'))->set($db->qn('user_id') . ' = ' . $db->q($user_id));
			$db->setQuery($query);
			$db->execute();
		}

		$columns = array();

		// load fields
		$fields = RSMembership::getCustomFields();
		
		$exceptions = array('checkbox', 'radio');
		foreach ( $fields as $field ) 
		{
			if (!isset($post[$field->name]) && !in_array($field->type, $exceptions)) 
				continue;
			if (!isset($post[$field->name])) {
				$post[$field->name] = '';
			}
			else if (is_array($post[$field->name])) 
				$post[$field->name] = implode("\n", $post[$field->name]);

			$columns[] = $db->qn('f'.$field->id).' = '.$db->q($post[$field->name]);
		}

		if ( !empty($columns) ) {
			$query->clear();
			$query->update($db->qn('#__rsmembership_subscribers'))->set( implode(', ', $columns) )->where( $db->qn('user_id') . ' = ' . $db->q($user_id) );
			$db->setQuery($query);
			$db->execute();
		}
	}

	public static function updateGid($user_id, $gid, $unblock=false, $action='add')
	{
		try {
			jimport('joomla.user.helper');
			$user_id 	 = (int) $user_id;

			if (!is_array($gid))
				$gid = explode(',', $gid);

			JArrayHelper::toInteger($gid);

			// old version
			if (RSMembershipHelper::getConfig('replace_gid')) 
			{
				JUserHelper::setUserGroups($user_id, $gid);
			} else {
				foreach ($gid as $group) {
					if ($action == 'add') {
						self::syslog('gid', "Adding user($user_id) to group($group)");
						JUserHelper::addUserToGroup($user_id, $group);
					} elseif ($action == 'remove') {
						self::syslog('gid', "Removing user($user_id) from group($group)");
						JUserHelper::removeUserFromGroup($user_id, $group);
					}
				}
			}

			if ($unblock) 
				RSMembership::enableUser($user_id);
		} catch (Exception $e) {
			self::syslog('gid', "Error on changing group for user($user_id). Message: ".$e->getMessage());
		}
	}

	public static function disableUser($user_id)
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);

		$query->update($db->qn('#__users'))->set($db->qn('block').' = '.$db->q('1'))->where($db->qn('id').' = '.$db->q($user_id));
		$db->setQuery($query);
		$db->execute();
	}
	
	public static function enableUser($user_id)
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		
		$query->update($db->qn('#__users'))->set($db->qn('block').' = '.$db->q('0'))->where($db->qn('id').' = '.$db->q($user_id).' AND '.$db->qn('block').' = '.$db->q('1').' AND '.$db->qn('lastvisitDate').' != '.$db->q('0000-00-00 00:00:00'));
		$db->setQuery($query);
		$db->execute();
	}
	
	public static function updateIdev($params=array())
	{
		if (!isset($params['profile']))
			$params['profile'] = 72198;
			
		$get = array();
		foreach ($params as $param => $value)
			$get[] = urlencode($param).'='.urlencode($value);
		
		$url = RSMembershipHelper::getConfig('idev_url').'sale.php?'.implode('&', $get);
		
		if (function_exists('curl_init'))
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:13.0) Gecko/20100101 Firefox/13.0.1');
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			$result = curl_exec($ch);
			$error = curl_error($ch);
			$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			if ($result === false || $code != 200)
				return array('success' => 0, 'error' => $error, 'result' => $result, 'code' => $code, 'url' => $url);
			
			return array('success' => 1, 'result' => $result, 'url' => $url);
		}
		
		return array('success' => 0, 'error' => JText::_('COM_RSMEMBERSHIP_CURL_NOT_AVAILABLE'), 'code' => 0, 'url' => $url);
	}

	public static function getCustomFields($where = array('published'=>1)) {
		static $fields = array();
		$hash = md5(implode(',',$where));
		
		if (!isset($fields[$hash])) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*')
				  ->from($db->qn('#__rsmembership_fields'));
			// where conditions	  
			foreach ($where as $column => $value) {
				$query->where($db->qn($column).' = '.$db->q($value));
			}
			$query->order($db->qn('ordering').' '.$db->escape('asc'));
			
			$db->setQuery($query);
			$fields[$hash] = $db->loadObjectList();
		}
		return $fields[$hash];
	}
	
	public static function getCustomMembershipFields($membership_id, $where = array('published'=>1)) {
		static $fields = array();
		$hash = md5($membership_id.implode(',',$where));
		
		if (!isset($fields[$hash])) {
			$db		= JFactory::getDBO();
			$query	= $db->getQuery(true);
			$query->select('*')->
				from($db->qn('#__rsmembership_membership_fields'))->
				where($db->qn('membership_id').' = '.$db->q($membership_id));
				// where conditions	  
				foreach ($where as $column => $value) {
					$query->where($db->qn($column).' = '.$db->q($value));
				}
				$query->order($db->qn('ordering').' '.$db->escape('asc'));
			$db->setQuery($query);
			$fields[$hash] = $db->loadObjectList();
		}
		return $fields[$hash];
	}
	
	public static function getUserData($transaction_id) {
		static $user_data = array();
		
		if (!isset($user_data[$transaction_id])) {
			$db		= JFactory::getDBO();
			$query	= $db->getQuery(true);
			$query->select($db->qn('user_data'))->
				from($db->qn('#__rsmembership_transactions'))->
				where($db->qn('id').' = '.$db->q($transaction_id));
			$db->setQuery($query);
			$data = $db->loadResult();
			
			if (!empty($data)) {
				$data = (object) unserialize($data);
			}
			$user_data[$transaction_id] = $data;
		}
		return $user_data[$transaction_id];
	}
	
	public static function sendNotifications($memberships,$cid = null, $resend = false) {
		
		// Get custom fields
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$date 	= JFactory::getDate();
		$fields = RSMembership::getCustomFields();
		$config = RSMembershipConfig::getInstance();
		
		$update_ids = array();
		foreach ($memberships as $membership)
		{
			$date 		= JFactory::getDate();
			$interval 	= $membership->expire_notify_interval;
			$date->modify("+$interval days");
			
			// Select all the subscriptions that match (about to expire)
			$query->clear();
			$query->select($db->qn('u.id', 'user_id'))
				  ->select($db->qn('u.email', 'user_email'))
				  ->select($db->qn('u.name', 'user_name'))
				  ->select($db->qn('u.username', 'user_username'))
				  ->select($db->qn('mu.id', 'muid'))
				  ->select($db->qn('mu.extras'))
				  ->select($db->qn('mu.membership_end'))
				  ->select($db->qn('mu.from_transaction_id'))
				  ->from($db->qn('#__rsmembership_membership_subscribers','mu'))
				  ->join('left', $db->qn('#__users','u') . ' ON ' . $db->qn('mu.user_id') . ' = ' . $db->qn('u.id') )
				  ->where( $db->qn('mu.status') . ' = ' . $db->q(MEMBERSHIP_STATUS_ACTIVE) )
				  ->where($db->qn('mu.published').' = '.$db->q(1));
				  
			if(!$resend) {
				  $query->where( $db->qn('mu.notified') . ' = ' . $db->q($db->getNullDate()) );
			}
			$query->where( $db->qn('mu.membership_end') . ' != ' . $db->q($db->getNulldate()) )
				  ->where( $db->qn('mu.membership_end') . ' < ' . $db->q($date->toSql()) )
				  ->where( $db->qn('mu.membership_id') . ' = ' . $db->q($membership->id) );
				  if($cid!=null && is_array($cid) && count($cid)>0) {
						$ids = implode($db->q(','),$cid);
						$query->where($db->qn('mu.id').' IN ('.$ids.')');
				  }
			$db->setQuery($query, 0, $config->get('expire_emails'));
			$results = $db->loadObjectList();
			
			// No results, next membership
			if (empty($results)) {
				continue;
			}
			
			$now = JFactory::getDate()->toUnix();
			
			$sentToUser = false;
			$sentToAdmin = false;
			foreach ($results as $result)
			{
				$extras = '';
				// Performance check
				if ($result->extras && (strpos($membership->user_email_expire_text.$membership->user_email_expire_subject, '{extras}') !== false || strpos($membership->admin_email_expire_text.$membership->admin_email_expire_subject, '{extras}') !== false))
				{
					$extras = RSMembershipHelper::getExtrasNames($result->extras);
				}

				$expireDate = JFactory::getDate($result->membership_end);
				$expireIn 	= ceil(($expireDate->toUnix() - $now)/86400);
				$placeholders = array(
					'{membership}' 		=> $membership->name,
					'{membership_end}' 	=> RSMembershipHelper::showDate($result->membership_end),
					'{extras}' 			=> $extras,
					'{email}' 			=> $result->user_email,
					'{name}' 			=> $result->user_name,
					'{username}' 		=> $result->user_username,
					'{interval}'		=> $expireIn
				);

				$replace = array_keys($placeholders);
				$with	 = array_values($placeholders);
				
				$query->clear();
				$query->select('*')->from($db->qn('#__rsmembership_subscribers'))->where($db->qn('user_id').' = '.$db->q($result->user_id));
				$db->setQuery($query);
				$user_data_tmp = $db->loadObject();
				
				$user_data = array();
				foreach ($fields as $field)
				{
					$field_id = 'f'.$field->id;
					$user_data[$field->name] = isset($user_data_tmp->{$field_id}) ? $user_data_tmp->{$field_id} : '';
				}
				unset($user_data_tmp);
				
				foreach ($fields as $field)
				{
					$name = $field->name;
					$replace[] = '{'.$name.'}';
					if (isset($user_data[$name]))
						$with[] = is_array($user_data[$name]) ? implode("\n", $user_data[$name]) : $user_data[$name];
					else
						$with[] = '';
				}
				
				$membership_fields = RSMembership::getCustomMembershipFields($membership->id);
				$transaction_user_data = RSMembership::getUserData($result->from_transaction_id);
				
				foreach ($membership_fields as $field)
				{
					$name 	= $field->name;
					$replace[] = '{'.$name.'}';
					if (isset($transaction_user_data->membership_fields[$name])) 
						$with[] = is_array($transaction_user_data->membership_fields[$name]) ? implode("\n", $transaction_user_data->membership_fields[$name]) : $transaction_user_data->membership_fields[$name];
					else
						$with[] = '';
				}
				
				$jconfig = JFactory::getConfig();
				
				if ($membership->user_email_expire_subject)
				{
					$message = str_replace($replace, $with, $membership->user_email_expire_text);
					// from address
					$from = $membership->user_email_use_global ? $jconfig->get('mailfrom') : $membership->user_email_from_addr;
					// from name
					$fromName = $membership->user_email_use_global ? $jconfig->get('fromname') : $membership->user_email_from;
					// recipient
					$recipient = $result->user_email; // user email
					// subject
					$subject = str_replace($replace, $with, $membership->user_email_expire_subject);
					// body
					$body = $message;
					// mode
					$mode = $membership->user_email_mode; 
					// cc
					$cc = null;
					// bcc
					$bcc = null;

					// attachments
					$query->clear();
					$query
						->select($db->qn('path'))
						->from($db->qn('#__rsmembership_membership_attachments'))
						->where($db->qn('membership_id').' = '.$db->q($membership->id))
						->where($db->qn('email_type').' = '.$db->q('user_email_expire'))
						->where($db->qn('published').'='.$db->q('1'))
						->order($db->qn('ordering').' ASC');
					$db->setQuery($query);
					$attachment = $db->loadColumn();

					// reply to
					$replyto = $from;
					// reply to name
					$replytoname = $fromName;
					// send to user
					RSMembershipHelper::sendMail($from, $fromName, $recipient, $subject, $body, $mode, $cc, $bcc, $attachment, $replyto, $replytoname);
					$sentToUser = true;
					self::syslog('expiry-notification', "Membership: ".$membership->name." (".$membership->id.") | Email sent to $recipient (Subject: $subject)");
				}
				
				// admin emails
				if ($membership->admin_email_expire_subject)
				{
					$message = str_replace($replace, $with, $membership->admin_email_expire_text);
					// from address
					$from = (trim($membership->admin_email_from_addr) != '' ? $membership->admin_email_from_addr : $result->user_email);
					// from name
					$fromName = $result->user_name;
					// recipient
					$recipient = $membership->admin_email_to_addr;
					// subject
					$subject = str_replace($replace, $with, $membership->admin_email_expire_subject);
					// body
					$body = $message;
					// mode
					$mode = $membership->admin_email_mode;
					// cc
					$cc = null;
					// bcc
					$bcc = null;
					// attachments
					$attachment = null;
					// reply to
					$replyto = $from;
					// reply to name
					$replytoname = $fromName;
					// send to admin
					if ($subject != '') {
						RSMembershipHelper::sendMail($from, $fromName, $recipient, $subject, $body, $mode, $cc, $bcc, $attachment, $replyto, $replytoname);
						$sentToAdmin = true;
						self::syslog('expiry-notification', "Membership: ".$membership->name." (".$membership->id.") | Admin email sent to $recipient (Subject: $subject)");
					}
				}
				if ($sentToUser || $sentToAdmin) {
					$update_ids[] = $result->muid;
				}
			}
		}
		
		if (!empty($update_ids))
		{
			$query->clear();
			$query->update($db->qn('#__rsmembership_membership_subscribers'))
				  ->set($db->qn('notified').' = '.$db->q($date->toSql()))
				  ->where($db->qn('id').' IN (\''.implode($db->q(','), $update_ids).'\')');
			$db->setQuery($query);
			$db->execute();
			
			return true;
		}
		else return false;
	}
	
	// syslog function
	public static function syslog($type, $message) {
		// date and time of the log
        $time = JFactory::getDate()->toSql();
		
		// get the db object
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		
		if (!empty($type) && !empty($message)) {
			$query->clear();
			$query->insert($db->qn('#__rsmembership_syslog'))
					->set($db->qn('type').' = '.$db->q($type))
					->set($db->qn('message').' = '.$db->q($message))
					->set($db->qn('date').' = '.$db->q($time));
					
			$db->setQuery($query);
			$db->execute();
		}		
    }
	
	public static function checkMembership($id) {
		static $ids = array();
		
		if (!isset($ids[$id])) {
			$db 	= JFactory::getDBO();
			$query	= $db->getQuery(true);
			$query->clear();

			$query
				->select('COUNT('.$db->qn('id').')')
				->from($db->qn('#__rsmembership_memberships'))
				->where($db->qn('id').' = '.$db->q($id));
			$db->setQuery($query);
			
			$ids[$id] = (int) $db->loadResult();
		} 
		return $ids[$id];
	}
}