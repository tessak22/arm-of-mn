<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipModelMembership_Fields extends JModelList
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) 
			$config['filter_fields'] = array('f.id', 'membership_name', 'f.name', 'label', 'type', 'rule', 'required', 'f.published', 'f.ordering');

		parent::__construct($config);
	}

	protected function getListQuery()
	{
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);
		
		$membership_id = $this->getMembershipID();
		$query->select('f.*')
			  ->select($db->qn('m.name', 'membership_name'))
			  ->from($db->qn('#__rsmembership_membership_fields', 'f'))
			  ->join('left', $db->qn('#__rsmembership_memberships', 'm').' ON ('.$db->qn('f.membership_id').' = '.$db->qn('m.id').')');
		
		if ($membership_id) {
			$query->where($db->qn('membership_id').' = '.$db->q($membership_id));
		}

		// search filter
		$filter_word = $this->getState($this->context.'.filter.search');
		if (strlen($filter_word)) 
			$query->where($db->qn('f.name').' LIKE '.$db->q('%'.$filter_word.'%'));
		// state filter
		$filter_state = $this->getState($this->context.'.filter.filter_state');
		if (is_numeric($filter_state)) 
			$query->where($db->qn('f.published').' = '.$db->q($filter_state));

		$listOrdering  	= $this->getState('list.ordering', 'f.ordering');
		$listDirection 	= $this->getState('list.direction', 'ASC');

		$query->order($listOrdering.' '.$listDirection);
		return $query;
	}
	
	public function getMembershipID() {
		$membership_id = $this->getState($this->context.'.filter.membership_id');

		return $membership_id;
	}

	protected function populateState($ordering = null, $direction = null) 
	{
		$app = JFactory::getApplication();

		$this->setState($this->context.'.filter.search', 		 $app->getUserStateFromRequest($this->context.'.fields.search', 		'filter_search'));
		$this->setState($this->context.'.filter.filter_state', 	 $app->getUserStateFromRequest($this->context.'.fields.filter_state', 	'filter_state'));
		$this->setState($this->context.'.filter.membership_id',  $app->getUserStateFromRequest($this->context.'.filter.membership_id', 	'filter_membership_id'));

		parent::populateState('f.ordering', 'ASC');
	}

	public function getOrdering() { 
		require_once JPATH_COMPONENT.'/helpers/adapters/ordering.php';

		$ordering = new RSOrdering();
		return $ordering;
	}

	public function getFilterBar() 
	{
		require_once JPATH_COMPONENT.'/helpers/adapters/filterbar.php';

		// Search filter
		$options['search'] = array(
			'label' => JText::_('JSEARCH_FILTER'),
			'value' => $this->getState($this->context.'.filter.search')
		);
		
		// Ordering results
		$options['listOrder']  = $this->getState('list.ordering', 'f.ordering');
		$options['listDirn']   = $this->getState('list.direction', 'asc');
		$options['sortFields'] = array(
			JHtml::_('select.option', 'f.id',			JText::_('JGRID_HEADING_ID')),
			JHtml::_('select.option', 'membership_name',		JText::_('COM_RSMEMBERSHIP_MEMBERSHIP')),
			JHtml::_('select.option', 'f.name',		JText::_('COM_RSMEMBERSHIP_FIELD')),
			JHtml::_('select.option', 'label',		JText::_('COM_RSMEMBERSHIP_LABEL')),
			JHtml::_('select.option', 'type',		JText::_('COM_RSMEMBERSHIP_TYPE')),
			JHtml::_('select.option', 'rule', 		JText::_('COM_RSMEMBERSHIP_VALIDATION_RULE')),
			JHtml::_('select.option', 'required',	JText::_('COM_RSMEMBERSHIP_REQUIRED')),
			JHtml::_('select.option', 'f.published',	JText::_('JPUBLISHED')),
			JHtml::_('select.option', 'f.ordering',	JText::_('JGRID_HEADING_ORDERING'))
		);

		// Fields States filter
		$options['states'] = array(
			JHtml::_('select.option', '', JText::_('COM_RSMEMBERSHIP_MEMBERSHIPS_FILTER_BY_STATE')),
			JHtml::_('select.option', '1', JText::_('COM_RSMEMBERSHIP_MEMBERSHIPS_FILTER_PUBLISHED')),
			JHtml::_('select.option', '0', JText::_('COM_RSMEMBERSHIP_MEMBERSHIPS_FILTER_UNPUBLISHED'))
		);
		$options['filter_state'] 	= $this->getState($this->context.'.filter.filter_state');
		
		$memberships = $this->getMemberships();
		$options['memberships'] = array();
		foreach ($memberships as $membership) {
			$options['memberships'][] = JHtml::_('select.option',  $membership->id, $membership->name);
		}
		

		// Joomla 2.5
		$options['rightItems'] = array(
			array(
				'input' => '<select name="filter_state" class="inputbox" onchange="this.form.submit()">'."\n"
						   .JHtml::_('select.options', $options['states'], 'value', 'text', $options['filter_state'], false)."\n"
						   .'</select>'
			),
			array(
				'input' => '<select name="filter_membership_id" class="inputbox" onchange="this.form.submit()">'."\n"
						   .JHtml::_('select.options', $options['memberships'], 'value', 'text',  $this->getMembershipID(), false)."\n"
						   .'</select>'
			)
		);

		$bar = new RSFilterBar($options);

		return $bar;
	}

	public function getSideBar() 
	{
		require_once JPATH_COMPONENT.'/helpers/toolbar.php';
		
		// Fields States filter
		$options['states'] = array(
			JHtml::_('select.option', '1', JText::_('COM_RSMEMBERSHIP_MEMBERSHIPS_FILTER_PUBLISHED')),
			JHtml::_('select.option', '0', JText::_('COM_RSMEMBERSHIP_MEMBERSHIPS_FILTER_UNPUBLISHED'))
		);
		$options['filter_state'] 	= $this->getState($this->context.'.filter.filter_state');
		
		
		$memberships = $this->getMemberships();
		$options['memberships'] = array();
		foreach ($memberships as $membership) {
			$options['memberships'][] = JHtml::_('select.option',  $membership->id, $membership->name);
		}
		
		RSMembershipToolbarHelper::addFilter(
			JText::_('COM_RSMEMBERSHIP_MEMBERSHIPS_FILTER_BY_STATE'),
			'filter_state',
			JHtml::_('select.options', $options['states'], 'value', 'text', $options['filter_state'], true)
		);
		
		RSMembershipToolbarHelper::addFilter(
			JText::_('COM_RSMEMBERSHIP_VIEW_SELECT_MEMBERSHIP'),
			'filter_membership_id',
			JHtml::_('select.options', $options['memberships'], 'value', 'text', $this->getMembershipID(), true)
		);

		return RSMembershipToolbarHelper::render();
	}
	
	public function getMemberships() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		
		$query->select($db->qn('id').', '.$db->qn('name'))
			  ->from($db->qn('#__rsmembership_memberships'))
			  ->order($db->qn('ordering').', '.$db->qn('ordering').' ASC');
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	public function getTable($type = 'Field', $prefix = 'RSMembershipTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
}