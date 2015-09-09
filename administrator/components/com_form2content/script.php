<?php
defined('JPATH_PLATFORM') or die('Restricted acccess');

/**
 * Script file of Form2Content component
 */
class com_Form2ContentInstallerScript
{
        /**
         * method to run before an install/update/uninstall method
         *
         * @return void
         */
        function preflight($type, $parent) 
        {
        	$joomlaVersionRequired = '3.1.5';
        	
        	if(!$this->checkJoomlaVersion($joomlaVersionRequired))
        	{
        		JFactory::getApplication()->enqueueMessage(sprintf(JText::_('COM_FORM2CONTENT_JOOMLA_VERSION_TOO_LOW'), $joomlaVersionRequired), 'error');
        		return false;
        	}

		 	if(!(extension_loaded('gd') && function_exists('gd_info')))
		 	{
		 		JFactory::getApplication()->enqueueMessage(JText::_('COM_FORM2CONTENT_GDI_NOT_INSTALLED'), 'warning');
		 	}
        	
        	return true;
        }
	
    /**
     * method to install the component
     *
     * @return void
     */
    function install($parent) 
    {
    	$this->__createPath(JPATH_SITE . '/images/stories/com_form2content');
    	$this->__createPath(JPATH_SITE . '/media/com_form2content/templates');
    	$this->__createPath(JPATH_SITE . '/media/com_form2content/documents');
    	$this->__createPath(JPATH_SITE . '/media/com_form2content/import/archive');
    	$this->__createPath(JPATH_SITE . '/media/com_form2content/import/error');
    	$this->__createPath(JPATH_SITE . '/media/com_form2content/export');
		?>	
		<div align="left">
		<img src="../media/com_form2content/images/OSD_logo.png" width="350" height="180" border="0">
		<h2><?php JText::_('COM_FORM2CONTENT_WELCOME_TO_F2C'); ?></h2>
		<p>&nbsp;</p>	
		<p><strong><?php echo JText::_('COM_FORM2CONTENT_INSTALL_SAMPLE_DATA_QUESTION'); ?></strong></p>
		<p><?php echo JText::_('COM_FORM2CONTENT_INSTALL_SAMPLE_DATA_RECOMMEND'); ?></p>
		<p>
			<button class="btn btn-large btn-success" onclick="location.href='index.php?option=com_form2content&task=projects.installsamples';return false;" href="#">
				<i class="icon-apply icon-white"></i>
				<?php echo JText::_('COM_FORM2CONTENT_YES_INSTALL_SAMPLE_DATA'); ?>
			</button>
			<button class="btn btn-large btn-danger" onclick="location.href='index.php?option=com_form2content';return false;" href="#">
				<i class="icon-apply icon-white"></i>
				<?php echo JText::_('COM_FORM2CONTENT_NO_DO_NOT_INSTALL_SAMPLE_DATA'); ?>
			</button>
		</p>
		</div>
		<?php        	
        }
 
        /**
     * method to uninstall the component
     *
     * @return void
     */
    function uninstall($parent) 
    {
    }
 
        /**
     * method to update the component
     *
     * @return void
     */
        function update($parent) 
        {
        	// Update F2C Lite to F2C Pro
	    	$this->__createPath(JPATH_SITE . '/media/com_form2content/documents');
        	$this->__createPath(JPATH_SITE . '/media/com_form2content/import/archive');
	    	$this->__createPath(JPATH_SITE . '/media/com_form2content/import/error');
	    	$this->__createPath(JPATH_SITE . '/media/com_form2content/export');
        				
			$db = JFactory::getDBO();
			
			// Add missing fields
			$sql = 'INSERT INTO #__f2c_fieldtype (`id`, `description`) SELECT 4, \'Check box\' FROM #__f2c_fieldtype Where id = 4 HAVING COUNT(*) = 0';
			$db->setQuery($sql);
			$db->execute();			
			
			$sql = 'INSERT INTO #__f2c_fieldtype (`id`, `description`) SELECT 7, \'IFrame\' FROM #__f2c_fieldtype Where id = 7 HAVING COUNT(*) = 0';
			$db->setQuery($sql);
			$db->execute();
			
			$sql = 'INSERT INTO #__f2c_fieldtype (`id`, `description`) SELECT 8, \'E-mail\' FROM #__f2c_fieldtype Where id = 8 HAVING COUNT(*) = 0';
			$db->setQuery($sql);
			$db->execute();
			
			$sql = 'INSERT INTO #__f2c_fieldtype (`id`, `description`) SELECT 9, \'Hyperlink\' FROM #__f2c_fieldtype Where id = 9 HAVING COUNT(*) = 0';
			$db->setQuery($sql);
			$db->execute();
			
			$sql = 'INSERT INTO #__f2c_fieldtype (`id`, `description`) SELECT 10, \'Multi select list (checkboxes)\' FROM #__f2c_fieldtype Where id = 10 HAVING COUNT(*) = 0';
			$db->setQuery($sql);
			$db->execute();
		
			$sql = 'INSERT INTO #__f2c_fieldtype (`id`, `description`) SELECT 11, \'Info Text\' FROM #__f2c_fieldtype Where id = 11 HAVING COUNT(*) = 0';
			$db->setQuery($sql);
			$db->execute();
		
			$sql = 'INSERT INTO #__f2c_fieldtype (`id`, `description`) SELECT 12, \'Date Picker\' FROM #__f2c_fieldtype Where id = 12 HAVING COUNT(*) = 0';
			$db->setQuery($sql);
			$db->execute();
		
			$sql = 'INSERT INTO #__f2c_fieldtype (`id`, `description`) SELECT 13, \'Display List\' FROM #__f2c_fieldtype Where id = 13 HAVING COUNT(*) = 0';
			$db->setQuery($sql);
			$db->execute();
		
			$sql = 'INSERT INTO #__f2c_fieldtype (`id`, `description`) SELECT 14, \'File Upload\' FROM #__f2c_fieldtype Where id = 14 HAVING COUNT(*) = 0';
			$db->setQuery($sql);
			$db->execute();
			
			$sql = 'INSERT INTO #__f2c_fieldtype (`id`, `description`) SELECT 15, \'Database Lookup\' FROM #__f2c_fieldtype Where id = 15 HAVING COUNT(*) = 0';
			$db->setQuery($sql);
			$db->execute();
		
			$sql = 'INSERT INTO #__f2c_fieldtype (`id`, `description`) SELECT 16, \'Geo Coder\' FROM #__f2c_fieldtype Where id = 16 HAVING COUNT(*) = 0';
			$db->setQuery($sql);
			$db->execute();		
			
			$sql = 'INSERT INTO #__f2c_fieldtype (`id`, `description`) SELECT 17, \'Database Lookup (Multi select)\' FROM #__f2c_fieldtype Where id = 17 HAVING COUNT(*) = 0';
			$db->setQuery($sql);
			$db->execute();

			$sql = 'INSERT INTO #__f2c_fieldtype (`id`, `description`) SELECT 18, \'Image Gallery\' FROM #__f2c_fieldtype Where id = 18 HAVING COUNT(*) = 0';
			$db->setQuery($sql);
			$db->execute();
			
			$sql = 'INSERT INTO #__f2c_fieldtype (`id`, `description`) SELECT 19, \'Color Picker\' FROM #__f2c_fieldtype Where id = 19 HAVING COUNT(*) = 0';
			$db->setQuery($sql);
			$db->execute();
			
			// Remove the sectionid column
			$db->setQuery('SHOW COLUMNS FROM #__f2c_form LIKE \'sectionid\'');
			
			if($db->loadResult())
			{
				$db->setQuery('ALTER TABLE #__f2c_form DROP COLUMN `sectionid`');
				$db->execute();
			}
			
			// add extended column (release 6.3.0)
			$db->setQuery('SHOW COLUMNS FROM #__f2c_form LIKE \'extended\'');
			
			if(!$db->loadResult())
			{
				$db->setQuery('ALTER TABLE #__f2c_form ADD COLUMN `extended` TEXT NOT NULL  AFTER `language`');
				$db->execute();
			}	

			// add name column to fieldtype table (release 6.8.0)
			$db->setQuery('SHOW COLUMNS FROM #__f2c_fieldtype LIKE \'name\'');
			
			if(!$db->loadResult())
			{
				$db->setQuery('ALTER TABLE #__f2c_fieldtype ADD COLUMN `name` VARCHAR(45) NOT NULL  AFTER `description`');
				$db->execute();
				$db->setQuery('UPDATE #__f2c_fieldtype set `name` = \'Singlelinetext\' WHERE id = 1');
				$db->execute();
				$db->setQuery('UPDATE #__f2c_fieldtype set `name` = \'Multilinetext\' WHERE id = 2');
				$db->execute();
				$db->setQuery('UPDATE #__f2c_fieldtype set `name` = \'Editor\' WHERE id = 3');
				$db->execute();
				$db->setQuery('UPDATE #__f2c_fieldtype set `name` = \'Checkbox\' WHERE id = 4');
				$db->execute();
				$db->setQuery('UPDATE #__f2c_fieldtype set `name` = \'Singleselectlist\' WHERE id = 5');
				$db->execute();
				$db->setQuery('UPDATE #__f2c_fieldtype set `name` = \'Image\' WHERE id = 6');
				$db->execute();
				$db->setQuery('UPDATE #__f2c_fieldtype set `name` = \'Iframe\' WHERE id = 7');
				$db->execute();
				$db->setQuery('UPDATE #__f2c_fieldtype set `name` = \'Email\' WHERE id = 8');
				$db->execute();
				$db->setQuery('UPDATE #__f2c_fieldtype set `name` = \'Hyperlink\' WHERE id = 9');
				$db->execute();
				$db->setQuery('UPDATE #__f2c_fieldtype set `name` = \'Multiselectlist\' WHERE id = 10');
				$db->execute();
				$db->setQuery('UPDATE #__f2c_fieldtype set `name` = \'Infotext\' WHERE id = 11');
				$db->execute();
				$db->setQuery('UPDATE #__f2c_fieldtype set `name` = \'Datepicker\' WHERE id = 12');
				$db->execute();
				$db->setQuery('UPDATE #__f2c_fieldtype set `name` = \'Displaylist\' WHERE id = 13');
				$db->execute();
				$db->setQuery('UPDATE #__f2c_fieldtype set `name` = \'Fileupload\' WHERE id = 14');
				$db->execute();
				$db->setQuery('UPDATE #__f2c_fieldtype set `name` = \'Databaselookup\' WHERE id = 15');
				$db->execute();
				$db->setQuery('UPDATE #__f2c_fieldtype set `name` = \'Geocoder\' WHERE id = 16');
				$db->execute();
				$db->setQuery('UPDATE #__f2c_fieldtype set `name` = \'Databaselookupmulti\' WHERE id = 17');
				$db->execute();
				$db->setQuery('UPDATE #__f2c_fieldtype set `name` = \'Imagegallery\' WHERE id = 18');
				$db->execute();
				$db->setQuery('UPDATE #__f2c_fieldtype set `name` = \'Colorpicker\' WHERE id = 19');
				$db->execute();
				// Change the id column to auto increment
				$db->setQuery('ALTER TABLE #__f2c_fieldtype MODIFY COLUMN id int(10) unsigned NOT NULL auto_increment');
				$db->execute();
			}	
        }
 
        /**
     * method to run after an install/update/uninstall method
     *
     * @return void
     */
    function postflight($type, $parent) 
    {
    	if($type == 'install' || $type == 'update')
    	{
    		$this->__setImportExportDefaults();
    	}
    }
	
    function __createPath($path)
    {
		if(!JFolder::exists($path))
		{
			JFolder::create($path, 0775);
		}
    }
    
    function __setImportExportDefaults()
    {
		$db = JFactory::getDBO();		
		$db->setQuery('SELECT extension_id FROM #__extensions WHERE name=\'com_form2content\'');
		
		$extensionId = $db->loadResult();

    	$configTable =  JTable::getInstance('extension');
		$configTable->load($extensionId);
		
		$params = new JRegistry($configTable->params);

    	if($params->get('import_dir') == '' && $params->get('export_dir') == '' && 
    	   $params->get('import_archive_dir') == '' && $params->get('import_error_dir') == '')
  		{
  			$params->set('import_dir', JPATH_SITE . '/media/com_form2content/import');
  			$params->set('export_dir', JPATH_SITE . '/media/com_form2content/export');
  			$params->set('import_archive_dir', JPATH_SITE . '/media/com_form2content/import/archive');
  			$params->set('import_error_dir', JPATH_SITE . '/media/com_form2content/import/error');
  		}

  		$configTable->params = $params->toString();
		$configTable->store();  		
    }
    
    private function checkJoomlaVersion($versionNumber)
    {
    	$version = new JVersion();
    	return $version->isCompatible($versionNumber);
    }
}
?>