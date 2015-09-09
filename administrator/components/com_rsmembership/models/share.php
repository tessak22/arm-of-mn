<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipModelShare extends JModelList
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) 
			$config['filter_fields'] = array('id', 'name', 'published', 'ordering');

		parent::__construct($config);
	}

	protected function getListQuery() 
	{
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);

		$layout 		= JFactory::getApplication()->input->get('layout', '', 'cmd');
		$filter_word 	= $this->getState($this->context.'.filter.search', '');
		$listOrdering  	= $this->getState('list.ordering', 'ordering');
		$listDirection 	= $this->getState('list.direction', 'ASC');

		switch ($layout) 
		{
			case 'article':
				$query->select('a.*, '.$db->qn('c.title', 'categorytitle'))->from($db->qn('#__content', 'a'))->join('left', $db->qn('#__categories','c').' ON '.$db->qn('c.id').' = '.$db->qn('a.catid'));

				if ( $filter_word ) 
					$query->where($db->qn('a.title').' LIKE '.$db->q('%'.$filter_word.'%'));

				$query->order($listOrdering.' '.$listDirection);
			break;

			case 'category':
				$query->select('*')->from($db->qn('#__categories'))->where($db->qn('extension').' LIKE '.$db->q('com_content'));

				if ( $filter_word ) 
					$query->where($db->qn('title').' LIKE '.$db->q('%'.$filter_word.'%'));

				$listOrdering = $db->qn('id'); // we don't have an ordering column for 

				$query->order($listOrdering.' '.$listDirection);
			break;

			case 'module':
				$query->select('*')->from($db->qn('#__modules'));

				if ( $filter_word ) 
					$query->where($db->qn('title').' LIKE '.$db->q('%'.$filter_word.'%').' OR '.$db->qn('module').' LIKE '.$db->q('%'.$filter_word.'%'));

				$query->order($listOrdering.' '.$listDirection);
			break;

			case 'menu':
				$query->
					select($db->qn('id').', '.$db->qn('title','name').', '.$db->qn('menutype').', '.$db->qn('published'))->
					from($db->qn('#__menu','m'))->where($db->qn('published').' != '.$db->q('-2').' AND '.$db->qn('client_id').' = '.$db->q('0').' AND '.$db->qn('parent_id').' > '.$db->q('0'));

				if ( $filter_word ) {
					$query->where($db->qn('title').' LIKE '.$db->q('%'.$filter_word.'%').' OR '.$db->qn('menutype').' LIKE '.$db->q('%'.$filter_word.'%'));
				}

				if ( RSMembershipHelper::isJ3() ) 
					$listOrdering = $db->qn('lft');

				$query->order($listOrdering.' '.$listDirection);

			break;
		}

		return $query;
	}

	public function getItems() 
	{
		$db 	= JFactory::getDBO();
		$items  = $this->_isPlugin() ? array() : parent::getItems();

		if ( empty($items) ) 
		{
			if ( $this->_isPlugin() ) 
			{
				$instances = RSMembership::getSharedContentPlugins();
				foreach ($instances as $instance)
				{
					if ( method_exists($instance, 'getData') ) 
						$instance->getData($this->getShareType(), $items, $this->getState($this->context.'.share.limitstart'), $this->getState($this->context.'.share.limit'));
				}
			}
			else
				$items = $db->getList($this->getListQuery(), $this->getState($this->context.'.share.limitstart'), $this->getState($this->context.'.share.limit'));
		}

		return $items;
	}

	public function getTotal() 
	{
		if ($this->_isPlugin())
		{
			$instances = RSMembership::getSharedContentPlugins();
			foreach ($instances as $instance)
				if ( method_exists($instance, 'getTotal') ) 
					$instance->getTotal($this->getShareType(), $total);
		} else 
			$total = parent::getTotal();

		
		return $total;
	}

	protected function populateState($ordering = null, $direction = null) {
		$app = JFactory::getApplication();
		$this->setState($this->context.'.filter.search', $app->getUserStateFromRequest($this->context.'.share.search', 'search'));
		$this->setState($this->context.'.share.limitstart', $app->getUserStateFromRequest($this->context.'.share.limitstart', 0, 'int'));
		$this->setState($this->context.'.share.limit', $app->getUserStateFromRequest($this->context.'.share.limit', 'limit', $app->getCfg('list_limit')));

		parent::populateState('ordering', 'ASC');
	}

	public function getFilterBar() 
	{
		require_once JPATH_COMPONENT.'/helpers/adapters/filterbar.php';

		// Search filter
		$options['search'] = array(
			'label' => JText::_('JSEARCH_FILTER'),
			'value' => $this->getState($this->context.'.filter.search')
		);

		$bar = new RSFilterBar($options);

		return $bar;
	}

	public function getSideBar() 
	{
		require_once JPATH_COMPONENT.'/helpers/toolbar.php';

		return RSMembershipToolbarHelper::render();
	}

	public function getRSFieldset() 
	{
		require_once JPATH_COMPONENT.'/helpers/adapters/fieldset.php';

		$fieldset = new RSFieldset();
		return $fieldset;
	}

	public function getHeaders() 
	{
		$headers = array();
		
		$instances = RSMembership::getSharedContentPlugins();
		foreach ($instances as $instance)
			if (method_exists($instance, 'getHeaders'))
				$instance->getHeaders($this->getShareType(), $headers);
		
		return $headers;
	}

	public function _isPlugin() 
	{
		return JFactory::getApplication()->input->get('layout', '', 'string') == 'plugin';
	}

	public function getPluginShareTypes()
	{
		$plugins 	= array();
		$instances 	= RSMembership::getSharedContentPlugins();

		foreach ($instances as $instance)
		{
			if ( method_exists($instance, 'getSupportedSharedTypes') ) 
				$plugins = array_merge($plugins, $instance->getSupportedSharedTypes());
		}	

		return $plugins;
	}

	function getShareType() 
	{
		return JFactory::getApplication()->input->get('share_type', '', 'string');
	}

	function addItems($items, $type, $shared_type)
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);

		$membership_id  = JFactory::getApplication()->input->get('membership_id',  0, 'int');
		$extra_value_id = JFactory::getApplication()->input->get('extra_value_id', 0, 'int');

		foreach ($items as $item)
		{
			if ($type == 'membership') 
			{
				$row = $this->getTable('MembershipShared','RSMembershipTable');
				$row->membership_id = $membership_id;
			}
			else
			{
				$row = $this->getTable('ExtraValueShared','RSMembershipTable');
				$row->extra_value_id = $extra_value_id;
			}

			$row->params = $item;
			$row->type = $shared_type;

			$query->clear();
			if ($type == 'membership')
				$query->select('*')->from($db->qn('#__rsmembership_membership_shared'))->where($db->qn('params').' = '.$db->q($item).' AND '.$db->qn('membership_id').' = '.$db->q($membership_id).' AND '.$db->qn('type').' = '.$db->q($row->type));
			else
				$query->select('*')->from($db->qn('#__rsmembership_extra_value_shared'))->where($db->qn('params').' = '.$db->q($item).' AND '.$db->qn('extra_value_id').' = '.$db->q($extra_value_id).' AND '.$db->qn('type').' = '.$db->q($row->type));

			$db->setQuery($query);
			$db->execute();

			if ($db->getNumRows())
				continue;

			if ($type == 'membership')
				$row->ordering = $row->getNextOrder($db->qn('membership_id').' = '.$db->q($row->membership_id));
			else
				$row->ordering = $row->getNextOrder($db->qn('extra_value_id').' = '.$db->q($row->extra_value_id));

			$row->store();
		}

		return true;
	}

	public function getSortColumn()
	{
		$sortColumn = JFactory::getApplication()->input->get('filter_order', 'ordering', 'string');
		if ($this->_isPlugin())
		{
			$instances = RSMembership::getSharedContentPlugins();
			foreach ($instances as $instance)
				if (method_exists($instance, 'getSortColumn'))
					$instance->getSortColumn($this->getShareType(), $sortColumn);
		}
		
		return $sortColumn;
	}
}
