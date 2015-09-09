<?php
defined('JPATH_PLATFORM') or die('Restricted acccess');

class F2cFieldDatabaseLookupMulti extends F2cFieldBase
{	
	function __construct($field)
	{
		$this->reset();
		parent::__construct($field);
	}
	
	public function getPrefix()
	{
		return 'dlm';
	}
	
	public function reset()
	{
		$this->values['VALUE']				= array();
		$this->internal['fieldcontentid']	= null;
	}
	
	public function render($translatedFields, $contentTypeSettings, $parms = array(), $form, $formId)
	{
		$html 				= '';
		$attributesTable	= $this->settings->get('dlm_attributes_table') ? $this->settings->get('dlm_attributes_table') : 'border="1"';
		$valueList			= array();
		$translate 		= $this->f2cConfig->get('custom_translations', false);
		
		// Prepare drop down list
		if($this->settings->get('dlm_show_empty_choice_text'))
		{
			$emptyText = $translate ? JText::_($this->settings->get('dlm_empty_choice_text')) : $this->settings->get('dlm_empty_choice_text');
			$listOptions[] = JHTML::_('select.option', '', $emptyText, 'key', 'value');
		}
		
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		
		$sql = $this->settings->get('dlm_query');
		$sql = str_replace('{$CURRENT_USER_ID}', $user->id, $sql);
		$sql = str_replace('{$CURRENT_USER_GROUPS}', implode(',', $user->groups), $sql);
		$sql = str_replace('{$LANGUAGE}', JFactory::getLanguage()->getTag(), $sql);
		
		$db->setQuery($sql);
		$rowList = $db->loadRowList(0);

		if(count($rowList))
		{
			foreach($rowList as $row)
			{
				$listOptions[] = JHTML::_('select.option', $row[0], $row[1],'key','value');
			}
		}
		
		$html .= '<table><tr><td>';
		
		$html .= '<table '.$attributesTable.' id="'.$this->elementId.'" cellspacing="0" cellpadding="0">		
					<tr '.$this->settings->get('dlm_attributes_tr').'>
						<th '.$this->settings->get('dlm_attributes_th').' style="width:200px;">' . Jtext::_('COM_FORM2CONTENT_LIST_ITEM') . '</th>
						<th '.$this->settings->get('dlm_attributes_th').'></th>			
					</tr>';
		
		$rowcount = 0;
		$imgPath = JURI::root(true).'/media/com_form2content/images/';
						
		if(count($this->values['VALUE']))
		{
			foreach($this->values['VALUE'] as $value)
			{
				$rowId = $this->elementId.'_'.$rowcount;
				$rowcount++;
				
				if(array_key_exists($value, $rowList))
				{
					$html .= '<tr id="'.$rowId.'" '.$this->settings->get('dlm_attributes_tr').'>
							  <td '.$this->settings->get('dlm_attributes_td').'>
							  	<input type="hidden" name="'.$this->elementId.'RowKey[]" value="'.$rowId.'"/>
							  	<input type="hidden" id="'.$rowId.'val" name="'.$rowId.'val" value="' . htmlspecialchars($value) . '" />'.
								$this->stringHTMLSafe($rowList[$value][1]).'
							  </td>
							  <td '.$this->settings->get('dlm_attributes_td').'>';
					$html .= 	'<a href="javascript:moveUp(\''.$this->elementId.'\',\''.$rowId.'\');"><i class="icon-f2carrow-up f2c_row_button" title="' . JText::_('COM_FORM2CONTENT_UP') . '"></i></a>';
					$html .=	'<a href="javascript:moveDown(\''.$this->elementId.'\',\''.$rowId.'\');"><i class="icon-f2carrow-down f2c_row_button" title="' . JText::_('COM_FORM2CONTENT_DOWN') . '"></i></a>';
					$html .=	'<a href="javascript:removeRow(\''.$rowId.'\');"><i class="icon-f2cminus f2c_row_button" title="' . JText::_('COM_FORM2CONTENT_DELETE') . '"></i></a>';
					$html .=	'</td>
							  </tr>';
				}
			}
		}
		
		$html .= 	'</table>
					 <br/>				
					 <input type="hidden" name="'.$this->elementId.'MaxKey" id="'.$this->elementId.'MaxKey" value="'.$rowcount.'"/>';
					 		
		$html .= '</td><td valign="top">';
		
		if(JFactory::getApplication()->isSite())
		{
			$html 	.= $this->renderRequiredText($contentTypeSettings);
			$html 	.= $this->getFieldDescription($translatedFields);
		}
		
		$html .= '</td></tr></table>';
		$html .= $this->renderHiddenField('hid'.$this->elementId, $this->id);
		$html .= JHTMLSelect::genericlist($listOptions, $this->elementId.'_lookup', $this->settings->get('dlm_attributes_select'), 'key', 'value', '').'&nbsp;';
		$html .= '<input type="button" value="' . Jtext::_('COM_FORM2CONTENT_ADD') . '" '.$this->settings->get('dlm_attributes_add_button').' onclick="addDbLookupkMultiRow(\''.$this->elementId.'\',\'\');" class="btn" />';
		
		return $html;		
	}
	
	public function prepareSubmittedData($formId)
	{
		$jinput = JFactory::getApplication()->input;
		
		$this->internal['fieldcontentid'] 	= $jinput->getInt('hid'.$this->elementId);
		$this->values['VALUE'] 				= array();
		$rowKeys 							= $jinput->get($this->elementId.'RowKey', array(), 'ARRAY');
		
		if(count($rowKeys))
		{
			foreach($rowKeys as $rowKey)
			{
				$value =  $jinput->get($rowKey . 'val', '', 'RAW');
												
				// prevent duplicate and empty entries
				if(!array_key_exists($value, $this->values['VALUE']) && $value != '')
				{
					$this->values['VALUE'][] = $value;
				}
			}
		}
		
		return $this;
	}
	
	public function store($formid)
	{
		$content	= array();							
		$fieldId 	= array_key_exists('fieldcontentid', $this->internal)? $this->internal['fieldcontentid'] : 0;
			
		if(count($this->values['VALUE']))
		{
			foreach($this->values['VALUE'] as $item)
			{ 
				$content[] 	= new F2cFieldHelperContent($fieldId, 'VALUE', $item, 'INSERT');
			}
		}
		
		// Remove all previous entries
		if(!empty($fieldId))
		{
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
					if(!empty($value)) return;
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
		$output 	= '';		
		$db 		= JFactory::getDBO();
		$assocArray	= array();
		$user 		= JFactory::getUser($form->created_by);	
		$sql 		= $this->settings->get('dlm_query');
		$sql 		= str_replace('{$CURRENT_USER_ID}', $user->id, $sql);
		$sql 		= str_replace('{$CURRENT_USER_GROUPS}', implode(',', $user->groups), $sql);
		
		$db->setQuery($sql);				
		$dicValues = $db->loadRowList(0);

		if(count($this->values))
		{
			foreach($this->values['VALUE'] as $value)
			{
				$output .= '<li>'.$dicValues[$value][1].'</li>';
				$assocArray[$value] = $dicValues[$value][1];
			}	
			
			if($this->settings->get('dlm_output_mode'))
			{
				$output = '<ul>'.$output.'</ul>';
			}
			else
			{
				$output = '<ol>'.$output.'</ol>';				
			}				
		}
		
		$templateEngine->addVar($this->fieldname.'_VALUES', $assocArray);
		$templateEngine->addVar($this->fieldname, $output);
		$templateEngine->addVar($this->fieldname.'_CSV', implode(', ', $assocArray));		
	}
	
	public function getTemplateParameterNames()
	{
		$names = array(	strtoupper($this->fieldname).'_VALUES',
						strtoupper($this->fieldname).'_CSV');
		
		return array_merge($names, parent::getTemplateParameterNames());
	}
	
	public function setData($data)
	{
		$this->values[$data->attribute][] = $data->content;
	}
}
?>