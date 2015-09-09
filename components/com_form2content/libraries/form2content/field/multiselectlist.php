<?php
defined('JPATH_PLATFORM') or die('Restricted acccess');

class F2cFieldMultiSelectList extends F2cFieldBase
{	
	function __construct($field)
	{
		$this->reset();
		parent::__construct($field);
	}
	
	public function getPrefix()
	{
		return 'msl';
	}
	
	public function reset()
	{
		$this->values['VALUE'] 				= array();
		$this->internal['fieldcontentid']	= null;
	}
	
	public function render($translatedFields, $contentTypeSettings, $parms = array(), $form, $formId)
	{
		$html			= '';
		$valueList 		= array();
		$translate 		= $this->f2cConfig->get('custom_translations', false);
		
		$html .= '<table>';

		if(($this->renderRequiredText($contentTypeSettings) || $this->getFieldDescription($translatedFields)) && JFactory::getApplication()->isSite())
		{
			$html .= '<tr><td>'.$this->getFieldDescription($translatedFields).$this->renderRequiredText($contentTypeSettings).'</td></tr>';
		}
		
		$html .= '<tr><td>';

		if(count($this->values['VALUE']))
		{
			foreach($this->values['VALUE'] as $valueListItem)
			{
				$valueList[$valueListItem] = $valueListItem;
			}
		}

		foreach((array)$this->settings->get('msl_options') as $optionKey => $optionValue)
		{
			$html .= '<div class="checkbox_wrapper"><input type=checkbox name="' . $this->elementId . '[]" value="' . $this->stringHTMLSafe($optionKey) . '" '.$this->settings->get('msl_attributes');
			
			if(array_key_exists($optionKey, $valueList))
			{
				$html .= ' checked';
			}
			
			if($translate)
			{
				$optionValue = JText::_($optionValue);
			}
			
			$html .= '><div class="checkbox_label">' . $this->stringHTMLSafe($optionValue) . '</div></div><div style="clear:both;"></div>';
		}

		$html .= '</td>';
		$html .= '</tr></table>';
		
		$html .= $this->renderHiddenField('hid'.$this->elementId, $this->id);
		
		return $html;
	}
	
	public function prepareSubmittedData($formId)
	{
		$jinput = JFactory::getApplication()->input;
		
		$this->internal['fieldcontentid'] = $jinput->getInt('hid'.$this->elementId);		
		$this->values['VALUE'] = $jinput->get($this->elementId, array(), 'array');
		
		return $this;
	}
	
	public function store($formid)
	{
		$content 		= array();
		$fieldId 		= $this->internal['fieldcontentid'];
		$selections		= $this->values['VALUE'];
		
		if($selections && count($selections))
		{
			foreach($selections as $value)
			{
				$content[] 	= new F2cFieldHelperContent($fieldId, 'VALUE', $value, 'INSERT');			
			}
		}
				
		if(!empty($fieldId))
		{
			// Remove all previous entries
			$db 	= JFactory::getDbo();
			$query 	= $db->getQuery(true);
			$query->delete('#__f2c_fieldcontent')->where('formid='.$formid)->where('fieldid='.$fieldId);

			$db->setQuery($query);
			$db->execute();
		}
				
		return $content;				
	}
	
	public function validate()
	{
		if($this->settings->get('requiredfield'))
		{
	      	if(count($this->values['VALUE']))
	      	{
	      		foreach($this->values['VALUE'] as $value)
	      		{
	      			if(trim($value) != '') return;
	      		}
	      	}
		
			throw new Exception($this->getRequiredFieldErrorMessage());
		}
	}

	public function export($xmlFields, $formId)
	{
      	$xmlField = $xmlFields->addChild('field');
      	$xmlField->fieldname = $this->fieldname;
      	$xmlFieldContent = $xmlField->addChild('contentMultipleTextValue');
      	$xmlFieldValues = $xmlFieldContent->addChild('values');
      						
      	if(count($this->values['VALUE']))
      	{
      		foreach($this->values['VALUE'] as $item)
      		{
      			$xmlFieldValues->addChild('value', self::valueReplace($item));
      		}
      	}
	}
	
	public function import($xmlField, $existingInternalData, $formId)
	{
      	$this->values['VALUE'] 				= array();
      	$this->internal['fieldcontentid'] 	= $this->id;
      					
      	if(count($xmlField->contentMultipleTextValue->values->children()))
      	{
      		foreach($xmlField->contentMultipleTextValue->values->children() as $xmlValue)
      		{
      			$this->values['VALUE'][] = (string)$xmlValue;
      		}
      	}
	}
	
	public function addTemplateVar($templateEngine, $form)
	{
		$tag 			= '';		
		$customFormat 	= ''; 
		$assocArray		= array();
		$options		= (array)$this->settings->get('msl_options');
		$rowNumber		= 1;
		$translate 		= $this->f2cConfig->get('custom_translations', false);
		
		if($this->values['VALUE'] && count($this->values['VALUE']))
		{
			$customFormat .= $this->settings->get('msl_pre_list_tag');  

			foreach($options as $key => $value)
			{
				foreach($this->values['VALUE'] as $selectedValue)				
				{
					if($key == $selectedValue)
					{
						if($translate)
						{
							$value = JText::_($value);
						}
						
						$class 						= 'option_'.$key;						
						$class 						.= ($rowNumber++ %2 == 1) ? ' odd' : ' even';
						$tag 						.= '<li class="'.$class.'">' . htmlspecialchars($value) . '</li>';
						$customFormat 				.= $this->settings->get('msl_pre_element_tag').$value.$this->settings->get('msl_post_element_tag');
						$assocArray[$selectedValue] = $value;
						break;		
					}
				}
			}

			$customFormat .= $this->settings->get('msl_post_list_tag'); 				
		}

		$templateEngine->addVar($this->fieldname, $tag);		
		$templateEngine->addVar($this->fieldname.'_CUSTOM_FORMAT', $customFormat);
		$templateEngine->addVar($this->fieldname.'_VALUES', $assocArray);
		$templateEngine->addVar($this->fieldname.'_CSV', implode(', ', $assocArray));
	}
	
	public function getTemplateParameterNames()
	{
		$names = array(	strtoupper($this->fieldname).'_CUSTOM_FORMAT',
						strtoupper($this->fieldname).'_VALUES',
						strtoupper($this->fieldname).'_CSV');
		
		return array_merge($names, parent::getTemplateParameterNames());
	}
	
	public function setData($data)
	{
		$this->values[$data->attribute][] = $data->content;
	}
}
?>