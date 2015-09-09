<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipModelMemberships extends JModelList
{
	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'm.id', 'm.name', 'category_name', 'm.period_type', 'm.price', 'm.published', 'm.ordering'
			);
		}

		parent::__construct($config);
	}

	protected function getListQuery() {
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);

		$query->
			select('m.*, '.$db->qn('c.name', 'category_name'))->
			from($db->qn('#__rsmembership_memberships', 'm'))->
			join('left',$db->qn('#__rsmembership_categories', 'c').' ON '.$db->qn('m.category_id').' = '.$db->qn('c.id'));
		
		// search filter
		$filter_word = $this->getState($this->context.'.filter.search');
		if (strlen($filter_word)) 
			$query->where($db->qn('m.name').' LIKE '.$db->q('%'.$filter_word.'%'));

		// category filter
		$filter_category = (int) $this->getState($this->context.'.filter.filter_category');
		if ($filter_category)
			$query->where($db->qn('m.category_id')." = ".$db->q($filter_category));

		// state filter
		$filter_state = $this->getState($this->context.'.filter.filter_state');
		if (is_numeric($filter_state)) 
			$query->where($db->qn('m.published').' = '.$db->q($filter_state));

		$listOrdering  	= $this->getState('list.ordering', 'm.ordering');
		$listDirection 	= $this->getState('list.direction', 'ASC');

		$query->order($listOrdering.' '.$listDirection);

		return $query;
	}

	protected function populateState($ordering = null, $direction = null) {
		$app = JFactory::getApplication();

		$this->setState($this->context.'.filter.search', 		  $app->getUserStateFromRequest($this->context.'.memberships.search', 'filter_search'));
		$this->setState($this->context.'.filter.filter_category', $app->getUserStateFromRequest($this->context.'.memberships.filter_category', 'filter_category'));
		$this->setState($this->context.'.filter.filter_state', 	  $app->getUserStateFromRequest($this->context.'.memberships.filter_state', 'filter_state'));

		parent::populateState('m.ordering', 'ASC');
	}
	
	public function getTable($type = 'Membership', $prefix = 'RSMembershipTable', $config = array()) {
		return JTable::getInstance($type, $prefix, $config);
	}
	
	// duplicate function 
	public function duplicate($cid)
	{
		$db 	= JFactory::getDBO();
		$query 	= $db->getQuery(true);

		jimport('joomla.filesystem.file');
		$row = $this->getTable();

		$row->load($cid);
		$row->published = 0;
		$row->name = JText::_('COM_RSMEMBERSHIP_COPY_OF').' '.$row->name;
		$row->ordering = $row->getNextOrder();
		$row->id = null;
		$row->store();

		$membership_new_id = $row->id;

		if (!empty($row->thumb))
		{
			$old_thumb = JPATH_ROOT.'/components/com_rsmembership/assets/thumbs/'.$row->thumb;
			$new_thumb = JPATH_ROOT.'/components/com_rsmembership/assets/thumbs/'.$row->id.'.jpg';
			$copied = JFile::copy($old_thumb, $new_thumb);
			if ($copied)
			{
				$row->thumb = $row->id.'.jpg';
				$row->store();
			}
			else
			{
				$row->thumb = '';
				$row->store();
			}
		}

		$query->select('*')->from($db->qn('#__rsmembership_membership_attachments'))->where($db->qn('membership_id').' = '.$db->q($cid));
		$db->setQuery($query);
		$attachments = $db->loadObjectList();

		if (!empty($attachments))
			foreach ($attachments as $attachment)
			{
				$row = JTable::getInstance('MembershipAttachment', 'RSMembershipTable');
				$row->bind($attachment);
				$row->membership_id = $membership_new_id;
				$row->id = null;
				$row->store();
			}

		$query->clear();
		$query->select('*')->from($db->qn('#__rsmembership_membership_extras'))->where($db->qn('membership_id').' = '.$db->q($cid));
		$db->setQuery($query);
		$extras = $db->loadObjectList();
		if (!empty($extras))
			foreach ($extras as $extra)
			{
				$row = JTable::getInstance('MembershipExtras', 'RSMembershipTable');
				$row->bind($extra);
				$row->membership_id = $membership_new_id;
				$row->id = null;
				$row->store();
			}
		
		$query->clear();
		$query->select('*')->from($db->qn('#__rsmembership_membership_shared'))->where($db->qn('membership_id').' = '.$db->q($cid));
		$db->setQuery($query);
		$shares = $db->loadObjectList();
		if (!empty($shares))
			foreach ($shares as $share)
			{
				$row = JTable::getInstance('MembershipShared', 'RSMembershipTable');
				$row->bind($share);
				$row->membership_id = $membership_new_id;
				$row->id = null;
				$row->store();
			}

		return true;
	}

	public function getCategories()  {
		$db 	= JFactory::getDBO();
		$query  = $db->getQuery(true);
		$query->select($db->qn('id').', '.$db->qn('name'))->from('#__rsmembership_categories')->order($db->qn('ordering').' ASC');
		$db->setQuery($query);
		$results = $db->loadObjectList();

		return $results;
	}
	
	public function getSideBar()  {
		require_once JPATH_COMPONENT.'/helpers/toolbar.php';
		
		// Categories filter
		$options['filter_category'] = $this->getState($this->context.'.filter.filter_category');
		$options['categories'] 		= array();
		$categories = $this->getCategories();
		foreach ($categories as $category) {
			$options['categories'][] = JHtml::_('select.option', $category->id, $category->name);
		}

		// Memberships States filter
		$options['filter_state'] 	= $this->getState($this->context.'.filter.filter_state');
		$options['states'] = array(
			JHtml::_('select.option', '1', JText::_('COM_RSMEMBERSHIP_MEMBERSHIPS_FILTER_PUBLISHED')),
			JHtml::_('select.option', '0', JText::_('COM_RSMEMBERSHIP_MEMBERSHIPS_FILTER_UNPUBLISHED'))
		);
		
		RSMembershipToolbarHelper::addFilter(
			JText::_('COM_RSMEMBERSHIP_ALL_CATEGORIES'),
			'filter_category',
			JHtml::_('select.options', $options['categories'], 'value', 'text', $options['filter_category'], true)
		);
		RSMembershipToolbarHelper::addFilter(
			JText::_('COM_RSMEMBERSHIP_MEMBERSHIPS_FILTER_BY_STATE'),
			'filter_state',
			JHtml::_('select.options', $options['states'], 'value', 'text', $options['filter_state'], false)
		);
		
		return RSMembershipToolbarHelper::render();
	}

	public function getOrdering() {
		require_once JPATH_COMPONENT.'/helpers/adapters/ordering.php';

		$ordering = new RSOrdering();
		return $ordering;
	}

	public function getFilterBar() {
		require_once JPATH_COMPONENT.'/helpers/adapters/filterbar.php';
		// search filter
		$options['search'] = array(
			'label' => JText::_('JSEARCH_FILTER'),
			'value' => $this->getState($this->context.'.filter.search')
		);

		// Ordering results
		$options['listOrder']  = $this->getState('list.ordering', 'm.ordering');
		$options['listDirn']   = $this->getState('list.direction', 'asc');
		$options['sortFields'] = array(
			JHtml::_('select.option', 'm.id', 			JText::_('COM_RSMEMBERSHIP_ID')),
			JHtml::_('select.option', 'm.ordering', 	JText::_('JGRID_HEADING_ORDERING')),
			JHtml::_('select.option', 'm.name', 		JText::_('COM_RSMEMBERSHIP_MEMBERSHIP')),
			JHtml::_('select.option', 'category_name', 	JText::_('COM_RSMEMBERSHIP_CATEGORY_NAME')),
			JHtml::_('select.option', 'm.period_type', 	JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_LENGTH')),
			JHtml::_('select.option', 'm.price', 		JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_PRICE')),
			JHtml::_('select.option', 'm.published', 	JText::_('JPUBLISHED'))
		);

		// Categories filter
		$options['filter_category'] = $this->getState($this->context.'.filter.filter_category');
		$options['categories'] 		= array(JHtml::_('select.option', '', JText::_('COM_RSMEMBERSHIP_ALL_CATEGORIES')));
		$categories = $this->getCategories();
		foreach ($categories as $category) {
			$options['categories'][] = JHtml::_('select.option', $category->id, $category->name);
		}

		// Memberships States filter
		$options['filter_state'] 	= $this->getState($this->context.'.filter.filter_state');
		$options['states'] = array(
			JHtml::_('select.option', '', JText::_('COM_RSMEMBERSHIP_MEMBERSHIPS_FILTER_BY_STATE')),
			JHtml::_('select.option', '1', JText::_('COM_RSMEMBERSHIP_MEMBERSHIPS_FILTER_PUBLISHED')),
			JHtml::_('select.option', '0', JText::_('COM_RSMEMBERSHIP_MEMBERSHIPS_FILTER_UNPUBLISHED'))
		);

		// Joomla 2.5
		$options['rightItems'] = array(
			array(
				'input' => '<select name="filter_state" class="inputbox" onchange="this.form.submit()">'."\n"
						   .JHtml::_('select.options', $options['states'], 'value', 'text', $options['filter_state'], false)."\n"
						   .'</select>'
			),
			array(
				'input' => '<select name="filter_category" class="inputbox" onchange="this.form.submit()">'."\n"
						   .JHtml::_('select.options', $options['categories'], 'value', 'text', $options['filter_category'], false)."\n"
						   .'</select>'
			)
			
		);

		$bar = new RSFilterBar($options);

		return $bar;
	}
}