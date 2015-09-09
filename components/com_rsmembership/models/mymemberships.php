<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class RSMembershipModelMymemberships extends JModelList
{
	public $_context = 'com_rsmembership.mymemberships';

	public function __construct($config = array())
	{
		parent::__construct($config);

		$user = JFactory::getUser();
		if ( $user->get('guest') ) 
		{
			$link 		 = JURI::getInstance();
			$link 		 = base64_encode($link);
			$user_option = 'com_users';

			JFactory::getApplication()->redirect('index.php?option='.$user_option.'&view=login&return='.$link);
		}
	}

	public function getTable($type = 'Membership', $prefix = 'RSMembershipTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	protected function getListQuery()
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$user 	= JFactory::getUser();
		$cid 	= $user->get('id');

		$query
			->select('u.*, '.$db->qn('m.name'))
			->from($db->qn('#__rsmembership_membership_subscribers', 'u'))
			->join('left', $db->qn('#__rsmembership_memberships', 'm').' ON '.$db->qn('u.membership_id').' = '.$db->qn('m.id'))
			->where($db->qn('user_id').' = '.$db->q($cid))
			->where($db->qn('m.published').' = '.$db->q('1'))
			->where($db->qn('u.published').' = '.$db->q('1'));

		return $query;
	}

	function getTransactions() 
	{
		$user 	= JFactory::getUser();
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);

		$query
			->select('*')
			->from($db->qn('#__rsmembership_transactions'))
			->where($db->qn('user_id').' = '.$db->q($user->get('id')))
			->where($db->qn('status').' = '.$db->q('pending'))
			->order($db->qn('date').' DESC');

		return $this->_getList($query);
	}
}