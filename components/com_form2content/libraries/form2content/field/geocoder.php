<?php
defined('JPATH_PLATFORM') or die('Restricted acccess');

class F2cFieldGeocoder extends F2cFieldBase
{	
	function __construct($field)
	{
		$this->reset();
		parent::__construct($field);
	}
	
	public function getPrefix()
	{
		return 'gcd';
	}
	
	public function reset()
	{
		$this->values['ADDRESS']		= '';
		$this->values['LAT']			= '';
		$this->values['LON']			= '';						
		$this->internal['addressid']	= null;
		$this->internal['latid']		= null;
		$this->internal['lonid']		= null;
	}
	
	public function render($translatedFields, $contentTypeSettings, $parms = array(), $form, $formId)
	{
		$defaultLat			= $this->settings->get('gcd_map_lat', '55.166085');
		$defaultLon			= $this->settings->get('gcd_map_lon', '10.712890');
		$mapWidth 			= $this->settings->get('gcd_map_width', '350');
		$mapHeight 			= $this->settings->get('gcd_map_height', '350');
		$attributesAddress 	= $this->settings->get('gcd_attributes_address', 'class="inputbox"');	
		$attributesLookup 	= $this->settings->get('gcd_attributes_lookup_lat_lon', 'class="btn"');		
		$attributesClear 	= $this->settings->get('gcd_attributes_clear_results', 'class="btn"');						
		$addressValue 		= $this->values['ADDRESS'];
		$addressId 			= $this->internal['addressid'];
		$latValue 			= $this->values['LAT'];
		$latId 				= $this->internal['latid'];
		$lonValue 			= $this->values['LON'];
		$lonId 				= $this->internal['lonid'];
		
		$latLonDisplay = ($latValue && $lonValue) ? '('.$latValue.', '.$lonValue.')' : '';
				
		if($this->settings->get('gcd_show_map'))
		{
			$document = JFactory::getDocument();
		
			$latOnMap = $latLonDisplay ? $latValue : $defaultLat;
			$lonOnMap = $latLonDisplay ? $lonValue : $defaultLon;
		
			$js =	'window.addEvent(\'load\', function() {
					 var latlng = new google.maps.LatLng('.$latOnMap.', '.$lonOnMap.');
    				 var myOptions = { zoom: '.$this->settings->get('gcd_map_zoom').', center: latlng, mapTypeId: google.maps.MapTypeId.'.$this->settings->get('gcd_map_type').' }; ' .
    				$this->elementId.'_map = new google.maps.Map(document.getElementById("'.$this->elementId.'_map_canvas"), myOptions); ';

			if($latLonDisplay)
			{
				// initialize the marker
				$js .= 'eval('.$this->elementId.'_marker = new google.maps.Marker({ map: '.$this->elementId.'_map, position: latlng }));';
			}

			$js .=	' });';
			  		
			$document->addScriptDeclaration($js);
		}
										
		$html = '';
		
		if($this->settings->get('gcd_show_map'))
		{
			$html .= '<div id="'.$this->elementId.'_map_canvas" class="f2c_field_geocoder" style="width: '.$mapWidth.'px; height: '.$mapHeight.'px;"></div><br/>';
		}
		
 		$html .= '<table>';
 		$html .= '<tr><td>'.Jtext::_('COM_FORM2CONTENT_ADDRESS_OF_LOCATION').': </td><td><input id="'.$this->elementId.'_address" name="'.$this->elementId.'_address" type="text" '.$attributesAddress.' value="'.$this->stringHTMLSafe($addressValue).'" style="width:300px;">';
 		$html .= '</td></tr>';
		$html .= '<tr><td colspan="2">';
 		$html .= '<input type="button" '.$attributesLookup.' value="'.Jtext::_('COM_FORM2CONTENT_LOOKUP_LAT_LON').'" onclick="F2C_GeoCoderConvertAddress(\''.$this->elementId.'\');">';
 		$html .= '&nbsp;<input type="button" '.$attributesClear.' value="'.Jtext::_('COM_FORM2CONTENT_CLEAR_RESULTS').'" onclick="F2C_GeoCoderClearResults(\''.$this->elementId.'\');">';
 		
 		if(JFactory::getApplication()->isSite())
 		{
			$html 	.= $this->renderRequiredText($contentTypeSettings);
			$html 	.= $this->getFieldDescription($translatedFields);
 		}
 		
 		$html .= $this->renderHiddenField($this->elementId.'_hid_lat', $latValue);
 		$html .= $this->renderHiddenField($this->elementId.'_hid_lon', $lonValue);
 		$html .= '</td></tr>';
 		$html .= '<tr><td>'.Jtext::_('COM_FORM2CONTENT_LAT_LON').': </td><td><span id="'.$this->elementId.'_latlon" name="'.$this->elementId.'_latlon">'.$latLonDisplay.'</span>';
 		$html .= '<span id="'.$this->elementId.'_error" name="'.$this->elementId.'_error" style="display: none;">'.Jtext::_('COM_FORM2CONTENT_ERROR_GEOCODER_PROCESS').'</span></td></tr>';
 		$html .= '</table>';
 		$html .= $this->renderHiddenField('hid'.$this->elementId.'_lat', $latId);
 		$html .= $this->renderHiddenField('hid'.$this->elementId.'_lon', $lonId);
 		$html .= $this->renderHiddenField('hid'.$this->elementId.'_address', $addressId);
		
		return $html;
	}
	
	public function prepareSubmittedData($formId)
	{
		$jinput = JFactory::getApplication()->input;
		
		$this->internal['addressid'] 	= $jinput->getInt('hid'.$this->elementId.'_address');
		$this->internal['latid'] 		= $jinput->getInt('hid'.$this->elementId.'_lat');
		$this->internal['lonid'] 		= $jinput->getInt('hid'.$this->elementId.'_lon');
		$this->values['ADDRESS']		= $jinput->getString($this->elementId.'_address');
		$this->values['LAT']			= $jinput->getString($this->elementId.'_hid_lat');
		$this->values['LON']			= $jinput->getString($this->elementId.'_hid_lon');
		
		return $this;
	}
	
	public function store($formid)
	{
		$addressId		= $this->internal['addressid'];
		$addressValue 	= $this->values['ADDRESS'];		
		$latId			= $this->internal['latid'];
		$latValue		= $this->values['LAT'];
		$lonId			= $this->internal['lonid'];
		$lonValue 		= $this->values['LON'];		
				
		if($addressId)
		{
			// existing record
			$action = (!$addressValue && !$latValue && !$lonValue) ? 'DELETE' : 'UPDATE';
		}
		else
		{
			// new record
			$action = ($addressValue || $latValue || $lonValue) ? 'INSERT' : '';
		}
		
		$content 	= array();					
		$content[] 	= new F2cFieldHelperContent($addressId, 'ADDRESS', $addressValue, $action);
		$content[] 	= new F2cFieldHelperContent($latId, 'LAT', $latValue, $action);
		$content[] 	= new F2cFieldHelperContent($lonId, 'LON', $lonValue, $action);
		
		return $content;					
	}
	
	public function validate()
	{
		if($this->settings->get('requiredfield'))
		{
			if(!(trim($this->values['ADDRESS']) && $this->values['LAT'] && $this->values['LON']))		
			{
				throw new Exception($this->getRequiredFieldErrorMessage());
			}
		}
	}
	
	public function getClientSideInitializationScript()
	{
		static $initialized = false;
		
		$script = '';
		
		if(!$initialized)
		{
			JHtml::script('com_form2content/f2c_google.js', false, true);
			JHtml::script(JUri::getInstance()->getScheme().'://maps.google.com/maps/api/js?sensor=false');		
			JFactory::getDocument()->addScriptDeclaration('window.addEvent(\'load\', function() { geocoder = new google.maps.Geocoder(); });');		
			$initialized = true;
			$script .= "var geocoder;\n";
		}
		
		$script .= "var t".$this->id."_map=null;\n";	
		$script .= "var t".$this->id."_marker=null;\n";										
		
		return $script;
	}
	
	public function copy($formId)
	{
		$this->internal['addressid'] = null;
		$this->internal['latid'] = null;
		$this->internal['lonid'] = null;
	}
	
	public function export($xmlFields, $formId)
	{
      	$xmlField = $xmlFields->addChild('field');
      	$xmlField->fieldname = $this->fieldname;
      	$xmlFieldContent = $xmlField->addChild('contentGeocoder');
      	$xmlFieldContent->address = $this->values['ADDRESS'];
      	$xmlFieldContent->lat = $this->values['LAT'];
      	$xmlFieldContent->lon= $this->values['LON'];
    }
    
	public function import($xmlField, $existingInternalData, $formId)
	{
		$this->values['ADDRESS'] = (string)$xmlField->contentGeocoder->address;
		$this->values['LAT'] = (string)$xmlField->contentGeocoder->lat;
		$this->values['LON'] = (string)$xmlField->contentGeocoder->lon;
		$this->internal['addressid'] = $formId ? $existingInternalData['addressid'] : 0;
		$this->internal['latid'] = $formId ? $existingInternalData['latid'] : 0;
		$this->internal['lonid'] = $formId ? $existingInternalData['lonid'] : 0;
	}
	
	public function addTemplateVar($templateEngine, $form)
	{
		if($this->values)
		{
			$templateEngine->addVar($this->fieldname.'_ADDRESS', $this->stringHTMLSafe($this->values['ADDRESS']));
			$templateEngine->addVar($this->fieldname.'_LAT', $this->values['LAT']);
			$templateEngine->addVar($this->fieldname.'_LON', $this->values['LON']);
		}
		else
		{
			$templateEngine->addVar($this->fieldname.'_ADDRESS', '');
			$templateEngine->addVar($this->fieldname.'_LAT', '');
			$templateEngine->addVar($this->fieldname.'_LON', '');
		}
	}
	
	public function getTemplateParameterNames()
	{
		$names = array(	strtoupper($this->fieldname).'_ADDRESS',
						strtoupper($this->fieldname).'_LAT', 
						strtoupper($this->fieldname).'_LON');
		
		return $names;
	}
	
	public function setData($data)
	{
		$this->values[$data->attribute] = $data->content;
		
		switch($data->attribute)
		{
			case 'ADDRESS':
				$this->internal['addressid'] = $data->fieldcontentid;
				break;
			case 'LAT':
				$this->internal['latid'] = $data->fieldcontentid;
				break;
			case 'LON':
				$this->internal['lonid'] = $data->fieldcontentid;
				break;
		}						
	}
}
?>