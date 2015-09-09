<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class RSMembershipModelUser extends JModelItem 
{

	function __construct()
	{
		parent::__construct();
		
		$app = JFactory::getApplication();
		$option = 'com_rsmembership';
		
		$user = JFactory::getUser();
		if ($user->get('guest'))
		{
			$link = base64_encode(JURI::getInstance());
			$app->redirect('index.php?option=com_users&view=login&return='.$link);
		}
	}
	
	function getUser()
	{
		$user 	= JFactory::getUser();
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);

		$query->select('*')->from($db->qn('#__rsmembership_subscribers'))->where($db->qn('user_id').' = '.$db->q($user->get('id')));
		$db->setQuery($query);

		return $db->loadObject();
	}

	function save()
	{
		$user 	= JFactory::getUser();
		$fields = JFactory::getApplication()->input->get('rsm_fields', array(), 'array');
		RSMembership::createUserData($user->get('id'), $fields);
	}
	
	function _bindData($verbose=true)
	{
		$return = true;
		$rsm_fields = JFactory::getApplication()->input->get('rsm_fields', array(), 'array');

		if (empty($rsm_fields))
			return false;
		
		$fields = RSMembership::getCustomFields(array('published'=>1, 'required'=>1));

		foreach ( $fields as $field ) 
			if ( ($field->required && empty($rsm_fields[$field->name])) || (!empty($rsm_fields[$field->name]) && $field->rule && is_callable('RSMembershipValidation', $field->rule) && !call_user_func(array('RSMembershipValidation', $field->rule), $rsm_fields[$field->name])) ) 
			{
				$validation_message = JText::_($field->validation);
				if ( empty($validation_message) ) 
					$validation_message = JText::sprintf('COM_RSMEMBERSHIP_VALIDATION_DEFAULT_ERROR', JText::_($field->label));

				if ( $verbose ) 
					JError::raiseWarning(500, $validation_message);

				$return = false;
			}

		return $return;
	}

	public function getRSFieldset() 
	{
		require_once JPATH_ADMINISTRATOR.'/components/com_rsmembership/helpers/adapters/fieldset.php';

		$fieldset = new RSFieldset();
		return $fieldset;
	}
}