<?php
defined('JPATH_PLATFORM') or die('Restricted acccess');

/**
 * Form2Content implementation of a color picker field.
 * This field is based upon the evol.colorpicker
 * The color picker package is located in components/com_form2content/libraries/evol.colorpicker
 * Online it can be found at http://evoluteur.github.io/colorpicker/
 * 
 * @package     Joomla.Site
 * @subpackage  com_form2content
 * @since       6.8.0
 */
class F2cFieldColorPicker extends F2cFieldBase
{
	/**
	 * The constructor creates the field datastructure and resets its values to the default values.
	 * Since Color Picker is simple field having only one stored value, we can use the base reset function.
	 *
	 * @param	object		$field		Field object as created from the database information
	 * 
	 * @return  void
	 * 
	 * @since   6.8.0
	 */
	function __construct($field)
	{
		$this->reset();
		parent::__construct($field);
	}
	
	/**
	 * The prefix is unique value that can be used to prefix field settings that share this same setting
	 * across multiple custom fields.
	 *
	 * @return  string	The Prefix for the field
	 * 
	 * @since   6.8.0
	 */
	public function getPrefix()
	{
		return 'col';
	}
	
	/**
	 * This function builds the client-side Javascript that will be used to initialize the field.
	 * Sometimes initialization is shared between multiple fields of the same type, while other
	 * initialization code will run for each field separately.
	 *
	 * @param	int		$validationCounter	Counter that must be increased on every call of this function
	 * 
	 * @return  string	Generated script
	 * 
	 * @since   6.8.0
	 */
	public function getClientSideInitializationScript()
	{
		// Flag to indicate whether initialization took place
		static $initialized = false;
		
		$script = '';
		
		if(!$initialized)
		{
			/*
			 * Initializtion code within this if statement will execute only once for all the
			 * possible color pickers on the form.
			 */ 
			JHtml::script('components/com_form2content/libraries/colpick/js/colpick.js', true);
			JHtml::stylesheet('components/com_form2content/libraries/colpick/css/colpick.css');
		
			$initialized = true;
		}
		
		// Get the current or default color (when there's no current color)
		$color = empty($this->values['VALUE']) ? $this->settings->get('default_value', 'ffffff') : $this->values['VALUE'];
		// get the color scheme from the field settings
		$colorScheme =	$this->settings->get('color_scheme');
		
		/*
		 *  jQuery script to initialize the Color Picker field. Each Color Picker on the form
		 *  will have its own intialization script.
		 */ 
		$script = "jQuery(document).ready(function()
					{
						jQuery('#".$this->elementId."_colorpicker').colpick(
						{
							layout:'rgbhex',
							color:'$color',
							colorScheme:'$colorScheme',
							onSubmit:function(hsb,hex,rgb,el) 
							{
								jQuery(el).css('background-color', '#'+hex);
								jQuery(el).colpickHide();
								jQuery('#$this->elementId').val(hex);
								var hexValue = jQuery('#".$this->elementId."_hexvalue');
								if(hexValue){ hexValue.text('#'+hex); }
							}
						})
						.css('background-color', '#$color');
					});\n";
		
		return $script;
	}
	
	/**
	 * This function will generate the HTML for the custom field on the F2C Article form
	 *
	 * @param	array		$translatedFields		Array of field translations
	 * @param	array		$contentTypeSettings	Array containing settings for the Content Type
	 * @param	array		$parms					Array with additional parameters
	 * @param	JForm		$form					Form object
	 * @param	int			$formId					Id of the current form
	 * 
	 * @return  string		HTML containing the rendered field
	 * 
	 * @since   6.8.0
	 */
	public function render($translatedFields, $contentTypeSettings, $parms = array(), $form, $formId)
	{
		$html = '';
		
		// Get the current or default color
		$color = empty($this->values['VALUE']) ? $this->settings->get('default_value') : $this->values['VALUE'];
		
		$html .= '<div id="'.$this->elementId.'_colorpicker" class="f2ccolorpicker"><i class="icon-edit f2ccolorpickericon"></i></div>';
		
		if($this->settings->get('show_hex_value', true))
		{
			$html .= '<div class="f2ccolorpicker_hexvalue"><div class="valign" id="'.$this->elementId.'_hexvalue">'.($color?'#':'').$color.'</div></div>';
		}
		
		if(JFactory::getApplication()->isSite())
		{
			$html .= $this->renderRequiredText($contentTypeSettings);
			$html .= $this->getFieldDescription($translatedFields);
		}
		
		$html .= $this->renderHiddenField($this->elementId, $color);
		$html .= $this->renderHiddenField('hid'.$this->elementId, $this->internal['fieldcontentid']);

		return $html;
	}
	
	/**
	 * Method to convert the submitted (post) data into the internal field data structure.
	 *
	 * @param	int			$formId			Id of the current form
	 * 
	 * @return  void
	 * 
	 * @since   6.8.0
	 */
	public function prepareSubmittedData($formId)
	{
		$jinput = JFactory::getApplication()->input;
		
		$this->internal['fieldcontentid'] 	= $jinput->getInt('hid'.$this->elementId);
		$this->values['VALUE'] 				= $jinput->getString($this->elementId);
		return $this;
	}
		
	/**
	 * Method to create an array of F2cFieldHelperContent objects to pass to the storage engine.
	 *
	 * @param	int			$formId			Id of the current form
	 * 
	 * @return  array		Array of F2cFieldHelperContent objects
	 * 
	 * @since   6.8.0
	 */
	public function store($formid)
	{
		$content 	= array();
		$value 		= isset($this->values['VALUE']) ? $this->values['VALUE'] : '';
		$fieldId 	= $this->internal['fieldcontentid'];
		$action 	= ($value != '') ? (($fieldId) ? 'UPDATE' : 'INSERT') : (($fieldId) ? 'DELETE' : '');
		$content[] 	= new F2cFieldHelperContent($fieldId, 'VALUE', $value, $action);

		return $content;		
	}
		
	/**
	 * Method to validate the field data. Throws an Exception when validation fails.
	 *
	 * @return  void
	 * 
	 * @since   6.8.0
	 */
	public function validate()
	{
		$value = trim($this->values['VALUE']);
		
		// Check if this is a required field and if so, the user did not select a color
		if($this->settings->get('requiredfield') && empty($value))
		{
			throw new Exception($this->getRequiredFieldErrorMessage());
		}

		if($value)
		{
			// Test for a valid color, ranging from 000000 to ffffff
			if(!preg_match('/^[0-9a-fA-F]{6}$/', $value))
			{
				throw new Exception(sprintf(JText::_('COM_FORM2CONTENT_ERROR_COLOR_VALIDATION'), $value));
			}
		}
	}
		
	/**
	 * Method to generate client-side script to validate the field
	 *
	 * @param	int		$validationCounter	Counter that must be increased on every call of this function
	 * 
	 * @return  string	Generated script
	 * 
	 * @since   6.8.0
	 */
	public function getClientSideValidationScript(&$validationCounter)
	{
		$script = '';
		// Get the value for the color picker field
		$script .= 'var val'.$this->elementId.'=jQuery(\'#'.$this->elementId.'\').val().trim();';
		
		if($this->settings->get('requiredfield'))
		{
			// Generate an error message when the field is set to required and its value is empty
			$script .= 'if(val'.$this->elementId.'=="") {alert("'.addslashes($this->getRequiredFieldErrorMessage()).'"); return false;}';
		}
		
		// Check if the field contains a valid color (range 000000 - fffff)
		$script .= 'if(val'.$this->elementId.'!="" && !F2C_ValPatternMatch("'.$this->elementId.'", "^[0-9a-fA-F]{6}$"))';
		$script .= '{ ';
		$script .= 'var msg = \''.JText::_('COM_FORM2CONTENT_ERROR_COLOR_VALIDATION', true).'\';';
		$script .= 'alert(msg.replace(\'%s\', val'.$this->elementId.'));';
		$script .= 'return false; } ';
		
		return $script;
	}
	
	/**
	 * Method to create an Export XML node based upon the field data.
	 *
	 * @param	object		$xmlFields				XML node to append to
	 * @param	int			$formId					Id of the current form
	 * 
	 * @return  void
	 * 
	 * @since   6.8.0
	 */
	public function export($xmlFields, $formId)
	{
      	$xmlField = $xmlFields->addChild('field');
      	$xmlField->fieldname = $this->fieldname;
      	$xmlFieldContent = $xmlField->addChild('contentSingleTextValue');
      	$xmlFieldContent->value = $this->values['VALUE'];
	}
	
	/**
	 * Method to fill the internal field data based on an XML import node.
	 *
	 * @param	object		$xmlField				XML node containing the data
	 * @param	object		$existingInternalData	Actual values for the field as present in the database
	 * @param	int			$formId					Id of the current form
	 * 
	 * @return  void
	 * 
	 * @since   6.8.0
	 */
	public function import($xmlField, $existingInternalData, $formId)
	{
		$this->values['VALUE'] = (string)$xmlField->contentSingleTextValue->value;
		$this->internal['fieldcontentid'] = $formId ? $existingInternalData['fieldcontentid'] : 0;
	}	
	
	/**
	 * Method to add field specific template parameters.
	 *
	 * @param	object		$smarty		Template engine object
	 * @param	JForm		$form		Form object
	 * 
	 * @return  void
	 * 
	 * @since   6.8.0
	 */
	public function addTemplateVar($templateEngine, $form)
	{
		$templateEngine->addVar($this->fieldname, $this->values['VALUE']);
		
		if($this->values['VALUE'])
		{
			$templateEngine->addVar($this->fieldname.'_RED', JString::substr($this->values['VALUE'], 0, 2));
			$templateEngine->addVar($this->fieldname.'_GREEN', JString::substr($this->values['VALUE'], 2, 2));
			$templateEngine->addVar($this->fieldname.'_BLUE', JString::substr($this->values['VALUE'], 4, 2));
		}
		else 
		{
			$templateEngine->addVar($this->fieldname.'_RED', '');
			$templateEngine->addVar($this->fieldname.'_GREEN', '');
			$templateEngine->addVar($this->fieldname.'_BLUE', '');
		}		
	}
	
	/**
	 * Method to fill the field data structure from an external data structure. 
	 * (called from createFormDataObjects)
	 *
	 * @param	object		$data	Data structure containing the form data
	 * 
	 * @return  void
	 * 
	 * @since   6.8.0
	 */
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