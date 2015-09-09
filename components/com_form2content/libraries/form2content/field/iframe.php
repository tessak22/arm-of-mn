<?php
defined('JPATH_PLATFORM') or die('Restricted acccess');

class F2cFieldIFrame extends F2cFieldBase
{
	function __construct($field)
	{
		$this->reset();
		parent::__construct($field);
	}
	
	public function getPrefix()
	{
		return 'ifr';
	}
	
	public function reset()
	{
		$this->values['URL'] 				= '';
		$this->values['WIDTH'] 				= null;
		$this->values['HEIGHT'] 			= null;
		$this->internal['fieldcontentid']	= null;
	}
	
	public function render($translatedFields, $contentTypeSettings, $parms = array(), $form, $formId)
	{
		$html = '';
		$html .= '<table><tr>';
		$html .= '<td>'.Jtext::_('COM_FORM2CONTENT_URL').':</td>';
		$html .= '<td>'.$this->renderTextBox($this->elementId, $this->values['URL'], 65, 200, $this->settings->get('ifr_attributes_iframe'));

		if(JFactory::getApplication()->isSite())
		{
			$html 	.= $this->renderRequiredText($contentTypeSettings);
			$html 	.= $this->getFieldDescription($translatedFields);
		}
		
		$html .= '</td></tr><tr>';
		$html .= '<td>'.Jtext::_('COM_FORM2CONTENT_WIDTH').':</td>';
		$html .= '<td>'.$this->renderTextBox($this->elementId.'_width', $this->values['WIDTH'], 5, 4, $this->settings->get('ifr_attributes_width')).'&nbsp;';		      							
		$html .= Jtext::_('COM_FORM2CONTENT_HEIGHT').':&nbsp;';
		$html .= $this->renderTextBox($this->elementId.'_height', $this->values['HEIGHT'], 5, 4, $this->settings->get('ifr_attributes_height')).'</td>';		      							
		$html .= '</tr></table>';
		$html .= $this->renderHiddenField('hid'.$this->elementId, $this->internal['fieldcontentid']);
		
		return $html;	
	}
	
	public function prepareSubmittedData($formId)
	{
		$jinput = JFactory::getApplication()->input;
		
		$this->internal['fieldcontentid'] = $jinput->getInt('hid'.$this->elementId);
		
		$this->values['URL'] = $jinput->getString($this->elementId);
		$this->values['WIDTH'] = $jinput->getInt($this->elementId . '_width');
		$this->values['HEIGHT'] = $jinput->getInt($this->elementId . '_height');
		
		return $this;
	}
	
	public function store($formid)
	{
		$content 		= array();
		$iframe			= new JRegistry();
		$fieldId 		= $this->internal['fieldcontentid'];
		
		$iframe->set('url', $this->values['URL']);
		$iframe->set('width', $this->values['WIDTH']);
		$iframe->set('height', $this->values['HEIGHT']);
		
		$value 			= $iframe->toString();
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
      	$xmlFieldContent = $xmlField->addChild('contentIframe');
      	$xmlFieldContent->url = $this->values['URL'];
      	$xmlFieldContent->width = $this->values['WIDTH'];
      	$xmlFieldContent->height = $this->values['HEIGHT'];
    }
    
	public function import($xmlField, $existingInternalData, $formId)
	{
		$this->values['URL'] = (string)$xmlField->contentIframe->url;
		$this->values['WIDTH'] = (string)$xmlField->contentIframe->width;
		$this->values['HEIGHT'] = (string)$xmlField->contentIframe->height;
		$this->internal['fieldcontentid'] = $formId ? $existingInternalData['fieldcontentid'] : 0;
	}

	public function addTemplateVar($templateEngine, $form)
	{
		$iframeTag = '';
		
		if($this->values['URL'])
		{
			$iframeTag = '<iframe src="' . $this->values['URL'] . '" height="' . $this->values['HEIGHT'] . '" width="' . $this->values['WIDTH'] . '"></iframe>';
		}

		$templateEngine->addVar($this->fieldname, $iframeTag);
	}
	
	public function setData($data)
	{
		$this->internal['fieldcontentid']	= $data->fieldcontentid;
		$values 							= new JRegistry($data->content);
		$this->values['URL'] 				= $values->get('url');
		$this->values['WIDTH'] 				= $values->get('width');
		$this->values['HEIGHT'] 			= $values->get('height');
	}
}
?>