<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class RSMembershipModelExtra extends JModelAdmin
{
	protected $text_prefix = 'COM_RSMEMBERSHIP';

	public function getTable($type = 'Extra', $prefix = 'RSMembershipTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_rsmembership.extra', 'extra', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
			return false;

		return $form;
	}

	protected function loadFormData() 
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_rsmembership.edit.extra.data', array());

		if (empty($data))
			$data = $this->getItem();

		return $data;
	}

	public function getRSFieldset() 
	{
		require_once JPATH_COMPONENT.'/helpers/adapters/fieldset.php';

		$fieldset = new RSFieldset();
		return $fieldset;
	}
	
	public function delete(&$cids)
	{
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$in_cids 	= "'".implode($db->q(','), $cids)."'";

		// delete extras
		$query->delete()->from($db->qn('#__rsmembership_extras'))->where($db->qn('id').' IN ('.$in_cids.')');
		$db->setQuery($query);
		$db->execute();

		// delete extra valuea shared items
		$query->clear();
		$query->select($db->qn('id'))->from($db->qn('#__rsmembership_extra_values'))->where($db->qn('extra_id').' IN ('.$in_cids.')');
		$db->setQuery($query);
		$ids = $db->loadColumn();

		if (!empty($ids)) 
		{
			$query->clear();
			$query->delete()->from($db->qn('#__rsmembership_extra_value_shared'))->where($db->qn('extra_value_id').' IN ('."'".implode($db->q(','), $ids)."'".')');
			$db->setQuery($query);
			$db->execute();
		}

		// delete extra values
		$query->clear();
		$query->delete()->from($db->qn('#__rsmembership_extra_values'))->where($db->qn('extra_id').' IN ('.$in_cids.')');
		$db->setQuery($query);
		$db->execute();

		// delete extras assigned to memberships
		$query->clear();
		$query->delete()->from($db->qn('#__rsmembership_membership_extras'))->where($db->qn('extra_id').' IN ('.$in_cids.')');
		$db->setQuery($query);
		$db->execute();

		return true;
	}

}