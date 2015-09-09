<?php
defined('JPATH_PLATFORM') or die('Restricted acccess');

class F2cFieldSingleLineText extends F2cFieldBase
{	
	function __construct($field)
	{
		$this->reset();
		parent::__construct($field);
	}
	
	public function getPrefix()
	{
		return 'slt';
	}
	
	public function render($translatedFields, $contentTypeSettings, $parms = array(), $form, $formId)
	{
		if(!count($parms))
		{
			$parms = JFactory::getApplication()->isSite() ? array(50, 100) : array(100, 100);
		}
		
		$html			= '';
		$value 			= $this->values['VALUE'];
		$size			= $this->settings->get('slt_size', $parms[0]);	
		$maxLength		= $this->settings->get('slt_max_length', $parms[1]);	
		$attributes		= $this->settings->get('slt_attributes');	
		$htmlInputType	= $this->settings->get('html_inputtype', 'text');
		
		$html .= $this->renderTextBox($this->elementId, $value, $size, $maxLength, $attributes, $htmlInputType);
		
		if(JFactory::getApplication()->isSite())
		{
			$html .= $this->renderRequiredText($contentTypeSettings);
			$html .= $this->getFieldDescription($translatedFields);
		}
		
		$html .= $this->renderHiddenField('hid'.$this->elementId, $this->internal['fieldcontentid']);

		return $html;
	}
	
	public function prepareSubmittedData($formId)
	{
		$jinput = JFactory::getApplication()->input;
		
		$this->internal['fieldcontentid'] = $jinput->getInt('hid'.$this->elementId);
		$this->values['VALUE'] = $jinput->get($this->elementId, '', 'RAW');

		return $this;
	}
	
	public function store($formid)
	{
		$content 	= array();
		$value 		= isset($this->values['VALUE']) ? $this->values['VALUE'] : '';
		$fieldId 	= $this->internal['fieldcontentid'];
		$action 	= ($value != '') ? (($fieldId) ? 'UPDATE' : 'INSERT') : (($fieldId) ? 'DELETE' : '');
		$content[] 	= new F2cFieldHelperContent($fieldId, 'VALUE', $value, $action);

		return $content;		
	}
	
	public function validate()
	{
		$value = trim($this->values['VALUE']);
		
		if($this->settings->get('requiredfield') && empty($value))
		{
			throw new Exception($this->getRequiredFieldErrorMessage());
		}
				
		$pattern = $this->settings->get('slt_pattern_server');
		
		if(!empty($pattern))
		{
			if(!preg_match($pattern, $this->values['VALUE']))
			{
				throw new Exception($this->settings->get('slt_pattern_message') != '' ? $this->settings->get('slt_pattern_message') : sprintf(JText::_('COM_FORM2CONTENT_VALIDATION_PATTERN_MESSAGE_EMPTY'), $this->title));
			}
		}
	}

	public function getClientSideValidationScript(&$validationCounter)
	{
		$script = parent::getClientSideValidationScript($validationCounter);
		
		$pattern = $this->settings->get('slt_pattern_client');
		
		if(!empty($pattern))
		{
			$message = $this->settings->get('slt_pattern_message');
		
			if(empty($message))
			{
				$message = sprintf(JText::_('COM_FORM2CONTENT_VALIDATION_PATTERN_MESSAGE_EMPTY'), $this->title);
			}
			
			$script .= 'if(!F2C_ValPatternMatch("t'.$this->id.'", '.$pattern.'))';
			$script .= '{ ';
			$script .= 'alert(\'' . sprintf(JText::_($message, true), $this->title) . '\'); ';
			$script .= 'return false; } ';
		}
		
		return $script;
	}
	
	public function export($xmlFields, $formId)
	{
      	$xmlField = $xmlFields->addChild('field');
      	$xmlField->fieldname = $this->fieldname;
      	$xmlFieldContent = $xmlField->addChild('contentSingleTextValue');
      	$xmlFieldContent->value = $this->values['VALUE'];
    }
    
	public function import($xmlField, $existingInternalData, $formId)
	{
		$this->values['VALUE'] = (string)$xmlField->contentSingleTextValue->value;
		$this->internal['fieldcontentid'] = $formId ? $existingInternalData['fieldcontentid'] : 0;
	}
	
	public function addTemplateVar($templateEngine, $form)
	{
		$templateEngine->addVar($this->fieldname, $this->stringHTMLSafe($this->values['VALUE']));
		$templateEngine->addVar($this->fieldname .'_RAW', $this->values['VALUE']);
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