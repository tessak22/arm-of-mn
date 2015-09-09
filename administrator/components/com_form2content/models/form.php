<?php
defined('JPATH_PLATFORM') or die('Restricted acccess');

require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_form2content'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'project.php');
require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_form2content'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'formbase.php');

class Form2ContentModelForm extends Form2ContentModelFormBase
{
	public function save($data)
	{
		if($data['created'])
		{
			if($date = F2cDateTimeHelper::ParseDate($data['created'], '%Y-%m-%d'))
			{
				$data['created'] = $date->toSql();			
			}
			else
			{
				$this->setError(JText::sprintf(JText::_('COM_FORM2CONTENT_ERROR_DATE_FIELD_INCORRECT_DATE'), JText::_('COM_FORM2CONTENT_FIELD_CREATED_LABEL'), $translatedDateFormat));
				return false;
			}
		}

		if($data['publish_up'])
		{
			if($date = F2cDateTimeHelper::ParseDate($data['publish_up'], '%Y-%m-%d'))
			{
				$data['publish_up'] = $date->toSql();			
			}
			else
			{
				$this->setError(JText::sprintf(JText::_('COM_FORM2CONTENT_ERROR_DATE_FIELD_INCORRECT_DATE'), JText::_('COM_FORM2CONTENT_FIELD_PUBLISH_UP_LABEL'), $translatedDateFormat));
				return false;
			}
		}

		if($data['publish_down'])
		{
			if($date = F2cDateTimeHelper::ParseDate($data['publish_down'], '%Y-%m-%d'))
			{
				$data['publish_down'] = $date->toSql();			
			}
			else
			{
				$this->setError(JText::sprintf(JText::_('COM_FORM2CONTENT_ERROR_DATE_FIELD_INCORRECT_DATE'), JText::_('COM_FORM2CONTENT_FIELD_PUBLISH_DOWN_LABEL'), $translatedDateFormat));
				return false;
			}
		}

		return parent::save($data);
	}

	public function getJArticle($id) 
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		// Construct the query
		$query->select('*');
		$query->from('#__content');
		$query->where('id = ' . (int)$id);		

		$db->setQuery($query);
		
		$obj = $db->loadObject();
		
		if(!$obj)
		{
			$obj = new JObject();
			$obj->hits = 0;
			$obj->version = 0;
			$obj->modified_by = null;
		}
		
		return $obj;
	}
	
	public function export($cid)
	{
		require_once(JPATH_COMPONENT_SITE.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'SimpleXMLExtended.php');

		$db 		= JFactory::getDbo();
		$f2cConfig 	= F2cFactory::getConfig();
		$query		= $db->getQuery(true);
		$nullDate	= $db->getNullDate();
		$exportDir	= $f2cConfig->get('export_dir');
		$xml 		= new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><forms xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://schemas.form2content.com/forms f2c_forms_1_1_0.xsd" xmlns="http://schemas.form2content.com/forms"></forms>');
		$arrState	= array(F2C_STATE_TRASH => 'trashed', F2C_STATE_UNPUBLISHED => 'unpublished', F2C_STATE_PUBLISHED => 'published');
		
		// Build the Category Alias lookup lists
		$this->InitXmlImport();
		
		if(empty($exportDir))
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_FORM2CONTENT_ERROR_EXPORT_DIR_EMPTY'), 'notice');
			return false;
		}

		if(!JFolder::exists($exportDir))
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_FORM2CONTENT_ERROR_EXPORT_DIR_DOES_NOT_EXIST'), 'notice');
			return false;
		}
		
		// load all tag ids and their corresponding paths
		$queryTags = $db->getQuery(true);
		$queryTags->select('id, path')->from('#__tags');
		
		$db->setQuery($queryTags);		
		$dicTags = $db->loadAssocList('id', 'path');
		
		foreach($cid as $id)
		{
			$form 		= $this->getItem($id);
			$xmlForm 	= $xml->addChild('form');
			
			$xmlForm->id = $form->id;
			$xmlForm->contenttype = $this->dicContentTypeId[$form->projectid];				
			$xmlForm->title = $form->title;					
			$xmlForm->alias = $form->alias;					
			$xmlForm->created_by_username = $this->resolveUserid($form->created_by);					
			$xmlForm->created_by_alias = $form->created_by_alias;					
			$xmlForm->created = $this->dateToIso8601OrEmpty($form->created);					
			$xmlForm->modified = $this->dateToIso8601OrEmpty($form->modified);					
			$xmlForm->metakey = $form->metakey;					
			$xmlForm->metadesc = $form->metadesc;					
			$xmlForm->cat_alias_path = $this->dicCatId[$form->catid];
			$xmlForm->intro_template = $form->intro_template;					
			$xmlForm->main_template = $form->main_template;					
			$xmlForm->ordering = $form->ordering;					
			$xmlForm->publish_up = $this->dateToIso8601OrEmpty($form->publish_up);					
			$xmlForm->publish_down = $this->dateToIso8601OrEmpty($form->publish_down);
			$xmlForm->state = $arrState[$form->state];
			$xmlForm->featured = $form->featured ? "yes" : "no";		
			$xmlForm->access = $this->dicViewingAccessLevelId[$form->access];
			$xmlForm->language = $form->language;
			
      		$xmlFieldAttribs = $xmlForm->addChild('attribs');
      			
      		if($form->attribs)
      		{
      			$this->addArrayToXml($xmlFieldAttribs, $form->attribs);
      		}

      		$xmlFieldMetadata = $xmlForm->addChild('metadata');
      			
      		if($form->metadata)
      		{
      			$this->addArrayToXml($xmlFieldMetadata, $form->metadata);
      		}

      		// Add the full path for each tag
      		$xmlTags = $xmlForm->addChild('tags');
      		      		
      		if(count($form->tags))
      		{
      			foreach ($form->tags as $tag) 
      			{
      				$xmlTags->addChild('tag', self::valueReplace($dicTags[$tag]));
      			}
      		}
      		
      		$xmlFields = $xmlForm->addChild('fields');
      		
      		if(count($form->fields))
      		{
      			foreach($form->fields as $field)
      			{
      				$field->export($xmlFields, $form->id);
      			}
      		}
		}
		
		// Write the export file
		$timestamp = new JDate();
		$fileName = Path::Combine($exportDir, $timestamp->format('YmdHis'). '_F2C_Export.xml');
		$xmlFileContent = $xml->asXML(); 
		JFile::write($fileName, $xmlFileContent);	
		
		JFactory::getApplication()->enqueueMessage(JText::sprintf(JText::_('COM_FORM2CONTENT_ARTICLE_EXPORT_COMPLETE'), count($cid), $fileName));
		return true;
	}
	
	/*
	 * Support batch processing of the forms
	 */
	public function batch($commands, $pks, $contexts)
	{
		if(parent::batch($commands, $pks, $contexts))
		{
			// refresh the forms
			$modelForm = new Form2ContentModelForm(array('ignore_request' => true));
			return $modelForm->publish($pks, F2C_STATE_RETAIN);
		}
	}
	
	/**
	 * Batch copy items to a new category or current.
	 *
	 * @param   integer  $value     The new category.
	 * @param   array    $pks       An array of row IDs.
	 * @param   array    $contexts  An array of item contexts.
	 *
	 * @return  mixed  An array of new IDs on success, boolean false on failure.
	 *
	 * @since	6.0.0
	 */
	protected function batchCopy($value, $pks, $contexts)
	{
		$categoryId = (int)$value;
		$i = 0;

		// Check that the category exists
		if ($categoryId)
		{
			$categoryTable = JTable::getInstance('Category');
			if (!$categoryTable->load($categoryId))
			{
				if ($error = $categoryTable->getError())
				{
					// Fatal error
					$this->setError($error);
					return false;
				}
				else
				{
					$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_MOVE_CATEGORY_NOT_FOUND'));
					return false;
				}
			}
		}

		if (empty($categoryId))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_MOVE_CATEGORY_NOT_FOUND'));
			return false;
		}
		
		// Check that the user has create permission for the component
		$user = JFactory::getUser();

		if (!$user->authorise('core.create', 'com_form2content'))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_CREATE'));
			return false;
		}
		
		$modelForm = new Form2ContentModelForm(array('ignore_request' => true));
		$newIds = $modelForm->copy($pks, $categoryId);		
		
		// Clean the cache
		$this->cleanCache();

		return $newIds;		
	}
	
	/**
	 * Batch tag a list of item.
	 *
	 * @param   integer  $value     The value of the new tag.
	 * @param   array    $pks       An array of row IDs.
	 * @param   array    $contexts  An array of item contexts.
	 *
	 * @return  void.
	 *
	 * @since   6.5.0
	 */
	protected function batchTag($value, $pks, $contexts)
	{
		// Set the variables
		$user = JFactory::getUser();
		$table = $this->getTable();

		foreach ($pks as $pk)
		{
			if ($user->authorise('core.edit', $contexts[$pk]))
			{
				$table->reset();
				$table->load($pk);
				
				$tags 		= array($value);
				$extended	= new JRegistry($table->extended);
				
				if($extended->get('tags') != '')
				{
					$oldTags = explode(',', $extended->get('tags'));
				}
				else 
				{
					$oldTags = array();
				}
				
				if(count($tags))
				{
					foreach($tags as $tag)
					{
						if(!array_key_exists($tag, $oldTags))
						{
							$oldTags[] = $tag;
						}
					}
				}
				
				$extended = new JRegistry();
				$extended->set('tags', implode(',',$oldTags));
				
				$table->extended = $extended->toString();
				$table->store();
			}
			else
			{
				$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

				return false;
			}
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}
	
	/*
	 * Convert an array to an XML structure
	 */
	private function addArrayToXml($node, $array, $keyIsElement = true)
	{
		if(is_array($array))
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
					$xmlArrayElement	= $node->addChild('arrayelement');
					
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
	
	private function valueReplace($value)
	{
		$value = str_replace('&nbsp;', '&amp;nbsp;', $value);
		$value = str_replace('&gt;', '&amp;gt;', $value);
		$value = str_replace('&lt;', '&amp;lt;', $value);
		$value = str_replace('&apos;', '&amp;apos;', $value);
		
		return $value;
	}
	
	private function dateToIso8601OrEmpty($date)
	{
		if($date != JFactory::getDbo()->getNullDate())
		{
			$formattedDate = new JDate($date);
			return $formattedDate->toISO8601();
		}
		else 
		{
			return '';				
		}
		
		return $formattedDate;
	}
	
	private function resolveUserid($userid)
	{
		static $usernames = array();
		
		if(array_key_exists($userid, $usernames))
		{
			return $usernames[$userid];
		}
		else 
		{
			$user = JUser::getInstance($userid);
			return $user->username;			
		}
	}
}
?>