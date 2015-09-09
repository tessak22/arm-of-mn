<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipModelShare_url extends JModelAdmin
{
	public function __construct() {
		parent::__construct();
	}

	public function getTable($type = 'MembershipShared', $prefix = 'RSMembershipTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	
	public function getForm($data = array(), $loadData = true) {
		// Get the form.
		$form = $this->loadForm('com_rsmembership.share_url', 'share_url', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form)) 
			return false;

		return $form;
	}

	
	protected function loadFormData() 
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_rsmembership.edit.category.data', array());

		if (empty($data))
			$data = $this->getItem();

		return $data;
	}
	
	public function getItem($pk = null)
	{
		$jinput 		= JFactory::getApplication()->input;
		$cid 			= !empty($pk) ? $pk : $jinput->get('cid', 0, 'int');
		$membership_id 	= $jinput->get('membership_id', 0, 'int');

		if (!empty($membership_id))
			$row = $this->getTable('MembershipShared','RSMembershipTable');
		else
			$row = $this->getTable('ExtraValueShared','RSMembershipTable');

		$row->load($cid);

		return $row;
	}

	public function getRSFieldset() {
		require_once JPATH_COMPONENT.'/helpers/adapters/fieldset.php';
		
		$fieldset = new RSFieldset();
		return $fieldset;
	}

	function addMembershipURL($cid) 
	{
		$row 			= $this->getTable('MembershipShared','RSMembershipTable');
		$jinput 		= JFactory::getApplication()->input;
		$jform 			= $jinput->get('jform', array(), 'array');

		$row->id = $cid;
		$row->membership_id = $jinput->get('membership_id', 0, 'int');
		$row->params = $jform['params'];
		$row->type 	 = $jform['where'];

		if (empty($row->id))
			$row->ordering = $row->getNextOrder("`membership_id`='".$row->membership_id."'");

		$row->store();
		return true;
	}

	function addExtraValueURL($cid) 
	{
		$row 			= $this->getTable('ExtraValueShared','RSMembershipTable');
		$jinput 		= JFactory::getApplication()->input;
		$jform 			= $jinput->get('jform', array(), 'array');

		$row->id = $cid;
		$row->extra_value_id = $jinput->get('extra_value_id', 0, 'int');
		$row->params = $jform['params'];
		$row->type 	 = $jform['where'];

		if (empty($row->id))
			$row->ordering = $row->getNextOrder("`extra_value_id`='".$row->extra_value_id."'");

		$row->store();
		return true;
	}
}