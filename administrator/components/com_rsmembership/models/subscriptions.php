<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipModelSubscriptions extends JModelList
{
	protected $context = null;

	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array('ms.user_id', 'u.username', 'u.email', 'ms.membership_start', 'ms.membership_end', 'm.name','ms.status', 'ms.notified', 'ms.published');
		}
		
		$config['ignore_request'] = false;
		
		parent::__construct($config);
	}
	
	public function getContext() {
		return $this->context;
	}

	protected function getListQuery() {
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);
		
		
		$query
			->select($db->qn('ms.id'))
			->select($db->qn('ms.user_id'))
			->select($db->qn('m.name'))
			->select($db->qn('ms.extras'))
			->select($db->qn('u.username'))
			->select($db->qn('u.email'))
			->select($db->qn('ms.status'))
			->select($db->qn('ms.notified'))
			->select($db->qn('ms.membership_start'))
			->select($db->qn('ms.membership_end'))
			->select($db->qn('ms.published'))
			->select($db->qn('ms.membership_id'))
			->from($db->qn('#__rsmembership_membership_subscribers', 'ms'))
			->join('left', $db->qn('#__users','u').' ON '.$db->qn('u.id').' = '.$db->qn('ms.user_id'))
			->join('left', $db->qn('#__rsmembership_memberships', 'm').' ON '.$db->qn('ms.membership_id').' = '.$db->qn('m.id'));


		// search filter
		$where = "";
		
		$filter_word = $this->getState($this->context.'.filter.filter_search');
		if ( strlen($filter_word) ) {
			$where .= "( ";
			$where .= $db->qn('u.email').' LIKE '.$db->q('%'.$filter_word.'%').' OR ';
			$where .= $db->qn('u.username').' LIKE '.$db->q('%'.$filter_word.'%').' OR ';
			$where .= $db->qn('u.name').' LIKE '.$db->q('%'.$filter_word.'%');
			$where .= " )";
		}

		// status filter
		$filter_status = $this->getState($this->context.'.filter.filter_status');
		if (is_numeric($filter_status))
			$where .= ($where!='' ? ' AND ': '').$db->qn('ms.status') . ' = ' . $db->q($filter_status);
			
		// membersips filter	
		$filter_memberships = $this->getState($this->context.'.filter.filter_memberships');
		$filter_resetselected = $this->getState($this->context.'.filter.filter_resetselected'); // reset memberships filter
		if($filter_resetselected == '0') {
			if (is_array($filter_memberships)) {
				$where .= ($where!='' ? ' AND (': '(');
				foreach ($filter_memberships as $membership) {
					$where .= $db->qn('ms.membership_id') . ' = ' . $db->q($membership).' OR ';
				}
				$where = substr($where, 0 , -4);
				$where .= " )";
			}
		}
		
		if ($where) {
			$query->where($where);
		}

		$listOrdering  	= $this->getState('list.ordering', 'ms.user_id');
		$listDirection 	= $this->getState('list.direction', 'ASC');

		$query->order($db->qn($listOrdering).' '.$db->escape($listDirection));
		
		return $query;
	}

	protected function populateState($ordering = null, $direction = null) {
		$app = JFactory::getApplication();
		$this->setState($this->context.'.filter.filter_search', 	$app->getUserStateFromRequest($this->context.'.filter_search', 'filter_search'));
		$this->setState($this->context.'.filter.filter_status', 	$app->getUserStateFromRequest($this->context.'.filter_status', 'filter_status'));
		$this->setState($this->context.'.filter.filter_memberships', 	$app->getUserStateFromRequest($this->context.'.filter_memberships', 'filter_memberships'));
		$this->setState($this->context.'.filter.filter_resetselected', 	$app->getUserStateFromRequest($this->context.'.filter_resetselected', 'filter_resetselected'));

		parent::populateState('ms.user_id', 'ASC');
	}
	

	public function getFilterBar() {
		 require_once JPATH_COMPONENT.'/helpers/adapters/filterbar.php';

		// Search filter
		$options['search'] = array(
			'label' => JText::_('JSEARCH_FILTER'),
			'value' => $this->getState($this->context.'.filter.filter_search')
		);
		
		$options['listOrder']  = $this->getState('list.ordering', 'ms.user_id');
		$options['listDirn']   = $this->getState('list.direction', 'ASC');
		$options['sortFields'] = array(
			JHtml::_('select.option', 'ms.user_id', JText::_('COM_RSMEMBERSHIP_SUBSCRIBER_ID')),
			JHtml::_('select.option', 'u.username', JText::_('COM_RSMEMBERSHIP_USERNAME')),
			JHtml::_('select.option', 'u.email', 	JText::_('COM_RSMEMBERSHIP_EMAIL')),
			JHtml::_('select.option', 'ms.membership_start', 	JText::_('COM_RSMEMBERSHIP_START_DATE')),
			JHtml::_('select.option', 'ms.membership_end', 	JText::_('COM_RSMEMBERSHIP_START_END')),
			JHtml::_('select.option', 'ms.status', 	JText::_('COM_RSMEMBERSHIP_STATUS')),
			JHtml::_('select.option', 'ms.notified', 	JText::_('COM_RSMEMBERSHIP_NOTIFIED')),
			JHtml::_('select.option', 'm.name', 	JText::_('COM_RSMEMBERSHIP_MEMBERSHIP')),
			JHtml::_('select.option', 'ms.published', 	JText::_('JPUBLISHED'))
		);
		
		$options['limitBox'] = $this->getPagination()->getLimitBox();
		
		$options['filter_status'] = $this->getState($this->context.'.filter.filter_status');
		$statuses = array(MEMBERSHIP_STATUS_ACTIVE, MEMBERSHIP_STATUS_PENDING, MEMBERSHIP_STATUS_EXPIRED, MEMBERSHIP_STATUS_CANCELLED);
		$options['statuses'][] = JHtml::_('select.option', '',  JText::_('COM_RSMEMBERSHIP_SELECT_STATUS'));
		foreach ( $statuses as $status ) {
			$options['statuses'][] = JHtml::_('select.option', $status,  JText::_('COM_RSMEMBERSHIP_STATUS_'.$status));
		}
		
		// Joomla 2.5
		$options['rightItems'] = array(
			array(
				'input' => '<select name="filter_status" class="inputbox" onchange="this.form.submit()">'."\n"
						   .JHtml::_('select.options', $options['statuses'], 'value', 'text', $options['filter_status'], false)."\n"
						   .'</select>'
			)
		);
			

		$bar = new RSFilterBar($options);

		return $bar;
	}
	
	public function getSideBar() 
	{
		require_once JPATH_COMPONENT.'/helpers/toolbar.php';

		
		// Status filter
		$options['filter_status'] = $this->getState($this->context.'.filter.filter_status');
		$statuses = array(MEMBERSHIP_STATUS_ACTIVE, MEMBERSHIP_STATUS_PENDING, MEMBERSHIP_STATUS_EXPIRED, MEMBERSHIP_STATUS_CANCELLED);
		foreach ( $statuses as $status ) {
			$options['statuses'][] = JHtml::_('select.option', $status,  JText::_('COM_RSMEMBERSHIP_STATUS_'.$status));
		}

		RSMembershipToolbarHelper::addFilter(
			JText::_('COM_RSMEMBERSHIP_SELECT_STATUS'),
			'filter_status',
			JHtml::_('select.options', $options['statuses'], 'value', 'text', $options['filter_status'], true)
		);
		
		return RSMembershipToolbarHelper::render();
	}
	
	public function getMemberships() {
		$db		= JFactory::getDBO();
		$query = $db->getQuery(true);
		$query
			->select($db->qn('id'))
			->select($db->qn('name'))
			->from($db->qn('#__rsmembership_memberships'))
			->where($db->qn('published')." = 1")
			->order($db->qn('ordering').' ASC');
		
		$db->setQuery($query);
		$memberships = $db->loadObjectList();
		
		return $memberships;
	}
	
	public function getTotalItems() {
		$db				= JFactory::getDBO();
		$query  		= $this->getListQuery();
		$db->setQuery($query);
		$data = $db->loadObjectList();
		
		return count($data);
	}
	
	public function writeCSV($from, $fileHash = '') {
		require_once JPATH_COMPONENT.'/helpers/export.php';
		
		// setting the function arguments
		$query  		= $this->getListQuery();
		$totalItems  	= (int) $this->getTotalItems();
		
		$filename 		= JText::_('COM_RSMEMBERSHIP_SUBSCRIPTIONS');
		$type 			= 'subscriptions';
		
		return RSMembershipExport::writeCSV($type, $query, $totalItems, $from, $fileHash, $filename);
	}
	
	public function getExtraValues() {
		$cache = RSMembershipHelper::getCache();
		return $cache->extra_values;
	}

}