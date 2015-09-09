<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class RSMembershipModelUpgrade extends JModelAdmin
{
	protected $text_prefix = 'COM_RSMEMBERSHIP';

	public function getTable($type = 'Upgrade', $prefix = 'RSMembershipTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_rsmembership.upgrade', 'upgrade', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
			return false;

		return $form;
	}

	public function getItem($pk = null)
	{
		$item = parent::getItem($pk = null);

		$membership 	 = $this->getInstance('Membership', 'RSMembershipModel');
		$membership_from = $membership->getItem($item->membership_from_id);
		$membership_to 	 = $membership->getItem($item->membership_to_id);

		$item->name_from = $membership_from->name;
		$item->name_to 	 = $membership_to->name;

		return $item;
	}

	protected function loadFormData() 
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_rsmembership.edit.upgrade.data', array());

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
}