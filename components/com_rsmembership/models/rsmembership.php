<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class RSMembershipModelRSMembership extends JModelList
{
	public $_context = 'rsmembership';

	public function getTable($type = 'Membership', $prefix = 'RSMembershipTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	protected function getListQuery() 
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$params = $this->getState($this->context.'.params');

		$query
			->select('COALESCE(`c`.`name`, '.$db->q('').') AS '.$db->qn('category_name').', m.*')
			->from($db->qn('#__rsmembership_memberships', 'm'))
			->join('left', $db->qn('#__rsmembership_categories', 'c').' ON '.$db->qn('c.id').' = '.$db->qn('m.category_id'))
			->where($db->qn('m.published').' = '.$db->q('1'));

		$category_id = JFactory::getApplication()->input->get('catid', 0, 'int');
		if ( $category_id ) 
		{
			$query->where( $db->qn('m.category_id')." = ".$db->q($category_id) );
		}
		else 
		{
			$categories = $params->get('categories', array());
			if ( !is_array($categories) )
				$categories = (array) $categories;

			if ( !empty($categories) ) 
				$query->where($db->qn('m.category_id').'  IN (\''.implode($db->q(','), $categories).'\')');
		}

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

	public function getItems()
	{
		$items = parent::getItems();

		if (!empty($items))
		{
			$pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';

			if (is_array($items))
			{
				foreach ( $items as $i => $row )
				{
					if ( $row->use_trial_period ) 
						$items[$i]->price = $row->trial_price;

					if ( preg_match($pattern, $row->description) )
						list($row->description, $fulldescription) = preg_split($pattern, $row->description, 2);
				}
			}
		}

		return $items;
	}
}