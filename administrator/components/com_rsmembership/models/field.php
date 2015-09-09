<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class RSMembershipModelField extends JModelAdmin
{
	protected $text_prefix = 'COM_RSMEMBERSHIP';

	public function getTable($type = 'Field', $prefix = 'RSMembershipTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_rsmembership.field', 'field', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
			return false;

		return $form;
	}

	protected function loadFormData() 
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_rsmembership.edit.field.data', array());

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
		$db 	= JFactory::getDBO();

		parent::delete($cids);

		foreach ($cids as $cid)
		{
			$db->setQuery("ALTER TABLE #__rsmembership_subscribers DROP `f".$cid."`");
			$db->query();
		}

		return true;
	}

	public function save($data) 
	{
		$db 	= JFactory::getDBO();

		parent::save($data);
		
		$field_id = $this->getState($this->getName() . '.id', 'id');

		$db->setQuery("SHOW COLUMNS FROM #__rsmembership_subscribers WHERE `Field` = 'f".$field_id."'");
		if (!$db->loadResult())
		{
			$type = 'VARCHAR(255)';
			if (in_array($data['type'], array('freetext', 'textarea')))
				$type = 'TEXT';

			$db->setQuery("ALTER TABLE #__rsmembership_subscribers ADD `f".$field_id."` ".$type." NOT NULL");
			$db->query();
		}

		return true;
	}
	
	public function changevalue($pks, $value = 1, $task) {
		
		$user = JFactory::getUser();
		$table = $this->getTable();
		$pks = (array) $pks;
		
		foreach ($pks as $i => $pk) {
			if ($table->load($pk)) {
				if ($task == 'showinsubscribers' || $task == 'hideinsubscribers') $table->showinsubscribers = $value;
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