<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class RSMembershipModelExtras extends JModelList
{
	public $_context = 'extras';

	public function getTable($type = 'Extra', $prefix = 'RSMembershipTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	protected function getListQuery() 
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		
		$membership_id = JFactory::getApplication()->input->get('cid', 0, 'int');

		if ( $membership_id ) 
		{
			$query
				->select('*')
				->from($db->qn('#__rsmembership_membership_extras', 'me'))
				->join('left', $db->qn('#__rsmembership_extras', 'e').' ON '.$db->qn('me.extra_id').' = '.$db->qn('e.id'))
				->where($db->qn('me.membership_id').' = '.$db->q($membership_id))
				->where($db->qn('e.published').' = '.$db->q('1'))
				->order($db->qn('e.ordering').' ASC');
		}

		return $query;
	}

	public function getExtraValues($extra_id)
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);

		$query
			->select('*')
			->from($db->qn('#__rsmembership_extra_values'))
			->where($db->qn('published').' = '.$db->q('1'))
			->where($db->qn('extra_id').' = '.$db->q($extra_id))
			->order($db->qn('ordering').' ASC');
		$db->setQuery($query);

		return $this->_getList($query);
	}
}