<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class RSMembershipModelAddExtra extends JModelLegacy
{
	var $_html = '';
	var $transaction_id = 0;
	var $context = 'com_rsmembership.addextra';
	
	function __construct()
	{
		parent::__construct();
		
		$user = JFactory::getUser();
		if ($user->get('guest'))
		{
			$app = JFactory::getApplication();
			$link = JURI::getInstance();
			$link = base64_encode($link);
			$app->redirect(JRoute::_('index.php?option=com_users&view=login&return='.$link, false));
		}
		
		$this->_execute();
	}
	
	function _execute()
	{
		$app 	= JFactory::getApplication();
		$task 	= $app->input->get('task', '', 'string');

		if ($task == 'addextra') 
		{
			$this->_bindId();
		}
		else
		{
			$this->_setId();

			if ($task == 'addextrapayment') 
			{
				// empty session
				$this->_emptySession();

				$membership 	= $this->getMembership();
				$extra 			= $this->getExtra();
				$paymentplugin 	= $app->input->get('payment', 'none', 'cmd');

				// calculate the total price
				$total = $extra->price;

				$user 		= JFactory::getUser();
				$user_id 	= $user->get('id');

				$row 			 = JTable::getInstance('Transaction','RSMembershipTable');
				$row->user_id 	 = $user_id;
				$row->user_email = $user->get('email');

				$this->_data = new stdClass();
				$this->_data->username 	= $user->get('username');
				$this->_data->name 		= $user->get('name');
				$this->_data->email 	= $user->get('email');
				$this->_data->fields 	= RSMembershipHelper::getUserFields($user->get('id'));
				$membership_fields 				= RSMembershipHelper::getTransactionMembershipFields($user->get('id'), $membership->last_transaction_id);
				if (count($membership_fields)) {
					$this->_data->membership_fields = $membership_fields;
				}

				$row->user_data = serialize($this->_data);

				$row->type = 'addextra';
				$params = array();
				$params[] = 'id='.$this->_id;
				$params[] = 'membership_id='.$membership->id;
				$params[] = 'extras='.$extra->id;

				$row->params 	= implode(';', $params); // params, membership, extras etc
				$row->date 		= JFactory::getDate()->toSql();
				$row->ip 		= $_SERVER['REMOTE_ADDR'];
				$row->price 	= $total;
				$row->currency 	= RSMembershipHelper::getConfig('currency');
				$row->hash = '';
				$row->gateway 	= $paymentplugin == 'none' ? 'No Gateway' : RSMembership::getPlugin($paymentplugin);
				$row->status 	= 'pending';

				$this->_html = '';

				// trigger the payment plugin
				$delay = false;
				$args  = array(
					'plugin' => $paymentplugin,
					'data' => &$this->_data,
					'extras' => array(),
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
						$app->redirect(JRoute::_(RSMembershipRoute::ThankYou(), false));
				}
			}
		}
	}

	function _setId()
	{
		$session 		= JFactory::getSession();
		$this->_id 		= (int) $session->get($this->context.'.addextra.cid', 0);
		$this->extra_id = (int) $session->get($this->context.'.addextra.extra_id', 0);
	}

	function _bindId()
	{
		$this->_id 		= JFactory::getApplication()->input->get('cid', 0, 'int');
		$this->extra_id = JFactory::getApplication()->input->get('extra_id', 0, 'int');

		$session = JFactory::getSession();
		$session->set($this->context.'.addextra.cid', $this->_id);
		$session->set($this->context.'.addextra.extra_id', $this->extra_id);
	}

	function _emptySession() 
	{
		$session = JFactory::getSession();
		$session->set($this->context.'.addextra.cid', null);
		$session->set($this->context.'.addextra.extra_id', null);
	}

	function getCid() 
	{
		return JFactory::getApplication()->input->get('cid', 0, 'int');
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
		
		if ( $membership->status > 0 ) 
		{
			JError::raiseWarning(500, JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_NOT_ACTIVE'));
			$app->redirect(JRoute::_(RSMembershipRoute::MyMemberships(), false));
		}
		
		$last_transaction_id = $membership->last_transaction_id;
		
		$query->clear();
		$query
			->select('*')
			->from($db->qn('#__rsmembership_memberships'))
			->where($db->qn('published').' = '.$db->q(1))
			->where($db->qn('id').' = '.$db->q($membership->membership_id));
		$db->setQuery($query);
		$membership = $db->loadObject();
		
		if ($membership) 
		{
			$query->clear();
			$query->select('*')->from($db->qn('#__rsmembership_membership_extras'))->where($db->qn('membership_id').' = '.$db->q($membership->id));
			$db->setQuery($query);
			$this->_extras = $db->loadColumn();
		}
		$membership->last_transaction_id = $last_transaction_id ;
		return $membership;
	}

	function getExtra()
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);

		$extra_value_id = $this->extra_id;
		$extra_value 	= JTable::getInstance('ExtraValue','RSMembershipTable');
		$extra_value->load($extra_value_id);

		$query
			->select('type')
			->from($db->qn('#__rsmembership_extras'))
			->where($db->qn('published').' = '.$db->q(1))
			->where($db->qn('id').' = '.$db->q($extra_value->extra_id));
		$db->setQuery($query);
		$extra_value->type = $db->loadResult();

		return $extra_value;
	}

	function getData()
	{
		$user 	= JFactory::getUser();
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);

		$query
			->select('*')
			->from($db->qn('#__rsmembership_subscribers'))
			->where($db->qn('user_id').' = '.$db->q($user->get('id')));
		$db->setQuery($query);

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