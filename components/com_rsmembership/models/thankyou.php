<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class RSMembershipModelThankYou extends JModelLegacy
{
	var $message;
	
	function __construct()
	{
		parent::__construct();

		$app 	 = JFactory::getApplication();
		$option  = 'com_rsmembership';

		$session  = JFactory::getSession();
		$action   = $session->get($option.'.subscribe.action', null);		
		$message  = $session->get($option.'.subscribe.thankyou', null);
		$redirect = $session->get($option.'.subscribe.redirect', null);

		$session->set($option.'.subscribe.action', null);
		$session->set($option.'.subscribe.thankyou', null);
		$session->set($option.'.subscribe.redirect', null);

		if ( is_null($action) ) 
			$app->redirect(JRoute::_('index.php?option=com_rsmembership', false));
		
		if ( $action == 1 ) 
			$app->redirect($redirect);
		
		$this->message = $message;
	}
	
	function getMessage()
	{		
		return $this->message;
	}
}