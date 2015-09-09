<?php
defined('JPATH_PLATFORM') or die('Restricted acccess');

class F2cFieldDatabaseLookup extends F2cFieldBase
{
	function __construct($field)
	{
		$this->reset();
		parent::__construct($field);
	}
	
	public function getPrefix()
	{
		return 'dbl';
	}
	
	public function render($translatedFields, $contentTypeSettings, $parms = array(), $form, $formId)
	{
		$html			= '';
		$listOptions 	= array();
		$fieldValue		= $this->values['VALUE'];
		$translate 		= $this->f2cConfig->get('custom_translations', false);

		if($this->settings->get('dbl_show_empty_choice_text'))
		{
			$emptyText = $translate ? JText::_($this->settings->get('dbl_empty_choice_text')) : $this->settings->get('dbl_empty_choice_text');
			$listOptions[] = JHTML::_('select.option', '', $emptyText, 'key', 'value');
		}
			      				
		$db 	= JFactory::getDBO();
		$user 	= JFactory::getUser();
		
		$sql = $this->settings->get('dbl_query');
		$sql = str_replace('{$CURRENT_USER_ID}', $user->id, $sql);
		$sql = str_replace('{$CURRENT_USER_GROUPS}', implode(',', $user->groups), $sql);
		$sql = str_replace('{$LANGUAGE}', JFactory::getLanguage()->getTag(), $sql);
		
		$db->setQuery($sql);
		$rowList = $db->loadRowList();

		if(count($rowList))
		{
			foreach($rowList as $row)
			{
				$listOptions[] = JHTML::_('select.option', $row[0], $row[1],'key','value');
			}
		}

		if($this->settings->get('dbl_display_mode') == 0)
		{
			$html .= JHTMLSelect::genericlist($listOptions, $this->elementId, $this->settings->get('dbl_attributes'), 'key', 'value', $fieldValue);
		}
		else
		{  
			$html .= JHTML::_('select.radiolist', $listOptions, $this->elementId, $this->settings->get('dbl_attributes'), 'key', 'value', $fieldValue);	
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
		
		$this->internal['fieldcontentid'] 	= $jinput->getInt('hid'.$this->elementId);
		$this->values['VALUE'] = $jinput->get($this->elementId, '', 'RAW');
		
		return $this;
	}
	
	public function store($formid)
	{
		$content 	= array();
		$value 		= $this->values['VALUE'];
		$fieldId 	= $this->internal['fieldcontentid'];
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
		$text = '';
		$value = '';
				
		if($this->values['VALUE'])
		{
			$value 		= $this->values['VALUE'];
			$db 		= JFactory::getDBO();
			$user 		= JFactory::getUser($form->created_by);
			$sql 		= $this->settings->get('dbl_query');
			$sql 		= str_replace('{$CURRENT_USER_ID}', $user->id, $sql);
			$sql 		= str_replace('{$CURRENT_USER_GROUPS}', implode(',', $user->groups), $sql);

			$db->setQuery($sql);
							
			$rowList 	= $db->loadRowList(0);
			$text 		= $rowList[$value][1];	
		}	
			
		$templateEngine->addVar($this->fieldname, $value);
		$templateEngine->addVar($this->fieldname.'_TEXT', $text);
	}
	
	public function getTemplateParameterNames()
	{
		$names = array(strtoupper($this->fieldname).'_TEXT');
		
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