<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipModelPayments extends JModelList
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) 
			$config['filter_fields'] = array('id', 'name', 'ordering', 'published');

		parent::__construct($config);
	}

	protected function getListQuery()
	{
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);

		$query->
			select('*')->
			from($db->qn('#__rsmembership_payments'));

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

		$this->setState($this->context.'.filter.search', 		 $app->getUserStateFromRequest($this->context.'.payments.search', 'filter_search'));
		$this->setState($this->context.'.filter.filter_state', 	 $app->getUserStateFromRequest($this->context.'.payments.filter_state', 'filter_state'));

		parent::populateState('ordering', 'ASC');
	}

	public function getTable($type = 'Payment', $prefix = 'RSMembershipTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	// get plugin payments and wirepayments
	public function getPayments()
	{
		$payments = parent::getItems();

		$db 	  = JFactory::getDBO();
		$query	  = $db->getQuery(true);
		$plugins  = RSMembership::getPlugins();

		$return = array();
		foreach ($plugins as $paymentplugin => $name)
		{
			if (preg_match('#rsmembershipwire([0-9]+)#', $paymentplugin, $match)) continue;

			$tmp = new stdClass();
			$tmp->name 		  = $name;
			$tmp->limitations = '';

			$className = 'plgSystem'.$paymentplugin;
			if (class_exists($className) && method_exists($className, 'getLimitations'))
			{
				$dispatcher  	  = JDispatcher::getInstance();
				$plugin 	 	  = new $className($dispatcher, array());
				$tmp->limitations = $plugin->getLimitations();
			}

			$query->clear();
			$query->select($db->qn('extension_id'))->from($db->qn('#__extensions'))->where($db->qn('type').' = '.$db->q('plugin').' AND '.$db->qn('client_id').'= '.$db->q('0').' AND '.$db->qn('element').' = '.$db->q($paymentplugin));

			$db->setQuery($query);
			$tmp->cid = $db->loadResult();

			$payments[] = $tmp;
		}

		return $payments;
	}

	function getLimitations()
	{
		$plugins = RSMembership::getPlugins();
		$return = array();
		foreach ($plugins as $paymentplugin => $plugin)	
		{
			$return[$paymentplugin] = '';
		}
		return $return;
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

		$options['orderDir'] = false;
		$bar = new RSFilterBar($options);

		return $bar;
	}

	public function getSideBar() 
	{
		require_once JPATH_COMPONENT.'/helpers/toolbar.php';

		return RSMembershipToolbarHelper::render();
	}
	
}