<?php
defined('JPATH_PLATFORM') or die('Restricted acccess');

require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_form2content'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'projectbase.php');
require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_form2content'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'form.php');

class Form2ContentModelProject extends Form2ContentModelProjectBase
{	
	public function save($data)
	{
		$jConfig	= JFactory::getConfig();
		$tzoffset 	= $jConfig->get('config.offset');
		$dateNow	= JFactory::getDate(null, $tzoffset); 
		$isNew		= empty($data['id']);
		$isImport	= array_key_exists('import', $data);

		if($isNew)
		{
			$user 				= JFactory::getUser();
			$data['created_by']	= $user->id;		
			$data['created']	= $dateNow->toSql();
			
			if($configInfo = JInstaller::parseXMLInstallFile(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'manifest.xml')) 
			{
				$data['version'] = $configInfo['version'];
			}			
		}

		$data['modified'] = $dateNow->toSql();
				
		if(!parent::save($data))
		{
			return false;
		}
	
		$data['id'] = $this->getState('project.id');
		
		// check if we need to generate a default template
		if(!$isImport && $isNew && F2cFactory::getConfig()->get('generate_sample_template'))
		{
			$data['settings']['intro_template'] = 'default_intro_template_' . JFile::makeSafe($data['title']) . '.tpl';
			$data['settings']['main_template']  = 'default_main_template_' . JFile::makeSafe($data['title']) . '.tpl';

			if(!parent::save($data))
			{
				return false;
			}
		}
		
		return true;
	}

	public function syncJoomlaAdvancedParms($id)
	{
		$query = 	'UPDATE #__f2c_form frm ';
		$query .= 	'INNER JOIN #__f2c_project prj ON frm.projectid = prj.id AND prj.id = ' . (int)$id . ' ';
		$query .=	'SET frm.attribs = prj.attribs';

		$this->_db->setQuery($query);
		
		if(!$this->_db->execute())
		{			
			$this->setError($this->_db->getErrorMsg());
			return false; 
		}

		return true;
	}
	
	function syncMetadata($id)
	{
		$sql = 	'UPDATE #__f2c_form frm ' .
				'INNER JOIN #__f2c_project prj ON frm.projectid = prj.id AND prj.id = ' . (int)$id . ' ' . 
				'SET frm.metadata = prj.metadata, frm.metakey = prj.metakey, frm.metadesc = prj.metadesc';

		$this->_db->setQuery($sql);
		
		if(!$this->_db->execute())
		{			
			$this->setError($this->_db->getErrorMsg());
			return false; 
		}
		else
		{
			return true;
		}
	}
	
	public function copy(&$pks)
	{
		$contentTypeTable		= $this->getTable(); 				
		$contentTypeFieldRow	= JTable::getInstance('ProjectField','Form2ContentTable'); 	
		$dateNow 				= JFactory::getDate();
		$timestamp 				= $dateNow->toSql();
		
		foreach ($pks as $i => $pk)
		{
			if(!$contentTypeTable->load($pk))
			{
				$this->setError($contentTypeTable->getError());
				return false;
			}
			
			$contentTypeTable->title 	= JText::_('COM_FORM2CONTENT_COPY_OF') . ' ' . $contentTypeTable->title;
			$contentTypeTable->id 		= null; // force insert
			$contentTypeTable->asset_id = null; // force insert
			$contentTypeTable->created 	= $timestamp;
			$contentTypeTable->modified = $this->_db->getNullDate();
			
			if(!$contentTypeTable->store())
			{
				$this->setError($contentTypeTable->getError());
				return false;
			}
			
			// copy the ContentType Fields
			$query = $this->_db->getQuery(true);
			$query->select('*');
			$query->from('#__f2c_projectfields');
			$query->where('projectid = ' . (int)$pk);
			
			$this->_db->setQuery($query->__toString());
			
			$contentTypeFields = $this->_db->loadAssocList();

			if(count($contentTypeFields))
			{
				foreach($contentTypeFields as $contentTypeField)
				{
					if (!$contentTypeFieldRow->bind($contentTypeField)) 
					{
						$this->setError($this->_db->getErrorMsg());
						return false;
					}

					$contentTypeFieldRow->id = 0; // force insert
					$contentTypeFieldRow->projectid = $contentTypeTable->id;
				
					if(!$contentTypeFieldRow->store())
					{
						$this->setError($contentTypeFieldRow->getError());
						return false;
					}
					
					// Inserting new Content Type fields generated new ordering
					// Resave the field with the original ordering
					$contentTypeFieldRow->ordering = $contentTypeField['ordering'];
					
					if(!$contentTypeFieldRow->store())
					{
						$this->setError($contentTypeFieldRow->getError());
						return false;
					}
				}
			}
		}
		
		return true;
	}
	
	public function delete(&$pks)
	{
		// Initialise variables.
		$dispatcher			= JDispatcher::getInstance();
		$pks				= (array)$pks;
		$context 			= $this->option.'.'.$this->name;
		$modelForm			= new Form2ContentModelForm();
		$contentTypeTable	= $this->getTable();
		
		// Include the content plugins for the on delete events.
		JPluginHelper::importPlugin('form2content');
		
		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk) 
		{
			$contentType = F2cFactory::getContentType($pk);
			
			if($contentTypeTable->load($pk)) 
			{
				// Get the list of forms for this Content Type
				$query = $this->_db->getQuery(true);
				$query->select('id')->from('#__f2c_form')->where('projectid = ' . (int)$pk);
				
				$this->_db->setQuery($query);
				
				$formIds = $this->_db->loadColumn();
				
				if(!$modelForm->delete($formIds))
				{
					$this->setError($modelForm->getError());
					return false;
				}
				
				foreach($contentType->fields as $field)
				{
					$field->deleteContentType();
				}
				
				// Delete the translations
				$this->_db->setQuery('DELETE tra.* FROM #__f2c_translation tra ' . 
									 'INNER JOIN #__f2c_projectfields pfl ON pfl.id = tra.reference_id ' .
									 'WHERE pfl.projectid ='.(int)$pk);
				
				if(!$this->_db->execute())
				{
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
				
				// Delete the Content Type Field definitions
				$query = $this->_db->getQuery(true);
				$query->delete('#__f2c_projectfields')->where('projectid = ' . (int)$pk);
				
				$this->_db->setQuery($query);
				
				if(!$this->_db->execute())
				{
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
	
				// Delete the Content Type			
				if (!$contentTypeTable->delete($pk)) 
				{
					$this->setError($contentTypeTable->getError());
					return false;
				}
			}
			else
			{
				$this->setError($contentTypeTable->getError());
				return false;
			}						
		}

		// Clear the component's cache
		$cache = JFactory::getCache($this->option);
		$cache->clean();

		return true;
	}
	
	public function export()
	{ 
	}
	
	public function import($file)
	{
		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'projectfield.php');
				
		$f2cConfig 			= F2cFactory::getConfig();
		$contentTypeData 	= array();
		$data				= array();
		
		if(!$xml = @simplexml_load_file($file))
		{
			$this->setError(JText::_('COM_FORM2CONTENT_ERROR_IMPORT_CONTENTTYPE_NO_CONTENTTYPE'));
			return false;
		}

		if($xml->getName() != 'contenttype')
		{
			$this->setError(JText::_('COM_FORM2CONTENT_ERROR_IMPORT_CONTENTTYPE_NO_CONTENTTYPE'));
			return false;
		}
		
		$contentTypeTitle 	= $xml->title;
		$version 			= $xml->version;	
		$nodeSettings		= $xml->settings;
		$introTemplate 		= $nodeSettings->intro_template;
		$mainTemplate 		= $nodeSettings->main_template;
		$formTemplate 		= $nodeSettings->form_template;
		
		// Check if the version of the component is equal or higher
		// to the version of the imported Content Type
		$versionCheck = false;
		
		if(!$version)
		{
			$this->setError(JText::sprintf(JText::_('COM_FORM2CONTENT_ERROR_IMPORT_CONTENTTYPE_INCOMPATIBLE_VERSION'), $componentVersion, $version));
			return false;
		}
		
		list($importMajor, $importMinor, $importRevision) = explode('.', $version);
		
		$componentInfo = JInstaller::parseXMLInstallFile(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'manifest.xml');
		$componentVersion = $componentInfo['version'];
		list($compMajor, $compMinor, $compRevision) = explode('.', $componentVersion);
		
		// Major versions must be the same
		if((int)$compMajor != (int)$importMajor)
		{
			$this->setError(JText::sprintf(JText::_('COM_FORM2CONTENT_ERROR_IMPORT_CONTENTTYPE_INCOMPATIBLE_VERSION'), $componentVersion, $version));
			return false;
		}
		
		if((int)$compMinor > (int)$importMinor)
		{
			$versionCheck = true;
		}
		else if(((int)$compMinor == (int)$importMinor) &&
				((int)$compRevision >= (int)$importRevision))
		{
			$versionCheck = true;
		}
				
		if(!$versionCheck)
		{
			$this->setError(JText::sprintf(JText::_('COM_FORM2CONTENT_ERROR_IMPORT_CONTENTTYPE_VERSION_TOO_LOW'), $componentVersion, $version));
			return false;
		}
		
		// Check if the Content Type doesn't exist yet
		$query = $this->_db->getQuery(true);
		$query->select('count(*)');
		$query->from('#__f2c_project');
		$query->where('title = ' . $this->_db->quote($contentTypeTitle));
		
		$this->_db->setQuery($query);
				
		if($this->_db->loadResult())
		{
			$this->setError(JText::sprintf(JText::_('COM_FORM2CONTENT_ERROR_IMPORT_CONTENTTYPE_EXISTS'), $contentTypeTitle));
			return false;			
		}
		
		// Check if the templates don't exist yet
      	$introTemplateFile = Path::Combine($f2cConfig->get('template_path'), $introTemplate);

      	if(JFile::exists($introTemplateFile))
      	{
			$this->setError(JText::sprintf(JText::_('COM_FORM2CONTENT_ERROR_IMPORT_CONTENTTYPE_TEMPLATE_EXISTS'), $introTemplate));
			return false;			
      	}
		
      	$mainTemplateFile = Path::Combine($f2cConfig->get('template_path'), $mainTemplate);
      	
      	if(JFile::exists($mainTemplateFile))
      	{
			$this->setError(JText::sprintf(JText::_('COM_FORM2CONTENT_ERROR_IMPORT_CONTENTTYPE_TEMPLATE_EXISTS'), $mainTemplate));
			return false;			
      	}

      	// Only perform check when there is a form template
      	if($formTemplate)
      	{
	      	$formTemplateFile = Path::Combine($f2cConfig->get('template_path'), $formTemplate);
	      	
	      	if(JFile::exists($formTemplateFile))
	      	{
				$this->setError(sprintf(JText::_('COM_FORM2CONTENT_ERROR_IMPORT_CONTENTTYPE_TEMPLATE_EXISTS'), $formTemplate));
				return false;			
	      	}
      	}
      	
      	$data['import']		= true;
		$data['title'] 		= (string)$xml->title;
		$data['id'] 		= null; // force insert
		$data['asset_id'] 	= null; // force insert
		$data['published']	= (string)$xml->published;
		$data['metakey']	= (string)$xml->metakey;
		$data['metadesc']	= (string)$xml->metadesc;
		$data['settings'] 	= $this->xmlToArray($xml->settings);
		$data['attribs'] 	= $this->xmlToArray($xml->attribs);
		$data['metadata'] 	= $this->xmlToArray($xml->metadata);
		
		if(!$this->save($data))
		{
			return false;
		}	
		
		$contentTypeId =  $this->getState('project.id');
		
      	if($xml->fields->children())
      	{
      		foreach($xml->fields->children() as $field)
      		{
				$fld 						= new Form2ContentModelProjectField();				
				$fldData 					= array();
				$fldData['projectid'] 		= $contentTypeId;
				$fldData['fieldname'] 		= (string)$field->fieldname;
				$fldData['title'] 			= (string)$field->title;
				$fldData['description'] 	= (string)$field->description;
				$fldData['frontvisible']	= (string)$field->frontvisible;
				$fldData['fieldtypeid'] 	= (string)$field->fieldtypeid;
				$fldData['settings']		= $this->xmlToArray($field->settings);
				
				$fld->save($fldData, false);
      		}
      	}
		
		// Write the template files
		JFile::write($introTemplateFile, $xml->introtemplatefile);
		JFile::write($mainTemplateFile, $xml->maintemplatefile);
		
		if($formTemplate != '')
		{
			JFile::write($formTemplateFile, $xml->formtemplatefile);
		}
		
		return true;
	}	
	
	function xmlToArray($node)
	{
		$array = array();
		
		if(count($node->children()))
		{
			foreach($node->children() as $elementName => $child)
			{
				if($child->children())
				{
					if($elementName != 'arrayelement')
					{
						$array[$elementName] = self::xmlToArray($child);;
					}
					else 
					{
						$array[(string)$child->key] = (string)$child->value;
					}					
				}
				else
				{
					$array[$elementName] = (string)$child;
				}
			}
		}
		
		return $array;
	}
	
	function createSampleFormTemplate($id, $overwrite = 0, $classic = 0)
	{
		$template 			= '';
		$contentType		= F2cFactory::getContentType($id);
		$templateName 		= 'default_form_template_'.$contentType->title . '.tpl';
		$filename 			= Path::Combine(F2cFactory::getConfig()->get('template_path'), $templateName);
		
		if(JFile::exists($filename) && !$overwrite)
		{
			return '1;'.$templateName;
		}
		
		$template = $classic ? $this->createSampleFormTemplateClassic($contentType) : $this->createSampleFormTemplateModern($contentType);
		
		JFile::write($filename, $template);
		
		return '0;'.$templateName;
	}
	
	private function createSampleFormTemplateClassic($contentType)
	{
		$template 			= '';
		
		$buttons = '<table style="width:100%;">
					<tr class="f2c_buttons">
						<td><div style="float: right;">{$F2C_BUTTON_APPLY}{$F2C_BUTTON_SAVE}{$F2C_BUTTON_SAVE_AND_NEW}{$F2C_BUTTON_SAVE_AS_COPY}{$F2C_BUTTON_CANCEL}</div></td>
					</tr>
					</table>';
		
		$template .= $buttons;
		$template .= '<div class="width-60 fltlft"><fieldset class="adminform"><table class="adminform" width="100%">'.PHP_EOL;

		if($contentType->settings['id_front_end'])
		{
			$template .= '<tr class="f2c_field f2c_id"><td valign="top" class="f2c_field_label">{$F2C_ID_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_ID}</td></tr>'.PHP_EOL;
		}
		if($contentType->settings['title_front_end'])
		{
			$template .= '<tr class="f2c_field f2c_title"><td valign="top" class="f2c_field_label">{$F2C_TITLE_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_TITLE}</td></tr>'.PHP_EOL;
		}
		if($contentType->settings['title_alias_front_end'])
		{
			$template .= '<tr class="f2c_field f2c_title_alias"><td valign="top" class="f2c_field_label">{$F2C_TITLE_ALIAS_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_TITLE_ALIAS}</td></tr>'.PHP_EOL;
		}
		if($contentType->settings['metadesc_front_end'])
		{
			$template .= '<tr class="f2c_field f2c_metadesc"><td valign="top" class="f2c_field_label">{$F2C_METADESC_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_METADESC}</td></tr>'.PHP_EOL;
		}
		if($contentType->settings['metakey_front_end'])
		{
			$template .= '<tr class="f2c_field f2c_metakey"><td valign="top" class="f2c_field_label">{$F2C_METAKEY_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_METAKEY}</td></tr>'.PHP_EOL;
		}
		if($contentType->settings['tags_front_end'])
		{
			$template .= '<tr class="f2c_field f2c_tags"><td valign="top" class="f2c_field_label">{$F2C_TAGS_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_TAGS}</td></tr>'.PHP_EOL;
		}
		if($contentType->settings['frontend_catsel'])
		{
			$template .= '<tr class="f2c_field f2c_catid"><td valign="top" class="f2c_field_label">{$F2C_CATID_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_CATID}</td></tr>'.PHP_EOL;
		}	
		if($contentType->settings['author_front_end'])
		{
			$template .= '<tr class="f2c_field f2c_created_by"><td valign="top" class="f2c_field_label">{$F2C_CREATED_BY_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_CREATED_BY}</td></tr>'.PHP_EOL;
		}
		if($contentType->settings['author_alias_front_end'])
		{
			$template .= '<tr class="f2c_field f2c_created_by_alias"><td valign="top" class="f2c_field_label">{$F2C_CREATED_BY_ALIAS_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_CREATED_BY_ALIAS}</td></tr>'.PHP_EOL;
		}
		if($contentType->settings['access_level_front_end'])
		{
			$template .= '<tr class="f2c_field f2c_access"><td valign="top" class="f2c_field_label">{$F2C_ACCESS_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_ACCESS}</td></tr>'.PHP_EOL;
		}
		if($contentType->settings['frontend_templsel'])
		{
			$template .= '<tr class="f2c_field f2c_intro_template"><td valign="top" class="f2c_field_label">{$F2C_INTRO_TEMPLATE_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_INTRO_TEMPLATE}</td></tr>'.PHP_EOL;
			$template .= '<tr class="f2c_field f2c_main_template"><td valign="top" class="f2c_field_label">{$F2C_MAIN_TEMPLATE_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_MAIN_TEMPLATE}</td></tr>'.PHP_EOL;
		}
		if($contentType->settings['date_created_front_end'])
		{
			$template .= '<tr class="f2c_field f2c_created"><td valign="top" class="f2c_field_label">{$F2C_CREATED_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_CREATED}</td></tr>'.PHP_EOL;
		}
		if($contentType->settings['frontend_pubsel'])
		{
			$template .= '<tr class="f2c_field f2c_publish_up"><td valign="top" class="f2c_field_label">{$F2C_PUBLISH_UP_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_PUBLISH_UP}</td></tr>'.PHP_EOL;
			$template .= '<tr class="f2c_field f2c_publish_down"><td valign="top" class="f2c_field_label">{$F2C_PUBLISH_DOWN_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_PUBLISH_DOWN}</td></tr>'.PHP_EOL;
		}
		if($contentType->settings['state_front_end'])
		{
			$template .= '<tr class="f2c_field f2c_state"><td valign="top" class="f2c_field_label">{$F2C_STATE_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_STATE}</td></tr>'.PHP_EOL;
		}
		if($contentType->settings['language_front_end'])
		{
			$template .= '<tr class="f2c_field f2c_language"><td valign="top" class="f2c_field_label">{$F2C_LANGUAGE_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_LANGUAGE}</td></tr>'.PHP_EOL;
		}
		if($contentType->settings['featured_front_end'])
		{
			$template .= '<tr class="f2c_field f2c_featured"><td valign="top" class="f2c_field_label">{$F2C_FEATURED_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_FEATURED}</td></tr>'.PHP_EOL;
		}
		
		if(count($contentType->fields))
		{
			foreach($contentType->fields as $contentTypeField)
			{
				if($contentTypeField->frontvisible)
				{
					$fieldname = strtoupper($contentTypeField->fieldname);
					$template .= '<tr class="f2c_field"><td width="100" align="left" class="key f2c_field_label" valign="top">{$'.$fieldname.'_CAPTION}</td><td valign="top" class="f2c_field_value">{$'.$fieldname.'}</td></tr>'.PHP_EOL;
				}
			}
		}
		
		$template .= '</table></fieldset>';
		
		if($contentType->settings['captcha_front_end'])
		{
			$template .= '{$F2C_CAPTCHA}';
		}
		
		$template .= '</div><div class="clr"></div>'.PHP_EOL;			
		$template .= $buttons;
		
		return $template;	
	}
	
	private function createSampleFormTemplateModern($contentType)
	{
		$template 			= '';
		
		$buttons = '<div class="f2c_button_bar">
						{$F2C_BUTTON_APPLY}{$F2C_BUTTON_SAVE}{$F2C_BUTTON_SAVE_AND_NEW}{$F2C_BUTTON_SAVE_AS_COPY}{$F2C_BUTTON_CANCEL}
					</div>
					<div class="clearfix"></div>';
				
		$template .= $buttons;
		$template .= '<div class="row-fluid form-horizontal">'.PHP_EOL;
		
		if($contentType->settings['id_front_end'])
		{
			$template .= 	'<div class="control-group f2c_field f2c_id">
								<div class="control-label f2c_field_label">{$F2C_ID_CAPTION}</div>
								<div class="controls f2c_field_value">{$F2C_ID}</div>
							</div>'.PHP_EOL;
		}
				
		if($contentType->settings['title_front_end'])
		{
			$template .= 	'<div class="control-group f2c_field f2c_title">
								<div class="control-label f2c_field_label">{$F2C_TITLE_CAPTION}</div>
								<div class="controls f2c_field_value">{$F2C_TITLE}</div>
							</div>'.PHP_EOL;
		}
		
		if($contentType->settings['title_alias_front_end'])
		{
			$template .= 	'<div class="control-group f2c_field f2c_title_alias">
								<div class="control-label f2c_field_label">{$F2C_TITLE_ALIAS_CAPTION}</div>
								<div class="controls f2c_field_value">{$F2C_TITLE_ALIAS}</div>
							</div>'.PHP_EOL;
		}
		
		if($contentType->settings['metadesc_front_end'])
		{
			$template .= 	'<div class="control-group f2c_field f2c_metadesc">
								<div class="control-label f2c_field_label">{$F2C_METADESC_CAPTION}</div>
								<div class="controls f2c_field_value">{$F2C_METADESC}</div>
							</div>'.PHP_EOL;
		}
		
		if($contentType->settings['metakey_front_end'])
		{
			$template .= 	'<div class="control-group f2c_field f2c_metakey">
								<div class="control-label f2c_field_label">{$F2C_METAKEY_CAPTION}</div>
								<div class="controls f2c_field_value">{$F2C_METAKEY}</div>
							</div>'.PHP_EOL;
		}
		
		if($contentType->settings['tags_front_end'])
		{
			$template .= 	'<div class="control-group f2c_field f2c_tags">
								<div class="control-label f2c_field_label">{$F2C_TAGS_CAPTION}</div>
								<div class="controls f2c_field_value">{$F2C_TAGS}</div>
							</div>'.PHP_EOL;
		}
		
		if($contentType->settings['frontend_catsel'])
		{
			$template .= 	'<div class="control-group f2c_field f2c_catid">
								<div class="control-label f2c_field_label">{$F2C_CATID_CAPTION}</div>
								<div class="controls f2c_field_value">{$F2C_CATID}</div>
							</div>'.PHP_EOL;
		}
		
		if($contentType->settings['author_front_end'])
		{
			$template .= 	'<div class="control-group f2c_field f2c_created_by">
								<div class="control-label f2c_field_label">{$F2C_CREATED_BY_CAPTION}</div>
								<div class="controls f2c_field_value">{$F2C_CREATED_BY}</div>
							</div>'.PHP_EOL;
		}
		
		if($contentType->settings['author_alias_front_end'])
		{
			$template .= 	'<div class="control-group f2c_field f2c_created_by_alias">
								<div class="control-label f2c_field_label">{$F2C_CREATED_BY_ALIAS_CAPTION}</div>
								<div class="controls f2c_field_value">{$F2C_CREATED_BY_ALIAS}</div>
							</div>'.PHP_EOL;
		}
		
		if($contentType->settings['access_level_front_end'])
		{
			$template .= 	'<div class="control-group f2c_field f2c_access">
								<div class="control-label f2c_field_label">{$F2C_ACCESS_CAPTION}</div>
								<div class="controls f2c_field_value">{$F2C_ACCESS}</div>
							</div>'.PHP_EOL;
		}
		
		if($contentType->settings['frontend_templsel'])
		{
			$template .= 	'<div class="control-group f2c_field f2c_intro_template">
								<div class="control-label f2c_field_label">{$F2C_INTRO_TEMPLATE_CAPTION}</div>
								<div class="controls f2c_field_value">{$F2C_INTRO_TEMPLATE}</div>
							</div>'.PHP_EOL;
			$template .= 	'<div class="control-group f2c_field f2c_main_template">
								<div class="control-label f2c_field_label">{$F2C_MAIN_TEMPLATE_CAPTION}</div>
								<div class="controls f2c_field_value">{$F2C_MAIN_TEMPLATE}</div>
							</div>'.PHP_EOL;
		}
		
		if($contentType->settings['date_created_front_end'])
		{
			$template .= 	'<div class="control-group f2c_field f2c_created">
								<div class="control-label f2c_field_label">{$F2C_CREATED_CAPTION}</div>
								<div class="controls f2c_field_value">{$F2C_CREATED}</div>
							</div>'.PHP_EOL;
		}
		
		if($contentType->settings['frontend_pubsel'])
		{
			$template .= 	'<div class="control-group f2c_field f2c_publish_up">
								<div class="control-label f2c_field_label">{$F2C_PUBLISH_UP_CAPTION}</div>
								<div class="controls f2c_field_value">{$F2C_PUBLISH_UP}</div>
							</div>'.PHP_EOL;
			$template .= 	'<div class="control-group f2c_field f2c_publish_down">
								<div class="control-label f2c_field_label">{$F2C_PUBLISH_DOWN_CAPTION}</div>
								<div class="controls f2c_field_value">{$F2C_PUBLISH_DOWN}</div>
							</div>'.PHP_EOL;
		}
		
		if($contentType->settings['state_front_end'])
		{
			$template .= 	'<div class="control-group f2c_field f2c_state">
								<div class="control-label f2c_field_label">{$F2C_STATE_CAPTION}</div>
								<div class="controls f2c_field_value">{$F2C_STATE}</div>
							</div>'.PHP_EOL;
		}
		
		if($contentType->settings['language_front_end'])
		{
			$template .= 	'<div class="control-group f2c_field f2c_language">
								<div class="control-label f2c_field_label">{$F2C_LANGUAGE_CAPTION}</div>
								<div class="controls f2c_field_value">{$F2C_LANGUAGE}</div>
							</div>'.PHP_EOL;
		}
		
		if($contentType->settings['featured_front_end'])
		{
			$template .= 	'<div class="control-group f2c_field f2c_featured">
								<div class="control-label f2c_field_label">{$F2C_FEATURED_CAPTION}</div>
								<div class="controls f2c_field_value">{$F2C_FEATURED}</div>
							</div>'.PHP_EOL;
		}
		
		if(count($contentType->fields))
		{
			foreach($contentType->fields as $contentTypeField)
			{
				if($contentTypeField->frontvisible)
				{
					$fieldname = strtoupper($contentTypeField->fieldname);
					
					$template .= '	<div class="control-group f2c_field f2c_' . $fieldname . '">
										<div class="control-label f2c_field_label">{$'.$fieldname.'_CAPTION}</div>
										<div class="controls f2c_field_value">{$'.$fieldname.'}</div>
									</div>';
				}
			}
		}
			
		if($contentType->settings['captcha_front_end'])
		{
			$template .= '{$F2C_CAPTCHA}';
		}
		
		$template .= '</div>';		
		$template .= '<div class="clearfix"></div>'.PHP_EOL;			
		$template .= $buttons;
		
		return $template;
	}
}
?>