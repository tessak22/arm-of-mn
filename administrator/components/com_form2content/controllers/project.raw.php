<?php
defined('JPATH_PLATFORM') or die('Restricted acccess');

jimport('joomla.application.component.controllerform');

class Form2ContentControllerProject extends JControllerForm
{
	public function __construct($config = array())
	{
		// Access check.
		if (!JFactory::getUser()->authorise('core.admin')) 
		{
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
		}
		
		parent::__construct($config);
	}

	public function getModel($name = 'Project', $prefix = 'Form2ContentModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
	
	function export()
	{
		$document	= JFactory::getDocument();
		$vName		= 'project';
		$vFormat	= 'raw';
		
		$document->setType($vFormat);

		// Get and render the view.
		if ($view = $this->getView($vName, $vFormat)) 
		{
			// Get the model for the view.
			$model = $this->getModel($vName);

			// Push the model into the view (as default).
			$view->setModel($model, true);

			// Push document object into the view.
			$view->assignRef('document', $document);

			$view->display();
			die();
		}
	}
	
	function createSampleFormTemplate()
	{
		$contentTypeId 	= $this->input->getInt('id');
		$overwrite 		= $this->input->getInt('overwrite', 0);	
		$classic 		= $this->input->getInt('classic', 0);	
		$model 			= $this->getModel();
		
		// clean the response
		ob_end_clean();
		echo $model->createSampleFormTemplate($contentTypeId, $overwrite, $classic);
		die();
	}
}
?>