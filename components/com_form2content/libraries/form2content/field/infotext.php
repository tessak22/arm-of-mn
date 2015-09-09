<?php
defined('JPATH_PLATFORM') or die('Restricted acccess');

class F2cFieldInfoText extends F2cFieldBase
{	
	public function getPrefix()
	{
		return 'inf';
	}
	
	public function reset()
	{
	}
	
	public function render($translatedFields, $contentTypeSettings, $parms = array(), $form, $formId)
	{
		return $this->settings->get('inf_text') . $this->getFieldDescription($translatedFields);
	}
	
	public function prepareSubmittedData($formId)
	{
		return $this;
	}
	
	public function store($formid)
	{
		return array();		
	}
	
	public function renderLabel($translatedFields)
	{
		return '';
	}
	
	public function validate()
	{
	}
	
	public function export($xmlFields, $formId)
	{
	}
	
	public function import($xmlField, $existingInternalData, $formId)
	{
	}	
	
	public function addTemplateVar($templateEngine, $form)
	{
		$templateEngine->addVar($this->fieldname, '');
	}
	
	public function getTemplateParameterNames()
	{
		return array();
	}

	public function setData($data)
	{
	}	
}
?>