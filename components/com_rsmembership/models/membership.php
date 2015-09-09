<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class RSMembershipModelMembership extends JModelItem
{
	function __construct()
	{
		parent::__construct();
	}

	public function getItem($pk = null)
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$cid	= JFactory::getApplication()->input->get('cid', 0, 'int');

		$item 	= $this->getTable('Membership', 'RSMembershipTable');
		$item->load($cid);

		$query->select($db->qn('name'))->from($db->qn('#__rsmembership_categories'))->where($db->qn('id').' = '.$db->q($item->category_id));
		$db->setQuery($query);
		$item->category_name = $db->loadResult();

		if ( $item->use_trial_period ) 
			$item->price = $item->trial_price;

		// disable buy button and out of stock warning
		if ($item->stock == -1) 
		{
			JError::raiseWarning(500, JText::_('COM_RSMEMBERSHIP_OUT_OF_STOCK'));
		}

		return $item;
	}
}