<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipModelLogs extends JModelList
{
	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'date', 'ip', 'path'
			);
		}

		parent::__construct($config);
	}
	
	protected function getListQuery() {
		$db 	= JFactory::getDBO();
		$query 	= $db->getQuery(true);
		
		// get filtering states
		$search  = $this->getState('filter.search');
		$user_id = $this->getUserId();
		
		$query->select('*')
			  ->from('#__rsmembership_logs')
			  ->where($db->qn('user_id').'='.$db->q($user_id));
		// search
		if ($search != '') {
			$search = $db->quote('%'.str_replace(' ', '%', $db->escape($search, true)).'%', false);
			$query->where('('.$db->quoteName('path').' LIKE '.$search.' OR '.$db->quoteName('ip').' LIKE '.$search.')');
		}
		
		// order by
		$query->order($db->quoteName($this->getState('list.ordering', 'date')).' '.$db->escape($this->getState('list.direction', 'desc')));
		
		return $query;
	}
	
	protected function populateState($ordering = null, $direction = null) {
		$this->setState('filter.search',  $this->getUserStateFromRequest($this->context.'.filter.search',  'filter_search'));
		
		// List state information.
		parent::populateState('date', 'desc');
	}
	
	public function getIsJ30() {
		$jversion = new JVersion();
		return $jversion->isCompatible('3.0');
	}
	
	public function getFilterBar() {
		require_once JPATH_COMPONENT.'/helpers/adapters/filterbar.php';
		
		$options = array();
		$options['search'] = array(
			'label' => JText::_('JSEARCH_FILTER'),
			'value' => $this->getState('filter.search')
		);
		$options['limitBox']  = $this->getPagination()->getLimitBox();
		$options['listDirn']  = $this->getState('list.direction', 'desc');
		$options['listOrder'] = $this->getState('list.ordering', 'date');
		$options['sortFields'] = array(
			JHtml::_('select.option', 'date', JText::_('COM_RSMEMBERSHIP_DATE')),
			JHtml::_('select.option', 'ip', JText::_('COM_RSMEMBERSHIP_IP')),
			JHtml::_('select.option', 'path', JText::_('COM_RSMEMBERSHIP_PATH'))
		);
		$options['rightItems'] = array();
		
		$bar = new RSFilterBar($options);
		
		return $bar;
	}
	
	public function getSideBar() {
		require_once JPATH_COMPONENT.'/helpers/toolbar.php';
		
		return RSMembershipToolbarHelper::render();
	}
	
	public function getUserId() {
		return JFactory::getApplication()->input->getInt('user_id');
	}
}