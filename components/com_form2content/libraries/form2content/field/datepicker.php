<?php
defined('JPATH_PLATFORM') or die('Restricted acccess');

class F2cFieldDatePicker extends F2cFieldBase
{	
	function __construct($field)
	{
		$this->reset();
		parent::__construct($field);
	}
	
	public function getPrefix()
	{
		return 'dat';
	}
	
	public function render($translatedFields, $contentTypeSettings, $parms = array(), $form, $formId)
	{
		$html 			= '';
		$value 			= $this->values['VALUE'];
		$attributes 	= $this->settings->get('dat_attributes') ? $this->settings->get('dat_attributes') : 'class="inputbox"';
		
		if($value)
		{
			$date 		= new JDate($value);
			$dateFormat = str_replace('%', '', $this->f2cConfig->get('date_format'));
			$value 		= $date->format($dateFormat);			
		}

		$html .= HtmlHelper::renderCalendar($value, $this->values['VALUE'], $this->elementId, $this->elementId, $this->f2cConfig->get('date_format'), $attributes);

		if(JFactory::getApplication()->isSite())
		{
			$html .= $this->renderRequiredText($contentTypeSettings);
			$html 	.= $this->getFieldDescription($translatedFields);
		}
		
		$html .= $this->renderHiddenField('hid'.$this->elementId, $this->internal['fieldcontentid']);
		
		return $html;
	}
	
	public function prepareSubmittedData($formId)
	{
		$jinput = JFactory::getApplication()->input;
		
		$this->internal['fieldcontentid'] 	= $jinput->getInt('hid'.$this->elementId);		
		$this->values['VALUE'] 				= '';		
		$value 								= $jinput->getString($this->elementId, '');
		
		if($value)
		{
			$date = F2cDateTimeHelper::ParseDate($value, $this->f2cConfig->get('date_format'));
			$this->values['VALUE'] = ($date) ? $date->toISO8601() : '';						
		}
		
		return $this;
	}
	
	public function store($formid)
	{
		$content	= array();
		$fieldId 	= $this->internal['fieldcontentid'];
		$value 		= $this->values['VALUE'];
		$action 	= ($value) ? (($fieldId) ? 'UPDATE' : 'INSERT') : (($fieldId) ? 'DELETE' : '');
		$content[] 	= new F2cFieldHelperContent($fieldId, 'VALUE', $value, $action);
		
		return $content;		
	}
	
	public function validate()
	{
		if($this->settings->get('requiredfield') && empty($this->values['VALUE']))
		{
			throw new Exception($this->getRequiredFieldErrorMessage());
		}
	}
	
	public function getClientSideValidationScript(&$validationCounter)
	{
		$script = parent::getClientSideValidationScript($validationCounter);
		
		$dateFormat		= $this->f2cConfig->get('date_format');
		$displayFormat	= F2cDateTimeHelper::getTranslatedDateFormat();
		
		$script .= 'if(!F2C_ValDateField(\''.$this->elementId.'\', \''.$dateFormat.'\'))';
		$script .= '{ ';
		$script .= 'alert(\'' . sprintf(JText::_('COM_FORM2CONTENT_ERROR_DATE_FIELD_INCORRECT_DATE', true), $this->title, $displayFormat) . '\'); ';
		$script .= 'return false; } ';
		
		return $script;
	}
	
	public function export($xmlFields, $formId)
	{
      	$xmlField = $xmlFields->addChild('field');
      	$xmlField->fieldname = $this->fieldname;
      	$xmlFieldContent = $xmlField->addChild('contentDate');
      	$xmlFieldContent->value = $this->values['VALUE'];
    }
    
	public function import($xmlField, $existingInternalData, $formId)
	{
		$this->values['VALUE'] = (string)$xmlField->contentDate->value;
		$this->internal['fieldcontentid'] = $formId ? $existingInternalData['fieldcontentid'] : 0;
	}
	
	public function addTemplateVar($templateEngine, $form)
	{
		$value 			= '';
		$unixTimestamp 	= '';
		$dateFormat		= str_replace('%', '', $this->f2cConfig->get('date_format'));
		
		if($this->values['VALUE'])
		{
			$date 			= new JDate($this->values['VALUE']);
			$value			= $date->format($dateFormat);
			$unixTimestamp	= $date->toUnix();
		}

		$templateEngine->addVar($this->fieldname, $value);
		$templateEngine->addVar($this->fieldname . '_RAW', $unixTimestamp);
	}
	
	public function getTemplateParameterNames()
	{
		$names = array(	strtoupper($this->fieldname).'_RAW');
		
		return array_merge($names, parent::getTemplateParameterNames());
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