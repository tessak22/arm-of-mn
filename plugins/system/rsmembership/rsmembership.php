<?php
/**
* @package RSMembership!
* @copyright (C) 2009-2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.plugin.plugin');

class plgSystemRSMembership extends JPlugin
{
	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
		
		if (file_exists(JPATH_ADMINISTRATOR.'/components/com_rsmembership/helpers/rsmembership.php')) {
			require_once JPATH_ADMINISTRATOR.'/components/com_rsmembership/helpers/rsmembership.php';
		}
	}
	
	public function onAfterInitialise() {
		if (!class_exists('RSMembershipHelper')) {
			return;
		}

		$this->loadLanguage('plg_system_rsmembership');

		$this->updateMemberships();
		$this->sendExpirationEmails();
	}
	
	protected function updateMemberships() {
		$db 		= JFactory::getDbo();
		$query		= $db->getQuery(true);
		$config   	= RSMembershipConfig::getInstance();
		
		$date		= JFactory::getDate();
		$unixDate 	= $date->toUnix();
		if ( ( $config->get('last_check') + ($config->get('interval') * 60) ) > $unixDate )
			return;
		
		// update config value Last Check
		$config->set('last_check', $unixDate);
		
		$offset = $config->get('delete_pending_after');
		if ($offset < 1) $offset = 1;
		$offset = $offset * 3600;

		// delete pending transactions
		$date->modify("-$offset seconds");
		$query->delete()
			  ->from($db->qn('#__rsmembership_transactions'))
			  ->where($db->qn('status').' = '.$db->q('pending'))
			  ->where($db->qn('date') . ' < '. $db->q($date->toSql()));
		$db->setQuery($query);
		$db->execute();
		$query->clear();

		// Limit 10 so we don't overload the server
		$query->select($db->qn('mu.id'))
			  ->select($db->qn('m.gid_enable'))
			  ->select($db->qn('m.gid_expire'))
			  ->select($db->qn('m.disable_expired_account'))
			  ->select($db->qn('mu.user_id'))
			  ->from( $db->qn('#__rsmembership_membership_subscribers', 'mu') )
			  ->join( 'left', $db->qn('#__rsmembership_memberships', 'm') .' ON '. $db->qn('mu.membership_id').' = '. $db->qn('m.id'))
			  ->where($db->qn('mu.status').' = '.$db->q('0'))
			  ->where($db->qn('mu.membership_end').' != '.$db->q($db->getNullDate()))
			  ->where($db->qn('mu.membership_end').' < '.$db->q(JFactory::getDate()->toSql()));
		$db->setQuery( $query, 0, 10 );
		$updates 	= $db->loadObjectList('id');
		$query->clear();
		$to_update 	= array_keys($updates);

		if (!empty($to_update))
		{
			$query->update($db->qn('#__rsmembership_membership_subscribers'))
				  ->set($db->qn('status').' = '. $db->q(MEMBERSHIP_STATUS_EXPIRED))
				  ->where($db->qn('id') . ' IN (\''.implode($db->q(','), $to_update).'\')');
			$db->setQuery($query);
			$db->execute();
			$query->clear();
		}
		
		foreach ( $updates as $update ) 
		{
			if ($update->gid_enable)
				RSMembership::updateGid($update->user_id, $update->gid_expire, false, 'remove');
			if ($update->disable_expired_account) {
				// Do not disable the user if he has active subscriptions.
				list($memberships, $extras) = RSMembershipHelper::getUserSubscriptions($update->user_id);
				if (!$memberships) {
					RSMembership::disableUser($update->user_id);
				}
			}
		}
	}
	
	protected function sendExpirationEmails() 
	{
		$db 	= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$date 	= JFactory::getDate();
		$config = RSMembershipConfig::getInstance();

		// Check the last time this has been run
		$now = $date->toUnix();
		if ($now < $config->get('expire_last_run') + $config->get('expire_check_in')*60) 
			return;

		// update config value for last time the expiration emails were sent
		$config->set('expire_last_run', $now);

		// Get expiration intervals and memberships
		// Performance check - if no emails can be sent, no need to grab the membership
		$query->select('*')
			  ->from($db->qn('#__rsmembership_memberships'))
			  ->where('('.$db->qn('user_email_from_addr').' != '.$db->q('').' OR '.$db->qn('user_email_use_global').' = '.$db->q(1).')')
			  ->where($db->qn('published').' = '.$db->q(1));
		$db->setQuery($query);
		$memberships = $db->loadObjectList();
		
		if ($memberships) {
			RSMembership::sendNotifications($memberships, null, false);
		}
	}

	public function onAfterDispatch()
	{
		if (!class_exists('RSMembershipHelper') || !RSMembershipHelper::getConfig('disable_registration')) {
			return;
		}

		$jinput	= JFactory::getApplication()->input;
		$option = $jinput->get('option', '', 'cmd');
		$view 	= $jinput->get('view',   '', 'cmd');
		$task 	= $jinput->get('task',   '', 'cmd');

		if ($option == 'com_users' && ($task == 'registration.register' || $view == 'registration')) {
			$url 		= JRoute::_('index.php?option=com_rsmembership', false);
			$custom_url = RSMembershipHelper::getConfig('registration_page');
			if (!empty($custom_url))
				$url = $custom_url;
			
			$app = JFactory::getApplication();
			$app->redirect($url);
		}
	}

	public function onAfterRoute()
	{
		if (class_exists('RSMembershipHelper')) {
			RSMembershipHelper::checkShared();
		}
	}

	public function onAfterRender() 
	{
		$app 	= JFactory::getApplication();
		$db 	= JFactory::getDbo();

		if ($app->isAdmin() || !class_exists('RSMembershipHelper')) {
			return;
		}

		$body = JResponse::getBody();
		if (strpos($body, '{rsmembership-subscribe') !== false) {
			$pattern = '#\{rsmembership-subscribe ([0-9]+)\}#i';
			if (preg_match_all($pattern, $body, $matches)) {
				$find 		= array();
				$replace 	= array();
				foreach ($matches[1] as $i => $membership_id) {
					$membership_id = (int) $membership_id;
					$query	= $db->getQuery(true);
					$query
						->select( $db->qn('id') . ', ' . $db->qn('name') )
						->from($db->qn('#__rsmembership_memberships'))
						->where($db->qn('published').' = '.$db->q(1))
						->where($db->qn('id').' = '.$db->q($membership_id));
					$db->setQuery($query);
					if ($membership = $db->loadObject()) {
						$find[]    = $matches[0][$i];
						$replace[] = JRoute::_('index.php?option=com_rsmembership&task=subscribe&cid='.$membership_id.':'.JFilterOutput::stringURLSafe($membership->name));
					}
				}

				$body = str_replace($find, $replace, $body);
				JResponse::setBody($body);
			}
		}
		
		if (strpos($body, '{rsmembership ') !== false) {
			$pattern = '#\{rsmembership (id|category)="([0-9,\s]+)"\}(.*?){/rsmembership}#is';
			if (preg_match_all($pattern, $body, $matches)) {
				$find 		= array();
				$replace	= array();
				
				// Get current user's memberships and extras
				list($userMemberships, $userExtras) = RSMembershipHelper::getUserSubscriptions();
				
				foreach ($matches[0] as $i => $fullmatch) {
					$type 	= strtolower($matches[1][$i]);
					$values	= explode(',', $matches[2][$i]);
					$inside = $matches[3][$i];
					$find[] = $fullmatch;
					
					// Make sure we have only numbers.
					JArrayHelper::toInteger($values);
					
					// Two argument types: either membership IDs or category IDs
					if ($type == 'id') {
						$sharedMemberships = $values;
					} elseif ($type == 'category') {
						$query = $db->getQuery(true);
						$query->select('id')
							  ->from($db->qn('#__rsmembership_memberships'))
							  ->where($db->qn('category_id').' IN ('.implode(',', $values).')');
						$sharedMemberships = $db->setQuery($query)->loadColumn();
					}
					
					// Do we have an {else} statement?
					if (strpos($inside, '{else}') !== false) {
						list($inside, $other) = explode('{else}', $inside, 2);
					} else {
						$other = '';
					}
					
					// Does the user have the required memberships?
					if (array_intersect($sharedMemberships, $userMemberships)) {
						$replace[] = $inside;
					} else {
						$replace[] = $other;
					}
				}
				
				$body = str_replace($find, $replace, $body);
				JResponse::setBody($body);
			}
		}
	}

	public function onCreateModuleQuery(&$extra)
	{
		if (class_exists('RSMembershipHelper'))
			if (is_array($extra->where))
			{
				$where = RSMembershipHelper::getModulesWhere();
				if ($where)
					$extra->where[] = $where;
			}
			else
				$extra->where .= RSMembershipHelper::getModulesWhere();
	}
	
	public function onUserAfterDelete($user, $succes, $msg)
	{
		if (!$succes) 
			return false;

		$db 	= JFactory::getDbo();
		$query  = $db->getQuery(true);

		// delete from transactions
		$query->delete($db->qn('#__rsmembership_transactions'))->where($db->qn('user_id').' = '. $db->q( (int) $user['id'] ));
		$db->setQuery($query);
		$db->execute();
		$query->clear();

		// delete from subscribers
		$query->delete($db->qn('#__rsmembership_subscribers'))->where($db->qn('user_id').' = '. $db->q( (int) $user['id'] ));
		$db->setQuery($query);
		$db->execute();
		$query->clear();

		// delete from membership_subscribers
		$query->delete($db->qn('#__rsmembership_membership_subscribers'))->where($db->qn('user_id').' = '. $db->q( (int) $user['id'] ));
		$db->setQuery($query);
		$db->execute();
		$query->clear();

		// delete from logs
		$query->delete($db->qn('#__rsmembership_logs'))->where($db->qn('user_id').' = '. $db->q( (int) $user['id'] ));
		$db->setQuery($query);
		$db->execute();
		$query->clear();

		return true;
	}
}