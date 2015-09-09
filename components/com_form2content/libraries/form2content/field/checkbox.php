<?php
defined('JPATH_PLATFORM') or die('Restricted acccess');

class F2cFieldCheckbox extends F2cFieldBase
{	
	function __construct($field)
	{
		$this->reset();
		parent::__construct($field);
		
	}
	
	public function getPrefix()
	{
		return 'chk';
	}
		
	public function render($translatedFields, $contentTypeSettings, $parms = array(), $form, $formId)
	{
		$html		= '';
		$value		= $this->values['VALUE'];
		$html 		.= '<input type="checkbox" name="'.$this->elementId.'" id="'.$this->elementId.'" '.$this->settings->get('chk_attributes').' value="true"'.(($value) ? ' checked' : '') . '>';

		if(JFactory::getApplication()->isSite())
		{
			$html 	.= $this->renderRequiredText($contentTypeSettings);
			$html 	.= $this->getFieldDescription($translatedFields);
		}
		
		$html 	.= $this->renderHiddenField('hid'.$this->elementId, $this->internal['fieldcontentid']);
			
		return $html;
	}
	
	public function prepareSubmittedData($formId)
	{
		$jinput = JFactory::getApplication()->input;	
		$this->internal['fieldcontentid'] = $jinput->getInt('hid'.$this->elementId);
		$this->values['VALUE'] = $jinput->getString($this->elementId);
		
		return $this;
	}
	
	public function store($formId)
	{
		$content 	= array();
		$value 		= $this->values['VALUE'];
		$fieldId 	= $this->internal['fieldcontentid'];
		$action 	= ($value && strtolower($value) != 'false') ? (($fieldId) ? 'UPDATE' : 'INSERT') : (($fieldId) ? 'DELETE' : '');
		$content[] 	= new F2cFieldHelperContent($fieldId, 'VALUE', $value, $action);
		
		return $content;		
	}
	
	public function validate()
	{
		if($this->settings->get('requiredfield') && (empty($this->values['VALUE']) || strtolower($this->values['VALUE']) == 'false'))
		{
			throw new Exception($this->getRequiredFieldErrorMessage());
		}
	}
	
	public function export($xmlFields, $formId)
	{
      	$xmlField = $xmlFields->addChild('field');
      	$xmlField->fieldname = $this->fieldname;
      	$xmlFieldContent = $xmlField->addChild('contentBoolean');
      	$xmlFieldContent->value = $this->values['VALUE'] ? $this->values['VALUE'] : 'false';
	}
	
	public function import($xmlField, $existingInternalData, $formId)
	{
		$this->values['VALUE'] = (string)$xmlField->contentBoolean->value;
		$this->internal['fieldcontentid'] = $formId ? $existingInternalData['fieldcontentid'] : 0;
	}
	
	public function addTemplateVar($templateEngine, $form)
	{
		if($this->values['VALUE'])
		{
			$templateEngine->addVar($this->fieldname, $this->values['VALUE']); 
		}
		else 
		{
			$templateEngine->addVar($this->fieldname, '');
		}
	}	
	
	public function setData($data)
	{
		if($data->attribute)
		{
			$this->values[$data->attribute] 	= $data->content;
			$this->internal['fieldcontentid'] 	= $data->fieldcontentid;
		}
	}
}
?>