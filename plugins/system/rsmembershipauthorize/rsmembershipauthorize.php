<?php
/**
* @package RSMembership!
* @copyright (C) 2009-2015 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class plgSystemRSMembershipAuthorize extends JPlugin
{
	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
		if ($this->canRun() && $this->params) {
			$this->_loadLanguage();
			$name = $this->params->get('payment_name', 'Credit Card');
			RSMembership::addPlugin($this->getTranslation($name), 'rsmembershipauthorize');
		}
	}
	
	protected function getTranslation($text) {
		$lang = JFactory::getLanguage();
		$key  = str_replace(' ', '_', $text);
		if ($lang->hasKey($key)) {
			return JText::_($key);
		} else {
			return $text;
		}
	}

	protected function canRun() {
		static $checked = false;
		
		if (!class_exists('RSMembershipHelper') && file_exists(JPATH_ADMINISTRATOR.'/components/com_rsmembership/helpers/rsmembership.php')) {
			require_once JPATH_ADMINISTRATOR.'/components/com_rsmembership/helpers/rsmembership.php';
		}
		
		return class_exists('RSMembershipHelper') && class_exists('RSMembership');
	}
	
	public function onMembershipCancelPayment($plugin, $data, $membership, &$transaction) 
	{
		if (!$this->canRun()) return;
		if ($plugin != 'rsmembershipauthorize') return false;
		if (!$membership->recurring || $membership->period == 0) return false;

		$content =	"<ARBCancelSubscriptionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
					"<merchantAuthentication>".
					"<name>" . $this->params->get('x_login') . "</name>".
					"<transactionKey>" . $this->params->get('x_tran_key') . "</transactionKey>".
					"</merchantAuthentication>".
					"<refId>0</refId>".
					"<subscriptionId>".$transaction->custom."</subscriptionId>".
					"</ARBCancelSubscriptionRequest>";
		
		$post_url = $this->params->get('mode') ? "https://api.authorize.net/xml/v1/request.api" : "https://apitest.authorize.net/xml/v1/request.api";
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $post_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		curl_close($ch); // close curl object
		
		$log = array();
		$log[] = 'Cancelled by request. Response is below:';
		$log[] = '--- START ---';
		list ($refId, $resultCode, $code, $text, $subscriptionId) = $this->_parseReturn($response);
		$log[] = 'Ref Id: '.$refId;
		$log[] = 'Result Code: '.$resultCode;
		$log[] = 'Code: '.$code;
		$log[] = 'Text: '.$text;
		$log[] = 'Subscription Id: '.$subscriptionId;
		$log[] = '--- END ---';
		
		RSMembership::saveTransactionLog($log, $transaction->id);
		
		if ($resultCode == 'Ok')
			return true;
		
		JError::raiseWarning(500, $text);
		return false;
	}
	
	protected function showControl($label, $input, $help='') {
		$html = '<div class="control-group">'."\n".
					"\t".'<div class="control-label">'."\n".
						"\t\t".$label."\n".
					"\t".'</div>'."\n".
					"\t".'<div class="controls">'."\n".
						"\t\t".$input."\n".
						$help.
					"\t".'</div>'."\n".
				'</div>'."\n";
		return $html;
	}
	
	public function onMembershipPayment($plugin, $data, $extra, $membership, $transaction, $html) {
		if (!$this->canRun()) return;
		if ($plugin != 'rsmembershipauthorize') return false;

		// Generate invoice number
		$this->_setInvoiceNumber();
		
		$document = JFactory::getDocument();

		$document->addScript(JURI::root(true).'/plugins/system/rsmembershipauthorize/rsmembershipauthorize/script.js');
		$document->addStyleSheet(JURI::root(true).'/plugins/system/rsmembershipauthorize/rsmembershipauthorize/style.css');
		
		JHtml::_('behavior.tooltip');
		
		$fields = $this->_getFields();
		
		$transaction->gateway = 'Authorize.Net';

		$actionUrl 		= JRoute::_('index.php?option=com_rsmembership&task=thankyou');
		$authorizeUrl 	= JURI::root(true).'/index.php?option=com_rsmembership&plugin_task=authorize';
		$payMessage		= JText::sprintf('PLG_SYSTEM_RSMEMBERSHIPAUTHORIZE_AUTHORIZE_PRICE_DESC', RSMembershipHelper::getPriceFormat($transaction->price + $this->_getTax($transaction->price)));
		$html = '<form method="post" class="form-horizontal" action="'.$actionUrl.'" onsubmit="return rsm_check_authorize(\''.addslashes($authorizeUrl).'\');">'."\n".
				'<fieldset>'."\n".
					'<legend>'.JText::_('PLG_SYSTEM_RSMEMBERSHIPAUTHORIZE_AUTHORIZE_CARD_INFO').'</legend>'."\n".
					$this->showControl($fields['cc_number'][0], $fields['cc_number'][1], '<span class="help-inline"><img src="'.JURI::root(true).'/plugins/system/rsmembershipauthorize/rsmembershipauthorize/images/cc_logos.gif" /></span>'."\n").
					$this->showControl($fields['csc_number'][0], $fields['csc_number'][1], '<span class="help-inline" id="rsm_whats_csc" onmouseover="rsm_tooltip.show(\'rsm_tooltip\');" onmouseout="rsm_tooltip.hide();">'.JText::_('PLG_SYSTEM_RSMEMBERSHIPAUTHORIZE_AUTHORIZE_WHATS_CSC').'</span>'."\n").
					$this->showControl($fields['cc_exp_mm'][0], $fields['cc_exp_mm'][1]).
					$this->showControl($fields['cc_exp_yy'][0], $fields['cc_exp_yy'][1]).
					$this->showControl($fields['cc_fname'][0], $fields['cc_fname'][1]).
					$this->showControl($fields['cc_lname'][0], $fields['cc_lname'][1]).
					'<p>'.$payMessage.'</p>'."\n".
					'<div id="rsm_response" class="rsm_response_error"></div>'."\n".
					'<div class="form-actions">'."\n".
						'<button class="button btn btn-large btn-success" type="submit" id="rsm_pay_button">'.JText::_('PLG_SYSTEM_RSMEMBERSHIPAUTHORIZE_AUTHORIZE_PAY_NOW').'</button>'.
						'<img src="'.JURI::root(true).'/components/com_rsmembership/assets/images/load.gif" id="rsm_loading" alt="" style="display: none;" />'."\n".
					'</div>'."\n".
				'</fieldset>'."\n".
				'<input type="hidden" name="membership_id" id="membership_id" value="'.$membership->id.'" />'."\n".
				'<input type="hidden" name="option" value="com_rsmembership" />'."\n".
				'<input type="hidden" name="task" value="thankyou" />'."\n".
				'</form>'."\n";
		
		// Tooltip
		$html .= '<div id="rsm_tooltip" style="display: none;">'."\n".
					'<div>'.JText::_('PLG_SYSTEM_RSMEMBERSHIPAUTHORIZE_AUTHORIZE_WHATS_CSC_DESC').'</div>'."\n".
					'<div align="center"><img src="'.JURI::root(true).'/plugins/system/rsmembershipauthorize/rsmembershipauthorize/images/cc_csc.gif" alt="CSC" /></div>'."\n".
				 '</div>'."\n";
		
		$warning = '<img src="'.JURI::root(true).'/plugins/system/rsmembershipauthorize/rsmembershipauthorize/images/warning.png" alt="" id="rsm_warning" />';
		
		// Script
		$html .= '<script type="text/javascript">'."\n".
				 'function rsm_get_error_message(code) {'."\n".
				 "if (code == 0) return '".$warning." ".JText::_('PLG_SYSTEM_RSMEMBERSHIPAUTHORIZE_AUTHORIZE_ERRORS', true)."';\n".
				 '}'."\n".
				 '</script>'."\n";
				 
		return $html;
	}
	
	protected function _getFields() {
		$fields = array();

		$field = new stdClass();
		$field->id 	       = '';
		$field->name       = 'cc_number';
		$field->label      = JText::_('PLG_SYSTEM_RSMEMBERSHIPAUTHORIZE_AUTHORIZE_CC_NUMBER');
		$field->type       = 'textbox';
		$field->values     = '';
		$field->additional = 'maxlength="16" autocomplete="off"'; // onkeydown="return rsm_check_card(this);" onkeyup="return rsm_check_card(this);"
		$field->required   = 1;
		$fields['cc_number'] = RSMembershipHelper::showCustomField($field, array(), true, false);
		
		$field = new stdClass();
		$field->id 	       = '';
		$field->name       = 'csc_number';
		$field->label      = JText::_('PLG_SYSTEM_RSMEMBERSHIPAUTHORIZE_AUTHORIZE_CSC');
		$field->type       = 'textbox';
		$field->values     = '';
		$field->additional = 'style="width: 45px; text-align: center;" maxlength="4" autocomplete="off"'; // onkeydown="return rsm_check_card(this);" onkeyup="return rsm_check_card(this);"
		$field->required   = 1;
		$fields['csc_number'] = RSMembershipHelper::showCustomField($field, array(), true, false);
		
		$field = new stdClass();
		$field->id 	       = '';
		$field->name       = 'cc_image';
		$field->label      = '';
		$field->type       = 'freetext';
		$field->values     = JHTML::image('plugins/system/rsmembershipauthorize/rsmembershipauthorize/images/cc_logos.gif', 'Credit Cards');
		$field->additional = '';
		$field->required   = 0;
		$fields['cc_image'] = RSMembershipHelper::showCustomField($field, array(), true, false);

		$field = new stdClass();
		$field->id 	       = '';
		$field->name       = 'cc_exp_mm';
		$field->label      = JText::_('PLG_SYSTEM_RSMEMBERSHIPAUTHORIZE_MONTH');
		$field->type       = 'select';
		$field->values     = array();
		for ($i=1; $i<=12; $i++)
			$field->values[] = ($i < 10 ? '0'.$i : $i).'-'.JText::_('PLG_SYSTEM_RSMEMBERSHIPAUTHORIZE_AUTHORIZE_MONTH_'.$i);
		$field->values 	   = implode("\n", $field->values);
		$field->additional = '';
		$field->required   = 1;
		$fields['cc_exp_mm'] = RSMembershipHelper::showCustomField($field, array(), true, false);
		
		$field = new stdClass();
		$field->id 	       = '';
		$field->name       = 'cc_exp_yy';
		$field->label      = JText::_('PLG_SYSTEM_RSMEMBERSHIPAUTHORIZE_YEAR');
		$field->type       = 'textbox';
		$field->values     = '';
		$field->additional = 'style="width: 35px; text-align: center;" maxlength="4" onblur="rsm_check_year(this)" autocomplete="off"'; // onkeydown="return rsm_check_card(this);" onkeyup="return rsm_check_card(this);"
		$field->required   = 1;
		$fields['cc_exp_yy'] = RSMembershipHelper::showCustomField($field, array(), true, false);
		
		$field = new stdClass();
		$field->id 	       = '';
		$field->name       = 'cc_fname';
		$field->label      = JText::_('PLG_SYSTEM_RSMEMBERSHIPAUTHORIZE_AUTHORIZE_FIRST_NAME');
		$field->type       = 'textbox';
		$field->values     = '';
		$field->additional = 'maxlength="64" autocomplete="off"';
		$field->required   = 1;
		$fields['cc_fname'] = RSMembershipHelper::showCustomField($field, array(), true, false);
		
		$field = new stdClass();
		$field->id 	       = '';
		$field->name       = 'cc_lname';
		$field->label      = JText::_('PLG_SYSTEM_RSMEMBERSHIPAUTHORIZE_AUTHORIZE_LAST_NAME');
		$field->type       = 'textbox';
		$field->values     = '';
		$field->additional = 'maxlength="64" autocomplete="off"';
		$field->required   = 1;
		$fields['cc_lname'] = RSMembershipHelper::showCustomField($field, array(), true, false);
		
		return $fields;
	}
	
	protected function _getTax($price) {
		$tax_value = $this->params->get('tax_value');
		if (!empty($tax_value))
		{
			$tax_type = $this->params->get('tax_type');
			
			// percent ?
			if ($tax_type == 0)
				$tax_value = $price * ($tax_value / 100);
		}

		return $tax_value;
	}

	public function hasDelayTransactionStoring() {
		return true;
	}

	public function delayTransactionStoring($vars) {
		if ($vars['plugin'] == 'rsmembershipauthorize') {
			$vars['delay'] 	= true;
			$properties 	= $vars['properties'];
			$session 		= JFactory::getSession();
			
			$session->set('transaction', $properties, 'rsmembership');
			return true;
		}
	}

	protected function getDelayedTransaction() {
		return JFactory::getSession()->get('transaction', null, 'rsmembership');
	}

	protected function emptyDelayedTransaction() {
		JFactory::getSession()->set('transaction', null, 'rsmembership');
	}
	
	public function getLimitations() {
		return JText::_('PLG_SYSTEM_RSMEMBERSHIPAUTHORIZE_AUTHORIZE_LIMITATIONS');
	}
	
	protected function onPaymentNotification() {
		if (!$this->canRun()) return;
		
		$jinput = JFactory::getApplication()->input;
		
		$subscription_id = $jinput->get('x_subscription_id', 0, 'int');
		if ($subscription_id) {
			$db 	= JFactory::getDBO();
			$query	= $db->getQuery(true);

			$response_code = $jinput->get('x_response_code', '', 'int');
			$reason_code   = $jinput->get('x_response_reason_code', '', 'int');

			$query
				->select('*')
				->from($db->qn('#__rsmembership_transactions'))
				->where($db->qn('gateway').' = '.$db->q('Authorize.Net'))
				->where($db->qn('custom').' = '.$db->q($subscription_id));
			$db->setQuery($query);
			$transaction = $db->loadObject();
			if (!$transaction)  return;

			$log = array();
			if ($response_code == 1) 
			{
				$query->clear();
				$query
					->select($db->qn('id'))
					->select($db->qn('membership_id'))
					->from($db->qn('#__rsmembership_membership_subscribers'))
					->where($db->qn('from_transaction_id').' = '.$db->q($transaction->id));
				$db->setQuery($query);
				$membership = $db->loadObject();

				JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_rsmembership/tables');
				$row 	= JTable::getInstance('Transaction','RSMembershipTable');
				$date 	= JFactory::getDate();

				$row->user_id 	 = $transaction->user_id;
				$row->user_email = $transaction->user_email;
				$row->user_data	 = $transaction->user_data;
				$row->type 		 = 'renew';
				$params = array();
				$params[] = 'id='.$membership->id;
				$params[] = 'membership_id='.$membership->membership_id;
				$row->params 	 = implode(';', $params); // params, membership, extras etc
				$row->date 		 = $date->toSql();
				$row->ip 		 = $_SERVER['REMOTE_ADDR'];
				$row->price 	 = $jinput->get('x_amount', '', 'string');
				$row->currency 	 = RSMembershipHelper::getConfig('currency');
				$row->hash 		 = $jinput->get('x_trans_id', '', 'string');
				$row->gateway 	 = 'Authorize.Net';
				$row->status 	 = 'pending';
				// store the transaction
				$row->store();

				RSMembership::finalize($row->id);
				RSMembership::approve($row->id);
			} elseif ($response_code == 2) {
				// declined
				$log[] = 'Payment has been declined - Response Code: '.$response_code.' Reason code: '.$reason_code;
			} elseif ($response_code == 3 && $reason_code == 8) {
				// expired card
				$log[] = 'Credit card has expired - Response Code: '.$response_code.' Reason code: '.$reason_code;
			} else {
				// other ?
				$log[] = 'Other - Response Code: '.$response_code.' Reason code: '.$reason_code;
			}

			if ($log) {
				RSMembership::saveTransactionLog($log, $transaction->id);
			}
		}
	}
	
	protected function escape($string) {
		return htmlentities($string, ENT_COMPAT, 'utf-8');
	}
	
	protected function _AIMrequest($post_values) {
		$post_url = $this->params->get('mode') ? "https://secure.authorize.net/gateway/transact.dll" : "https://test.authorize.net/gateway/transact.dll";
		
		$string = '';
		foreach ($post_values as $key => $value)
			$string .= "$key=" . urlencode( $value ) . "&";
		$string = rtrim($string, "& ");
		unset($post_values);

		$ch = curl_init($post_url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $string);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$response 	= curl_exec($ch);
		$error 		= curl_error($ch);
		curl_close($ch);
		
		if (!$response || $error) {
			throw new Exception(JHTML::image('plugins/system/rsmembershipauthorize/rsmembershipauthorize/images/error.png', 'Error').' '.JText::_('PLG_SYSTEM_RSMEMBERSHIPAUTHORIZE_AUTHORIZE_GENERAL_ERROR').' ('.$error.')');
		}
		
		$response = explode('|', $response);
		
		return $response;
	}
	
	protected function _authorize($data) {
		extract($data);
		$input		   = JFactory::getApplication()->input;
		$cc_number 	   = $input->get('cc_number', '', 'cmd');
		$csc_number	   = $input->get('csc_number', '', 'string');
		$cc_expiration = substr($input->get('cc_exp_mm', '', 'cmd'), 0, 2).'-'.$input->get('cc_exp_yy', 0, 'int');
		$cc_fname	   = $input->get('cc_fname', '', 'string');
		$cc_lname	   = $input->get('cc_lname', '', 'string');

		$user_data 	 = !empty($row->user_data) ? (object) unserialize($row->user_data) : (object) array();
		$user_fields = is_object($user_data) && isset($user_data->fields) ? $user_data->fields : array();
		
		$address 	= $this->_getMappedField('x_address', $user_fields);
		$state 		= $this->_getMappedField('x_state', $user_fields);
		$city 		= $this->_getMappedField('x_city', $user_fields);
		$ipaddress 	= isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
		$country 	= $this->_getMappedField('x_country', $user_fields);
		$zipcode 	= $this->_getMappedField('x_zip', $user_fields);
		$phone 		= $this->_getMappedField('x_phone', $user_fields);
		$fax 		= $this->_getMappedField('x_fax', $user_fields);
		$company 	= $this->_getMappedField('x_company', $user_fields);
		
		$x_login	= $this->params->get('x_login');
		$x_tran_key	= $this->params->get('x_tran_key');
		
		// Authorization
		$post_values = array(
			"x_login"			=> $x_login,
			"x_tran_key"		=> $x_tran_key,

			"x_version"			=> "3.1",
			"x_delim_data"		=> "TRUE",
			"x_delim_char"		=> "|",
			"x_relay_response"	=> "FALSE",

			"x_type"			=> "AUTH_ONLY",
			"x_method"			=> "CC",
			"x_card_num"		=> $cc_number,
			"x_exp_date"		=> $cc_expiration,
			"x_card_code"		=> $csc_number,

			"x_amount"			=> $row->price,
			"x_currency_code"	=> RSMembershipHelper::getConfig('currency'),
			"x_invoice_num"		=> md5(uniqid($x_login.' '.$x_tran_key)), // order num (unique)
			"x_description"		=> $description,

			"x_first_name"		=> $cc_fname,
			"x_last_name"		=> $cc_lname,
			"x_email"			=> $row->get('user_email'),
			"x_address"			=> !empty($address) ? $address : '',
			"x_state"			=> !empty($state) ? $state : '',
			"x_city"			=> !empty($city) ? $city : '',
			"x_customer_ip"		=> !empty($ipaddress) ? $ipaddress : '',
			"x_country"			=> !empty($country) ? $country : '',
			"x_zip"				=> !empty($zipcode) ? $zipcode : '',
			"x_phone"			=> !empty($phone) ? $phone : '',
			"x_fax"				=> !empty($fax) ? $fax : '',
			"x_company"			=> !empty($company) ? $company : '',
			"x_solution_id"		=> $this->params->get('mode') ? 'AAA100308' : 'AAA100302'
		);
		
		$response = $this->_AIMrequest($post_values);
		
		if ($response[0] == 1) {
			return $response;
		} else {
			$image = $response[0] == 4 ? 'warning' : 'error';
			throw new Exception(JHTML::image('plugins/system/rsmembershipauthorize/rsmembershipauthorize/images/'.$image.'.png', 'Information', array('id' => 'rsm_warning')).' '.$this->escape($response[3]));
		}
	}
	
	protected function _capture($auth_trans_id) {
		$x_login	= $this->params->get('x_login');
		$x_tran_key	= $this->params->get('x_tran_key');
		
		// now, grab the first payment
		$post_values = array(
			"x_login"			=> $x_login,
			"x_tran_key"		=> $x_tran_key,

			"x_version"			=> "3.1",
			"x_delim_data"		=> "TRUE",
			"x_delim_char"		=> "|",
			"x_relay_response"	=> "FALSE",

			"x_type"			=> "PRIOR_AUTH_CAPTURE",
			"x_trans_id"		=> $auth_trans_id
		);
		
		$response = $this->_AIMrequest($post_values);
		
		if ($response[0] == 1) {
			return $response;
		} else {
			$image = $response[0] == 4 ? 'warning' : 'error';
			throw new Exception(JHTML::image('plugins/system/rsmembershipauthorize/rsmembershipauthorize/images/'.$image.'.png', 'Information', array('id' => 'rsm_warning')).' '.$this->escape($response[3]));
		}
	}
	
	protected function _subscribe($data) {
		extract($data);
		list($length, $unit) 	= $this->_getAuthorizeLength($membership);
		$invoiceNumber 			= $this->_getInvoiceNumber();
		$startDate 		  		= date('Y-m-d', strtotime("+$length $unit", JFactory::getDate()->toUnix()));
		$params 				= RSMembershipHelper::parseParams($row->params);
		$input		   			= JFactory::getApplication()->input;
		$db						= JFactory::getDbo();
		$query					= $db->getQuery(true);
		
		// Calculate extra total
		$extra_total = 0;
		if (!empty($params['extras'])) {
			$extras_q = '';
			foreach ($params['extras'] as $k => $id)
				$extras_q[] = $db->q($id);

			$query->clear();
			$query
				->select('SUM('.$db->qn('price').')')
				->from($db->qn('#__rsmembership_extra_values'))
				->where($db->qn('id').' IN ('.implode(',', $extras_q).')');
			$db->setQuery($query);
			$extra_total = $db->loadResult();
		}

		// Calculate amount to be paid
		$amount  = $membership->use_renewal_price ? $membership->renewal_price : $membership->price;
		$amount	+= $extra_total;
		$amount += $this->_getTax($amount);

		// Are we using trials?
		$trialOccurrences = $membership->use_trial_period ? 1 : 0;
		$trialAmount 	  = $membership->use_trial_period ? $membership->trial_price : 0;
		$trialAmount	 += $extra_total;
		$trialAmount	 += $this->_getTax($trialAmount);
		
		// Number of recurring times
		$totalOccurences  = ($membership->recurring_times > 0 ? $membership->recurring_times : '9999');

		$x_login		= $this->params->get('x_login');
		$x_tran_key		= $this->params->get('x_tran_key');
		$cc_number 	   	= $input->get('cc_number', '', 'cmd');
		$csc_number	   	= $input->get('csc_number', '', 'string');
		$cc_expiration 	= substr($input->get('cc_exp_mm', '', 'cmd'), 0, 2).'-'.$input->get('cc_exp_yy', 0, 'int');
		$cc_fname	   	= $input->get('cc_fname', '', 'string');
		$cc_lname	   	= $input->get('cc_lname', '', 'string');
		
		$user_data 	 = !empty($row->user_data) ? (object) unserialize($row->user_data) : (object) array();
		$user_fields = is_object($user_data) && isset($user_data->fields) ? $user_data->fields : array();
		
		$address 	= $this->_getMappedField('x_address', $user_fields);
		$state 		= $this->_getMappedField('x_state', $user_fields);
		$city 		= $this->_getMappedField('x_city', $user_fields);
		$ipaddress 	= isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
		$country 	= $this->_getMappedField('x_country', $user_fields);
		$zipcode 	= $this->_getMappedField('x_zip', $user_fields);
		$phone 		= $this->_getMappedField('x_phone', $user_fields);
		$fax 		= $this->_getMappedField('x_fax', $user_fields);
		$company 	= $this->_getMappedField('x_company', $user_fields);
		$email		= $row->user_email;
		
		$content =
			"<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
			"<ARBCreateSubscriptionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
				"<merchantAuthentication>".
					"<name>" . $x_login . "</name>".
					"<transactionKey>" . $x_tran_key . "</transactionKey>".
				"</merchantAuthentication>".
				"<refId>0</refId>".
				"<subscription>".
				"<name>" . $this->escape($description) . "</name>".
				"<paymentSchedule>".
					"<interval>".
					"<length>". $length ."</length>".
					"<unit>". $unit ."</unit>".
					"</interval>".
					"<startDate>" . $startDate . "</startDate>".
					"<totalOccurrences>".$totalOccurences."</totalOccurrences>".
					"<trialOccurrences>". $trialOccurrences . "</trialOccurrences>".
				"</paymentSchedule>".
				"<amount>". $amount ."</amount>".
				"<trialAmount>" . $trialAmount . "</trialAmount>".
				"<payment>".
					"<creditCard>".
						"<cardNumber>" . $cc_number . "</cardNumber>".
						"<expirationDate>" . $cc_expiration . "</expirationDate>".
						"<cardCode>" . $this->escape($csc_number) . "</cardCode>".
					"</creditCard>".
				"</payment>".
				"<order>".
					"<invoiceNumber>".$invoiceNumber."</invoiceNumber>".
				"</order>".
				"<customer>".
					"<email>".$this->escape($email)."</email>".
					"<phoneNumber>".$this->escape($phone)."</phoneNumber>".
					"<faxNumber>".$this->escape($fax)."</faxNumber>".
				"</customer>".
				"<billTo>".
					"<firstName>". $this->escape($cc_fname) . "</firstName>".
					"<lastName>" . $this->escape($cc_lname) . "</lastName>".
					"<company>" . $this->escape($company) . "</company>".
					"<address>" . $this->escape($address) . "</address>".
					"<city>" . $this->escape($city) . "</city>".
					"<state>" . $this->escape($state) . "</state>".
					"<zip>" . $this->escape($zipcode) . "</zip>".
					"<country>" . $this->escape($country) . "</country>".
				"</billTo>".
				"</subscription>".
			"</ARBCreateSubscriptionRequest>";

		$post_url = $this->params->get('mode') ? "https://api.authorize.net/xml/v1/request.api" : "https://apitest.authorize.net/xml/v1/request.api";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $post_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml"));
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		$error = curl_error($ch);
		curl_close($ch);
		
		if ($response) {
			// connected successfully, grab response
			list($refId, $resultCode, $code, $text, $subscriptionId) = $this->_parseReturn($response);
			
			if ($resultCode == 'Ok' || strpos($text, 'Successful') !== false) {
				return $subscriptionId;
			} else {
				// show the error
				$image = 'error';
				
				if (!$text) {
					$text = explode("\r\n\r\n", $response, 2);
					$text = strip_tags($text[1]);
				}
				
				throw new Exception(JHTML::image('plugins/system/rsmembershipauthorize/rsmembershipauthorize/images/'.$image.'.png', 'Information', array('id' => 'rsm_warning')).' '.$this->escape($text));
			}
		} else {
			// cURL error
			throw new Exception(JHTML::image('plugins/system/rsmembershipauthorize/rsmembershipauthorize/images/error.png', 'Error').' '.JText::_('PLG_SYSTEM_RSMEMBERSHIPAUTHORIZE_AUTHORIZE_GENERAL_ERROR').' ('.$error.')');
		}
	}

	public function onAfterRoute() {
		// Get application
		$app = JFactory::getApplication();
		
		// Do not run in the administrator section
		if ($app->isAdmin()) {
			return true;
		}
		
		// Get jinput for covenience
		$jinput	= $app->input;

		// Is this a recurring payment notification?
		if ( $jinput->get('authorizepayment', '', 'string') ) {
			return $this->onPaymentNotification();
		}

		// Get option and task
		$option = $jinput->get('option', '', 'string');
		$task   = $jinput->get('plugin_task', '', 'cmd');

		// Is this an Authorize.NET request?
		if ($option == 'com_rsmembership' && $task == 'authorize') {
			if (ob_get_contents()) {
				ob_end_clean();
			}

			$db 	= JFactory::getDBO();
			$query	= $db->getQuery(true);
			$membership_id = $jinput->get('membership_id', 0, 'int');

			$query
				->select('*')
				->from($db->qn('#__rsmembership_memberships'))
				->where($db->qn('id').' = '.$db->q($membership_id));
			$db->setQuery($query);
			$membership = $db->loadObject();

			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_rsmembership/tables');
			$row		 = JTable::getInstance('Transaction', 'RSMembershipTable');
			$transaction = $this->getDelayedTransaction();

			// Need to check if the transaction is still in the session
			if (empty($transaction)) {
				$app->enqueueMessage(JText::_('PLG_SYSTEM_RSMEMBERSHIPAUTHORIZE_SESSION_EXPIRED'), 'error');
				echo 'RSM_SESSION_END';
				die();
			}
			// Need to check that cURL is enabled
			if (!function_exists('curl_init')) {
				echo JHTML::image('plugins/system/rsmembershipauthorize/rsmembershipauthorize/images/error.png', 'Error', array('id' => 'rsm_warning')).' '.JText::_('PLG_SYSTEM_RSMEMBERSHIPAUTHORIZE_AUTHORIZE_CURL_ERROR');
				die();
			}
			$row->bind($transaction);

			// adjust price
			$row->price += $this->_getTax($row->price);
			$row->price  = $this->_convertNumber($row->price);

			$description  = $this->params->get('message_type') ? $membership->name : JText::sprintf('PLG_SYSTEM_RSMEMBERSHIPAUTHORIZE_MEMBERSHIP_PURCHASE_ON', RSMembershipHelper::showDate($row->date));
			$is_recurring = $membership->recurring && $membership->period > 0 && $row->type == 'new';
			
			$log = array();

			try {
				// Authorization - this is the only way to make sure credit card details are correct before creating the ARB subscription
				$response 		= $this->_authorize(array(
					'row' 			=> $row,
					'description' 	=> $description
				));
				$auth_code 		= $response[4];
				$auth_trans_id 	= $response[6];
				
				// If it's recurring, make sure you can setup a recurring payment
				if ($is_recurring) {
					$subscriptionId = $this->_subscribe(array(
						'row' 			=> $row,
						'membership' 	=> $membership,
						'description'	=> $description
					));
					
					$row->custom = $subscriptionId;
				}
				
				// Capture previously authorized payment (ie actual pay happens here)
				$response = $this->_capture($auth_trans_id);

				// Everything is ok, store the order number and the transaction
				$row->hash = $response[6];
				$row->store();
				
				// Time to remove the transaction from the session
				$this->emptyDelayedTransaction();

				RSMembership::finalize($row->get('id'));
				RSMembership::approve($row->get('id'));

				echo 'RSM_AUTHORIZE_OK';
			} catch (Exception $e) {
				echo $e->getMessage();
				die();
			}
			
			die();
		}
	}
	
	protected function _getMappedField($field, $user_fields) {
		$return = '';
		
		if ($name = $this->params->get($field)) {
			if (isset($user_fields[$name])) {
				$return = is_array($user_fields[$name]) ? implode(', ', $user_fields[$name]) : $user_fields[$name];
			}
		}
		
		return $return;
	}
	
	protected function _parseReturn($content) {
		$refId 			= $this->_between($content, '<refId>', '</refId>');
		$resultCode 	= $this->_between($content, '<resultCode>', '</resultCode>');
		$code 			= $this->_between($content, '<code>', '</code>');
		$text 			= $this->_between($content, '<text>', '</text>');
		$subscriptionId = $this->_between($content, '<subscriptionId>', '</subscriptionId>');
		return array ($refId, $resultCode, $code, $text, $subscriptionId);
	}
	
	protected function _between($haystack,$start,$end) {
		if (strpos($haystack, $start) === false || strpos($haystack, $end) === false) {
			return false;
		} else {
			$start_position = strpos($haystack, $start) + strlen($start);
			$end_position 	= strpos($haystack, $end);
			return substr($haystack, $start_position, $end_position - $start_position);
		}
	}
	
	protected function _getAuthorizeLength($membership) {
		$length = $membership->period;
		$unit 	= '';
		
		switch ($membership->period_type)
		{
			case 'h':
				$length = 7;
				$unit 	= 'days';
			break;
			
			case 'd':
				if ($membership->period > 365)
					$length = 365;
				
				$unit = 'days';
			break;
			
			case 'm':
				if ($membership->period > 12)
					$length = 12;
				
				$unit = 'months';
			break;
			
			case 'y':
				if ($membership->period > 1)
					$length = 365;
					
				$unit = 'days';
			break;
		}
		
		return array($length, $unit);
	}
	
	protected function _setInvoiceNumber() {
		JFactory::getSession()->set('com_rsmembership.invoice', substr(md5(uniqid('invoice')), 0, 20));
	}
	
	protected function _getInvoiceNumber() {
		return JFactory::getSession()->get('com_rsmembership.invoice');
	}
	
	protected function _convertNumber($number) {
		return number_format($number, 2, '.', '');
	}
	
	protected function _convertPeriod($period, $type) {
		$converted = array(
			$period,
			strtoupper($type)
		);

		return $converted;
	}
	
	protected function _loadLanguage() {
		$this->loadLanguage('plg_system_rsmembership');
		$this->loadLanguage('plg_system_rsmembershipauthorize');
	}
}