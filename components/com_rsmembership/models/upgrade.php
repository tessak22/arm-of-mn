<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class RSMembershipModelUpgrade extends JModelLegacy
{
	var $_html = '';
	var $transaction_id = 0;
	var $term_id;
	var $to_id = 0;
	var $context = 'com_rsmembership';

	function __construct() 
	{
		parent::__construct();

		$user = JFactory::getUser();
		if ($user->get('guest')) 
		{
			$app 	= JFactory::getApplication();
			$link 	= base64_encode(JURI::getInstance());
			$app->redirect( JRoute::_('index.php?option=com_users&view=login&return='.$link, false) );
		}

		$this->_execute();
	}
	
	function _execute()
	{
		$app 	= JFactory::getApplication();
		$task 	= $app->input->get('task', '', 'cmd');

		if ($task == 'upgrade') 
		{
			$this->_bindId();
		}
		else 
		{
			$this->_setId();

			if ($task == 'upgradepayment') 
			{
				// empty session
				$this->_emptySession();

				$extras 		= array();
				$upgrade 		= $this->getUpgrade();
				$membership 	= $this->getMembership($upgrade->membership_to_id);
				$paymentplugin 	= $app->input->get('payment', 'none', 'cmd');

				// calculate the total price
				$total = $upgrade->price;

				$user 	 = JFactory::getUser();
				$user_id = $user->get('id');

				$row = JTable::getInstance('Transaction','RSMembershipTable');
				$row->user_id = $user_id;
				$row->user_email = $user->get('email');
				
				$this->_data = new stdClass();
				$this->_data->username 	= $user->get('username');
				$this->_data->name 		= $user->get('name');
				$this->_data->email 	= $user->get('email');
				
				$membership_data=  $this->getSentData();
				if (isset($membership_data['custom_fields'])) {
					$this->_data->fields = $membership_data['custom_fields'];
				}
				if ($membership_data['to_id'] == $upgrade->membership_to_id ) {
					if (isset($membership_data['custom_fields'])) {
						$this->_data->membership_fields = $membership_data['membership_fields'];
					}
				}
				$row->user_data 		= serialize($this->_data);
				
				$row->type = 'upgrade';
				$params = array();
				$params[] = 'id='.$this->_id;
				$params[] = 'from_id='.$upgrade->membership_from_id;
				$params[] = 'to_id='.$upgrade->membership_to_id;
				
				$row->params 	= implode(';', $params); // params, membership, extras etc
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

				// trigger the payment plugin
				// plugin can delay the transaction storing
				if (!$delay) 
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
		$session 		= JFactory::getSession();
		$this->_id 		= (int) $session->get($this->context.'.upgrade.cid', 0);
		$this->to_id 	= (int) $session->get($this->context.'.upgrade.to_id', 0);
	}
	
	function _bindId() 
	{
		$jinput 		= JFactory::getApplication()->input;
		$this->_id 		= $jinput->get('cid', 0, 'int');
		$this->to_id 	= $jinput->get('to_id', 0, 'int');

		$session = JFactory::getSession();
		$session->set($this->context.'.upgrade.cid', $this->_id);
		$session->set($this->context.'.upgrade.to_id', $this->to_id);
	}
	
	function _emptySession()
	{
		$session = JFactory::getSession();
		$session->set($this->context.'.upgrade.cid', null);
		$session->set($this->context.'.upgrade.to_id', null);
	}
	
	function getCid() 
	{
		return JFactory::getApplication()->input->get('cid', 0, 'int');
	}
	
	public function storeData($params) {
		$session = JFactory::getSession();
	
		$context = $this->context.'.upgrade.';
		$session->set($context.'id', $params['id']);
		
		$newcontext = $context.$params['id'].'.';
		$session->set($newcontext.'membership_fields', $params['membership_fields']);
		$session->set($context.'custom_fields', $params['custom_fields']);
	}
	
	public function getSentData() {
		$session = JFactory::getSession();
		$params	 = array(
			'to_id' => 0
		);
		
		$context = $this->context.'.upgrade.';
		if ($id = $session->get($context.'id')) {
			$params['to_id'] = $id;
		}
		if ($params['to_id']) {
			$newcontext = $context.$params['to_id'].'.';
			
			if ($membership_fields = $session->get($newcontext.'membership_fields')) {
				$params['membership_fields'] = $membership_fields;
			}
		}
		if ($custom_fields = $session->get($context.'custom_fields')) {
			$params['custom_fields'] = $custom_fields;
		}
		return $params;
	}
	
	function getUpgrade()
	{
		$app 	= JFactory::getApplication();
		$user 	= JFactory::getUser();
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$cid 	= $this->_id;

		$query
			->select($db->qn('membership_id'))
			->select($db->qn('status'))
			->from($db->qn('#__rsmembership_membership_subscribers'))
			->where($db->qn('user_id').' = '.$db->q($user->get('id')))
			->where($db->qn('id').' = '.$db->q($cid));
		$db->setQuery($query);
		$membership = $db->loadObject();

		if ( empty($membership) ) 
			$app->redirect(JRoute::_(RSMembershipRoute::MyMemberships(), false));

		if ($membership->status != MEMBERSHIP_STATUS_ACTIVE) 
		{
			JError::raiseWarning(500, JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_NOT_ACTIVE'));
			$app->redirect(JRoute::_(RSMembershipRoute::MyMemberships(), false));
		}

		$query->clear();
		$query
			->select('u.*')
			->select($db->qn('mfrom.name', 'fromname'))
			->select($db->qn('mto.name', 'toname'))
			->select($db->qn('mto.term_id'))
			->from($db->qn('#__rsmembership_membership_upgrades', 'u'))
			->join('left', $db->qn('#__rsmembership_memberships', 'mfrom').' ON '.$db->qn('mfrom.id').' = '.$db->qn('u.membership_from_id'))
			->join('left', $db->qn('#__rsmembership_memberships', 'mto').' ON '.$db->qn('mto.id').' = '.$db->qn('u.membership_to_id'))
			->where($db->qn('u.membership_from_id').' = '.$db->q($membership->membership_id))
			->where($db->qn('u.membership_to_id').' = '.$db->q($this->to_id))
			->where($db->qn('u.published').' = '.$db->q(1));
		$db->setQuery($query);
		$return = $db->loadObject();
		

		$this->term_id = $return->term_id;
		if ( empty($return) ) 
		{
			$app->redirect(JRoute::_(RSMembershipRoute::MyMemberships(), false));
		}
		
		return $return;
	}
	
	function getMembership ($cid) 
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$cid	= (int) $cid;

		$query
			->select('*')
			->from($db->qn('#__rsmembership_memberships'))
			->where($db->qn('published').' = '.$db->q(1))
			->where($db->qn('id').' = '.$db->q($cid));
		$db->setQuery($query);

		return $db->loadObject();
	}

	function getMembershipTerms()
	{
		if (!empty($this->term_id)) 
		{
			$row = JTable::getInstance('Term','RSMembershipTable');
			$row->load($this->term_id);
			if ($row->published) 
				return $row;
		}

		return false;
	}
	
	function getData()
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$user 	= JFactory::getUser();

		$query
			->select('*')
			->from($db->qn('#__rsmembership_subscribers'))
			->where($db->qn('user_id').' = '.$db->q($user->get('id')));

		return $db->loadObject();
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