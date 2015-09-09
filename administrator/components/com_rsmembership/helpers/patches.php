<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipPatchesHelper
{
	public static function isJ32() {
		return version_compare(JVERSION, '3.2', '>=');
	}

	public static function isJ3() {
		return version_compare(JVERSION, '3.0', '>=');
	}

	public static function checkPatches($type)
	{
		jimport('joomla.filesystem.file');
		
		$file = RSMembershipPatchesHelper::getPatchFile($type);
		
		$buffer = JFile::read($file);

		if (strpos($buffer, 'RSMembershipHelper') !== false || strpos($buffer, 'RSMembershipPatchesHelper') !== false)
			return true;
			
		return false;
	}

	public static function getPatchFile($type)
	{
		if ($type == 'menu') 
		{
			return JPATH_SITE.'/modules/mod_menu/helper.php';
		}
		elseif ($type == 'module')
		{
			if( RSMembershipPatchesHelper::isJ32() ) 
				return JPATH_SITE.'/libraries/cms/module/helper.php';
			elseif ( RSMembershipPatchesHelper::isJ3() ) 
				return JPATH_SITE.'/libraries/legacy/module/helper.php';
			else 
				return JPATH_SITE.'/libraries/joomla/application/module/helper.php';
		}
	}
	
	public static function checkMenuShared(&$rows)
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$user 	= JFactory::getUser();

		$query->select( $db->qn('membership_id') )->from( $db->qn('#__rsmembership_membership_subscribers') )->where($db->qn('user_id').' = '.$db->q($user->get('id')).' AND '.$db->qn('status').' = '.$db->q('0'));
		$db->setQuery($query);
		$memberships = $db->loadColumn();

		$query->clear();
		$query->select($db->qn('extras'))->from($db->qn('#__rsmembership_membership_subscribers'))->where($db->qn('user_id').' = '.$db->q($user->get('id')) .' AND '. $db->qn('status') .' = '. $db->q('0'));
		$db->setQuery($query);
		$extras_array = $db->loadColumn();

		$extras = array();
		if ( is_array($extras_array) ) 
			foreach ( $extras_array as $extra ) 
			{
				if (empty($extra)) continue;

				$extra = explode(',', $extra);
				$extras = array_merge($extras, $extra);
			}
		
		$query->clear();
		$query->select($db->qn('membership_id').', '.$db->qn('params') )->from($db->qn('#__rsmembership_membership_shared'))->where($db->qn('type').' = '.$db->q('menu').' AND '.$db->qn('published').' = '.$db->q('1'));
		$db->setQuery($query);
		$shared = $db->loadObjectList();

		$query->clear();
		$query->select($db->qn('extra_value_id').', '.$db->qn('params'))->from($db->qn('#__rsmembership_extra_value_shared'))->where($db->qn('type').' = '.$db->q('menu').' AND '.$db->qn('published').' = '.$db->q('1'));
		$db->setQuery($query);
		$shared2 = $db->loadObjectList();

		if (!empty($shared2))
			$shared = array_merge($shared, $shared2);

		$allowed = array();
		foreach ($shared as $share)
		{
			$what  = isset($share->membership_id) ? 'membership_id' : 'extra_value_id';
			$where = isset($share->membership_id) ? $memberships : $extras;
			
			if (in_array($share->{$what}, $where))
				$allowed[] = $share->params;
		}

		foreach ($shared as $share)
		{
			$what  = isset($share->membership_id) ? 'membership_id' : 'extra_value_id';
			$where = isset($share->membership_id) ? $memberships : $extras;

			if (!in_array($share->params, $allowed))
			{
				if (is_array($rows))
				foreach ($rows as $i => $row)
					if ($row->id == $share->params)
					{
						unset($rows[$i]);
						break;
					}
			}
		}
	}

	public static function getModulesWhere()
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$user 	= JFactory::getUser();

		$query->select($db->qn('membership_id'))->from($db->qn('#__rsmembership_membership_subscribers'))->where($db->qn('user_id').' = '.$db->q($user->get('id')).' AND '.$db->qn('status').' = '.$db->q('0'));
		$db->setQuery($query);
		$memberships = $db->loadColumn();
			
		$query->clear();
		$query->select($db->qn('extras'))->from($db->qn('#__rsmembership_membership_subscribers'))->where($db->qn('user_id').' = '.$db->q($user->get('id')).' AND '.$db->qn('status').' = '.$db->q('0'));
		$db->setQuery($query);
		$extras_array = $db->loadColumn();


		$extras = array();
		if (is_array($extras_array))
			foreach ($extras_array as $extra)
			{
				if (empty($extra)) continue;
				
				$extra = explode(',', $extra);
				$extras = array_merge($extras, $extra);
			}

		$query->clear();
		$query->select($db->qn('membership_id').', '.$db->qn('params'))->from($db->qn('#__rsmembership_membership_shared'))->where($db->qn('type').' = '.$db->q('module').' AND '.$db->qn('published').' = '.$db->q('1'));
		$db->setQuery($query);
		$shared = $db->loadObjectList();

		if (empty($shared))
			$shared = array();

		$query->clear();
		$query->select($db->qn('extra_value_id').', '.$db->qn('params'))->from($db->qn('#__rsmembership_extra_value_shared'))->where($db->qn('type').' = '.$db->q('module').' AND '.$db->qn('published').' = '.$db->q('1'));
		$db->setQuery($query);
		$shared2 = $db->loadObjectList();

		if (!empty($shared2))
			$shared = array_merge($shared, $shared2);

		$allowed = array();
		$not_allowed = array();
		foreach ($shared as $share)
		{
			$what  = isset($share->membership_id) ? 'membership_id' : 'extra_value_id';
			$where = isset($share->membership_id) ? $memberships : $extras;
			
			if (in_array($share->{$what}, $where))
				$allowed[] = $share->params;
		}
		
		foreach ($shared as $share)
		{
			if (!in_array($share->params, $allowed))
				$not_allowed[] = $share->params;
		}
		
		if ($not_allowed)
			return " m.id NOT IN (".implode(',', $not_allowed).")";
		
		return '';
	}
}