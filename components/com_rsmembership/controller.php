<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class RSMembershipController extends JControllerLegacy
{
	public function __construct() {
		parent::__construct();

		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_rsmembership/tables');

		$document = JFactory::getDocument();
		$config   = RSMembershipConfig::getInstance();
		$version = (string) new RSMembershipVersion();

		// Load our CSS
		$document->addStyleSheet(JUri::root(true).'/components/com_rsmembership/assets/css/rsmembership.css?v='.$version);
		// Load our JS
		$document->addScript(JUri::root(true).'/components/com_rsmembership/assets/js/rsmembership.js?v='.$version);
		
		if (!RSMembershipHelper::isJ3()) {
			// Load 2.5 CSS
			$document->addStyleSheet(JUri::root(true).'/components/com_rsmembership/assets/css/j2.css?v='.$version);
			
			// Load Bootstrap on 2.5.x
			if ($config->get('load_bootstrap')) {
				$document->addStyleSheet(JUri::root(true).'/components/com_rsmembership/assets/css/bootstrap.min.css?v='.$version);
				$document->addScript(JUri::root(true).'/components/com_rsmembership/assets/js/jquery.min.js?v='.$version);
				$document->addScript(JUri::root(true).'/components/com_rsmembership/assets/js/jquery.noconflict.js?v='.$version);
				$document->addScript(JUri::root(true).'/components/com_rsmembership/assets/js/bootstrap.min.js?v='.$version);
			}
		} else {
			// Load 3.x CSS
			$document->addStyleSheet(JUri::root(true).'/components/com_rsmembership/assets/css/j3.css?v='.$version);
			
			// Load Bootstrap on 3.x
			if ($config->get('load_bootstrap')) {
				JHtml::_('bootstrap.framework');
			}
		}
	}

	public function display($cachable = false, $urlparams = false) {
		parent::display(true, $urlparams);
	}
	
	// @desc Entry point for the subscription process.
	public function subscribe($new=true) {
		$app 			= JFactory::getApplication();
		$membership_id 	= $app->input->get('cid', 0, 'int');
		$extras			= $app->input->get('rsmembership_extra', array(), 'array');
		$model 			= $this->getModel('subscribe');
		
		// Empty the session everytime this page is accessed directly and not from within the controller
		if ($new) {
			$model->clearData();
		}
		
		// Try to bind the membership
		if (!$model->bindMembership($membership_id)) {
			$app->enqueueMessage($model->getError(), 'error');
			return $app->redirect(JRoute::_('index.php?option=com_rsmembership', false));
		}
		
		// Check if the user can subscribe to this membership
		if (!$model->canSubscribe()) {
			$app->enqueueMessage($model->getError(), 'error');
			return $app->redirect(JRoute::_('index.php?option=com_rsmembership', false));
		}
		
		// Check if it's out of stock.
		$membership = $model->getMembership();
		if ($membership->stock < 0) {
			$app->enqueueMessage(JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_OUT_OF_STOCK'), 'error');
			return $app->redirect(JRoute::_('index.php?option=com_rsmembership', false));
		}
		
		// Try to bind extras
		if ($extras) {
			$model->bindExtras($extras);
		}

		$view = $this->getView('subscribe', 'html');
		$view->setModel($model, true);
		$view->display();
	}
	
	// @desc Validation during subscription.
	public function validateSubscribe() {
		$app 	= JFactory::getApplication();
		$model 	= $this->getModel('subscribe');
		
		// Get needed data.
		$membership_id 	= $app->input->get('cid', 0, 'int');
		$extras			= $app->input->get('rsmembership_extra', array(), 'array');
		$coupon 		= $app->input->get('coupon', '', 'string');
		$data			= array(
			'username' 			=> $app->input->get('username', '', 'string'),
			'email' 			=> $app->input->get('email', '', 'string'),
			'name' 				=> $app->input->get('name', '', 'string'),
			'password'			=> $app->input->get('password',  '', 'raw'),
			'password2'			=> $app->input->get('password2', '', 'raw'),
			'fields'			=> $app->input->get('rsm_fields', array(), 'array'),
			'membership_fields'	=> $app->input->get('rsm_membership_fields', array(), 'array')
		);
		
		// Try to bind the membership
		if (!$model->bindMembership($membership_id)) {
			$app->enqueueMessage($model->getError(), 'error');
			return $app->redirect(JRoute::_('index.php?option=com_rsmembership', false));
		}
		
		// Try to bind extras
		if ($extras) {
			$model->bindExtras($extras);
		}
		
		// Check if the user can subscribe to this membership
		if ($data['email'] && ($userId = RSMembership::checkUser($data['email']))) {
			$user = JFactory::getUser($userId);
			if (!$model->canSubscribe($user)) {
				$app->enqueueMessage($model->getError(), 'error');
				return $app->redirect(JRoute::_('index.php?option=com_rsmembership', false));
			}
		}
		
		// Store data in the session here, we're going to need it later on.
		$model->storeData(array(
			'id' 		=> $membership_id,
			'extras' 	=> $extras,
			'data'		=> $data,
			'coupon'	=> $coupon
		));
		
		// Validate Captcha, bind data and check coupon code.
		if (!$model->validateCaptcha() || !$model->bindData($data) || !$model->bindCoupon($coupon)) {			
			// Show some errors.
			$app->enqueueMessage(JText::_('COM_RSMEMBERSHIP_PLEASE_TYPE_FIELDS'), 'error');
			$app->enqueueMessage($model->getError(), 'error');
			
			// Redirect back.
			$app->redirect(JRoute::_('index.php?option=com_rsmembership&task=back&cid='.$membership_id, false));
		}
		
		// Mark data as correct (to prevent people from accessing the next pages with invalid data).
		$model->markCorrectData($membership_id);
		
		// If one page checkout is enabled, just redirect to the payment gateway.
		if (RSMembershipHelper::getConfig('one_page_checkout')) {
			$app->input->set('payment', $app->input->get('payment', 'none', 'cmd'));
			return $this->paymentRedirect();
		} else {
			// Show the preview page.
			$view = $this->getView('subscribe', 'html');
			$view->setLayout('preview');
			$view->setModel($model, true);
			$view->display();
		}
	}
	
	public function paymentRedirect() {
		$payment = JFactory::getApplication()->input->get('payment', 'none', 'cmd');
		$this->setRedirect(JRoute::_('index.php?option=com_rsmembership&task=payment&payment='.$payment, false));
	}
	
	public function payment() {
		$model 	= $this->getModel('subscribe');
		$app 	= JFactory::getApplication();
		
		// Set data from the session...
		if ($data = $model->getData()) {
			foreach ($data as $key => $value) {
				$app->input->set($key, $value);
			}
		}
		
		// Get needed data.
		$membership_id 	= $app->input->get('cid', 0, 'int');
		$extras			= $app->input->get('rsmembership_extra', array(), 'array');
		$coupon 		= $app->input->get('coupon', '', 'string');
		
		$username 	= $app->input->get('username', '', 'string');
		$username   = preg_replace('#[<>"\'%;()&\\\\]|\\.\\./#', '', $username);
		$data			= array(
			'username' 			=> $username,
			'email' 			=> $app->input->get('email', '', 'string'),
			'name' 				=> $app->input->get('name', '', 'string'),
			'password'			=> $app->input->get('password',  '', 'raw'),
			'password2'			=> $app->input->get('password2', '', 'raw'),
			'fields'			=> $app->input->get('rsm_fields', array(), 'array'),
			'membership_fields'	=> $app->input->get('rsm_membership_fields', array(), 'array')
		);
		$paymentPlugin 	= $app->input->get('payment', 'none', 'cmd');
		
		// Try to bind the membership
		if (!$model->bindMembership($membership_id)) {
			$app->enqueueMessage($model->getError(), 'error');
			return $app->redirect(JRoute::_('index.php?option=com_rsmembership', false));
		}
		
		// Try to bind extras
		if ($extras) {
			$model->bindExtras($extras);
		}
		
		if (!$model->bindData($data) || !$model->bindCoupon($coupon) || !$model->isCorrectData()) {
			// Show some errors.
			$app->enqueueMessage(JText::_('COM_RSMEMBERSHIP_PLEASE_TYPE_FIELDS'), 'error');
			$app->enqueueMessage($model->getError(), 'error');
			
			// Redirect back.
			$app->redirect(JRoute::_('index.php?option=com_rsmembership&task=back&cid='.$membership_id, false));
		}
		
		$membership	 	= $model->getMembership();
		$transaction 	= $model->saveTransaction($paymentPlugin);
		$showPayments 	= $model->showPaymentOptions();
		if (!$showPayments) {
			$app->redirect(JRoute::_('index.php?option=com_rsmembership&task=thankyou', false));
		}
		
		// Show the payment page.
		$view = $this->getView('subscribe', 'html');
		$view->setLayout('payment');
		$view->setModel($model, true);
		$view->display();
	}
	
	public function back() {
		$input 			= JFactory::getApplication()->input;
		$model 			= $this->getModel('subscribe');
		$membership_id 	= $input->get('cid', 0, 'int');
		
		// Set data back into the request
		if ($data = $model->getData()) {
			foreach ($data as $key => $value) {
				$input->set($key, $value);
			}
		}
		
		// Fallback for expired sessions
		if (empty($data) || empty($data['cid'])) {
			$input->set('cid', $membership_id);
		}
		
		$this->subscribe(false);
	}
	
	public function captcha() {
		$app   = JFactory::getApplication();
		$model = $this->getModel('subscribe');
		if ($model->getUseBuiltin()) {
			// Load Captcha
			if (!class_exists('JSecurImage')) {
				require_once JPATH_ADMINISTRATOR.'/components/com_rsmembership/helpers/securimage/securimage.php';
			}
			
			ob_end_clean();
			
			$captcha 				= new JSecurImage();
			$captcha->num_lines 	= RSMembershipHelper::getConfig('captcha_lines') ? 8 : 0;
			$captcha->code_length 	= RSMembershipHelper::getConfig('captcha_characters');
			$captcha->image_width 	= 30 * $captcha->code_length + 50;
			$captcha->show();
		}
		
		$app->close();
	}
	
	public function checkUsername() {
		$app			= JFactory::getApplication();
		$model 			= $this->getModel('subscribe');
		$suggestions 	= $model->checkUsername();
		
		echo implode('|', $suggestions);
		$app->close();
	}
	
	public function download() 
	{
		JFactory::getApplication()->input->set('view', 'mymembership');
		JFactory::getApplication()->input->set('layout', 'default');

		parent::display();
	}

	public function thankyou()
	{
		JFactory::getApplication()->input->set('view', 'thankyou');
		JFactory::getApplication()->input->set('layout', 'default');

		parent::display();
	}

	public function upgrade()
	{
		
		$user 	= JFactory::getUser();
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$jinput = JFactory::getApplication()->input;
		$to_id	= $jinput->get('to_id', 0, 'int');
		
		
		$query->select($db->qn('unique'))->from($db->qn('#__rsmembership_memberships'))->where($db->qn('id').' = '.$db->q($to_id));
		$db->setQuery($query);
		
		if ( $db->loadResult() > 0 ) 
		{
			$query->clear();
			$query->select($db->qn('id'))->from($db->qn('#__rsmembership_membership_subscribers'))->where($db->qn('user_id').' = '.$db->q($user->get('id')))->where( $db->qn('membership_id').' = '.$db->q($to_id) );
			$db->setQuery($query);

			if ( $db->loadResult() )
			{
				JError::raiseWarning(500, JText::_('COM_RSMEMBERSHIP_ALREADY_SUBSCRIBED'));
				return $this->setRedirect(JRoute::_('index.php?option=com_rsmembership', false));
			}
		}

		$jinput->set('view', 'upgrade');
		$jinput->set('layout', 'default');

		parent::display();
	}
	
	public function upgradePaymentRedirect()
	{
		$jinput  = JFactory::getApplication()->input;
		$payment = $jinput->get('payment', 'none', 'string');
		$cid 	 = $jinput->get('cid', 0, 'int');
		$model 	 = $this->getModel('upgrade');
		$upgrade = $model->getUpgrade();
		
		//$membership_fields 	= RSMembership::getCustomMembershipFields($upgrade->membership_to_id);
		
		$all_fields 			= RSMembership::getCustomFields();
		$membership_fields 		= RSMembership::getCustomMembershipFields($upgrade->membership_to_id);
		$all_fields 			= array_merge($all_fields, $membership_fields);
		
		if (count($all_fields)) {
			$to_id 	 		 		= $jinput->get('to_id', 0, 'int');
			$verifyFieldsMembership = $jinput->get('rsm_membership_fields', array(), 'array');
			$verifyFieldsUser 	 	= $jinput->get('rsm_fields', array(), 'array');
			$verifyFields			= array_merge($verifyFieldsUser, $verifyFieldsMembership);
			
			$fields  = $all_fields;
			foreach ($fields as $field) {
				if (($field->required && empty($verifyFields[$field->name])) ||
					($field->rule && !empty($verifyFields[$field->name]) && is_callable('RSMembershipValidation', $field->rule) && !call_user_func(array('RSMembershipValidation', $field->rule), $verifyFields[$field->name]))) {
					$message = JText::_($field->validation);
					if (empty($message)) {
						$message = JText::sprintf('COM_RSMEMBERSHIP_VALIDATION_DEFAULT_ERROR', JText::_($field->label));
					}
					
					JError::raiseWarning(500, $message);
					return $this->setRedirect(JRoute::_('index.php?option=com_rsmembership&task=upgrade&cid='.$cid.'&to_id='.$to_id, false));
				}
			}
			
			$model->storeData(array(
				'id' 		=> $to_id,
				'membership_fields'		=> $verifyFieldsMembership,
				'custom_fields'		=> $verifyFieldsUser
			));
		}
		
		$this->setRedirect(JRoute::_('index.php?option=com_rsmembership&task=upgradepayment&payment='.$payment, false));
	}

	public function upgradePayment()
	{
		JFactory::getApplication()->input->set('view', 'upgrade');
		JFactory::getApplication()->input->set('layout', 'payment');
		
		parent::display();
	}
	
	public function renew()
	{
		JFactory::getApplication()->input->set('view', 'renew');
		JFactory::getApplication()->input->set('layout', 'default');
		
		parent::display();
	}
	
	public function renewPaymentRedirect()
	{
		//$payment = JFactory::getApplication()->input->get('payment', 'none', 'string');
		
		$jinput  	= JFactory::getApplication()->input;
		$payment 	= $jinput->get('payment', 'none', 'string');
		$cid 	 	= $jinput->get('cid', 0, 'int');
		$model 	 	= $this->getModel('renew');
		$membership = $model->getMembership();
		
		$all_fields 			= RSMembership::getCustomFields();
		$membership_fields 		= RSMembership::getCustomMembershipFields($membership->id);
		$all_fields 			= array_merge($all_fields, $membership_fields);
		
		if (count($all_fields)) {
			$verifyFieldsMembership = $jinput->get('rsm_membership_fields', array(), 'array');
			$verifyFieldsUser 	 	= $jinput->get('rsm_fields', array(), 'array');
			$verifyFields			= array_merge($verifyFieldsUser, $verifyFieldsMembership);
			$fields  		 	    = $all_fields;
			foreach ($fields as $field) {
				if (($field->required && empty($verifyFields[$field->name])) ||
					($field->rule && !empty($verifyFields[$field->name]) && is_callable('RSMembershipValidation', $field->rule) && !call_user_func(array('RSMembershipValidation', $field->rule), $verifyFields[$field->name]))) {
					$message = JText::_($field->validation);
					if (empty($message)) {
						$message = JText::sprintf('COM_RSMEMBERSHIP_VALIDATION_DEFAULT_ERROR', JText::_($field->label));
					}
					
					JError::raiseWarning(500, $message);
					return $this->setRedirect(JRoute::_('index.php?option=com_rsmembership&task=renew&cid='.$cid, false));
				}
			}
			
			$model->storeData(array(
				'membership_fields'		=> $verifyFieldsMembership,
				'custom_fields'		=> $verifyFieldsUser
			));
		}
		
		
		$this->setRedirect(JRoute::_('index.php?option=com_rsmembership&task=renewpayment&payment='.$payment, false));
	}

	public function renewPayment()
	{
		JFactory::getApplication()->input->set('view', 'renew');
		JFactory::getApplication()->input->set('layout', 'payment');
		
		parent::display();
	}
	
	public function addExtra()
	{
		$extra_id 			 = JFactory::getApplication()->input->get('extra_id', 0, 'int');
		$membership_id 		 = JFactory::getApplication()->input->get('cid', 0, 'int');
		
		$my_membership_model = JModelLegacy::getInstance('MyMembership', 'RSMembershipModel');
		$add_extra_model 	 = JModelLegacy::getInstance('AddExtra', 'RSMembershipModel');

		$bought_extras 		 = $my_membership_model->getBoughtExtras();
		$current_extra 		 = $add_extra_model->getExtra();

		// check if extra is already purchased
		if (empty($current_extra) || ( $current_extra->type != 'checkbox' && isset($bought_extras[$current_extra->extra_id]) ) ) {
			JError::raiseWarning(500, JText::_('COM_RSMEMBERSHIP_ALREADY_BOUGHT_EXTRA'));
			$this->setRedirect(JRoute::_(RSMembershipRoute::MyMembership($membership_id), false));
		}

		JFactory::getApplication()->input->set('view', 'addextra');
		JFactory::getApplication()->input->set('layout', 'default');

		parent::display();
	}

	public function addExtraPaymentRedirect()
	{
		$payment 	= JFactory::getApplication()->input->get('payment', 'none', 'string');
		$this->setRedirect(JRoute::_('index.php?option=com_rsmembership&task=addextrapayment&payment='.$payment, false));
	}
	
	public function addExtraPayment()
	{
		JFactory::getApplication()->input->set('view', 'addextra');
		JFactory::getApplication()->input->set('layout', 'payment');
		
		parent::display();
	}
	
	public function validateuser() 
	{
		// Check for request forgeries
		JSession::checkToken() or jexit('Invalid Token');
		
		$model = $this->getModel('user');
		if (!$model->_bindData())
		{
			JError::raiseWarning(500, JText::_('COM_RSMEMBERSHIP_PLEASE_TYPE_FIELDS'));
			JFactory::getApplication()->input->set('view', 'user');
			JFactory::getApplication()->input->set('layout', 'default');

			parent::display();
		}
		else
		{
			$model->save();
			// Redirect
			$this->setRedirect(JRoute::_('index.php?option=com_rsmembership&view=user', false), JText::_('COM_RSMEMBERSHIP_USER_SAVED'));
		}
	}

	public function cancel()
	{
		$model = $this->getModel('mymembership');
		$model->cancel();
		
		$this->setRedirect(JRoute::_('index.php?option=com_rsmembership&view=mymembership&cid='.$model->getCid(), false), JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_CANCELLED'));
	}
}