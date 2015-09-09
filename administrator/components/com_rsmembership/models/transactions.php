<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class RSMembershipModelTransactions extends JModelList
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'email', 't.type', 't.date', 't.ip', 't.price', 't.coupon', 't.status', 't.gateway', 't.id'
			);
		}
		parent::__construct($config);
	}

	protected function getListQuery()
	{
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);

		$query->
			select('t.*, IFNULL('.$db->qn('u.email').', '.$db->qn('t.user_email').') AS email ')->
			from($db->qn('#__rsmembership_transactions', 't'))->
			join('left', $db->qn('#__users', 'u').' ON '.$db->qn('t.user_id').' = '.$db->qn('u.id'));

		// search filters
		$filter_word = $this->getState($this->context.'.filter.search');
		if ( strlen($filter_word) ) {
			$query->where('('.
				$db->qn('u.email').' LIKE '.$db->q('%'.$filter_word.'%').' OR '.
				$db->qn('t.hash').' LIKE '.$db->q('%'.$filter_word.'%').' OR '.
				$db->qn('t.id').' LIKE '.$db->q('%'.$filter_word.'%').
			')');
		}

		// filter by type
		$filter_type = $this->getState($this->context.'.filter.filter_type');
		if ( $filter_type ) 
			$query->where($db->qn("t.type")." = ".$db->q($filter_type));

		// filter by gateway
		$filter_gateway = $this->getState($this->context.'.filter.filter_gateway');
		if ( $filter_gateway ) 
			$query->where($db->qn("t.gateway")." = ".$db->q($filter_gateway));

		// filter by status
		$filter_status = $this->getState($this->context.'.filter.filter_status');
		if ( $filter_status ) 
			$query->where($db->qn('t.status')." = ".$db->q($filter_status));

		// filter by date from
		$from = $this->getState($this->context.'.filter.date_from');
		if ( $from ) {
			$date = JFactory::getDate($from);
			$query->where($db->qn('t.date').' >= '.$db->q($date));
		}

		// filter by date to
		$to = $this->getState($this->context.'.filter.date_to');
		if ( $to ) {
			$date = JFactory::getDate($to);
			$date = str_replace('00:00:00', '23:59:59', $date);
			$query->where($db->qn('t.date').' <= '.$db->q($date));
		}

		$listOrdering  	= $this->getState('list.ordering', 't.date');
		$listDirection 	= $this->getState('list.direction', 'ASC');

		$query->order($db->qn($listOrdering).' '.$listDirection);
		
		return $query;
	}

	protected function populateState($ordering = null, $direction = null) 
	{
		$app = JFactory::getApplication();

		$this->setState($this->context.'.filter.search', 		 $app->getUserStateFromRequest($this->context.'.transactions.search', 		  'filter_search'));
		$this->setState($this->context.'.filter.filter_type', 	 $app->getUserStateFromRequest($this->context.'.transactions.filter_type', 	  'filter_type')); 
		$this->setState($this->context.'.filter.filter_gateway', $app->getUserStateFromRequest($this->context.'.transactions.filter_gateway', 'filter_gateway'));
		$this->setState($this->context.'.filter.date_from', 	 $app->getUserStateFromRequest($this->context.'.transactions.date_from', 	  'date_from'));
		$this->setState($this->context.'.filter.date_to', 		 $app->getUserStateFromRequest($this->context.'.transactions.date_to', 		  'date_to'));
		$this->setState($this->context.'.filter.filter_status',  $app->getUserStateFromRequest($this->context.'.transactions.filter_status',  'filter_status'));

		parent::populateState('t.date', 'desc');
	}

	public function getFilterBar() 
	{
		$db 	= JFactory::getDBO();
		$query 	= $db->getQuery(true);
		require_once JPATH_COMPONENT.'/helpers/adapters/filterbar.php';

		// Transaction Types filter
		$options['filter_type'] 	  = $this->getState($this->context.'.filter.filter_type');
		$options['transaction_types'] = array(
			JHtml::_('select.option', '', JText::_('COM_RSMEMBERSHIP_TRANSACTION_ALL_TYPES')),
			JHtml::_('select.option', 'new', JText::_('COM_RSMEMBERSHIP_TRANSACTION_NEW')),
			JHtml::_('select.option', 'renew', JText::_('COM_RSMEMBERSHIP_TRANSACTION_RENEW')),
			JHtml::_('select.option', 'upgrade', JText::_('COM_RSMEMBERSHIP_TRANSACTION_UPGRADE')),
			JHtml::_('select.option', 'addextra', JText::_('COM_RSMEMBERSHIP_TRANSACTION_ADDEXTRA'))
		);

		// Gateway filter
		$options['filter_gateway'] 	= $this->getState($this->context.'.filter.filter_gateway');
		$options['gateways'] 		= array(JHtml::_('select.option', '', JText::_('COM_RSMEMBERSHIP_TRANSACTION_ALL_GATEWAYS')), JHtml::_('select.option', 'No Gateway', JText::_('COM_RSMEMBERSHIP_NO_GATEWAY')));
		$gateways = RSMembership::getPlugins();
		foreach ($gateways as $plugin => $name) 
		{
			if ($name == 'Credit Card') $name = 'Authorize.Net';
			$options['gateways'][] = JHtml::_('select.option', $name, $name);
		}
		// Status filter
		$options['filter_status'] = $this->getState($this->context.'.filter.filter_status');
		$statuses = array('pending', 'completed', 'denied');
		$options['statuses'] = array(JHtml::_('select.option', '', JText::_('COM_RSMEMBERSHIP_TRANSACTION_ALL_STATUSES')));
		foreach ( $statuses as $status ) 
			$options['statuses'][] = JHtml::_('select.option', $status, JText::_('COM_RSMEMBERSHIP_TRANSACTION_STATUS_'.$status));
			
		// Date from
		$options['date_from'] 	= $this->getState($this->context.'.filter.date_from');
		// Date to
		$options['date_to'] = $this->getState($this->context.'.filter.date_to');

		// search filter
		$options['search'] = array(
			'label' => JText::_('JSEARCH_FILTER'),
			'value' => $this->getState($this->context.'.filter.search')
		);
		$options['limitBox'] 	= $this->getPagination()->getLimitBox();

		// use only the column header ordering 
		$options['orderDir'] = true;
		
		$options['listOrder']  = $this->getState('list.ordering', 't.date');
		$options['listDirn']   = $this->getState('list.direction', 'ASC');
		$options['sortFields'] 	= array(
			JHtml::_('select.option', 't.id', 		JText::_('COM_RSMEMBERSHIP_ID')),
			JHtml::_('select.option', 't.gateway', 	JText::_('COM_RSMEMBERSHIP_GATEWAY')),
			JHtml::_('select.option', 't.status', 	JText::_('COM_RSMEMBERSHIP_STATUS')),
			JHtml::_('select.option', 't.coupon', 	JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_COUPON')),
			JHtml::_('select.option', 't.price', 	JText::_('COM_RSMEMBERSHIP_PRICE')),
			JHtml::_('select.option', 't.ip', 		JText::_('COM_RSMEMBERSHIP_IP')),
			JHtml::_('select.option', 't.date', 	JText::_('COM_RSMEMBERSHIP_DATE')),
			JHtml::_('select.option', 't.type', 	JText::_('COM_RSMEMBERSHIP_TYPE')),
			JHtml::_('select.option', 'email', 	JText::_('COM_RSMEMBERSHIP_EMAIL'))
		);
		
		// Joomla 2.5
		$options['rightItems'] = array(
			array(
				'input' => '<button class="btn btn-warning" onclick="document.id(\'date_to\').value=\'\';document.id(\'date_from\').value=\'\';this.form.submit();" type="button">'.JText::_('JSEARCH_RESET').'</button>'
			),
			array(
				'input' => '<button class="btn btn-info" type="submit">'.JText::_('COM_RSMEMBERSHIP_FILTER').'</button>'
			),
			array(
				'input' => '<label class="fleft">'.JText::_('COM_RSMEMBERSHIP_TO').'</label>'.JHTML::calendar($options['date_to'], 'date_to', 'date_to')
			),
			array(
				'input' => '<label class="fleft">'.JText::_('COM_RSMEMBERSHIP_FROM').'</label> '.JHTML::calendar($options['date_from'], 'date_from', 'date_from')
			),
			array(
				'input' => '<select name="filter_status" class="inputbox" onchange="this.form.submit()">'."\n"
						   .JHtml::_('select.options', $options['statuses'], 'value', 'text', $options['filter_status'], false)."\n"
						   .'</select>'
			),
			array(
				'input' => '<select name="filter_gateway" class="inputbox" onchange="this.form.submit()">'."\n"
						   .JHtml::_('select.options', $options['gateways'], 'value', 'text', $options['filter_gateway'], false)."\n"
						   .'</select>'
			),
			array(
				'input' => '<select name="filter_type" class="inputbox" onchange="this.form.submit()">'."\n"
						   .JHtml::_('select.options', $options['transaction_types'], 'value', 'text', $options['filter_type'], false)."\n"
						   .'</select>'
			)
		);

		$bar = new RSFilterBar($options);

		return $bar;
	}

	public function getSideBar() 
	{
		require_once JPATH_COMPONENT.'/helpers/toolbar.php';

		// Transaction Types filter
		$options['filter_type'] 	  = $this->getState($this->context.'.filter.filter_type');
		$options['transaction_types'] = array(
			JHtml::_('select.option', '', JText::_('COM_RSMEMBERSHIP_TRANSACTION_ALL_TYPES')),
			JHtml::_('select.option', 'new', JText::_('COM_RSMEMBERSHIP_TRANSACTION_NEW')),
			JHtml::_('select.option', 'renew', JText::_('COM_RSMEMBERSHIP_TRANSACTION_RENEW')),
			JHtml::_('select.option', 'upgrade', JText::_('COM_RSMEMBERSHIP_TRANSACTION_UPGRADE')),
			JHtml::_('select.option', 'addextra', JText::_('COM_RSMEMBERSHIP_TRANSACTION_ADDEXTRA'))
		);

		// Gateway filter
		$options['filter_gateway'] 	= $this->getState($this->context.'.filter.filter_gateway');
		$options['gateways'] 		= array(JHtml::_('select.option', '', JText::_('COM_RSMEMBERSHIP_TRANSACTION_ALL_GATEWAYS')), JHtml::_('select.option', 'No Gateway', JText::_('COM_RSMEMBERSHIP_NO_GATEWAY')));
		$gateways = RSMembership::getPlugins();
		foreach ($gateways as $plugin => $name) 
		{
			if ($name == 'Credit Card') $name = 'Authorize.Net';
			$options['gateways'][] = JHtml::_('select.option', $name, $name);
		}
		// Status filter
		$options['filter_status'] = $this->getState($this->context.'.filter.filter_status');
		$statuses = array('pending', 'completed', 'denied');
		$options['statuses'] = array(JHtml::_('select.option', '', JText::_('COM_RSMEMBERSHIP_TRANSACTION_ALL_STATUSES')));
		foreach ( $statuses as $status ) 
			$options['statuses'][] = JHtml::_('select.option', $status, JText::_('COM_RSMEMBERSHIP_TRANSACTION_STATUS_'.$status));


		RSMembershipToolbarHelper::addFilter(
			JText::_('COM_RSMEMBERSHIP_SELECT_STATUS'),
			'filter_status',
			JHtml::_('select.options', $options['statuses'], 'value', 'text', $options['filter_status'], true)
		);
		RSMembershipToolbarHelper::addFilter(
			JText::_('COM_RSMEMBERSHIP_SELECT_GATEWAY'),
			'filter_gateway',
			JHtml::_('select.options', $options['gateways'], 'value', 'text', $options['filter_gateway'], false)
		);
		RSMembershipToolbarHelper::addFilter(
			JText::_('COM_RSMEMBERSHIP_SELECT_TYPE'),
			'filter_type',
			JHtml::_('select.options', $options['transaction_types'], 'value', 'text', $options['filter_type'], false)
		);
		
		// Custom filters
		
		// Date from
		$options['date_from'] 	= $this->getState($this->context.'.filter.date_from');
		RSMembershipToolbarHelper::addFilter(
			JText::_('COM_RSMEMBERSHIP_FROM'),
			'date_from',
			$options['date_from'],
			'calendar'
		);
		
		// Date to
		$options['date_to'] = $this->getState($this->context.'.filter.date_to');
		RSMembershipToolbarHelper::addFilter(
			JText::_('COM_RSMEMBERSHIP_TO'),
			'date_to',
			$options['date_to'],
			'calendar'
		);
		
		// Calendar buttons
		$options['calendar_btn'] = array ('from_btn'=>'date_from', 'to_btn'=>'date_to', 'filter_desc'=>JText::_('COM_RSMEMBERSHIP_FILTER_DESC'));
		RSMembershipToolbarHelper::addFilter(
			JText::_('COM_RSMEMBERSHIP_FILTER'),
			JText::_('COM_RSMEMBERSHIP_FILTER_DESC'),
			$options['calendar_btn'],
			'calendar_btn'
		);
		
		return RSMembershipToolbarHelper::render();
	}

	function getCache()
	{
		return RSMembershipHelper::getCache();
	}

	function getLog()
	{
		$cid 		 = JFactory::getApplication()->input->get('cid', 0, 'int');
		$transaction = $this->getTable('Transaction','RSMembershipTable');
		$transaction->load($cid);

		return $transaction->response_log; 
	}
}