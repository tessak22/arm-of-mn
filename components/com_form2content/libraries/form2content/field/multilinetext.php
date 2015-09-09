<?php
defined('JPATH_PLATFORM') or die('Restricted acccess');

class F2cFieldMultiLineText extends F2cFieldBase
{	
	function __construct($field)
	{
		$this->reset();
		parent::__construct($field);
	}
	
	public function getPrefix()
	{
		return 'mlt';
	}
	
	public function render($translatedFields, $contentTypeSettings, $parms = array(), $form, $formId)
	{
		if(!count($parms))
		{
			$parms = JFactory::getApplication()->isSite() ? array('cols="50" rows="5" style="width:500px; height:120px"') : array('cols="100" rows="6"');
		}
		
		$html 					= '';
		$fieldHtml 				= '';
		$attribs				= '';
		$maxNumChars 			= (int)$this->settings->get('mlt_max_num_chars');		
		$value 					= $this->values['VALUE'];
		
		if((int)$this->settings->get('mlt_num_rows')) $attribs .= ' rows="'.(int)$this->settings->get('mlt_num_rows').'"';
		if((int)$this->settings->get('mlt_num_cols')) $attribs .= ' cols="'.(int)$this->settings->get('mlt_num_cols').'"';		
		if($this->settings->get('mlt_attributes')) $attribs .= ' '.$this->settings->get('mlt_attributes');

		if(!$attribs)
		{
			$attribs = $parms[0];
			$attribs .= ' class="text_area"';			
		}
		
		$fieldHtml .= ' '.$attribs;
		
		if($maxNumChars)
		{
			if(function_exists('mb_substr_count') && function_exists('mb_substr') && function_exists('mb_strlen'))
			{
				$numNewLines = mb_substr_count($value, "\r\n", 'UTF-8');
				$charsRemaining = $maxNumChars + $numNewLines - mb_strlen($value, 'UTF-8');			
				$fieldValue = mb_substr($value, 0, $maxNumChars + $numNewLines, 'UTF-8');
			}
			else
			{
				$numNewLines = substr_count($value, "\r\n");
				$charsRemaining = $maxNumChars + $numNewLines - strlen($value);			
				$fieldValue = substr($value, 0, $maxNumChars + $numNewLines);
			}
			
			if($charsRemaining < 0)
			{
				$charsRemaining = 0;
			}
			
			$fieldHtml .= ' onKeyDown="F2C_limitTextArea(this.form.'.$this->elementId.',this.form.'.$this->elementId .'remLen,'.$maxNumChars.');" onKeyUp="F2C_limitTextArea(this.form.' . $this->elementId . ',this.form.'.$this->elementId .'remLen,'.$maxNumChars.');"';
		}

		$html .= '<textarea name="'.$this->elementId.'" id="'.$this->elementId.'"'.$fieldHtml.'>'.$value.'</textarea>';
		
		if($maxNumChars)
		{
			$html .= '<div style="clear:both;"><input readonly type="text" name="'.$this->elementId .'remLen" size="6" width="6" value="'.$charsRemaining.'" class="mlt_charsleft"> '.Jtext::_('COM_FORM2CONTENT_CHARACTERS_LEFT').'</div>';		
		}
		
		if(JFactory::getApplication()->isSite())
		{
			$html 	.= $this->renderRequiredText($contentTypeSettings);
			$html 	.= $this->getFieldDescription($translatedFields);
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
		$value 		= $this->values['VALUE'];
		$fieldId 	= $this->internal['fieldcontentid'];
		$action 	= ($value) ? (($fieldId) ? 'UPDATE' : 'INSERT') : (($fieldId) ? 'DELETE' : '');
		
		if((int)$this->settings->get('mlt_max_num_chars'))
		{
			if(function_exists('mb_substr_count') && function_exists('mb_substr'))
			{
				$numNewLines = mb_substr_count ($value, "\r\n", 'UTF-8');
				$value = mb_substr($value, 0, (int)$this->settings->get('mlt_max_num_chars') + $numNewLines, 'UTF-8');
			}
			else
			{
				$numNewLines = substr_count ($value, "\r\n");
				$value = substr($value, 0, (int)$this->settings->get('mlt_max_num_chars') + $numNewLines);
			}
		}
							
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
		$templateEngine->addVar($this->fieldname, nl2br($this->stringHTMLSafe($this->values['VALUE'])));
		$templateEngine->addVar($this->fieldname .'_RAW', nl2br($this->values['VALUE']));
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