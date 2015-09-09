<?php
defined('JPATH_PLATFORM') or die('Restricted acccess');

class F2cFieldDisplayList extends F2cFieldBase
{	
	function __construct($field)
	{
		$this->reset();
		parent::__construct($field);
	}
	
	public function getPrefix()
	{
		return 'dsp';
	}
	
	public function reset()
	{
		$this->values['VALUE'] 				= array();
		$this->internal['fieldcontentid']	= null;
	}
	
	public function render($translatedFields, $contentTypeSettings, $parms = array(), $form, $formId)
	{
		$html 				= '';
		$fieldValue 		= $this->values['VALUE'];
		$attributesTable	= $this->settings->get('dsp_attributes_table') ? $this->settings->get('dsp_attributes_table') : 'border="1"';
		
		$html .= '<table><tr><td>';
		
		$html .= '<table '.$attributesTable.' id="'.$this->elementId.'" cellspacing="0" cellpadding="0">
					<tr '.$this->settings->get('dsp_attributes_tr').'>
						<th '.$this->settings->get('dsp_attributes_th').' style="width:200px;">' . Jtext::_('COM_FORM2CONTENT_LIST_ITEM') . '</th>
						<th '.$this->settings->get('dsp_attributes_th').'></th>	
					</tr>';
									
		$rowcount = 0;
		$imgPath = JURI::root(true).'/media/com_form2content/images/';
						
		if($fieldValue && count($fieldValue) > 0)
		{
			foreach($fieldValue as $value)
			{
				$rowId = $this->elementId.'_'.$rowcount;
				$rowcount++;
				$html .= '<tr id="'.$rowId.'" '.$this->settings->get('dsp_attributes_tr').'>
						  <td '.$this->settings->get('dsp_attributes_td').'>
						  	<input type="hidden" name="'.$this->elementId.'RowKey[]" value="'.$rowId.'"/>
						  	<input type="text" id="'.$rowId.'val" name="'.$rowId.'val" size="40" value="' . htmlspecialchars($value) . '" maxlength="255" '.$this->settings->get('dsp_attributes_item_text').' />
						  </td>
						  <td '.$this->settings->get('dsp_attributes_td').'>';
				$html .=	'<a href="javascript:moveUp(\''.$this->elementId.'\',\''.$rowId.'\');"><i class="icon-f2carrow-up f2c_row_button" title="' . JText::_('COM_FORM2CONTENT_UP') . '"></i></a>';
				$html .=	'<a href="javascript:moveDown(\''.$this->elementId.'\',\''.$rowId.'\');"><i class="icon-f2carrow-down f2c_row_button" title="' . JText::_('COM_FORM2CONTENT_DOWN') . '"></i></a>';
				$html .=	'<a href="javascript:removeRow(\''.$rowId.'\');"><i class="icon-f2cminus f2c_row_button" title="' . JText::_('COM_FORM2CONTENT_DELETE') . '"></i></a>';
				$html .=	'<a href="javascript:addDisplayListRow(\''.$this->elementId.'\',\''.$rowId.'\');"><i class="icon-f2cplus f2c_row_button" title="' . JText::_('COM_FORM2CONTENT_ADD') . '"></i></a>';
				$html .= '</td>
						  </tr>';
			}
		}
		
		$html .= 	'</table>
					 <br/>
					 <input type="button" value="' . Jtext::_('COM_FORM2CONTENT_ADD_LIST_ITEM') . '" '.$this->settings->get('dsp_attributes_add_button').' onclick="addDisplayListRow(\''.$this->elementId.'\',\'\');" class="btn" />
					 <input type="hidden" name="'.$this->elementId.'MaxKey" id="'.$this->elementId.'MaxKey" value="'.$rowcount.'"/>';
					 		
		$html .= '</td><td valign="top">';
		
		if(JFactory::getApplication()->isSite())
		{
			$html 	.= $this->renderRequiredText($contentTypeSettings);
			$html 	.= $this->getFieldDescription($translatedFields);
		}
		
		$html .= '</td></tr></table>';
		$html .= $this->renderHiddenField('hid'.$this->elementId, $this->internal['fieldcontentid']);
		
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
		$fieldId 	= $this->internal['fieldcontentid'];
		$listNew 	= null;
		$valueList	= new JRegistry();

		if(count($this->values['VALUE']))
		{
			foreach($this->values['VALUE'] as $displayItem)
			{ 
				$listNew[] = $displayItem;
			}
		}
		
		$valueList->loadArray($listNew);
				
		$value 		= $valueList->toString();		
		$action 	= ($value) ? (($fieldId) ? 'UPDATE' : 'INSERT') : (($fieldId) ? 'DELETE' : '');
		$content[] 	= new F2cFieldHelperContent($fieldId, 'VALUE', $value, $action);
		
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
		$this->values['VALUE'] = array();
      						
      	if(count($xmlField->contentMultipleTextValue->values->children()))
      	{
			$this->internal['fieldcontentid'] = $formId ? $existingInternalData['fieldcontentid'] : 0;
      					      							
      		foreach($xmlField->contentMultipleTextValue->values->children() as $xmlValue)
      		{
      			$this->values['VALUE'][] = (string)$xmlValue;
      		}
      	}
	}
	
	public function addTemplateVar($templateEngine, $form)
	{
		$output = '';
		$values	= array();
		
		if($this->values['VALUE'] && count($this->values['VALUE']))
		{
			foreach($this->values['VALUE'] as $value)
			{
				$output 	.= '<li>'.htmlspecialchars($value).'</li>';
				$values[] 	= $value;
			}	
			
			if($this->settings->get('dsp_output_mode'))
			{
				$output = '<ul>'.$output.'</ul>';
			}
			else
			{
				$output = '<ol>'.$output.'</ol>';				
			}				
		}
						
		$templateEngine->addVar($this->fieldname, $output);
		$templateEngine->addVar($this->fieldname.'_VALUES', $values);
		$templateEngine->addVar($this->fieldname.'_CSV', implode(', ', $values));
	}
	
	public function getTemplateParameterNames()
	{
		$names = array(	strtoupper($this->fieldname).'_VALUES',
						strtoupper($this->fieldname).'_CSV');
		
		return array_merge($names, parent::getTemplateParameterNames());
	}
	
	public function setData($data)
	{
		$this->internal['fieldcontentid']	= $data->fieldcontentid;
		$values 							= new JRegistry($data->content);											
		$this->values['VALUE'] 				= $values->toArray();
	}
}
?>