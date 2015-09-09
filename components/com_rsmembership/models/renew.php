<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class RSMembershipModelRenew extends JModelLegacy
{
	var $_html 			= '';
	var $transaction_id = 0;
	var $context 		= 'com_rsmembership';

	function __construct() 
	{
		parent::__construct();

		$user = JFactory::getUser();
		if ($user->get('guest'))
		{
			$app 	= JFactory::getApplication();
			$link 	= base64_encode(JURI::getInstance());

			$app->redirect(JRoute::_('index.php?option=com_users&view=login&return='.$link, false));
		}
		
		$this->_execute();
	}
	
	function _execute()
	{
		$app 	= JFactory::getApplication();
		$jinput = $app->input;

		$task 	= $jinput->get('task', '', 'cmd');
		
		if ( $task == 'renew' ) 
		{
			$this->_bindId();
		}
		else 
		{
			$this->_setId();

			if ( $task == 'renewpayment' ) 
			{
				// empty session
				$this->_emptySession();
				
				$membership 	= $this->getMembership();
				$extras 		= $this->getExtras();
				$paymentplugin 	= $jinput->get('payment', 'none', 'cmd');
				
				// calculate the total price
				$total = 0;
				$total += $membership->price;
				foreach ( $extras as $extra ) 
					$total += $extra->price;

				$user 		= JFactory::getUser();
				$user_id 	= $user->get('id');
				
				$row 			 = JTable::getInstance('Transaction','RSMembershipTable');
				$row->user_id 	 = $user_id;
				$row->user_email = $user->get('email');
				
				$this->_data = new stdClass();
				$this->_data->username 			= $user->get('username');
				$this->_data->name 				= $user->get('name');
				$this->_data->email 			= $user->get('email');	
				
				$membership_data =  $this->getSentData();
				
				if (isset($membership_data['custom_fields'])) {
					$this->_data->fields = $membership_data['custom_fields'];
				}
				
				if (isset($membership_data['membership_fields'])) {
					$this->_data->membership_fields = $membership_data['membership_fields'];
				}
				$row->user_data 		= serialize($this->_data);
				
				$row->user_data = serialize($this->_data);
				
				$row->type 	= 'renew';
				$params 	= array();
				$params[] 	= 'id='.$this->_id;
				$params[] 	= 'membership_id='.$membership->id;
				if ( is_array($this->_extras) && !empty($this->_extras) ) 
					$params[] = 'extras='.implode(',', $this->_extras);

				$row->params 	= implode(';', $params); // params, membership, extras etc
				$date 			= JFactory::getDate();
				$row->date 		= JFactory::getDate()->toSql();
				$row->ip 		= $_SERVER['REMOTE_ADDR'];
				$row->price 	= $total;
				$row->currency 	= RSMembershipHelper::getConfig('currency');
				$row->hash 		= '';
				$row->gateway 	= $paymentplugin == 'none' ? 'No Gateway' : RSMembership::getPlugin($paymentplugin);
				$row->status 	= 'pending';

				$this->_html = '';

				// trigger the payment plugin
				$delay = false;
				$args  = array(
					'plugin' => $paymentplugin,
					'data' => &$this->_data,
					'extras' => $extras,
					'membership' => $membership,
					'transaction' => &$row,
					'html' => &$this->_html
				);

				// trigger the payment plugin
				$returns = $app->triggerEvent('onMembershipPayment', $args);
				
				// PHP 5.4 fix...
				if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
					foreach ($returns as $value) {
						if ($value) {
							$this->_html = $value;
						}
					}
				}
				
				$properties = $row->getProperties();
				$returns = $app->triggerEvent('delayTransactionStoring', array(array('plugin' => $paymentplugin, 'properties' => &$properties, 'delay' => &$delay)));

				// PHP 5.4 fix...
				if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
					foreach ($returns as $value) {
						if ($value) {
							$delay = true;
						}
					}
				}
				
				// plugin can delay the transaction storing
				if ( !$delay ) 
				{
					// store the transaction
					$row->store();

					// store the transaction id
					$this->transaction_id = $row->id;
					
					// finalize the transaction (send emails)
					RSMembership::finalize($this->transaction_id);
					
					// approve the transaction
					if ( $row->status == 'completed' || ($row->price == 0 && $membership->activation != 0) )
						RSMembership::approve($this->transaction_id, true);
					
					if ( $row->price == 0 ) 
						$app->redirect(JRoute::_('index.php?option=com_rsmembership&task=thankyou', false));
				}
			}
		}
	}
	
	function _setId()
	{
		$session = JFactory::getSession();
		$this->_id = (int) $session->get($this->context.'.renew.cid', 0);
	}
	
	function _bindId()
	{
		$this->_id 	= JFactory::getApplication()->input->get('cid', 0, 'int');

		JFactory::getSession()->set($this->context.'.renew.cid', $this->_id);
	}
	
	function _emptySession()
	{
		JFactory::getSession()->set($this->context.'.renew.cid', null);
	}

	function getCid()
	{
		return JFactory::getApplication()->input->get('cid', 0, 'int');
	}
	
	public function storeData($params) {
		$session = JFactory::getSession();
		$session->set($this->context.'.renew.membership_fields', $params['membership_fields']);
		$session->set($this->context.'.renew.custom_fields', $params['custom_fields']);
	}
	
	public function getSentData() {
		$session = JFactory::getSession();
		$params	 = array();
			
		if ($membership_fields = $session->get($this->context.'.renew.membership_fields')) {
				$params['membership_fields'] = $membership_fields;
		}
		if ($custom_fields = $session->get($this->context.'.renew.custom_fields')) {
				$params['custom_fields'] = $custom_fields;
		}
		return $params;
	}

	function getMembership()
	{
		$cid 	= $this->_id;
		$app 	= JFactory::getApplication();
		$user 	= JFactory::getUser();
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);

		$query
			->select($db->qn('membership_id'))
			->select($db->qn('status'))
			->select($db->qn('extras'))
			->select($db->qn('last_transaction_id'))
			->from($db->qn('#__rsmembership_membership_subscribers'))
			->where($db->qn('user_id').' = '.$db->q($user->get('id')))
			->where($db->qn('id').' = '.$db->q($cid));
		$db->setQuery($query);
		$membership = $db->loadObject();

		if ( empty($membership) ) 
			$app->redirect(JRoute::_(RSMembershipRoute::MyMemberships(), false));
		
		if ($membership->status == 1) 
		{
			JError::raiseWarning(500, JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_NOT_EXPIRED'));
			$app->redirect(JRoute::_(RSMembershipRoute::MyMemberships(), false));
		}

		$extras = explode(',', $membership->extras);
		if ( !empty($extras[0]) ) 
			$this->_extras = $extras;
		else
			$this->_extras = array();
			
		$last_transaction_id = $membership->last_transaction_id;

		$query->clear();
		$query
			->select('*')
			->from($db->qn('#__rsmembership_memberships'))
			->where($db->qn('id').' = '.$db->q($membership->membership_id));
		$db->setQuery($query);
		$membership = $db->loadObject();

		if ( $membership->use_renewal_price ) 
			$membership->price = $membership->renewal_price;

		if ( $membership->no_renew ) 
		{
			JError::raiseWarning(500, JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_CANNOT_RENEW'));
			$app->redirect(JRoute::_(RSMembershipRoute::MyMemberships(), false));
		}

		$this->term_id = $membership->term_id;
		$membership->last_transaction_id = $last_transaction_id ;

		return $membership;
	}

	function getMembershipTerms()
	{
		if ( !empty($this->term_id) ) 
		{
			$row = JTable::getInstance('Term','RSMembershipTable');
			$row->load($this->term_id);
			if ( $row->published ) 
				return $row;
		}

		return false;
	}

	function getExtras() 
	{
		$return = array();
		
		if ( is_array($this->_extras) ) 
			foreach ( $this->_extras as $extra ) 
			{
				if ( empty($extra) ) continue;
				$row = JTable::getInstance('ExtraValue','RSMembershipTable');
				$row->load($extra);
				$return[] = $row;
			}			

		return $return;
	}

	function getData() 
	{
		$user 	= JFactory::getUser();
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		
		$query->clear();
		$query
			->select('*')
			->from($db->qn('#__rsmembership_subscribers'))
			->where($db->qn('user_id').' = '.$db->q($user->get('id')));
		$db->setQuery($query);

		return $db->loadObject();
	}

	function getConfig()
	{
		return RSMembershipHelper::getConfig();
	}

	function getUser() 
	{
		$user = JFactory::getUser();
		return $user;
	}

	function getTransactionId()
	{
		return $this->transaction_id;
	}

	function getHTML()
	{		
		return $this->_html;
	}
}