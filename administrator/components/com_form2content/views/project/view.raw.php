<?php
defined('JPATH_PLATFORM') or die('Restricted acccess');

jimport('joomla.application.component.view');

require_once(JPATH_COMPONENT_SITE.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'SimpleXMLExtended.php');

class Form2ContentViewProject extends JViewLegacy
{
	function display($tpl = null)
	{
		$version 			= new JVersion;
		$id					= JFactory::getApplication()->input->getInt('id');
		$model 				= $this->getModel();
		$this->item			= $model->getItem($id);
		$fields				= $model->getFieldDefinitions($id);
		$componentInfo 		= JInstaller::parseXMLInstallFile(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'manifest.xml');
		$componentVersion 	= $componentInfo['version'];		
		$filename 			= $this->item->title . '_f2c_pro_' . $componentVersion . '_'.$version->getShortVersion().'.xml';

		ob_end_clean();
		$document = JFactory::getDocument();		
		$document->setMimeEncoding('text/xml');
		
		header('Content-Disposition: attachment; filename="' . $filename . '"');
	
		$xml = new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><contenttype></contenttype>');
		
		$xml->title = $this->item->title;
		$xml->version = $componentVersion;
		$xml->published = $this->item->published;
		$xml->metakey = $this->item->metakey;
		$xml->metadesc = $this->item->metadesc;
		
      	$xmlSettings = $xml->addChild('settings');      	
      	$this->addArrayToXml($xmlSettings, $this->item->settings);

      	$xmlAttribs = $xml->addChild('attribs');      	
      	$this->addArrayToXml($xmlAttribs, $this->item->attribs);
      	
     	$xmlMetadata = $xml->addChild('metadata');      	
      	$this->addArrayToXml($xmlMetadata, $this->item->metadata);
      	
      	$xmlFields = $xml->addChild('fields');
		
      	if(count($fields))
      	{
      		foreach($fields as $field)
      		{
      			$xmlField = $xmlFields->addChild('field');
      			
      			$xmlField->fieldname = $field->fieldname;
     			$xmlField->title = $field->title;
      			$xmlField->description = $field->description;
     			$xmlField->fieldtypeid = $field->fieldtypeid;
      			$xmlField->ordering = $field->ordering;
     			$xmlField->frontvisible = $field->frontvisible;
      			     			     			
      			$xmlFieldSettings = $xmlField->addChild('settings');
      			
      			if($field->settings)
      			{
      				$this->addRegistryToXml($xmlFieldSettings, $field->settings);
      			}
      		}	
      	}
      	
      	$xmlIntroTemplateFile = $xml->addChild('introtemplatefile');
      	$xmlIntroTemplateFile->addCData($this->getTemplateContents($this->item->settings['intro_template']));
      	$xmlMainTemplateFile = $xml->addChild('maintemplatefile');
      	$xmlMainTemplateFile->addCData($this->getTemplateContents($this->item->settings['main_template']));
      	
      	if(array_key_exists('form_template', $this->item->settings) && $this->item->settings['form_template'])
      	{
	      	$xmlFormTemplateFile = $xml->addChild('formtemplatefile');     	
	      	$xmlFormTemplateFile->addCData($this->getTemplateContents($this->item->settings['form_template']));
      	}
      	
      	echo $xml->asXML();      	
	}
	
	/*
	 * Convert an array to an XML structure
	 */
	private function addArrayToXml($node, $array, $keyIsElement = true)
	{
		if(count($array))
		{
			foreach($array as $key => $value)
			{
				if($keyIsElement)
				{					
					if(is_array($value))
					{
						// The array key is the element name
						$xmlElement = $node->addChild($key);
						self::addArrayToXml($xmlElement, $value, false);
					}
					else 
					{
						$node->$key = $value;
					}
				}
				else
				{
					// 'key' is the element name. Use this when $key might 
					// not be a valid XML element name
					$xmlArrayElement = $node->addChild('arrayelement');
					
					$xmlArrayElement->key = $key;
					$xmlArrayElement->value = $value;
				}
			}
		}
	}
		
	private function addRegistryToXml($node, $registry)
	{
		$this->addArrayToXml($node, $registry->toArray());
	}
		
	private function getTemplateContents($template)
	{
      	$contents = '';
      	$templateFile = Path::Combine(F2cFactory::getConfig()->get('template_path'), $template);

      	if(JFile::exists($templateFile))
      	{
      		$contents = file_get_contents($templateFile);
      	}
		
      	return $contents;
	}
}