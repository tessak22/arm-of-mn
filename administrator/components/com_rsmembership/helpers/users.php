<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipUsersHelper
{
	protected static $groups = null;
	protected static $users = null;
	
	public static function getAdminGroups() {
		if (!is_array(self::$groups)) {
			self::$groups = array();
			
			$db 	= JFactory::getDbo();
			$query 	= $db->getQuery(true);
			$query->select($db->qn('id'))
				  ->select($db->qn('lft'))
				  ->select($db->qn('rgt'))
				  ->from($db->qn('#__usergroups'));
			$db->setQuery($query);
			if ($groups = $db->loadObjectList()) {
				$rules = JAccess::getAssetRules(1);
				foreach ($groups as $group) {				
					if ($rules->allow('core.admin', array($group->id))) {
						self::$groups[] = (int) $group->id;
						$children = self::getAdminGroupsChildren($group->lft, $group->rgt);
						foreach($children as $child_id) {
							self::$groups[] = (int) $child_id;
						}
					}
				}
			}
		}
		
		return self::$groups;
	}
	
	public static function getAdminGroupsChildren($lft = null, $rgt = null) {
		
		$children = array();
		
		if (!is_null($lft) && !is_null($rgt)) {
			$db 	= JFactory::getDbo();
			$query 	= $db->getQuery(true);
			
			$query->clear();
			$query->select($db->qn('id'))
				  ->from($db->qn('#__usergroups'))
				  ->where($db->qn('lft').' > '.$db->q($lft))
				  ->where($db->qn('rgt').' < '.$db->q($rgt));
			$db->setQuery($query);
			$groups = $db->loadObjectList();
			foreach ($groups as $group) {
				$children[] = $group->id;
			}
		}
		
		return $children;
	}
	
	public static function getAdminUsers() {
		if (!is_array(self::$users)) {
			self::$users = array();
			
			if ($groups	= self::getAdminGroups()) {
				$ids = array();
				foreach ($groups as $group) {
					$ids = array_merge($ids, JAccess::getUsersByGroup($group, true));
				}
				$ids = array_unique($ids);
				
				if ($ids) {
					$db 	= JFactory::getDbo();
					$query 	= $db->getQuery(true);
					$query->select('u.*')
						  ->from('#__users u')
						  ->where('u.id IN ('.implode(',', $ids).')')
						  ->order('u.username ASC');
					$db->setQuery($query);
					self::$users = $db->loadObjectList();
				}
			}
		}
		
		return self::$users;
	}
}