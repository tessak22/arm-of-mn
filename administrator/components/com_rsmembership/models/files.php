<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.path');

class RSMembershipModelFiles extends JModelList
{
	protected $folder;
	protected $isWindows;
	
	public function __construct($config = array()) {
		// Some workarounds are needed for Windows
		$this->isWindows = DIRECTORY_SEPARATOR == '\\';
		
		// Get requested folder
		$folder = JFactory::getApplication()->input->get('folder', '', 'string');

		// Check if it's a valid folder - else return to root.
		if (strlen($folder) && is_dir($folder)) {
			$this->folder = $this->cleanPath($folder);
		} else {
			$this->folder = $this->cleanPath(JPATH_SITE);
		}
		
		parent::__construct($config);
	}
	
	public function getIsWindows() {
		return $this->isWindows;
	}
	
	protected function cleanPath($path) {
		$path = realpath($path);
		$path = rtrim($path, '\\/');
		
		if ($this->isWindows) {
			$path = str_replace('\\', '/', $path);
		}
		
		return $path;
	}

	public function getFilterBar() {
		require_once JPATH_COMPONENT.'/helpers/adapters/filterbar.php';

		// No filters for J 3.0.
		$options['orderDir'] = false;;

		$bar = new RSFilterBar($options);

		return $bar;
	}

	public function getSideBar() {
		require_once JPATH_COMPONENT.'/helpers/toolbar.php';

		return RSMembershipToolbarHelper::render();
	}
	
	/* Folder navigation & listing */
	public function getFolders() {
		$return = array();

		$folders = JFolder::folders($this->folder);
		foreach ($folders as $name) {
			$return[] = (object) array(
				'name' 		=> $name,
				'fullpath' 	=> $this->folder.'/'.$name
			);
		}
		
		return $return;
	}
	
	public function getFiles() {
		$return = array();
		
		$files = JFolder::files($this->folder);
		foreach ($files as $filename) {
			$return[] = (object) array(
				'name'		=> $filename,
				'fullpath'	=> $this->folder.'/'.$filename,
				'published'	=> 1
			);
		}

		return $return;
	}

	public function getElements() {
		static $return;
		
		if (!is_array($return)) {
			$return = array();
			$parts	= explode('/', $this->folder);
			
			foreach ($parts as $i => $part) {
				$return[] = (object) array(
					'name'		=> $part,
					'fullpath'	=> isset($return[$i-1]) ? $return[$i-1]->fullpath.'/'.$part : $part
				);
			}
		}
		
		return $return;
	}

	public function getCurrent() {
		return $this->folder;
	}

	public function getPrevious() {
		$elements = $this->getElements();
		if (count($elements) > 1) {
			array_pop($elements);
		}
		
		$previous = end($elements);
		return $previous->fullpath;
	}

	/* Uploads */
	public function upload() 
	{
		$upload = JFactory::getApplication()->input->files->get('upload');

		if (!$upload['error']) 
			return JFile::upload($upload['tmp_name'], $this->folder.'/'.JFile::getName($upload['name']));
		else
			return false;
	}

	public function getCanUpload() {
		return is_writable($this->folder);
	}
	
	public function newdir($dirname) {
		return JFolder::create($this->folder.'/'.$dirname);
	}

	public function remove($cids) {
		$db 	= JFactory::getDbo();
		$query	= $db->getQuery(true);
		
		// Select all files and folders
		$query->select('*')
			  ->from($db->qn('#__rsmembership_files'));
		foreach ($cids as $cid) {
			$query->where($db->qn('path').' = '.$db->q($cid), 'OR');
			$query->where($db->qn('path').' LIKE '.$db->q($cid.'/%'), 'OR');
		}
		$db->setQuery($query);
		$files = $db->loadObjectList('id');
		
		// Mark files for deletion
		$toDelete = array();
		foreach ($files as $file) {
			if (!empty($file->thumb) && is_file(JPATH_ROOT.'/components/com_rsmembership/assets/thumbs/files/'.$file->thumb)) {
				$toDelete[] = JPATH_ROOT.'/components/com_rsmembership/assets/thumbs/files/'.$file->thumb;
			}
		}
		if ($toDelete) {
			JFile::delete($toDelete);
		}

		// Delete files from database if there are such entries.
		if ($files) {
			$query->clear();
			$query->delete()
				  ->from($db->qn('#__rsmembership_files'))
				  ->where($db->qn('id').' IN ('.implode(',', array_keys($files)).')');
			$db->setQuery($query);
			$db->execute();
		}

		// Delete files from server
		foreach ($cids as $cid) {
			if (is_dir($cid)) {
				JFolder::delete($cid);
			} elseif (is_file($cid)) {
				JFile::delete($cid);
			}
		}

		return true;
	}

	function addsubscriberfiles($files)
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$membership_id = JFactory::getApplication()->input->get('membership_id', 0, 'int');

		foreach ($files as $file)
		{
			$email_type = JFactory::getApplication()->input->get('email_type', '', 'cmd');
			$row 		= $this->getTable('MembershipAttachment','RSMembershipTable');

			$row->membership_id = $membership_id;
			$row->path 			= $file;
			$row->email_type 	= $email_type;
			$row->published 	= 1;

			$query->clear();
			$query->select($db->qn('id'))->from($db->qn('#__rsmembership_membership_attachments'))->where($db->qn('path').' = '.$db->q($file).' AND '.$db->qn('email_type').' = '.$db->q($email_type).' AND '.$db->qn('membership_id').' = '.$db->q($membership_id));
			$db->setQuery($query);

			if ($db->loadResult())
				continue;

			$row->ordering = $row->getNextOrder($db->qn('membership_id').' = '.$db->q($row->membership_id).' AND '.$db->qn('email_type').' = '.$db->q($email_type));

			if (is_file($row->path))
				$row->store();
		}
		return true;
	}
	
	function addmembershipfolders($folders)
	{
		$db 			= JFactory::getDBO();
		$query			= $db->getQuery(true);
		$membership_id  = JFactory::getApplication()->input->get('membership_id', 0, 'int');

		foreach ($folders as $folder)
		{			
			$row = $this->getTable('MembershipShared','RSMembershipTable');
			$row->membership_id = $membership_id;

			if (substr($folder, -1) != '/')
				$folder .= '/';

			$row->params = $folder;
			$row->type = 'folder';

			$query->clear();
			$query->select('*')->from($db->qn('#__rsmembership_membership_shared'))->where($db->qn('params').' = '.$db->q($folder).' AND '.$db->qn('membership_id').' = '.$db->q($membership_id).' AND '.$db->qn('type').' = '.$db->q('folder'));
			$db->setQuery($query);
			$db->execute();

			if ($db->getNumRows())
				continue;

			$row->ordering = $row->getNextOrder( $db->qn('membership_id').' = '.$db->q($row->membership_id) );
			
			if (is_dir($row->params))
				$row->store();
		}

		return true;
	}

	function addextravaluefolders($folders)
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);

		$extra_value_id = JFactory::getApplication()->input->get('extra_value_id', 0, 'int');

		foreach ( $folders as $folder ) 
		{
			$row = $this->getTable('ExtraValueShared','RSMembershipTable');
			$row->extra_value_id = $extra_value_id;
			if (substr($folder, -1) != '/')
				$folder .= '/';

			$row->params = $folder;
			$row->type = 'folder';

			$query->clear();
			$query->select('*')->from($db->qn('#__rsmembership_extra_value_shared'))->where($db->qn('params').' = '.$db->q($folder).' AND '.$db->qn('extra_value_id').' = '.$db->q($extra_value_id).' AND '.$db->qn('type').' = '.$db->q('folder'));
			$db->setQuery($query);
			$db->execute();

			if ($db->getNumRows())
				continue;

			$row->ordering = $row->getNextOrder($db->qn('extra_value_id')." = ".$db->q($row->extra_value_id));

			if ( is_dir($row->params) )
				$row->store();
		}
		return true;
	}
}