<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipViewSubscribe extends JViewLegacy
{
	public function display($tpl = null) {
		$app 		= JFactory::getApplication();
		$pathway 	= $app->getPathway();
		
		// Assign variables
		$this->membership = $this->get('Membership');
		$this->extras 	  = $this->get('Extras');
		$this->params 	  = clone($app->getParams('com_rsmembership'));
		$this->user 	  = JFactory::getUser();
		$this->logged	  = (bool) !$this->user->guest;
		$this->token 	  = JHtml::_('form.token');
		
		// Assign config variables
		$this->config			 = RSMembershipHelper::getConfig();
		$this->show_login 		 = $this->config->show_login;
		$this->choose_username 	 = $this->config->choose_username;
		$this->choose_password 	 = $this->config->choose_password;
		$this->currency 		 = $this->config->currency;
		$this->one_page_checkout = $this->config->one_page_checkout;
		$this->captcha_case_sensitive = $this->config->captcha_case_sensitive;
		$this->payments 		 = RSMembership::getPlugins();
		
		// Set pathway
		$pathway->addItem($this->membership->name, JRoute::_(RSMembershipRoute::Membership($this->membership->id, $app->input->getInt('Itemid'))));
		$pathway->addItem(JText::_('COM_RSMEMBERSHIP_SUBSCRIBE'), '');
		
		switch ($this->getLayout())
		{
			default:
				// Get the encoded return url
				$this->return 				= base64_encode(JURI::getInstance());
				$this->data 				= (object) $this->get('Data');
				$this->membershipterms 		= $this->get('MembershipTerms');
				$this->has_coupons 			= $this->get('HasCoupons');
				$this->fields_validation 	= RSMembershipHelper::getFieldsValidation($this->membership->id);
				$this->fields 			 	= RSMembershipHelper::getFields();
				$this->membership_fields 	= RSMembershipHelper::getMembershipFields($this->membership->id);
				
				// Handle CAPTCHA
				$this->use_captcha 	 		= $this->get('UseCaptcha');
				$this->use_builtin 	 		= $this->get('UseBuiltin');
				$this->use_recaptcha 		= $this->get('UseReCaptcha');
				$this->use_recaptcha_new 	= $this->get('UseReCaptchaNew');
				if ($this->use_recaptcha) {
					if (!class_exists('JReCAPTCHA')) {
						require_once JPATH_ADMINISTRATOR.'/components/com_rsmembership/helpers/recaptcha/recaptchalib.php';
					}
						
					$this->show_recaptcha = JReCAPTCHA::getHTML($this->get('ReCaptchaError'));
				}
				
				if ($this->use_recaptcha_new) {
					$doc = JFactory::getDocument();
					if ($doc->getType() == 'html') {
						$doc->addScript('https://www.google.com/recaptcha/api.js?hl='.JFactory::getLanguage()->getTag());
					}
				}
				
				$this->assignExtrasView();
			break;
			
			case 'preview':
				$this->fields 				= RSMembershipHelper::getFields(false);
				$this->membership_fields 	= RSMembershipHelper::getMembershipFields($this->membership->id, false);
				$this->data 				= (object) $this->get('Data');
			break;
			
			case 'payment':
				$this->html = $this->get('Html');
			break;
		}

		// Calculate the Total
		$this->total = $this->get('Total');
		
		// Do we need to display the payment options?
		$model = $this->getModel();
		$this->showPayments = $model->showPaymentOptions();

		parent::display();
	}
	
	protected function assignExtrasView() {
		// Create the View
		$view = new JViewLegacy(array(
			'name' 		=> 'extras',
			'base_path' => JPATH_SITE.'/components/com_rsmembership'
		));

		// Create the Model
		$model = JModelLegacy::getInstance('Extras', 'RSMembershipModel');
		
		// Assign the Model to the View and set it as default.
		$view->setModel($model, true);
		
		$view->model				= &$model;
		$view->item   				= $this->membership;
		$view->extras 				= $model->getItems();
		$view->show_subscribe_btn	= false;
		
		$this->extrasview = $view->loadTemplate();
	}
}