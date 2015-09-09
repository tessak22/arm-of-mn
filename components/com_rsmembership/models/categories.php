<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class RSMembershipModelCategories extends JModelList
{
	public $_context = 'com_rsmembership.categories';

	public function getTable($type = 'Category', $prefix = 'RSMembershipTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	protected function getListQuery()
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$params = $this->getState($this->context.'.params');

		$query
			->select('c.*')
			->select('COUNT('.$db->qn('m.category_id').') AS '.$db->qn('memberships'))
			->from($db->qn('#__rsmembership_categories','c'))
			->join('left', $db->qn('#__rsmembership_memberships', 'm').' ON '.$db->qn('m.category_id').' = '.$db->qn('c.id'))
			->where($db->qn('c.published').' = '.$db->q('1'))
			->where($db->qn('m.published').' = '.$db->q('1'))
			->group($db->qn('c.id'));

		$listOrdering  	= ( $params->get('orderby') ? $params->get('orderby', 'ordering') : $this->getState('list.ordering', 'ordering') );
		$listDirection 	= ( $params->get('orderdir') ? $params->get('orderdir', 'ASC') : $this->getState('list.direction', 'ASC') );

		$query->order($db->qn($listOrdering).' '.$listDirection);

		return $query;
	}

	protected function populateState($ordering = null, $direction = null) 
	{
		$app 	= JFactory::getApplication();
		$active = $app->getMenu()->getActive();
		$params = new JRegistry;

		if ($active)
			$params->loadString($active->params);

		$this->setState($this->context.'.params', $params);

		parent::populateState('ordering', 'ASC');
	}
}