<?php
/**
* @package RSEvents!Pro
* @copyright (C) 2015 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.plugin.plugin' );

class plgSystemRseproAuthorize extends JPlugin
{
	//set the value of the payment option
	var $rsprooption = 'authorize';
	
	public function plgSystemRseproAuthorize(&$subject, $config) {
		parent::__construct($subject, $config);
	}
	
	public function onAfterRender() {
		$app = JFactory::getApplication();		
		
		if($app->getName() != 'site') 
			return;
		
		$is_authorize = $app->input->getInt('authorize', '');
		if (!empty($is_authorize))
			$this->rsepro_processForm(null);
	}
	
	/*
	*	Is RSEvents!Pro installed
	*/
	
	protected function canRun() {
		$helper = JPATH_SITE.'/components/com_rseventspro/helpers/rseventspro.php';
		if (file_exists($helper)) {
			require_once $helper;
			JFactory::getLanguage()->load('plg_system_rseproauthorize',JPATH_ADMINISTRATOR);
			
			return true;
		}
		
		return false;
	}
	
	/*
	*	Add the current payment option to the Payments List
	*/
	
	public function rsepro_addOptions() {
		if ($this->canRun())
			return JHTML::_('select.option', $this->rsprooption, JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_NAME'));
		else return JHTML::_('select.option', '', '');
	}
	
	/*
	*	Add optional fields for the payment plugin. Example: Credit Card Number, etc.
	*	Please use the syntax <form method="post" action="index.php?option=com_rseventspro&task=process" name="paymentForm">
	*	The action provided in the form will actually run the rsepro_processForm() of your payment plugin.
	*/
	
	public function rsepro_showForm($vars) {
		//check to see if we can show something
		if (!$this->canRun()) 
			return;
		
		if (isset($vars['method']) && $vars['method'] == $this->rsprooption) {
			JFactory::getLanguage()->load('com_rseventspro',JPATH_SITE);
		
			$u		= JURI::getInstance();
			$db		= JFactory::getDbo();
			$query	= $db->getQuery(true);
			$app	= JFactory::getApplication();
			$jinput	= $app->input;
			
			//is the plugin enabled ?
			$enable = JPluginHelper::isEnabled('system', 'rseproauthorize');
			if (!$enable) return;
			
			$details = $vars['details'];
			$tickets = $vars['tickets'];
			
			$auth_id = $this->params->get('id','');
			if (empty($auth_id)) {
				JError::raiseWarning(500, '"Authorize API Login ID" is not set in the "RSEvents!Pro Authorize payment" plugin\'s parameters!');
				return;
			}
			
			$auth_key = $this->params->get('key','');
			if (empty($auth_key)) {
				JError::raiseWarning(500, '"Transaction key" is not set in the "RSEvents!Pro Authorize payment" plugin\'s parameters!');
				return;
			}
			
			//check to see if its a payment request
			if (empty($details->verification) && empty($details->ide) && empty($details->email) && empty($tickets)) {
				JError::raiseWarning(500, 'Missing transaction details!');
				return;
			}
			
			//get the currency
			$currency = $vars['currency'];			
			
			//check the status of this transaction
			$query->clear()
				->select($db->qn('state'))
				->from($db->qn('#__rseventspro_users'))
				->where($db->qn('id').' = '.(int) $details->id);
			
			$db->setQuery($query);
			$state = $db->loadResult();
			//if the user has the status to Accepted return;
			if ($state) {
				JError::raiseWarning(500, 'This transaction has already been paid!');
				return;
			}
			
			$use_ssl = $this->params->get('ssl',0);
			
			//set the ssl connection 
			if ($use_ssl && !$u->isSSL()) {
				$u->setScheme('https');
				$app->redirect($u->toString());
			}
			
			JFactory::getDocument()->addScript(JURI::root().'components/com_rseventspro/assets/js/scripts.js');
			
			$savedData = JFactory::getSession()->get('com_rseventspro.payment.verification.'.$details->verification, array());
			
			JHTML::_('behavior.tooltip');
			
			//get the months and years dropdowns
			$months[] = JHTML::_('select.option', '01', JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_JANUARY'));
			$months[] = JHTML::_('select.option', '02', JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_FEBRUARY'));
			$months[] = JHTML::_('select.option', '03', JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_MARCH'));
			$months[] = JHTML::_('select.option', '04', JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_APRIL'));
			$months[] = JHTML::_('select.option', '05', JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_MAY'));
			$months[] = JHTML::_('select.option', '06', JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_JUNE'));
			$months[] = JHTML::_('select.option', '07', JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_JULY'));
			$months[] = JHTML::_('select.option', '08', JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_AUGUST'));
			$months[] = JHTML::_('select.option', '09', JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_SEPTEMBER'));
			$months[] = JHTML::_('select.option', '10', JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_OCTOBER'));
			$months[] = JHTML::_('select.option', '11', JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_NOVEMBER'));
			$months[] = JHTML::_('select.option', '12', JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_DECEMBER'));
			
			$exp_month = JHTML::_('select.genericlist', $months, 'cc_exp_m', 'class="rs_select" size="1"','value','text', isset($savedData['cc_exp_m']) ? $savedData['cc_exp_m'] : '01');
			
			$years = array();
			$currentYear = JFactory::getDate()->format('Y');
			for ($i=$currentYear;$i<=$currentYear+10;$i++)
				$years[] = JHTML::_('select.option', $i, $i);
			
			$exp_year = JHTML::_('select.genericlist', $years, 'cc_exp_y', 'class="rs_select" size="1"','value','text', isset($savedData['cc_exp_y']) ? $savedData['cc_exp_y'] : $currentYear);
			
			//get the countries
			$query->clear()
				->select($db->qn('name'))
				->from($db->qn('#__rseventspro_countries'));
			
			$db->setQuery($query);
			$countries = $db->loadObjectList();
			
			$country_list = array();
			foreach ($countries as $country)
				$country_list[] = JHTML::_('select.option', $country->name, $country->name);
				
			$clist = JHTML::_('select.genericlist', $country_list, 'country', 'class="rs_select" size="1"', 'value', 'text', isset($savedData['country']) ? $savedData['country'] : null);
			
			//get the event name
			$query->clear()
				->select($db->qn('name'))
				->from($db->qn('#__rseventspro_events'))
				->where($db->qn('id').' = '.(int) $details->ide);
			
			$db->setQuery($query);
			$eventname = $db->loadResult();
			
			if (count($tickets) == 1) {
				$ticket		= $tickets[0];
				$item		= $eventname.' - '.$ticket->name;
				$total		= isset($ticket->price) ? $ticket->price : 0;
				$number		= isset($ticket->quantity) ? $ticket->quantity : 1;
				$auth_total	= $total * $number;
			} else  {
				$item		= $eventname.' - '.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_MULTIPLE');
				$auth_total	= 0;
				
				foreach ($tickets as $ticket) {
					if ($ticket->price > 0)
						$auth_total += ($ticket->price * $ticket->quantity);
				}
			}
			
			if (!empty($details->discount))
				$auth_total = $auth_total - $details->discount;
			
			if (empty($auth_total)) return;
			
			if ($details->early_fee)
				$auth_total = $auth_total - $details->early_fee;
			
			if ($details->late_fee)
				$auth_total = $auth_total + $details->late_fee;
			
			//tax
			if (!empty($details->tax)) 
				$auth_total = $auth_total + $details->tax;
			
			$html = '';
			$html .= '<fieldset>';
			$html .= '<legend>'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_TRANSACTION_DETAILS').'</legend>';
			$html .= '<table cellpading="0" cellspacing="5" class="table table-bordered rs_table">';
			$html .= '<tr><td>';
			$html .= '<b>'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_TICKET_NAME').':</b> ';
			$html .= '</td><td>';
			$html .= $item;
			$html .= '</td></tr>';
			$html .= '<tr><td>';
			$html .= '<b>'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_TICKET_PRICE').':</b> ';
			$html .= '</td><td>';
			
			foreach ($tickets as $ticket) {
				if (empty($ticket->price))
					$html .= $ticket->quantity. ' x '.$ticket->name.' ('.JText::_('COM_RSEVENTSPRO_GLOBAL_FREE'). ')<br />';
				else
					$html .= $ticket->quantity. ' x '.$ticket->name.' ('.rseventsproHelper::currency($ticket->price). ')<br />';
			}
			
			$html .= '</td></tr>';
			if (!empty($details->discount)) {
				$html .= '<tr><td>';
				$html .= '<b>'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_TICKET_DISCOUNT').':</b> ';
				$html .= '</td><td>';
				$html .= rseventsproHelper::currency($details->discount);
				$html .= '</td></tr>';
			}
			
			if (!empty($details->early_fee)) {
				$html .= '<tr><td>';
				$html .= '<b>'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_EARLY_FEE').':</b> ';
				$html .= '</td><td>';
				$html .= rseventsproHelper::currency($details->early_fee);
				$html .= '</td></tr>';
			}
			
			if (!empty($details->late_fee)) {
				$html .= '<tr><td>';
				$html .= '<b>'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_LATE_FEE').':</b> ';
				$html .= '</td><td>';
				$html .= rseventsproHelper::currency($details->late_fee);
				$html .= '</td></tr>';
			}
			
			if (!empty($details->tax)) {
				$html .= '<tr><td>';
				$html .= '<b>'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_TAX').':</b> ';
				$html .= '</td><td>';
				$html .= rseventsproHelper::currency($details->tax);
				$html .= '</td></tr>';
			}
			
			$html .= '<tr><td>';
			$html .= '<b>'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_TOTAL').':</b> ';
			$html .= '</td><td>';
			$html .= rseventsproHelper::currency($auth_total);
			$html .= '</td></tr>';
			$html .= '</table>';
			$html .= '</fieldset>';
			
			$form_url = $use_ssl ? JRoute::_('index.php?option=com_rseventspro&task=process', true, 1) : JRoute::_('index.php?option=com_rseventspro&task=process');
			
			$html .= '<form method="post" action="'.$form_url.'" name="paymentForm" autocomplete="off">'."\n";
			$html .= '<table cellspacing="20" cellpading="0" class="rs_table">'."\n";
			$html .= '<tr><td valign="top">'."\n";
			$html .= '<label for="cc_number">'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_CARD_NUMBER').'</label>'."\n";
			$html .= '</td><td>'."\n";
			$html .= '<input type="text" class="rs_textbox" id="cc_number" name="cc_number" onkeyup="javascript:this.value=this.value.replace(/[^0-9]/g, \'\');" maxlength="16" size="25" value="'.(isset($savedData['cc_number']) ? $this->escape($savedData['cc_number']) : '').'"/>'."\n";
			$html .= '<br/><br/><img src="'.JURI::root().'components/com_rseventspro/assets/images/cc_logo.gif" alt="CC" />'."\n";
			$html .= '</td></tr>'."\n";
			$html .= '<tr><td>'."\n";
			$html .= '<label for="cc_exp_date">'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_CARD_EXP_DATE').'</label>'."\n";
			$html .= '</td><td>'."\n";
			$html .= $exp_month.'  / '.$exp_year."\n";
			$html .= '</td></tr>'."\n";
			$html .= '<tr><td>'."\n";
			$html .= '<label for="cc_ccv">'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_CARD_CCV').'</label>'."\n";
			$html .= '</td><td>'."\n";
			$html .= '<input type="text" class="rs_textbox" name="cc_ccv" id="cc_ccv" size="5" onkeyup="javascript:this.value=this.value.replace(/[^0-9]/g, \'\');" maxlength="4" value="'.(isset($savedData['cc_ccv']) ? $this->escape($savedData['cc_ccv']) : '').'" />';
			$html .= ' <span onmouseout="rs_tooltip.hide();" onmouseover="rs_tooltip.show(\'rs_tooltip\');" id="rs_whats_csc">'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_CARD_CCV_WHATS_THIS').'</span>';
			$html .= '</td></tr>'."\n";
			$html .= '<tr><td>'."\n";
			$html .= '<label for="firstname">'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_CARD_FIRST_NAME').'</label>'."\n";
			$html .= '</td><td>'."\n";
			$html .= '<input type="text" class="rs_textbox" id="firstname" name="firstname" size="25" value="'.(isset($savedData['firstname']) ? $this->escape($savedData['firstname']) : $this->escape($details->name)).'"/>'."\n";
			$html .= '</td></tr>'."\n";
			$html .= '<tr><td>'."\n";
			$html .= '<label for="lastname">'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_CARD_LAST_NAME').'</label>'."\n";
			$html .= '</td><td>'."\n";
			$html .= '<input type="text" class="rs_textbox" id="lastname" name="lastname" size="25" value="'.(isset($savedData['lastname']) ? $this->escape($savedData['lastname']) : '').'"/>'."\n";
			$html .= '</td></tr>'."\n";
			$html .= '<tr><td>'."\n";
			$html .= '<label for="country">'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_COUNTRY').'</label>'."\n";
			$html .= '</td><td>'."\n";
			$html .= $clist."\n";
			$html .= '</td></tr>'."\n";
			$html .= '<tr><td>'."\n";
			$html .= '<label for="city">'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_CITY').'</label>'."\n";
			$html .= '</td><td>'."\n";
			$html .= '<input type="text" class="rs_textbox" id="city" name="city" size="25" value="'.(isset($savedData['city']) ? $this->escape($savedData['city']) : '').'"/>'."\n";
			$html .= '</td></tr>'."\n";
			$html .= '<tr><td>'."\n";
			$html .= '<label for="state">'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_STATE').'</label>'."\n";
			$html .= '</td><td>'."\n";
			$html .= '<input type="text" class="rs_textbox" id="state" name="state" size="25" value="'.(isset($savedData['state']) ? $this->escape($savedData['state']) : '').'"/>'."\n";
			$html .= '</td></tr>'."\n";
			$html .= '<tr><td>'."\n";
			$html .= '<label for="address">'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_ADDRESS').'</label>'."\n";
			$html .= '</td><td>'."\n";
			$html .= '<input type="text" class="rs_textbox" id="address" name="address" size="25" value="'.(isset($savedData['address']) ? $this->escape($savedData['address']) : '').'"/>'."\n";
			$html .= '</td></tr>'."\n";
			$html .= '<tr><td>'."\n";
			$html .= '<label for="zipcode">'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_ZIPCODE').'</label>'."\n";
			$html .= '</td><td>'."\n";
			$html .= '<input type="text" class="rs_textbox" id="zipcode" name="zipcode" size="6" value="'.(isset($savedData['zipcode']) ? $this->escape($savedData['zipcode']) : '').'"/>'."\n";
			$html .= '</td></tr>'."\n";
			
			// Phone number
			$html .= '<tr><td>'."\n";
			$html .= '<label for="phone">'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_PHONE').'</label>'."\n";
			$html .= '</td><td>'."\n";
			$html .= '<input type="text" class="rs_textbox" id="phone" name="phone" size="6" maxlength="25" value="'.(isset($savedData['phone']) ? $this->escape($savedData['phone']) : '').'"/>'."\n";
			$html .= '</td></tr>'."\n";
			
			$html .= '<tr><td colspan="2"><button type="submit" onclick="return cc_validate(\''.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_VALIDATE_CC_NUMBER',true).'\',\''.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_VALIDATE_CC_CCV',true).'\');" class="rs_button">'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_PAY').'</button></td></tr>'."\n";
			$html .= '</table>'."\n";
			$html .= '<input type="hidden" name="email" value="'.$this->escape($details->email).'" />'."\n";
			$html .= '<input type="hidden" name="amount" value="'.$this->convertprice($auth_total).'" />'."\n";
			$html .= '<input type="hidden" name="invoice" value="'.$this->escape($details->ide.$details->id).'" />'."\n";
			$html .= '<input type="hidden" name="hash" value="'.$this->escape($details->verification).'" />'."\n";
			$html .= '<input type="hidden" name="description" value="'.$this->escape($item).'" />'."\n";
			$html .= '<input type="hidden" name="ipaddress" value="'.$this->escape($_SERVER['REMOTE_ADDR']).'" />'."\n";
			$html .= '<input type="hidden" name="ide" value="'.$details->ide.'" />'."\n";
			$html .= '<input type="hidden" name="payment" value="cc" />'."\n";
			$html .= '</form>'."\n";
			
			$html .= '<div id="rs_tooltip" style="display: none;">'."\n"; 
			$html .= '<div>'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_CARD_CCV_DESC').'</div>'."\n";
			$html .= '<div align="center"><img src="'.JURI::root().'components/com_rseventspro/assets/images/cc_csc.gif" alt="CSC" /></div>';
			$html .= '</div>'."\n";
			
			echo $html;
		}
	}
	
	/*
	*	Process the form
	*/
	
	public function rsepro_processForm($vars) {
		//check to see if we can show something
		if (!$this->canRun()) return;
		
		//if the request didn`t come from $_POST return;
		if (empty($_POST)) return;
		
		$u		= JURI::getInstance();
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$log	= array();
		$params	= array();
		
		$data = $vars['data'];
		$hash = $data->getString('hash');
		
		$query->select($db->qn('gateway'))
			  ->from($db->qn('#__rseventspro_users'))
			  ->where($db->qn('verification').' = '.$db->q($hash));
		$db->setQuery($query);
		$gateway = $db->loadResult();
		
		if ($gateway != $this->rsprooption) return;
		
		//check for Authorize.net details
		$auth_id = $this->params->get('id','');
		if(empty($auth_id)) return;
		$auth_key = $this->params->get('key','');
		if(empty($auth_key)) return;
		
		$use_ssl = $this->params->get('ssl',0);
		
		//if this is not a secure page then return;
		if ($use_ssl && !$u->isSSL()) {
			JError::raiseWarning(500, 'Refusing to process request because it does not use the SSL communication protocol!');
			return;
		}
		
		//check the payment type
		$payment = $data->getString('payment');
		if (!isset($payment)) return;
		if ($payment != 'cc') return;
		
		$cc_number = $data->getString('cc_number');
		$cc_exp_m = $data->getString('cc_exp_m');
		$cc_exp_y = $data->getString('cc_exp_y');
		$cc_ccv = $data->getString('cc_ccv');
		$amount = $data->getString('amount');
		$firstname = $data->getString('firstname');
		$lastname = $data->getString('lastname');
		$ide = $data->getString('ide');
		$description = $data->getString('description');
		$address = $data->getString('address');
		$state = $data->getString('state');
		$city = $data->getString('city');
		$ipaddress = $data->getString('ipaddress');
		$country = $data->getString('country');
		$zipcode = $data->getString('zipcode');
		$phone = $data->getString('phone');
		
		if (empty($cc_number) || empty($cc_exp_m) || empty($cc_exp_y) || empty($cc_ccv) || empty($amount) || empty($firstname) || empty($lastname) || empty($ide)) return;
		
		// set session data
		$session = JFactory::getSession();
		$savedData = array(
			'cc_number' => $cc_number,
			'cc_exp_m' 	=> $cc_exp_m,
			'cc_exp_y' 	=> $cc_exp_y,
			'cc_ccv' 	=> $cc_ccv,
			'firstname' => $firstname,
			'lastname' 	=> $lastname,
			'country'	=> $country,
			'city' 		=> $city,
			'state' 	=> $state,
			'address' 	=> $address,
			'zipcode' 	=> $zipcode,
			'phone' 	=> $phone
		);
		
		//get the alias
		$query->clear()
			->select($db->qn('name'))
			->from($db->qn('#__rseventspro_events'))
			->where($db->qn('id').' = '.(int) $data->getString('ide'));
		
		$db->setQuery($query);
		$eventname = $db->loadResult();
		
		$log[] = "Receiving a new transaction from Authorize.NET";
		
		//set the url
		$url = $this->params->get('mode',0) ? 'https://secure.authorize.net/gateway/transact.dll' : 'https://test.authorize.net/gateway/transact.dll';
		
		$post_values = array(
			"x_login"			=> $auth_id,
			"x_tran_key"		=> $auth_key,

			"x_version"			=> "3.1",
			"x_delim_data"		=> "TRUE",
			"x_delim_char"		=> "|",
			"x_relay_response"	=> "FALSE",

			"x_type"			=> "AUTH_CAPTURE",
			"x_method"			=> "CC",
			"x_card_num"		=> $cc_number,
			"x_exp_date"		=> $cc_exp_m.$cc_exp_y,
			"x_card_code"		=> $cc_ccv,

			"x_amount"			=> $amount,
			"x_invoice_num"		=> $data->getString('invoice'),
			"x_description"		=> !empty($description) ? $description : 'RSEvents!Pro',

			"x_first_name"		=> $firstname,
			"x_last_name"		=> $lastname,
			"x_email"			=> $data->getString('email'),
			"x_address"			=> !empty($address) ? $address : '',
			"x_state"			=> !empty($state) ? $state : '',
			"x_city"			=> !empty($city) ? $city : '',
			"x_customer_ip"		=> !empty($ipaddress) ? $ipaddress : '',
			"x_country"			=> !empty($country) ? $country : '',
			"x_zip"				=> !empty($zipcode) ? $zipcode : '',
			"x_phone"			=> !empty($phone) ? $phone : '',
			"x_solution_id"		=> $this->params->get('mode',0) ? 'AAA100308' : 'AAA100302'
		);
	
		$string = "";
		foreach ($post_values as $key => $value)
			$string .= "$key=" . urlencode($value) . "&";
		$string = rtrim( $string, "& " );
		
		$request = curl_init($url);
		curl_setopt($request, CURLOPT_HEADER, 0);
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($request, CURLOPT_POSTFIELDS, $string);
		curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE);
		$response = curl_exec($request);
		curl_close ($request);
		
		$resp = explode('|',$response);
		
		$query->clear()
			->select('*')
			->from($db->qn('#__rseventspro_users'))
			->where($db->qn('verification').' = '.$db->q($hash));
		
		$db->setQuery($query);
		$subscriber = $db->loadObject();
		
		$success = false;
		
		if ($resp[0] == 1 && $data->getString('invoice') == $resp[7] && $amount == $resp[9]) {
			$success = true;
			$log[] = "Successfully added payment to database.";
			
			if(!empty($subscriber)) {
				$query->clear()
					->update($db->qn('#__rseventspro_users'))
					->set($db->qn('state').' = 1')
					->set($db->qn('params').' = '.$db->q($response))
					->where($db->qn('id').' = '.(int) $subscriber->id);
				
				
				$db->setQuery($query);
				$db->execute();
				
				//send the activation email
				require_once JPATH_SITE.'/components/com_rseventspro/helpers/emails.php';
				rseventsproHelper::confirm($subscriber->id);
			}
			
			$msg = JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_PAYMENT_OK');
		} else {
			$msg = $resp[3];
			$log[] = "Authorize.NET reported the following error: ".$msg;
		}
		
		rseventsproHelper::savelog($log,$subscriber->id);
		
		if ($success) {
			// clear session data
			$session->clear('com_rseventspro.payment.verification.'.$hash);
			
			$redirect = rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($ide,$eventname),false);
		} else {
			// save session data for future usage
			$session->set('com_rseventspro.payment.verification.'.$hash, $savedData);
			
			// redirect the user back to the payment form
			$hash = md5($subscriber->id.$subscriber->name.$subscriber->email);
			$redirect = rseventsproHelper::route('index.php?option=com_rseventspro&task=payment&method='.$this->rsprooption.'&hash='.$hash, false);
		}
		
		JFactory::getApplication()->redirect($redirect,$msg);
	}
	
	public function rsepro_tax($vars) {
		if (!$this->canRun()) return;
		
		if (isset($vars['method']) && $vars['method'] == $this->rsprooption) {
			$total		= isset($vars['total']) ? $vars['total'] : 0;
			$tax_value	= $this->params->get('tax_value',0);
			$tax_type	= $this->params->get('tax_type',0);
			
			return rseventsproHelper::setTax($total,$tax_type,$tax_value);
		}
	}
	
	public function rsepro_info($vars) {
		if (!$this->canRun()) return;
		
		if (isset($vars['method']) && $vars['method'] == $this->rsprooption) {
			$db		= JFactory::getDbo();
			$query	= $db->getQuery(true);
			$app	= JFactory::getApplication();
			$sid	= $app->input->getInt('id');
			
			$query->clear()
				->select($db->qn('params'))
				->from($db->qn('#__rseventspro_users'))
				->where($db->qn('id').' = '.$sid);
			
			$db->setQuery($query);
			$data = $db->loadResult();
			
			if (!empty($data)) {
				$data = explode('|',$data);
				
				echo $app->isAdmin() ? '<fieldset>' : '<fieldset class="rs_fieldset">';
				echo '<legend>'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_PAYMENT_DETAILS').'</legend>';
				echo '<table width="100%" border="0" class="adminform rs_table table table-striped">';
				echo '<tr>';
				echo '<td width="25%"><b>'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_RESPONSE').'</b></td>';
				echo '<td>'.(isset($data[3]) ? $data[3] : '').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td width="25%"><b>'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_AUTH_CODE').'</b></td>';
				echo '<td>'.(isset($data[4]) ? $data[4] : '').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td width="25%"><b>'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_TRANSACTION').'</b></td>';
				echo '<td>'.(isset($data[6]) ? $data[6] : '').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td width="25%"><b>'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_INVOICE').'</b></td>';
				echo '<td>'.(isset($data[7]) ? $data[7] : '').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td width="25%"><b>'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_ACCOUNT_NR').'</b></td>';
				echo '<td>'.(isset($data[50]) ? $data[50] : '').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td width="25%"><b>'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_CARD_TYPE').'</b></td>';
				echo '<td>'.(isset($data[51]) ? $data[51] : '').'</td>';
				echo '</tr>';
				echo '</table>';
				echo '</fieldset>';
			}
		}
	}
	
	public function rsepro_name($vars) {
		if (!$this->canRun()) return;
		
		if (isset($vars['gateway']) && $vars['gateway'] == $this->rsprooption) {
			return JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_AUTHORIZE_NAME_AUTHORIZE');
		}
	}
	
	protected function escape($string) {
		return htmlentities($string, ENT_COMPAT, 'UTF-8');
	}
	
	protected function convertprice($price) {
		return number_format($price, 2, '.', '');
	}
}