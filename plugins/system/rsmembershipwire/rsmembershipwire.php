<?php
/**
* @package RSMembership!
* @copyright (C) 2009-2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

class plgSystemRSMembershipWire extends JPlugin
{
	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);

		if ($this->canRun()) {
			require_once JPATH_ADMINISTRATOR.'/components/com_rsmembership/helpers/rsmembership.php';
			$this->_loadLanguage();
			$this->addOurPayments();
		}
	}
	
	protected function canRun() {
		return file_exists(JPATH_ADMINISTRATOR.'/components/com_rsmembership/helpers/rsmembership.php');
	}
	
	protected function addOurPayments() {
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);

		$query->select('*')
			  ->from($db->qn('#__rsmembership_payments'))
			  ->where($db->qn('published').' = '.$db->q('1'))
			  ->order($db->qn('ordering').' ASC');
		$db->setQuery($query);
		$payments = $db->loadObjectList();
		
		foreach ($payments as $payment) {
			RSMembership::addPlugin($this->getTranslation($payment->name), 'rsmembershipwire'.$payment->id);
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

	public function onMembershipPayment($plugin, $data, $extra, $membership, &$transaction, $html) {
		$this->loadLanguage('plg_system_rsmembership', JPATH_ADMINISTRATOR);
		$this->loadLanguage('plg_system_rsmembershipwire', JPATH_ADMINISTRATOR);

		if (preg_match('#rsmembershipwire([0-9]+)#', $plugin, $match)) {
			$id = $match[1];

			$payment = JTable::getInstance('Payment','RSMembershipTable');
			$payment->load($id);

			$tax_value = $payment->tax_value;
			if (!empty($tax_value)) {
				$tax_type = $payment->tax_type;

				// percent ?
				if ($tax_type == 0) 
					$tax_value = $transaction->price * ($tax_value / 100);

				$transaction->price = $transaction->price + $tax_value;
			}

			if (RSMembershipHelper::getConfig('trigger_content_plugins')) {
				$payment->details = JHtml::_('content.prepare', $payment->details);
			}
			
			$html = $payment->details;

			// Store the transaction so we can get an ID
			$transaction->store();
			
			$replacements = array(
				'{price}' 		=> RSMembershipHelper::getPriceFormat($transaction->price),
				'{transaction_id}' => $transaction->id,
				'{tax}'			=> RSMembershipHelper::getPriceFormat($tax_value),
				'{membership}' 	=> $membership->name,
			);
			
			if (!empty($data) && is_object($data)) {
				if (isset($data->username)) {
					$replacements['{username}'] = $data->username;
				}
				if (isset($data->name)) {
					$replacements['{name}'] = $data->name;
				}
				if (isset($data->email)) {
					$replacements['{email}'] = $data->email;
				}
				if (isset($data->coupon)) {
					$replacements['{coupon}'] = $data->coupon;
				}
				if (isset($data->fields) && is_array($data->fields)) {
					foreach ($data->fields as $field => $value) {
						if (is_array($value)) {
							$value = implode("\n", $value);
						}
						$replacements['{'.$field.'}'] = $value;
					}
				}
			}
			
			$replace = array_keys($replacements);
			$with 	 = array_values($replacements);
			
			$html = str_replace($replace, $with, $html);

			$html .= '<form method="post" action="'.JRoute::_('index.php?option=com_rsmembership&task=thankyou').'">';
			$html .= '<div class="form-actions"><input class="button btn btn-success pull-right" type="submit" value="'.JText::_('COM_RSMEMBERSHIP_CONTINUE').'" /></div>';
			$html .= '<input type="hidden" name="option" value="com_rsmembership" />';
			$html .= '<input type="hidden" name="task" value="thankyou" />';
			$html .= '</form>';
			
			// No hash for this
			$transaction->hash = '';
			$transaction->gateway = $payment->name;
			
			if ($membership->activation == 2) 
				$transaction->status = 'completed';
			
			return $html;
		}
	}
	
	protected function _loadLanguage() {
		$this->loadLanguage('plg_system_rsmembershipwire', JPATH_ADMINISTRATOR);
	}
}