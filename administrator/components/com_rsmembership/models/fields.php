<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipModelFields extends JModelList
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) 
			$config['filter_fields'] = array('id', 'name', 'label', 'type', 'rule', 'required','showinsubscribers', 'published', 'ordering');

		parent::__construct($config);
	}

	protected function getListQuery()
	{
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);

		$query->
			select('*')->
			from($db->qn('#__rsmembership_fields'));

		// search filter
		$filter_word = $this->getState($this->context.'.filter.search');
		if (strlen($filter_word)) 
			$query->where($db->qn('name').' LIKE '.$db->q('%'.$filter_word.'%'));
		// state filter
		$filter_state = $this->getState($this->context.'.filter.filter_state');
		if (is_numeric($filter_state)) 
			$query->where($db->qn('published').' = '.$db->q($filter_state));

		$listOrdering  	= $this->getState('list.ordering', 'ordering');
		$listDirection 	= $this->getState('list.direction', 'ASC');

		$query->order($listOrdering.' '.$listDirection);
		return $query;
	}

	protected function populateState($ordering = null, $direction = null) 
	{
		$app = JFactory::getApplication();

		$this->setState($this->context.'.filter.search', 		 $app->getUserStateFromRequest($this->context.'.fields.search', 'filter_search'));
		$this->setState($this->context.'.filter.filter_state', 	 $app->getUserStateFromRequest($this->context.'.fields.filter_state', 'filter_state'));

		parent::populateState('ordering', 'ASC');
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
		$options['listOrder']  = $this->getState('list.ordering', 'ordering');
		$options['listDirn']   = $this->getState('list.direction', 'asc');
		$options['sortFields'] = array(
			JHtml::_('select.option', 'id',			JText::_('JGRID_HEADING_ID')),
			JHtml::_('select.option', 'name',		JText::_('COM_RSMEMBERSHIP_FIELD')),
			JHtml::_('select.option', 'label',		JText::_('COM_RSMEMBERSHIP_LABEL')),
			JHtml::_('select.option', 'type',		JText::_('COM_RSMEMBERSHIP_TYPE')),
			JHtml::_('select.option', 'rule', 		JText::_('COM_RSMEMBERSHIP_VALIDATION_RULE')),
			JHtml::_('select.option', 'required',	JText::_('COM_RSMEMBERSHIP_REQUIRED')),
			JHtml::_('select.option', 'showinsubscribers',	JText::_('COM_RSMEMBERSHIP_SHOW_IN_SUBSCRIBERS')),
			JHtml::_('select.option', 'published',	JText::_('JPUBLISHED')),
			JHtml::_('select.option', 'ordering',	JText::_('JGRID_HEADING_ORDERING'))
		);

		// Fields States filter
		$options['states'] = array(
			JHtml::_('select.option', '', JText::_('COM_RSMEMBERSHIP_MEMBERSHIPS_FILTER_BY_STATE')),
			JHtml::_('select.option', '1', JText::_('COM_RSMEMBERSHIP_MEMBERSHIPS_FILTER_PUBLISHED')),
			JHtml::_('select.option', '0', JText::_('COM_RSMEMBERSHIP_MEMBERSHIPS_FILTER_UNPUBLISHED'))
		);
		$options['filter_state'] 	= $this->getState($this->context.'.filter.filter_state');

		// Joomla 2.5
		$options['rightItems'] = array(
			array(
				'input' => '<select name="filter_state" class="inputbox" onchange="this.form.submit()">'."\n"
						   .JHtml::_('select.options', $options['states'], 'value', 'text', $options['filter_state'], false)."\n"
						   .'</select>'
			)
		);

		$bar = new RSFilterBar($options);

		return $bar;
	}

	public function getSideBar() 
	{
		require_once JPATH_COMPONENT.'/helpers/toolbar.php';
		
		$options['states'] = array(
			JHtml::_('select.option', '1', JText::_('COM_RSMEMBERSHIP_MEMBERSHIPS_FILTER_PUBLISHED')),
			JHtml::_('select.option', '0', JText::_('COM_RSMEMBERSHIP_MEMBERSHIPS_FILTER_UNPUBLISHED'))
		);
		$options['filter_state'] 	= $this->getState($this->context.'.filter.filter_state');
		
		RSMembershipToolbarHelper::addFilter(
			JText::_('COM_RSMEMBERSHIP_MEMBERSHIPS_FILTER_BY_STATE'),
			'filter_state',
			JHtml::_('select.options', $options['states'], 'value', 'text', $options['filter_state'], true)
		);

		return RSMembershipToolbarHelper::render();
	}

	public function getTable($type = 'Field', $prefix = 'RSMembershipTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
}