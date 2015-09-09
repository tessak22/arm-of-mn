<?php
// No direct access
defined('JPATH_PLATFORM') or die;

require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_form2content'.DIRECTORY_SEPARATOR.'utils.form2content.php');

jimport('joomla.application.component.controller');

class Form2ContentController extends JControllerLegacy
{
	protected $default_view = 'forms';

	public function checkCaptcha()
	{
		if(!function_exists('recaptcha_check_answer'))
		{
			require_once(JPATH_COMPONENT_SITE.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'recaptcha'.DIRECTORY_SEPARATOR.'recaptchalib.php');
		}
		
		$app			= JFactory::getApplication();
		$challengeField = $app->input->getString('challenge','');
		$responseField 	= $app->input->getString('response','');		
		$resp 			= recaptcha_check_answer(F2cFactory::getConfig()->get('recaptcha_private_key'), $_SERVER["REMOTE_ADDR"], $challengeField, $responseField);	

		if($resp->is_valid)
		{
			$app->setUserState('F2cCaptchaState', '1');
			echo 'VALID';
		}
		else
		{
			$app->setUserState('F2cCaptchaState', '0');
			echo $resp->error;
		}		
	}
	
	public function ArticleImportCron()
	{
		$app		= JFactory::getApplication();
		$logFile	= Path::Combine(JFactory::getConfig()->get('log_path'), JFactory::getDate()->format('Ymd') . '_f2c_import.log');
		$log		= '';
				
		$model 	= $this->getModel('Form');
		
		$model->import();
		
		$queue = $app->getMessageQueue();

		if(JFile::exists($logFile))
		{
			$log = file_get_contents($logFile);
		}
		
		if(count($queue))
		{
			foreach($queue as $queueItem) 
			{
				$log .= $queueItem['message'] . PHP_EOL;
			}
		}
		
		JFile::write($logFile, $log);
		die();
	}
}
