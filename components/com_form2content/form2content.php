<?php
defined('JPATH_PLATFORM') or die('Restricted acccess');

require_once JPATH_COMPONENT_SITE.DIRECTORY_SEPARATOR.'const.form2content.php';
require_once JPATH_COMPONENT_SITE.DIRECTORY_SEPARATOR.'factory.form2content.php';

JLoader::registerPrefix('F2c', JPATH_COMPONENT_SITE.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'form2content');

// Include dependancies
jimport('joomla.application.component.controller');

// Check if we need to enable the logging
$f2cConfig = F2cFactory::getConfig();

if($f2cConfig->get('enable_logging', 0))
{
	jimport('joomla.log.log');
	JLog::addLogger(array('text_file' => 'com_form2content.logging.php'), JLog::ALL, array('com_form2content'));		
}

$jinput = JFactory::getApplication()->input;
$task 	= $jinput->get('task');

if(empty($task))
{
	switch($jinput->get('view'))
	{
		case 'forms':
			$jinput->set('task', 'forms.display');
			break;
		case 'form':
			$jinput->set('task', 'form.display');
			break;
	}
}

// Execute the task.
$controller	= JControllerLegacy::getInstance('Form2Content');
$controller->execute($jinput->get('task'));
$controller->redirect();
?>