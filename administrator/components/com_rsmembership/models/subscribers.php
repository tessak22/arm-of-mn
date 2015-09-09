<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipModelSubscribers extends JModelList
{
	protected $context = null;

	public function __construct($config = array()) {
		require_once JPATH_COMPONENT.'/helpers/rsmembership.php';
		
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array('mu.user_id', 'u.name', 'u.email', 'u.username', 'num_subs', 'num_activesubs');
			if ($customFields = RSMembership::getCustomFields(array('showinsubscribers' => 1))) {
				foreach ($customFields as $field) {
					$config['filter_fields'][] = 'mu.f'.$field->id;
				}
			}
		}
		
		$config['ignore_request'] = false;
		
		parent::__construct($config);
	}

	protected function getListQuery() {
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);
		
		$customFields = RSMembership::getCustomFields(array());
		
		$query
			->select($db->qn('u.id'))
			->select($db->qn('u.block'))
			->select($db->qn('u.name'))
			->select($db->qn('u.username'))
			->select($db->qn('u.email'));
			
		if ($customFields) {
			foreach ($customFields as $properties) {
				if ($properties->showinsubscribers) {
					$query->select($db->qn('mu.f'.$properties->id, JText::_($properties->name)));
				}
			}
		}
		
		$query->from($db->qn('#__users','u'))
			  ->join('left', $db->qn('#__rsmembership_subscribers', 'mu').' ON '.$db->qn('u.id').' = '.$db->qn('mu.user_id'))
			  ->select('COUNT('.$db->qn('subs.user_id').') AS num_subs')
			  ->select('COUNT('.$db->qn('activesubs.user_id').') AS num_activesubs')
			  ->join('left', $db->qn('#__rsmembership_membership_subscribers', 'subs').' ON '.$db->qn('u.id').' = '.$db->qn('subs.user_id'))
			  ->join('left', $db->qn('#__rsmembership_membership_subscribers', 'activesubs').' ON '.$db->qn('u.id').' = '.$db->qn('activesubs.user_id').' AND '.$db->qn('activesubs.status').'='.$db->q(0))
			  ->group($db->qn('u.id'));


		// search filter
		$filter_word = $this->getState($this->context.'.filter.filter_search');
		if (strlen($filter_word)) {
			$query->where($db->qn('u.name').' LIKE '.$db->q('%'.$filter_word.'%'),'OR');
			$query->where($db->qn('u.email').' LIKE '.$db->q('%'.$filter_word.'%'),'OR');
			$query->where($db->qn('u.username').' LIKE '.$db->q('%'.$filter_word.'%'), 'OR');
			
			if ($customFields) {
				foreach ($customFields as $properties) {
					$query->where($db->qn('mu.f'.$properties->id).' LIKE '.$db->q('%'.$filter_word.'%'), 'OR');
				}
			}
		}

		$listOrdering  	= $this->getState('list.ordering', 'mu.user_id');
		$listDirection 	= $this->getState('list.direction', 'ASC');

		$query->order($db->qn($listOrdering).' '.$db->escape($listDirection));
		
		return $query;
	}

	protected function populateState($ordering = null, $direction = null) {
		$app = JFactory::getApplication();

		$this->setState($this->context.'.filter.filter_search', 	$app->getUserStateFromRequest($this->context.'.filter_search', 'filter_search'));

		parent::populateState('mu.user_id', 'ASC');
	}

	public function getFilterBar() {
		require_once JPATH_COMPONENT.'/helpers/adapters/filterbar.php';

		// Search filter
		$options['search'] = array(
			'label' => JText::_('JSEARCH_FILTER'),
			'value' => $this->getState($this->context.'.filter.filter_search')
		);

		// reset button
		$options['reset_button'] = true;

		$options['listOrder']  = $this->getState('list.ordering', 'mu.user_id');
		$options['listDirn']   = $this->getState('list.direction', 'ASC');
		$options['sortFields'] = array(
			JHtml::_('select.option', 'mu.user_id', JText::_('COM_RSMEMBERSHIP_SUBSCRIBER_ID')),
			JHtml::_('select.option', 'u.username', JText::_('COM_RSMEMBERSHIP_USERNAME')),
			JHtml::_('select.option', 'u.name', 	JText::_('COM_RSMEMBERSHIP_NAME')),
			JHtml::_('select.option', 'u.email', 	JText::_('COM_RSMEMBERSHIP_EMAIL'))
		);
		$customFields = RSMembership::getCustomFields(array('showinsubscribers'=>1));
		if ($customFields) {
			foreach ($customFields as $id => $properties) {
				$options['sortFields'][] = JHtml::_('select.option', 'mu.f'.$properties->id, ($properties->label ? JText::_($properties->label) : JText::_('COM_RSMEMBERSHIP_NO_TITLE')));
			}
		}
		$options['sortFields'][] = JHtml::_('select.option', 'num_subs', JText::_('COM_RSMEMBERSHIP_TOTAL_SUBSCRIPTIONS'));
		$options['sortFields'][] = JHtml::_('select.option', 'num_activesubs', JText::_('COM_RSMEMBERSHIP_ACTIVE_SUBSCRIPTIONS'));
		$options['limitBox'] = $this->getPagination()->getLimitBox();

		$bar = new RSFilterBar($options);

		return $bar;
	}

	public function getSideBar() {
		require_once JPATH_COMPONENT.'/helpers/toolbar.php';
		
		return RSMembershipToolbarHelper::render();
	}

	public function getTable($type = 'Subscriber', $prefix = 'RSMembershipTable', $config = array()) {
		return JTable::getInstance($type, $prefix, $config);
	}
	
	public function getMemberships() {
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		
		$query->select($db->qn('id'))
			  ->select($db->qn('name'))
			  ->from($db->qn('#__rsmembership_memberships'))
			  ->order($db->qn('ordering').' ASC');
		$db->setQuery($query);
		return $db->loadObjectList();
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
		$customFields 	= RSMembership::getCustomFields(array('showinsubscribers'=>1));
		$filename 		= JText::_('COM_RSMEMBERSHIP_SUBSCRIBERS');
		$type 			= 'subscribers';
		
		return RSMembershipExport::writeCSV($type, $query, $totalItems, $from, $fileHash, $filename, $customFields);
		
	}
}