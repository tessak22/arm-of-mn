<?php
defined('JPATH_PLATFORM') or die('Restricted acccess');

class F2cFieldHyperlink extends F2cFieldBase
{	
	function __construct($field)
	{
		$this->reset();
		parent::__construct($field);
	}
	
	public function getPrefix()
	{
		return 'lnk';
	}
	
	public function reset()
	{
		$this->values['URL'] 				= '';
		$this->values['DISPLAY_AS'] 		= '';
		$this->values['TITLE'] 				= '';
		$this->values['TARGET'] 			= '';
		$this->internal['fieldcontentid']	= null;
	}
	
	public function render($translatedFields, $contentTypeSettings, $parms = array(), $form, $formId)
	{
		$html 			= '';
		$listTarget[] 	= JHTML::_('select.option', '_top', 'Parent window');
		$listTarget[] 	= JHTML::_('select.option', '_blank', 'New window');	

		$html .= '<table><tr><td>'.Jtext::_('COM_FORM2CONTENT_URL').':</td><td>';
		$html .= $this->renderTextBox($this->elementId, $this->values['URL'], 40, 300, $this->settings->get('lnk_attributes_url')); 
		
		if(JFactory::getApplication()->isSite())
		{
			$html 	.= $this->renderRequiredText($contentTypeSettings);
			$html 	.= $this->getFieldDescription($translatedFields);
		}
		
		$html .= '</td></tr>';
	
		if($this->settings->get('lnk_show_display_as'))
		{
			$html .= '<tr>';
			$html .= '<td>'.Jtext::_('COM_FORM2CONTENT_DISPLAY_AS').':</td>';
			$html .= '<td>'.$this->renderTextBox($this->elementId.'_display', $this->values['DISPLAY_AS'], 40, 100, $this->settings->get('lnk_attributes_display_as')).'</td>';
			$html .= '</tr>';
		}

		if($this->settings->get('lnk_show_title'))
		{
			$html .= '<tr>';
			$html .= '<td>'.Jtext::_('COM_FORM2CONTENT_TITLE').':</td>';
			$html .= '<td>'.$this->renderTextBox($this->elementId.'_title', $this->values['TITLE'], 40, 100, $this->settings->get('lnk_attributes_title')).'</td>';		      							
			$html .= '</tr>';
		}
		
		if($this->settings->get('lnk_show_target'))
		{
			$html .= '<tr>';
			$html .= '<td>'.Jtext::_('COM_FORM2CONTENT_TARGET').':</td>';	      							
			$html .= '<td>'.JHTMLSelect::genericlist($listTarget, $this->elementId . '_target',$this->settings->get('lnk_attributes_target') ,'value', 'text', $this->values['TARGET']).'</td>';
			$html .= '</tr>';
		}
		
		$html .= '</table>';
		
		if(!$this->settings->get('lnk_show_display_as'))
		{
			$html .= $this->renderHiddenField($this->elementId.'_display', '');
		}

		if(!$this->settings->get('lnk_show_title'))
		{
			$html .= $this->renderHiddenField($this->elementId.'_title', '');
		}
		
		if(!$this->settings->get('lnk_show_target'))
		{
			$html .= $this->renderHiddenField($this->elementId.'_target', '');
		}
		
		$html .= $this->renderHiddenField('hid'.$this->elementId, $this->internal['fieldcontentid']);

		return $html;
	}
	
	public function prepareSubmittedData($formId)
	{
		$jinput = JFactory::getApplication()->input;
		
		$this->internal['fieldcontentid'] = $jinput->getInt('hid'.$this->elementId);
		
		$this->values['URL'] 		= $jinput->getString($this->elementId);
		$this->values['DISPLAY_AS'] = $jinput->getString($this->elementId . '_display');
		$this->values['TITLE'] 		= $jinput->getString($this->elementId . '_title');
		$this->values['TARGET'] 	= $jinput->getString($this->elementId . '_target');
		
		return $this;
	}
	
	public function store($formid)
	{
		$content 		= array();					
		$link 			= new JRegistry();
		$fieldId 		= $this->internal['fieldcontentid'];
				
		$link->set('url', $this->values['URL']);
		$link->set('display', $this->values['DISPLAY_AS']);
		$link->set('title', $this->values['TITLE']);
		$link->set('target', $this->values['TARGET']);
		
		$value 			= $link->toString();
		$action 		= ($value) ? (($fieldId) ? 'UPDATE' : 'INSERT') : (($fieldId) ? 'DELETE' : '');
		$content[] 		= new F2cFieldHelperContent($fieldId, 'VALUE', $value, $action);
		
		return $content;		
	}
	
	public function validate()
	{
		$value = trim($this->values['URL']);
		
		if($this->settings->get('requiredfield') && empty($value))
		{
			throw new Exception($this->getRequiredFieldErrorMessage());
		}
	}
	
	public function export($xmlFields, $formId)
	{
      	$xmlField = $xmlFields->addChild('field');
      	$xmlField->fieldname = $this->fieldname;
      	$xmlFieldContent = $xmlField->addChild('contentHyperlink');
      	$xmlFieldContent->url = $this->values['URL'];
      	$xmlFieldContent->display_as = $this->values['DISPLAY_AS'];
      	$xmlFieldContent->title = $this->values['TITLE'];
      	$xmlFieldContent->target = $this->values['TARGET'];
    }
    
	public function import($xmlField, $existingInternalData, $formId)
	{
		$this->values['URL'] = (string)$xmlField->contentHyperlink->url;
		$this->values['DISPLAY_AS'] = (string)$xmlField->contentHyperlink->display_as;
		$this->values['TITLE'] = (string)$xmlField->contentHyperlink->title;
		$this->values['TARGET'] = (string)$xmlField->contentHyperlink->target;
		$this->internal['fieldcontentid'] = $formId ? $existingInternalData['fieldcontentid'] : 0;
	}
	
	public function addTemplateVar($templateEngine, $form)
	{
		$linkTitle = '';
		$linkTarget = '';
		$linkDisplay = '';
		$linkUrl = '';
		
		if($this->values['URL'])
		{
			$display 		= $this->values['DISPLAY_AS'] ? $this->values['DISPLAY_AS'] : $this->values['URL'];
			$linkTitle 		= $this->values['TITLE'];
			$linkTarget 	= $this->values['TARGET'];
			$linkDisplay 	= $this->values['DISPLAY_AS'];
			$linkUrl 		= $this->values['URL'];
			
			if($this->settings->get('lnk_add_http_prefix', 0))
			{
				if(!strstr($linkUrl, '://'))
				{
					$linkUrl = 'http://' . $linkUrl;
				}
			}
			
			if($this->settings->get('lnk_output_mode') == 0)
			{
				$linkTag = $linkUrl;
			}
			else
			{
				$linkTag = '<a href="' . $linkUrl . '" target="' . $this->values['TARGET'] . '" title="' . $this->values['TITLE'] . '">' . $display . '</a>';					
			}
			
			$templateEngine->addVar($this->fieldname, $linkTag);
		}
		else
		{
			$templateEngine->addVar($this->fieldname, '');
		}
		
		$templateEngine->addVar($this->fieldname.'_URL', $linkUrl);		
		$templateEngine->addVar($this->fieldname.'_TITLE', $linkTitle);		
		$templateEngine->addVar($this->fieldname.'_TARGET', $linkTarget);		
		$templateEngine->addVar($this->fieldname.'_DISPLAY', $linkDisplay);					
	}
	
	public function getTemplateParameterNames()
	{
		$names = array(	strtoupper($this->fieldname).'_URL',
						strtoupper($this->fieldname).'_TITLE', 
						strtoupper($this->fieldname).'_DISPLAY',
						strtoupper($this->fieldname).'_TARGET');
		
		return array_merge($names, parent::getTemplateParameterNames());
	}
	
	public function setData($data)
	{
		$this->internal['fieldcontentid']	= $data->fieldcontentid;
		$values 							= new JRegistry($data->content);
		$this->values['URL'] 				= $values->get('url');
		$this->values['DISPLAY_AS'] 		= $values->get('display');
		$this->values['TITLE'] 				= $values->get('title');
		$this->values['TARGET'] 			= $values->get('target');
	}
}
?>