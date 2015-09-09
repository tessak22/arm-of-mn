<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class RSMembershipModelMembership_Field extends JModelAdmin
{
	protected $text_prefix = 'COM_RSMEMBERSHIP';

	public function getTable($type = 'Membership_Field', $prefix = 'RSMembershipTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_rsmembership.membership_field', 'membership_field', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
			return false;

		return $form;
	}

	protected function loadFormData() 
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_rsmembership.edit.membership_field.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}
		if (!empty($data) && is_object($data) && !$data->id && !$data->membership_id) {
			$model = $this->getInstance('Membership_Fields', 'RSMembershipModel');
			$data->membership_id = $model->getState('filter.membership_id');
		}
		return $data;
	}

	public function getRSFieldset() 
	{
		require_once JPATH_COMPONENT.'/helpers/adapters/fieldset.php';

		$fieldset = new RSFieldset();
		return $fieldset;
	}
	
	protected function getReorderConditions($table) {
		$condition = array(
			'membership_id = '.(int) $table->membership_id
		);
		return $condition;
	}
	
	public function changevalue($pks, $value = 1, $task) {
		
		$user = JFactory::getUser();
		$table = $this->getTable();
		$pks = (array) $pks;
		
		foreach ($pks as $i => $pk) {
			if ($table->load($pk)) {
				if ($task == 'setrequired' || $task == 'unsetrequired') $table->required = $value;
				
				if (!$table->store()) {
					$this->setError($table->getError());
					return false;
				}
			}
		}
		
		$this->cleanCache();

		return true;
	
	}
}