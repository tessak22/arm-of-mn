<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class RSMembershipModelFile extends JModelAdmin
{
	protected $text_prefix = 'COM_RSMEMBERSHIP';

	public function getTable($type = 'File', $prefix = 'RSMembershipTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_rsmembership.file', 'file', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
			return false;

		return $form;
	}

	protected function loadFormData() 
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_rsmembership.edit.file.data', array());

		if (empty($data))
			$data = $this->getItem();

		return $data;
	}

	public function getItem($pk = null)
	{
		$cid 	= JFactory::getApplication()->input->get('cid', '', 'string');
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);

		$query->select('*')->from($db->qn('#__rsmembership_files'))->where($db->qn('path').' = '.$db->q($cid));
		$db->setQuery($query);
		$item = $db->loadObject();

		
		if (empty($item)) 
		{
			$item = $this->getTable('File','RSMembershipTable');
			$item->load(0);
			$item->path = $cid;
		}
		$item->thumb_resize = $item->thumb_w;

		return $item;
	}

	public function save($data) 
	{
		$row    = $this->getTable('File','RSMembershipTable');
		$jinput = JFactory::getApplication()->input;
		$jform  = $jinput->get('jform', array(), 'array');
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);

		if (!empty($jform['thumb_delete'])) 
			$jform['thumb'] = '';

		// Thumbnail width must not be less than 1px
		$jform['thumb_w'] = (int) $jform['thumb_w'];
		if ($jform['thumb_w'] <= 0)
			$jform['thumb_w'] = 48;

		if (!$row->bind($jform))
			return JError::raiseWarning(500, $row->getError());

		unset($row->thumb);

		if ($row->store()) 
		{
			// Process the thumbnail
			$files = $jinput->files->get('jform', array(), 'array');
			$thumb = $files['thumb'];

			jimport('joomla.filesystem.file');
			$thumb['db_name'] = JPATH_ROOT.'/components/com_rsmembership/assets/thumbs/files/'.$row->id;

			// Delete the thumbnail if requested
			if (!empty($jform['thumb_delete'])) 
			{
				JFile::delete($thumb['db_name'].'.jpg');
				$query->clear();
				$query->update($db->qn('#__rsmembership_files'))->set($db->qn('thumb').' = '.$db->q(''))->where($db->qn('id').' = '.$row->id);
				$db->setQuery($query);
				$db->execute();
			}

			// Add the thumbnail if uploaded
			if (!$thumb['error'] && !empty($thumb['tmp_name'])) 
			{
				// Resize the thumb if requested
				if (!empty($jform['thumb_resize'])) 
					$success = RSMembershipHelper::createThumb($thumb['tmp_name'], $thumb['db_name'], $row->thumb_w);
				else
					$success = JFile::upload($thumb['tmp_name'], $thumb['db_name'].'.jpg');

				// Add to database only if upload successful
				if ($success)
				{
					$query->clear();
					$query->update($db->qn('#__rsmembership_files'))->set($db->qn('thumb').' = '.$db->q(JFile::getName($thumb['db_name'].'.jpg')))->where($db->qn('id').' = '.$row->id);
					$db->setQuery($query);
					$db->execute();
				}
			}

			return true;
		}
		else
		{
			JError::raiseWarning(500, $row->getError());
			return false;
		}
	}

	public function getRSFieldset() 
	{
		require_once JPATH_COMPONENT.'/helpers/adapters/fieldset.php';

		$fieldset = new RSFieldset();
		return $fieldset;
	}

	function getIsFile()
	{
		$cid = JFactory::getApplication()->input->get('cid', '', 'string');

		return is_file($cid);
	}
	
	function pathExists()
	{
		$cid = JFactory::getApplication()->input->get('cid', '', 'string');

		return (is_file($cid) || is_dir($cid));
	}
	
		
	function getFolder()
	{	
		return dirname(JFactory::getApplication()->input->get('cid', '', 'string'));
	}
}