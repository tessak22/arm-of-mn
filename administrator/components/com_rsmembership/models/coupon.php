<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class RSMembershipModelCoupon extends JModelAdmin
{
	protected $text_prefix = 'COM_RSMEMBERSHIP';

	public function getTable($type = 'Coupon', $prefix = 'RSMembershipTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_rsmembership.coupon', 'coupon', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
			return false;

		return $form;
	}

	protected function loadFormData() 
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_rsmembership.edit.coupon.data', array());

		if (empty($data)) 
			$data = $this->getItem();

		return $data;
	}

	public function getItem($pk = null)
	{
		$db 	= JFactory::getDBO();
		$query 	= $db->getQuery(true);
		$item 	= parent::getItem($pk);

		$query->select($db->qn('membership_id'))->from($db->qn('#__rsmembership_coupon_items'))->where($db->qn('coupon_id').' = '.$db->q($item->id));
		$db->setQuery($query);

		// load coupon items
		$item->used_for = array();
		foreach($db->loadObjectList() as $coupon_item) 
			$item->used_for[] = $coupon_item->membership_id;

		return $item;
	}

	public function getRSFieldset() 
	{
		require_once JPATH_COMPONENT.'/helpers/adapters/fieldset.php';

		$fieldset = new RSFieldset();
		return $fieldset;
	}

	public function save($data) 
	{
		$db 		= JFactory::getDBO();
		$query		= $db->getQuery(true);

		if (empty($data['id'])) 
			$data['date_added'] = RSMembershipHelper::showDate(time(), 'Y-m-d H:i:s');

		parent::save($data);

		$coupon_id 	= $this->getState($this->getName() . '.id', 'id');

		// delete
		$query->delete()->from($db->qn('#__rsmembership_coupon_items'))->where($db->qn('coupon_id').' = '.$db->q($coupon_id));
		$db->setQuery($query);
		$db->execute();

		// insert in coupon_items 
		if (!empty($data['used_for'])) 
		{
			foreach($data['used_for'] as $membership_item) {
				$query->clear();
				$query->insert($db->qn('#__rsmembership_coupon_items'))->set($db->qn('coupon_id').' = '.$db->q($coupon_id).', '.$db->qn('membership_id').' = '.$db->q($membership_item));
				$db->setQuery($query);
				$db->execute();
			}
		}

		return true;
	}

	public function delete(&$cids)
	{
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$in_cids 	= "'".implode($db->q(','), $cids)."'";

		// delete coupon items
		$query->delete()->from($db->qn('#__rsmembership_coupon_items'))->where($db->qn('coupon_id').' IN ('.$in_cids.')');
		$db->setQuery($query);
		$db->execute();

		parent::delete($cids);

		return true;
	}

}