<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class RSMembershipModelUpgrades extends JModelList
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) 
			$config['filter_fields'] = array( 'id', 'published', 'from_name', 'price');

		parent::__construct($config);
	}

	protected function getListQuery()
	{
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);

		$query->
			select('`u`.*, '.$db->qn('mfrom.name', 'from_name').', '.$db->qn('mto.name','to_name'))->
			from($db->qn('#__rsmembership_membership_upgrades', 'u'))->
			join('left', $db->qn('#__rsmembership_memberships', 'mfrom').' ON '.$db->qn('mfrom.id').' = '.$db->qn('u.membership_from_id'))->
			join('left', $db->qn('#__rsmembership_memberships', 'mto').' ON '.$db->qn('mto.id').' = '.$db->qn('u.membership_to_id'));

		// state filter
		$filter_state = $this->getState($this->context.'.filter.filter_state');
		if (is_numeric($filter_state)) 
			$query->where($db->qn('u.published').' = '.$db->q($filter_state));

		$listOrdering  	= $this->getState('list.ordering', 'id');
		$listDirection 	= $this->getState('list.direction', 'ASC');

		$query->order($listOrdering.' '.$listDirection);

		return $query;
	}

	public function getTable($type = 'Upgrade', $prefix = 'RSMembershipTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	protected function populateState($ordering = null, $direction = null) 
	{
		$app = JFactory::getApplication();

		$this->setState($this->context.'.filter.filter_state', 	 $app->getUserStateFromRequest($this->context.'.upgrades.filter_state', 'filter_state'));

		parent::populateState('id', 'ASC');
	}

	public function getFilterBar() 
	{
		require_once JPATH_COMPONENT.'/helpers/adapters/filterbar.php';

		// Upgrades States filter
		$options['filter_state'] 	= $this->getState($this->context.'.filter.filter_state');
		$options['states'] = array(
			JHtml::_('select.option', '', JText::_('COM_RSMEMBERSHIP_MEMBERSHIPS_FILTER_BY_STATE')),
			JHtml::_('select.option', '1', JText::_('COM_RSMEMBERSHIP_MEMBERSHIPS_FILTER_PUBLISHED')),
			JHtml::_('select.option', '0', JText::_('COM_RSMEMBERSHIP_MEMBERSHIPS_FILTER_UNPUBLISHED'))
		);

		$options['listOrder']  = $this->getState('list.ordering', 'id');
		$options['listDirn']   = $this->getState('list.direction', 'ASC');
		$options['sortFields'] = array(
			JHtml::_('select.option', 'id', 		JText::_('COM_RSMEMBERSHIP_ID')),
			JHtml::_('select.option', 'from_name', 	JText::_('COM_RSMEMBERSHIP_UPGRADE')),
			JHtml::_('select.option', 'price', 		JText::_('COM_RSMEMBERSHIP_UPGRADE_PRICE')),
			JHtml::_('select.option', 'published', 	JText::_('COM_RSMEMBERSHIP_PUBLISHED'))
		);

		$config['filter_fields'] = array( 'id', 'published', 'from_name', 'price');
		
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

		return RSMembershipToolbarHelper::render();
	}
}