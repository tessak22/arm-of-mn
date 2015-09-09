<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipModelSubscribe extends JModelItem
{	
	protected $db;
	protected $recaptcha_error;
	protected $membership;
	protected $extras = array();
	protected $coupon;
	protected $data;
	
	protected $html;
	
	public function __construct() {
		parent::__construct();
		$this->db = JFactory::getDbo();
	}
	
	public function canSubscribe($user = null) {
		if (!$user) {
			$user = JFactory::getUser();
		}
		
		// If the user is logged in and the membership is unique we must ensure that a new subscription is not allowed
		if (!$user->guest && $this->membership->unique) {
			$db 			= &$this->db;
			$query			= $db->getQuery(true);
			$subscription 	= JTable::getInstance('Membership_Subscriber', 'RSMembershipTable');
			$keys			= array(
								'user_id' 		=> $user->id,
								'membership_id' => $this->membership->id
							);
			
			if ($subscription->load($keys)) {
				$this->setError(JText::_('COM_RSMEMBERSHIP_ALREADY_SUBSCRIBED'));
				return false;
			}
		}
		
		return true;
	}
	
	public function bindMembership($id) {
		// Can we get the JTable Object?
		$membership = JTable::getInstance('Membership', 'RSMembershipTable');
		$error		= '';
		
		// Does a membership with this ID exist?
		if ($membership && $id && $membership->load($id)) {
			// Is the membership published?
			if ($membership->published) {
				$membership->regular_price  		= $membership->price;
				$membership->regular_period 		= $membership->period;
				$membership->regular_period_type 	= $membership->period_type;
				
				// Adjust the period & price if it's a trial
				if ($membership->use_trial_period) {
					$membership->price 			= $membership->trial_price;
					$membership->period 		= $membership->trial_period;
					$membership->period_type 	= $membership->trial_period_type;
				}
				
				$this->membership = $membership;
				return true;
			} else {
				$error = 'COM_RSMEMBERSHIP_MEMBERSHIP_NOT_PUBLISHED';
			}
		} elseif (!$id) {
			$error = 'COM_RSMEMBERSHIP_SESSION_EXPIRED';
		} else {
			$error = 'COM_RSMEMBERSHIP_MEMBERSHIP_NOT_EXIST';
		}
		
		$this->setError(JText::_($error));
		return false;
	}
	
	public function bindExtras($extras) {
		// Do we have extras attached to this membership?
		$db 	= &$this->db;
		$query 	= $db->getQuery(true);
		
		// Load a list of extra IDs attached to this membership ID
		$query->select($db->qn('me.extra_id'))
			  ->from($db->qn('#__rsmembership_membership_extras', 'me'))
			  ->join('left', $db->qn('#__rsmembership_extras', 'e').' ON ('.$db->qn('me.extra_id').'='.$db->qn('e.id').')')
			  ->where($db->qn('me.membership_id').' = '.$db->q($this->membership->id))
			  ->where($db->qn('e.published').' = '.$db->q(1));
		$db->setQuery($query);
		if ($membershipExtras = $db->loadColumn()) {
			// Load a list of extra value IDs attached to this membership's extras.
			$query->clear();
			$query->select($db->qn('id'))
				  ->from($db->qn('#__rsmembership_extra_values'))
				  ->where($db->qn('extra_id').' IN ('.$this->qi($membershipExtras).')')
				  ->where($db->qn('published').'='.$db->q(1));
			$db->setQuery($query);
			$membershipExtraValues = $db->loadColumn();
			
			foreach ($extras as $extra_id => $values) {
				// Does this extra_id exist in our database
				// and is it attached to this membership ID?
				if (in_array($extra_id, $membershipExtras)) {
					// Convert all values to an array for commodity
					if (!is_array($values)) {
						$values = (array) $values;
					}
					
					foreach ($values as $value) {
						// Does this value exist?
						if (in_array($value, $membershipExtraValues)) {
							$this->extras[] = $value;
						}
					}
				}
			}
		}
	}
	
	// @desc qi = quote & implode
	protected function qi($array) {
		$db = &$this->db;
		foreach ($array as $k => $v) {
			$array[$k] = $db->q($v);
		}
		return implode(',', $array);
	}
	
	public function getMembership() {
		return $this->membership;
	}
	
	public function getExtras() {
		static $list = array();
		if (empty($list) && is_array($this->extras)) {
			foreach ($this->extras as $id) {
				$table = JTable::getInstance('ExtraValue', 'RSMembershipTable');
				$table->load($id);
				
				$list[] = $table;
			}
		}

		return $list;
	}
	
	public function getMembershipTerms() {
		if ($this->membership->term_id) {
			$item = JTable::getInstance('Term', 'RSMembershipTable');
			if ($item && $item->load($this->membership->term_id) && $item->published) {
				return $item;
			}
		}
		
		return false;
	}
	
	public function getUseCaptcha() {
		if (!RSMembershipHelper::getConfig('captcha_enabled')) {
			return false;
		}

		$is_logged 			 = !JFactory::getUser()->get('guest');
		$captcha_enabled_for = RSMembershipHelper::getConfig('captcha_enabled_for');
		
		// If Captcha is enabled for unregistered users
		// or Captcha is enabled for registered users
		if ((!$is_logged && in_array(0, $captcha_enabled_for)) || ($is_logged && in_array(1, $captcha_enabled_for))) {
			return true;
		}
		
		return false;
	}
	
	public function getUseBuiltin() {
		return (int) RSMembershipHelper::getConfig('captcha_enabled') === 1;
	}
	
	public function getUseReCaptcha() {
		return (int) RSMembershipHelper::getConfig('captcha_enabled') === 2;
	}
	
	public function getUseReCaptchaNew() {
		return (int) RSMembershipHelper::getConfig('captcha_enabled') === 3;
	}
	
	public function getReCaptchaError() {
		return $this->recaptcha_error;
	}
	
	public function validateCaptcha() {
		$builtin 		= $this->getUseBuiltin();
		$recaptcha 		= $this->getUseReCaptcha();
		$recaptcha_new 	= $this->getUseReCaptchaNew();
		$input			= JFactory::getApplication()->input;
		
		if ($this->getUseCaptcha()) {
			if ($builtin) {
				// Load Captcha
				if (!class_exists('JSecurImage')) {
					require_once JPATH_ADMINISTRATOR.'/components/com_rsmembership/helpers/securimage/securimage.php';
				}
				
				$image = new JSecurImage();
				$code  = $input->get('captcha', '', 'string');
				
				if (!$image->check($code)) {
					$this->setError(JText::_('COM_RSMEMBERSHIP_CAPTCHA_ERROR'));
					return false;
				}
			} elseif ($recaptcha) {
				// Load ReCaptcha
				if (!class_exists('JReCAPTCHA')) {
					require_once JPATH_ADMINISTRATOR.'/components/com_rsmembership/helpers/recaptcha/recaptchalib.php';
				}
				
				$privatekey = RSMembershipHelper::getConfig('recaptcha_private_key');
				$challenge	= $input->get('recaptcha_challenge_field', '', 'string');
				$response	= $input->get('recaptcha_response_field', '', 'string');
				
				$result = JReCAPTCHA::checkAnswer($privatekey, isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '', $challenge, $response);
				if (!$result || !$result->is_valid) {
					if ($result) {
						$this->recaptcha_error = $result->error;
					}
					$this->setError(JText::_('COM_RSMEMBERSHIP_CAPTCHA_ERROR'));
					return false;
				}
			} elseif ($recaptcha_new) {
				$response = $input->get('g-recaptcha-response', '', 'raw');
				$ip		  = $input->server->get('REMOTE_ADDR');
				$secret	  = RSMembershipHelper::getConfig('recaptcha_new_secret_key');
				
				try {
					jimport('joomla.http.factory');
					$http = JHttpFactory::getHttp();
					if ($request = $http->get('https://www.google.com/recaptcha/api/siteverify?secret='.urlencode($secret).'&response='.urlencode($response).'&remoteip='.urlencode($ip))) {
						$json = json_decode($request->body);
					}
				} catch (Exception $e) {
					$this->setError($e->getMessage());
					return false;
				}
				
				if (empty($json->success) || !$json->success) {					
					if (!empty($json) && isset($json->{'error-codes'}) && is_array($json->{'error-codes'})) {
						foreach ($json->{'error-codes'} as $code) {
							$this->setError(JText::_('COM_RSMEMBERSHIP_RECAPTCHA_NEW_ERR_'.str_replace('-', '_', $code)));
							return false;
						}
					}
				}
			}
		}
		
		return true;
	}
	
	public function getHasCoupons() {
		$db 		= &$this->db;
		$query		= $db->getQuery(true);
		$now		= JFactory::getDate()->toSql();
		$membership = $this->getMembership();

		$query->select($db->qn('c.name'))
			  ->select('COUNT('.$db->qn('t.coupon').') AS '.$db->qn('uses'))
			  ->select($db->qn('c.max_uses'))
			 ->from($db->qn('#__rsmembership_coupons', 'c'))
			 ->join('left', $db->qn('#__rsmembership_coupon_items', 'ci').' ON ('.$db->qn('c.id').'='.$db->qn('ci.coupon_id').')')
			 ->join('left', $db->qn('#__rsmembership_transactions', 't').' ON ('.$db->qn('c.name').'='.$db->qn('t.coupon').' AND '.$db->qn('t.coupon').' != '.$db->q('').' AND '.$db->qn('c.max_uses').' > '.$db->q(0).')')
			 ->where('('.$db->qn('ci.membership_id').' = '.$db->q($membership->id).' OR '.$db->qn('ci.membership_id').' IS NULL)')
			 ->where('('.$db->qn('c.date_start').' = '.$db->q($db->getNullDate()).' OR '.$db->qn('c.date_start').' < '.$db->q($now).')')
			 ->where('('.$db->qn('c.date_end').' = '.$db->q($db->getNullDate()).' OR '.$db->qn('c.date_end').' > '.$db->q($now).')')
			 ->where($db->qn('c.published').' = '.$db->q(1))
			 ->group($db->qn('c.name'))
			 ->having('('.$db->qn('max_uses').' > '.$db->qn('uses'). ' OR '.$db->qn('max_uses').' = '.$db->q(0).')');
		$db->setQuery($query);
		
		return $db->loadObject() ? true : false;
	}
	
	public function bindData($data) {
		$membership = $this->getMembership();
		$db		= &$this->db;
		$user 	= JFactory::getUser();
		$guest	= $user->guest;
		
		// Create the empty data
		$this->data = new stdClass();
		
		// Bind username
		if (RSMembershipHelper::getConfig('choose_username')) {
			$username = isset($data['username']) ? $data['username'] : '';
			
			if ($guest) {
				if (empty($username) || strlen(utf8_decode($username)) < 2) {
					$this->setError(JText::_('COM_RSMEMBERSHIP_PLEASE_TYPE_USERNAME'));
					return false;
				}

				$query = $db->getQuery(true);
				$query->select($db->qn('id'))
					  ->from($db->qn('#__users'))
					  ->where($db->qn('username').' = '.$db->q($username));
				$db->setQuery($query);
				if ($db->loadResult()) {
					$this->setError(JText::_('COM_RSMEMBERSHIP_USERNAME_NOT_OK'));
					return false;
				}
			}
			
			$this->data->username = $guest ? $username : $user->username;
		}
		
		// Bind password
		if (RSMembershipHelper::getConfig('choose_password')) {
			$password  = isset($data['password']) ? $data['password'] : '';
			$password2 = isset($data['password2']) ? $data['password2'] : '';
			
			if ($guest) {
				$version = new JVersion();
				// 3.x password strength
				if ($version->isCompatible('3.1.4')) {
					JFactory::getLanguage()->load('com_users', JPATH_SITE);
					$rule = JFormHelper::loadRuleType('password');
					$field = new SimpleXMLElement('<field></field>');
					if (!$rule->test($field, $password)) {
						return false;
					}
				} else {
					if (!strlen($password)) {
						$this->setError(JText::_('COM_RSMEMBERSHIP_PLEASE_TYPE_PASSWORD'));
						return false;
					} elseif (strlen($password) < 6) {
						$this->setError(JText::_('COM_RSMEMBERSHIP_PLEASE_TYPE_PASSWORD_6'));
						return false;
					}
				}
				
				if ($password != $password2) {
					$this->setError(JText::_('COM_RSMEMBERSHIP_PLEASE_CONFIRM_PASSWORD'));
					return false;
				}
			}
			$this->data->password = $guest ? md5($password) : '';
		}
		
		// Bind email
		$email = isset($data['email']) ? $data['email'] : '';
		if ($guest) {
			jimport('joomla.mail.helper');
			if (empty($email) || !JMailHelper::isEmailAddress($email)) {
				$this->setError(JText::_('COM_RSMEMBERSHIP_PLEASE_TYPE_EMAIL'));
				return false;
			}
		}
		$this->data->email = $guest ? $email : $user->email;
		
		// Bind name
		$name = isset($data['name']) ? $data['name'] : '';
		if ($guest && empty($name)) {
			$this->setError(JText::_('COM_RSMEMBERSHIP_PLEASE_TYPE_NAME'));
			return false;
		}
		$this->data->name = $guest ? $name : $user->name;
		
		$sentFields 			= isset($data['fields']) ? $data['fields'] : array();
		$sentMembershipFields 	= isset($data['membership_fields']) ? $data['membership_fields'] : array();
		
		if (isset($data['membership_fields'])) {
			$verifyFields = array_merge($sentFields, $sentMembershipFields);
		}

		$fields 			= RSMembership::getCustomFields();
		$membership_fields 	= RSMembership::getCustomMembershipFields($membership->id);
		$fields 			= array_merge($fields, $membership_fields);
		
		
		foreach ($fields as $field) {
			if (($field->required && empty($verifyFields[$field->name])) ||
				($field->rule && !empty($verifyFields[$field->name]) && is_callable('RSMembershipValidation', $field->rule) && !call_user_func(array('RSMembershipValidation', $field->rule), $verifyFields[$field->name]))) {
				$message = JText::_($field->validation);
				if (empty($message)) {
					$message = JText::sprintf('COM_RSMEMBERSHIP_VALIDATION_DEFAULT_ERROR', JText::_($field->label));
				}
				
				$this->setError($message);
				return false;
				
			}
		}
		$this->data->fields = $sentFields;
		$this->data->membership_fields = $sentMembershipFields;
		
		// Bind an empty coupon for legacy reasons
		$this->data->coupon = '';
		
		return true;
	}
	
	public function bindCoupon($coupon) {
		// Did the customer enter a coupon?
		if ($coupon) {
			$db 	= &$this->db;
			$query	= $db->getQuery(true);
			
			$query->select('*')
				  ->from($db->qn('#__rsmembership_coupons'))
				  ->where($db->qn('name').' = '.$db->q($coupon))
				  ->where($db->qn('published').' = '.$db->q(1));
			$db->setQuery($query);
			if ($coupon = $db->loadObject()) {
				$now 		= JFactory::getDate()->toUnix();
				$nullDate 	= $db->getNullDate();
				
				// Check if promotion hasn't started yet
				if ($coupon->date_start != $nullDate) {
					$start = JFactory::getDate($coupon->date_start)->toUnix();
					if ($start > $now) {
						$this->setError(JText::_('COM_RSMEMBERSHIP_COUPON_CODE_NOT_STARTED'));
						return false;
					}
				}
				
				// Check if promotion expired
				if ($coupon->date_end != $nullDate) {
					$end = JFactory::getDate($coupon->date_end)->toUnix();
					if ($end < $now) {
						$this->setError(JText::_('COM_RSMEMBERSHIP_COUPON_CODE_EXPIRED'));
						return false;
					}
				}
				
				// Check if valid for this membership
				$query->clear();
				$query->select($db->qn('membership_id'))
					  ->from($db->qn('#__rsmembership_coupon_items'))
					  ->where($db->qn('coupon_id').' = '.$db->q($coupon->id));
				$db->setQuery($query);
				$memberships = $db->loadColumn();
				if ($memberships && !in_array($this->membership->id, $memberships)) {
					$this->setError(JText::_('COM_RSMEMBERSHIP_COUPON_CODE_NOT_VALID_FOR_MEMBERSHIP'));
					return false;
				}

				// Check max uses
				if ($coupon->max_uses > 0) {
					$query->clear();
					$query->select('COUNT('.$db->qn('id').')')
						  ->from($db->qn('#__rsmembership_transactions'))
						  ->where($db->qn('status').' = '.$db->q('completed'))
						  ->where($db->qn('coupon') .' = '.$db->q($coupon->name));
					$db->setQuery($query);
					$used = $db->loadResult();
					if ($used >= $coupon->max_uses) {
						$this->setError(JText::_('COM_RSMEMBERSHIP_COUPON_MAX_USAGE'));
						return false;
					}
				}
				
				// Calculate percentage discount
				if ($coupon->discount_type == 0) {
					$coupon->discount_price = $this->membership->price * ($coupon->discount_price / 100);
				}

				// Adjust membership price.
				$this->membership->price -= $coupon->discount_price;
				if ($this->membership->price < 0) {
					$this->membership->price = 0;
				}
				
				// Bind coupon
				$this->coupon = $coupon;
				$this->data->coupon = $coupon->name;
			} else {
				$this->setError(JText::_('COM_RSMEMBERSHIP_COUPON_INVALID'));
				return false;
			}
		}
		
		return true;
	}
	
	public function getTotal() {
		$total = $this->membership->price;
		
		if ($extras = $this->getExtras()) {
			foreach ($extras as $extra) {
				$total += $extra->price;
			}
		}
		
		return $total;
	}
	
	public function saveTransaction($paymentPlugin) {
		// Empty the session, no point in keeping it.
		$this->clearData();
		
		// Empty the HTML variable.
		$this->html = '';
		
		// Get some data.
		$extras 	= $this->getExtras();
		$membership = $this->getMembership();
		$total 		= $this->getTotal();
		$user 		= JFactory::getUser();
		$app		= JFactory::getApplication();
		
		// Asign the user.
		$userId = 0;
		if ($user->guest) {
			// Create the user instantly if this option is enabled.
			if (RSMembershipHelper::getConfig('create_user_instantly')) {
				$userId = RSMembership::createUser($this->data->email, $this->data);
			}
		} else {
			// Grab logged in user's ID.
			$userId = $user->id;
			
			// Update user's custom fields.
			RSMembership::createUserData($userId, $this->data->fields);
		}
		// Create user data object.
		$newData = (object) array(
			'name' 				=> $this->data->name,
			'username' 			=> isset($this->data->username) ? $this->data->username : '',
			'fields' 			=> $this->data->fields,
			'membership_fields' => $this->data->membership_fields,
		);

		if (!empty($this->data->password)) {
			$newData->password = $this->data->password;
		}
		
		// Create transaction params array.
		$params = array('membership_id='.$membership->id);
		if ($this->extras) {
			$params[] = 'extras='.implode(',', $this->extras);
		}
		$params	= implode(';', $params);
		
		// Create the JTable object.
		$row = JTable::getInstance('Transaction', 'RSMembershipTable');
		$row->bind(array(
			'user_id' 		=> $userId,
			'user_email' 	=> $this->data->email,
			'user_data'		=> serialize($newData),
			'type'			=> 'new',
			'params'		=> $params,
			'date'			=> JFactory::getDate()->toSql(),
			'ip'			=> isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
			'price'			=> $total,
			'coupon'		=> $this->data->coupon,
			'currency'		=> RSMembershipHelper::getConfig('currency'),
			'gateway'		=> $paymentPlugin == 'none' ? 'No Gateway' : RSMembership::getPlugin($paymentPlugin),
			'status'		=> 'pending'
		));

		// Trigger the payment plugin
		$delay = false;
		$args  = array(
			'plugin' 		=> $paymentPlugin,
			'data' 			=> &$this->data,
			'extras'		=> $extras,
			'membership' 	=> $membership,
			'transaction' 	=> &$row,
			'html' 			=> &$this->html
		);

		$returns = $app->triggerEvent('onMembershipPayment', $args);
		
		// PHP 5.4 fix...
		if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
			foreach ($returns as $value) {
				if ($value) {
					$this->html = $value;
				}
			}
		}
		
		$properties = $row->getProperties();
		$returns = $app->triggerEvent('delayTransactionStoring', array(array('plugin' => $paymentPlugin, 'properties' => &$properties, 'delay' => &$delay)));

		// PHP 5.4 fix...
		if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
			foreach ($returns as $value) {
				if ($value) {
					$delay = true;
				}
			}
		}
		
		// Plugin can delay the transaction storing
		if (!$delay) {
			// Store the transaction
			$row->store();

			// Finalize the transaction (send emails)
			RSMembership::finalize($row->id);
			
			// Approve the transaction
			if ($row->status == 'completed' || (!$this->showPaymentOptions() && $membership->activation != MEMBERSHIP_ACTIVATION_MANUAL) || $membership->activation == MEMBERSHIP_ACTIVATION_INSTANT) {
				RSMembership::approve($row->id, true);
			}
		}
		
		return $row;
	}
	
	public function showPaymentOptions() {
		$total 			= $this->getTotal();
		$membership 	= $this->getMembership();
		$showPayments 	= false;
		
		// Do we have an amount to pay? If we do, show the payment options.
		if ((float) $total) {
			$showPayments = true;
		} else {
			// Trials can be sent to the payment gateway as well
			// Only if we have something to pay afterwards (regular price or renewal price)
			if ($membership->recurring && $membership->use_trial_period && ((float) $membership->regular_price || ($membership->use_renewal_price && (float) $membership->renewal_price))) {
				$showPayments = true;
			}
		}
		
		return $showPayments;
	}
	
	public function storeData($params) {
		$session = JFactory::getSession();
	
		$context = 'com_rsmembership.subscribe.';
		$session->set($context.'id', $params['id']);
		
		$newcontext = $context.$params['id'].'.';
		$session->set($newcontext.'extras', $params['extras']);
		$session->set($newcontext.'data', 	$params['data']);
		$session->set($newcontext.'coupon', $params['coupon']);
	}
	
	public function markCorrectData($id) {
		$session = JFactory::getSession();
		
		$context = 'com_rsmembership.subscribe.';
		$session->set($context.$id.'.correct', 1);
	}
	
	public function isCorrectData() {
		$session = JFactory::getSession();
		$id		 = $this->membership->id;
		$context = 'com_rsmembership.subscribe.'.$id.'.';
		
		if ($session->get($context.'correct', 0)) {
			return true;
		} else {
			$this->setError(JText::_('COM_RSMEMBERSHIP_THERE_WAS_AN_ERROR'));
			return false;
		}
	}
	
	// @desc Returns an array with data from the session.
	public function getData() {
		$session = JFactory::getSession();
		$params	 = array(
			'cid' => 0
		);
		
		$context = 'com_rsmembership.subscribe.';
		if ($id = $session->get($context.'id')) {
			$params['cid'] = $id;
		}
		
		if ($params['cid']) {
			$newcontext = $context.$params['cid'].'.';
			
			// Retrieve selected extras
			if ($extras = $session->get($newcontext.'extras')) {
				$params['rsmembership_extra'] = $extras;
			}
			
			// Retrieve coupon
			if ($coupon = $session->get($newcontext.'coupon')) {
				$params['coupon'] = $coupon;
			}
			
			// Retrieve data
			if ($data = $session->get($newcontext.'data')) {
				if (isset($data['username'])) {
					$params['username'] = $data['username'];
				}
				if (isset($data['email'])) {
					$params['email'] = $data['email'];
				}
				if (isset($data['name'])) {
					$params['name'] = $data['name'];
				}
				if (isset($data['password'])) {
					$params['password'] = $data['password'];
				}
				if (isset($data['password2'])) {
					$params['password2'] = $data['password2'];
				}
				if (isset($data['fields'])) {
					$params['rsm_fields'] = $data['fields'];
				}
				if (isset($data['membership_fields'])) {
					$params['rsm_membership_fields'] = $data['membership_fields'];
				}
			}
		}
		
		return $params;
	}
	
	public function clearData() {
		$session = JFactory::getSession();
		$context = 'com_rsmembership.subscribe.';
		if ($id = $session->get($context.'id')) {
			$session->clear($context.'id');
			
			$newcontext = $context.$id.'.';
			$session->clear($newcontext.'correct');
			$session->clear($newcontext.'extras');
			$session->clear($newcontext.'data');
			$session->clear($newcontext.'coupon');
		}
	}
	
	public function checkUsername() {
		// Get vars
		$jinput		= JFactory::getApplication()->input;
		$db 		= JFactory::getDBO();
		$query		= $db->getQuery(true);

		$username 	= $jinput->get('username', '', 'string');
		
		$username   = preg_replace('#[<>"\'%;()&\\\\]|\\.\\./#', '', $username);
		$name		= strtolower($jinput->get('name', '', 'string'));

		if ( strlen($name) < 2 ) 
			$name = '';
		$email		  = strtolower($jinput->get('email', '', 'string'));
		if ( strlen($email) < 2 ) 
			$email = '';

		// Keep the username intact
		$new_username = $username;

		// Local flags
		$used_name	  = false;
		$used_email	  = false;
		$reverted	  = false;

		// Return
		$suggestions  = array();

		// Check if username is available
		$query->select('Count('.$db->qn('id').')')
			  ->from($db->qn('#__users'))
			  ->where($db->qn('username').' = '.$db->q($new_username));
		$db->setQuery($query);
		
		while ( ( $num_rows = $db->loadResult() ) || count($suggestions) < 3)
		{
			// Add only if no rows are found
			if (!$num_rows && !in_array($new_username, $suggestions))
				$suggestions[] = $new_username;
			
			// Use a variation of the name, if available
			if ($name && !$used_name)
			{
				$used_name = true;
				$reverted = false;
				$new_username = str_replace('-', '_', JFilterOutput::stringURLSafe($name));
			}
			// Use a variation of the email, if available
			elseif ($email && !$used_email)
			{
				$used_email = true;
				$reverted = false;
				$new_username = str_replace('-', '_', JFilterOutput::stringURLSafe($email));
			}
			// Add random numbers to the username
			else
			{
				if (($used_name || $used_email) && !$reverted)
				{
					$reverted = true;
					$new_username = $username;
				}
				$new_username .= mt_rand(0,9);
			}
			
			if (strlen($new_username) < 2)
				$new_username = str_pad($new_username, 2, '_', STR_PAD_RIGHT);
			
			$query->clear();
			$query->select($db->qn('id'))
				  ->from($db->qn('#__users'))
				  ->where($db->qn('username').' = '.$db->q($new_username));
			$db->setQuery($query);
		}
		
		return $suggestions;
	}
	
	public function getHTML() {
		return $this->html;
	}
}