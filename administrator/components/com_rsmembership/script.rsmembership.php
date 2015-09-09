<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class com_rsmembershipInstallerScript
{
	public function postflight($type, $parent)
	{
		$source 	= $parent->getParent()->getPath('source');
		$installer 	= new JInstaller();
		$db			= JFactory::getDBO();
		$query 		= $db->getQuery(true);

		if ($type == 'install') 
		{
			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_rsmembership/tables');
			
			// insert default data in Fields, RSMembershipTable
			$new_fields = array(
				array('name' => 'address', 	'label' => 'Address', 'type' => 'textbox', 'values' => ''),
				array('name' => 'city', 	'label' => 'City',    'type' => 'textbox', 'values' => ''),
				array('name' => 'state', 	'label' => 'State',   'type' => 'textbox', 'values' => ''),
				array('name' => 'zip', 		'label' => 'ZIP', 	  'type' => 'textbox', 'values' => ''),
				array('name' => 'country', 	'label' => 'Country', 'type' => 'select',  'values' => "//<code>\r\n\$db = JFactory::getDBO();\r\n\$db->setQuery(\"SELECT name FROM #__rsmembership_countries\");\r\nreturn implode(\"\\n\", \$db->loadColumn());\r\n//</code>")
			);

			foreach ( $new_fields as $new_field ) 
			{
				$field = JTable::getInstance('Field', 'RSMembershipTable');
				$field->bind( $new_field );

				$field->required 	= 1;
				$field->published 	= 1;
				$field->ordering 	= $field->getNextOrder();

				if ($field->store()) 
				{
					$db->setQuery("SHOW COLUMNS FROM #__rsmembership_subscribers WHERE `Field` = 'f".$field->id."'");
					if (!$db->loadResult()) 
					{
						$db->setQuery("ALTER TABLE `#__rsmembership_subscribers` ADD `f".$field->id."` VARCHAR( 255 ) NOT NULL");
						$db->query();
					}
				}
			}

			// insert default Wire Payment
			$values = array(
				$db->qn('name') 		=> $db->q('Wire Transfer'),
				$db->qn('details') 		=> $db->q('<p>Please enter your transfer details here.</p>'),
				$db->qn('tax_type') 	=> $db->q(0),
				$db->qn('tax_value') 	=> $db->q(0),
				$db->qn('published')	=> $db->q(1)
			);
			
			$query->clear();
			$query->insert($db->qn('#__rsmembership_payments'))
				  ->columns(array_keys($values))
				  ->values(implode(', ', $values));
			$db->setQuery($query);
			$db->execute();
		}

		if ($type == 'update') 
		{
			$tables = $db->getTableList();

			if ( in_array($db->getPrefix().'rsmembership_users', $tables) ) 
			{
				$db->setQuery('RENAME TABLE '.$db->qn('#__rsmembership_users').' TO '.$db->qn('#__rsmembership_subscribers'));
				$db->execute();
			}
			if ( in_array($db->getPrefix().'rsmembership_membership_users', $tables) ) {
				$query->clear();
				$db->setQuery('RENAME TABLE '.$db->qn('#__rsmembership_membership_users').' TO '.$db->qn('#__rsmembership_membership_subscribers'));
				$db->execute();
			}
			// delete the old Module patch
			require_once JPATH_ADMINISTRATOR.'/components/com_rsmembership/helpers/patches.php';
			jimport('joomla.filesystem.file');

			$module 		= RSMembershipPatchesHelper::getPatchFile('module');
			$module_buffer 	= JFile::read($module);

			if ( strpos($module_buffer, 'RSMembershipHelper') !== false )
			{
				$with = "\$query->where('m.published = 1');";
				$replace = $with."\n"."\t\t"."if (file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_rsmembership'.DS.'helpers'.DS.'rsmembership.php')) {".
								 "\n"."\t\t\t"."include_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_rsmembership'.DS.'helpers'.DS.'rsmembership.php');".
								 "\n"."\t\t\t"."\$rsm_where = RSMembershipHelper::getModulesWhere();".
								 "\n"."\t\t\t"."if (\$rsm_where) \$query->where(\$rsm_where);".
								 "\n"."\t\t"."}".
								 "\n";
				$module_buffer = str_replace($replace, $with, $module_buffer);

				$replace = "\$db->setQuery(\$query);";
				// add the new patch 
				$with =  "\n"."\t\t"."if (file_exists(JPATH_ADMINISTRATOR.'/components/com_rsmembership/helpers/patches.php')) {".
						 "\n"."\t\t\t"."include_once JPATH_ADMINISTRATOR.'/components/com_rsmembership/helpers/patches.php';".
						 "\n"."\t\t\t"."\$rsm_where = RSMembershipPatchesHelper::getModulesWhere();".
						 "\n"."\t\t\t"."if (\$rsm_where) \$query->where(\$rsm_where);".
						 "\n"."\t\t"."}".
						 "\n"."\n"."\t\t".$replace;
				
				$module_buffer = str_replace($replace, $with, $module_buffer);

				if ( !JFile::write($module, $module_buffer) )
					JError::raiseWarning(1, JText::_('COM_RSMEMBERSHIP_REMOVE_OLD_MODULE_PATCHES_ERROR'));
			}

			// delete the old Menu patch
			$menu = RSMembershipPatchesHelper::getPatchFile('menu');
			$menu_buffer = JFile::read($menu);

			if ( strpos($menu_buffer, 'RSMembershipHelper') !== false ) 
			{
				$with 		 = "\$items 		= \$menu->getItems('menutype', \$params->get('menutype'));";
				$replace 	 = $with."\n"."\t\t"."if (file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_rsmembership'.DS.'helpers'.DS.'rsmembership.php')) {".
							 "\n"."\t\t\t"."include_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_rsmembership'.DS.'helpers'.DS.'rsmembership.php');".
							 "\n"."\t\t\t"."RSMembershipHelper::checkMenuShared(\$items);".
							 "\n"."\t\t"."}".
							 "\n";

				$menu_buffer = str_replace($replace, $with, $menu_buffer);

				// add new Menu patch
				$replace 	 = "\$menu->getItems('menutype', \$params->get('menutype'));";
				$with 		= $replace."\n\n"."\t\t\t"."if (file_exists(JPATH_ADMINISTRATOR.'/components/com_rsmembership/helpers/patches.php')) {".
									 "\n"."\t\t\t\t"."include_once JPATH_ADMINISTRATOR.'/components/com_rsmembership/helpers/patches.php';".
									 "\n"."\t\t\t\t"."RSMembershipPatchesHelper::checkMenuShared(\$items);".
									 "\n"."\t\t\t"."}".
									 "\n";
				
				$menu_buffer = str_replace($replace, $with, $menu_buffer);
				
				if ( !JFile::write($menu, $menu_buffer) )
					JError::raiseWarning(1, JText::_('COM_RSMEMBERSHIP_REMOVE_OLD_MENU_PATCHES_ERROR'));
			}

			// parsing sql 
			$sqlfile = JPATH_ADMINISTRATOR.'/components/com_rsmembership/sql/mysql/install.mysql.sql';
			$buffer = file_get_contents($sqlfile);
			if ($buffer === false)
			{
				JError::raiseWarning(1, JText::_('JLIB_INSTALLER_ERROR_SQL_READBUFFER'));
				return false;
			}

			jimport('joomla.installer.helper');
			$queries = JInstallerHelper::splitSql($buffer);
			if (count($queries) == 0) {
				// No queries to process
				return 0;
			}

			// Process each query in the $queries array (split out of sql file).
			foreach ($queries as $sqlquery)
			{
				$sqlquery = trim($sqlquery);
				if ($sqlquery != '' && $sqlquery{0} != '#')
				{
					$db->setQuery($sqlquery);
					if (!$db->query())
					{
						JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));
						return false;
					}
				}
			}
			// converting date from int(11) to datetime

			// transaction
			$transactions_columns = $db->getTableColumns('#__rsmembership_transactions');
			if ( $transactions_columns['date'] == 'int' ) 
			{
				$query->clear();
				$db->setQuery("ALTER TABLE ".$db->qn('#__rsmembership_transactions')." CHANGE ".$db->qn('date')." ".$db->qn('date')." VARCHAR(255) NOT NULL");
				$db->execute();

				// convert the date
				$query->clear();
				$query->update('#__rsmembership_transactions')
					  ->set($db->qn('date')." = IFNULL(CONVERT_TZ(FROM_UNIXTIME(".$db->qn('date')."), @@session.time_zone, 'UTC'), FROM_UNIXTIME(".$db->qn('date')."))");
				$db->setQuery($query);
				$db->execute();

				$query->clear();
				$query->update($db->qn('#__rsmembership_transactions'))->set($db->qn('date').' = '.$db->q('0000-00-00 00:00:00'))->where($db->qn('date').' LIKE '.$db->q('1970-01-01%'));
				$db->setQuery($query);
				$db->execute();
				
				$query->clear();
				$query->update($db->qn('#__rsmembership_transactions'))->set($db->qn('date').' = '.$db->q('0000-00-00 00:00:00'))->where($db->qn('date').' LIKE '.$db->q('1969-12-31%'));
				$db->setQuery($query);
				$db->execute();

				// change the column type
				$db->setQuery("ALTER TABLE ".$db->qn('#__rsmembership_transactions')." CHANGE ".$db->qn('date')." ".$db->qn('date')." DATETIME NOT NULL");
				$db->execute();
			}
			// index on coupon name
			$transactions_columns = $db->getTableColumns('#__rsmembership_transactions', false);
			if ($transactions_columns['coupon']->Key != 'MUL') {
				$db->setQuery('ALTER TABLE '.$db->qn('#__rsmembership_transactions').' ADD INDEX ( '.$db->qn('coupon').' )');
				$db->execute();
			}
			
			// subscribers
			$subscribers_columns = $db->getTableColumns('#__rsmembership_membership_subscribers');
			if ( $subscribers_columns['notified'] == 'tinyint' ) 
			{
				$query->clear(); 
				$db->setQuery("ALTER TABLE ".$db->qn('#__rsmembership_membership_subscribers')." CHANGE ".$db->qn('notified')." ".$db->qn('notified')." DATETIME NOT NULL");
				$db->execute();
			}
			
			// fields
			$fields_columns = $db->getTableColumns('#__rsmembership_fields');
			if ( !isset($fields_columns['showinsubscribers'])) 
			{
				$query->clear();
				$db->setQuery("ALTER TABLE ".$db->qn('#__rsmembership_fields')." ADD ".$db->qn('showinsubscribers')." TINYINT(1) NOT NULL");
				$db->execute();
			}
			
			// memberships
			$memberships_columns = $db->getTableColumns('#__rsmembership_memberships');
			if ( !isset($memberships_columns['admin_email_from_addr'])) 
			{
				$query->clear();
				$db->setQuery("ALTER TABLE ".$db->qn('#__rsmembership_memberships')." ADD ".$db->qn('admin_email_from_addr')." varchar(255) NOT NULL");
				$db->execute();
			}

			// coupons
			$coupons_columns = $db->getTableColumns('#__rsmembership_coupons');
			if ( $coupons_columns['date_added'] == 'int' ) 
			{
				$query->clear();
				$db->setQuery("ALTER TABLE ".$db->qn('#__rsmembership_coupons')." CHANGE ".$db->qn('date_added')." ".$db->qn('date_added')." VARCHAR(255) NOT NULL");
				$db->execute();

				// convert the date
				$query->update('#__rsmembership_coupons')
					  ->set($db->qn('date_added')." = IFNULL(CONVERT_TZ(FROM_UNIXTIME(".$db->qn('date_added')."), @@session.time_zone, 'UTC'), FROM_UNIXTIME(".$db->qn('date_added')."))");
				$db->setQuery($query);
				$db->execute();

				$query->clear();
				$query->update($db->qn('#__rsmembership_coupons'))->set($db->qn('date_added').' = '.$db->q('0000-00-00 00:00:00'))->where($db->qn('date_added').' LIKE '.$db->q('1970-01-01%'));
				$db->setQuery($query);
				$db->execute();
				
				$query->clear();
				$query->update($db->qn('#__rsmembership_coupons'))->set($db->qn('date_added').' = '.$db->q('0000-00-00 00:00:00'))->where($db->qn('date_added').' LIKE '.$db->q('1969-12-31%'));
				$db->setQuery($query);
				$db->execute();

				// change the column type
				$db->setQuery("ALTER TABLE ".$db->qn('#__rsmembership_coupons')." CHANGE ".$db->qn('date_added')." ".$db->qn('date_added')." DATETIME NOT NULL");
				$db->execute();
			}
			
			if ( $coupons_columns['date_start'] == 'int' ) 
			{
				$query->clear();
				$db->setQuery("ALTER TABLE ".$db->qn('#__rsmembership_coupons')." CHANGE ".$db->qn('date_start')." ".$db->qn('date_start')." VARCHAR(255) NOT NULL");
				$db->execute();

				// convert the date
				$query->update('#__rsmembership_coupons')
					  ->set($db->qn('date_start')." = IFNULL(CONVERT_TZ(FROM_UNIXTIME(".$db->qn('date_start')."), @@session.time_zone, 'UTC'), FROM_UNIXTIME(".$db->qn('date_start')."))");
				$db->setQuery($query);
				$db->execute();

				$query->clear();
				$query->update($db->qn('#__rsmembership_coupons'))->set($db->qn('date_start').' = '.$db->q('0000-00-00 00:00:00'))->where($db->qn('date_start').' LIKE '.$db->q('1970-01-01%'));
				$db->setQuery($query);
				$db->execute();
				
				$query->clear();
				$query->update($db->qn('#__rsmembership_coupons'))->set($db->qn('date_start').' = '.$db->q('0000-00-00 00:00:00'))->where($db->qn('date_start').' LIKE '.$db->q('1969-12-31%'));
				$db->setQuery($query);
				$db->execute();

				// change the column type
				$db->setQuery("ALTER TABLE ".$db->qn('#__rsmembership_coupons')." CHANGE ".$db->qn('date_start')." ".$db->qn('date_start')." DATETIME NOT NULL");
				$db->execute();
			}

			if ( $coupons_columns['date_end'] == 'int' ) 
			{
				$query->clear();
				$db->setQuery("ALTER TABLE ".$db->qn('#__rsmembership_coupons')." CHANGE ".$db->qn('date_end')." ".$db->qn('date_end')." VARCHAR(255) NOT NULL");
				$db->execute();

				// convert the date
				$query->update('#__rsmembership_coupons')
					  ->set($db->qn('date_end')." = IFNULL(CONVERT_TZ(FROM_UNIXTIME(".$db->qn('date_end')."), @@session.time_zone, 'UTC'), FROM_UNIXTIME(".$db->qn('date_end')."))");
				$db->setQuery($query);
				$db->execute();

				$query->clear();
				$query->update($db->qn('#__rsmembership_coupons'))->set($db->qn('date_end').' = '.$db->q('0000-00-00 00:00:00'))->where($db->qn('date_end').' LIKE '.$db->q('1970-01-01%'));
				$db->setQuery($query);
				$db->execute();
				
				$query->clear();
				$query->update($db->qn('#__rsmembership_coupons'))->set($db->qn('date_end').' = '.$db->q('0000-00-00 00:00:00'))->where($db->qn('date_end').' LIKE '.$db->q('1969-12-31%'));
				$db->setQuery($query);
				$db->execute();

				// change the column type
				$db->setQuery("ALTER TABLE ".$db->qn('#__rsmembership_coupons')." CHANGE ".$db->qn('date_end')." ".$db->qn('date_end')." DATETIME NOT NULL");
				$db->execute();
			}
			
			// index on coupon name
			$coupons_columns = $db->getTableColumns('#__rsmembership_coupons', false);
			if ($coupons_columns['name']->Key != 'MUL') {
				$db->setQuery('ALTER TABLE '.$db->qn('#__rsmembership_coupons').' ADD INDEX ( '.$db->qn('name').' )');
				$db->execute();
			}

			// logs
			$logs_columns = $db->getTableColumns('#__rsmembership_logs');
			if ( $logs_columns['date'] == 'int' ) 
			{
				$query->clear();
				$db->setQuery("ALTER TABLE ".$db->qn('#__rsmembership_logs')." CHANGE ".$db->qn('date')." ".$db->qn('date')." VARCHAR(255) NOT NULL");
				$db->execute();

				// convert the date
				$query->update('#__rsmembership_logs')
					  ->set($db->qn('date')." = IFNULL(CONVERT_TZ(FROM_UNIXTIME(".$db->qn('date')."), @@session.time_zone, 'UTC'), FROM_UNIXTIME(".$db->qn('date')."))");
				$db->setQuery($query);
				$db->execute();

				$query->clear();
				$query->update($db->qn('#__rsmembership_logs'))->set($db->qn('date').' = '.$db->q('0000-00-00 00:00:00'))->where($db->qn('date').' LIKE '.$db->q('1970-01-01%'));
				$db->setQuery($query);
				$db->execute();
				
				$query->clear();
				$query->update($db->qn('#__rsmembership_logs'))->set($db->qn('date').' = '.$db->q('0000-00-00 00:00:00'))->where($db->qn('date').' LIKE '.$db->q('1969-12-31%'));
				$db->setQuery($query);
				$db->execute();

				// change the column type
				$db->setQuery("ALTER TABLE ".$db->qn('#__rsmembership_logs')." CHANGE ".$db->qn('date')." ".$db->qn('date')." DATETIME NOT NULL");
				$db->execute();
			}
			
			// fields
			$memberships_columns = $db->getTableColumns('#__rsmembership_memberships');
			if ( !isset($memberships_columns['recurring_times'])) 
			{
				$query->clear();
				$db->setQuery("ALTER TABLE ".$db->qn('#__rsmembership_memberships')." ADD ".$db->qn('recurring_times')." INT(11) NOT NULL");
				$db->execute();
			}
			
			// membership_subscribers
			$membership_subscribers_columns = $db->getTableColumns('#__rsmembership_membership_subscribers');
			if ( $membership_subscribers_columns['membership_start'] == 'int' ) 
			{
				$query->clear();
				$db->setQuery("ALTER TABLE ".$db->qn('#__rsmembership_membership_subscribers')." CHANGE ".$db->qn('membership_start')." ".$db->qn('membership_start')." VARCHAR(255) NOT NULL");
				$db->execute();

				// convert the date
				$query->update('#__rsmembership_membership_subscribers')
					  ->set($db->qn('membership_start')." = IFNULL(CONVERT_TZ(FROM_UNIXTIME(".$db->qn('membership_start')."), @@session.time_zone, 'UTC'), FROM_UNIXTIME(".$db->qn('membership_start')."))");
				$db->setQuery($query);
				$db->execute();

				$query->clear();
				$query->update($db->qn('#__rsmembership_membership_subscribers'))->set($db->qn('membership_start').' = '.$db->q('0000-00-00 00:00:00'))->where($db->qn('membership_start').' LIKE '.$db->q('1970-01-01%'));
				$db->setQuery($query);
				$db->execute();
				
				$query->clear();
				$query->update($db->qn('#__rsmembership_membership_subscribers'))->set($db->qn('membership_start').' = '.$db->q('0000-00-00 00:00:00'))->where($db->qn('membership_start').' LIKE '.$db->q('1969-12-31%'));
				$db->setQuery($query);
				$db->execute();

				// change the column type
				$db->setQuery("ALTER TABLE ".$db->qn('#__rsmembership_membership_subscribers')." CHANGE ".$db->qn('membership_start')." ".$db->qn('membership_start')." DATETIME NOT NULL");
				$db->execute();
			}

			if ( $membership_subscribers_columns['membership_end'] == 'int' ) 
			{
				$query->clear();
				$db->setQuery("ALTER TABLE ".$db->qn('#__rsmembership_membership_subscribers')." CHANGE ".$db->qn('membership_end')." ".$db->qn('membership_end')." VARCHAR(255) NOT NULL");
				$db->execute();

				// convert the date
				$query->update('#__rsmembership_membership_subscribers')
					  ->set($db->qn('membership_end')." = IFNULL(CONVERT_TZ(FROM_UNIXTIME(".$db->qn('membership_end')."), @@session.time_zone, 'UTC'), FROM_UNIXTIME(".$db->qn('membership_end')."))");
				$db->setQuery($query);
				$db->execute();

				$query->clear();
				$query->update($db->qn('#__rsmembership_membership_subscribers'))->set($db->qn('membership_end').' = '.$db->q('0000-00-00 00:00:00'))->where($db->qn('membership_end').' LIKE '.$db->q('1970-01-01%'));
				$db->setQuery($query);
				$db->execute();
				
				$query->clear();
				$query->update($db->qn('#__rsmembership_membership_subscribers'))->set($db->qn('membership_end').' = '.$db->q('0000-00-00 00:00:00'))->where($db->qn('membership_end').' LIKE '.$db->q('1969-12-31%'));
				$db->setQuery($query);
				$db->execute();

				// change the column type
				$db->setQuery("ALTER TABLE ".$db->qn('#__rsmembership_membership_subscribers')." CHANGE ".$db->qn('membership_end')." ".$db->qn('membership_end')." DATETIME NOT NULL");
				$db->execute();
			}
			// end converting date from int(11) to datetime
			
			// Logs
			$query = $db->getQuery(true);
			$query->update($db->qn('#__rsmembership_logs'))
				  ->set($db->qn('path').' = CONCAT('.$db->q('[DWN] ').', '.$db->qn('path').')')
				  ->where($db->qn('path').' NOT LIKE '.$db->q('[DWN] %'))
				  ->where($db->qn('path').' NOT LIKE '.$db->q('[URL] %'));
			$db->setQuery($query);
			$db->execute();
		}

		$messages = array(
			'plg_rsmembershipwire' => false,
			'plg_rsmembership' => false
		);
		
		// Install the Wire Payment Plugin
		if ($installer->install($source.'/other/plg_rsmembershipwire')) {
			$query->clear();
			$query->update('#__extensions')
				  ->set($db->qn('enabled').'='.$db->q(1))
				  ->where($db->qn('element').'='.$db->q('rsmembershipwire'))
				  ->where($db->qn('type').'='.$db->q('plugin'))
				  ->where($db->qn('folder').'='.$db->q('system'));
			$db->setQuery($query);
			$db->execute();

			$messages['plg_rsmembershipwire'] = true;
		}

		// Install the System Plugin
		if ($installer->install($source.'/other/plg_rsmembership')) {
			$query->clear();
			$query->update('#__extensions')
				  ->set($db->qn('enabled').'='.$db->q(1))
				  ->where($db->qn('element').'='.$db->q('rsmembership'))
				  ->where($db->qn('type').'='.$db->q('plugin'))
				  ->where($db->qn('folder').'='.$db->q('system'));
			$db->setQuery($query);
			$db->execute();

			$messages['plg_rsmembership'] = true;
		}
		
		$this->showInstallMessage($messages);
	}
	
	protected function showInstallMessage($messages=array()) 
	{
?>
		<style type="text/css">
			.version-history {
				margin: 0 0 2em 0;
				padding: 0;
				list-style-type: none;
			}
			.version-history > li {
				margin: 0 0 0.5em 0;
				padding: 0 0 0 4em;
			}
			.version,
			.version-new,
			.version-fixed,
			.version-upgraded {
				float: left;
				font-size: 0.8em;
				margin-left: -4.9em;
				width: 4.5em;
				color: white;
				text-align: center;
				font-weight: bold;
				text-transform: uppercase;
				-webkit-border-radius: 4px;
				-moz-border-radius: 4px;
				border-radius: 4px;
			}

			.version {
				background: #000;
			}
			.version-new {
				background: #7dc35b;
			}
			.version-fixed {
				background: #e9a130;
			}
			.version-upgraded {
				background: #61b3de;
			}

			.install-ok {
				background: #7dc35b;
				color: #fff;
				padding: 3px;
			}

			.install-not-ok {
				background: #E9452F;
				color: #fff;
				padding: 3px;
			}

			#installer-left {
				float: left;
				width: 230px;
				padding: 5px;
			}

			#installer-right {
				float: left;
			}

			.com-rsmembership-button {
				display: inline-block;
				background: #459300 url(components/com_rsmembership/assets/images/bg-button-green.gif) top left repeat-x !important;
				border: 1px solid #459300 !important;
				padding: 2px;
				color: #fff !important;
				cursor: pointer;
				margin: 0;
				-webkit-border-radius: 5px;
				 -moz-border-radius: 5px;
					  border-radius: 5px;
			}
		</style>
		<div id="installer-left">
			<img src="components/com_rsmembership/assets/images/rsmembership-box.jpg" alt="RSMembership! Box" />
		</div>
		<div id="installer-right">
			<p>RSMembership! System Plugin
				<?php if ($messages['plg_rsmembership']) { ?>
				<b class="install-ok">Installed</b>
				<?php } else { ?>
				<b class="install-not-ok">Error installing!</b>
				<?php } ?>
			</p>
			<p>RSMembership! Wire Payment plugin
				<?php if ($messages['plg_rsmembershipwire']) { ?>
				<b class="install-ok">Installed</b>
				<?php } else { ?>
				<b class="install-not-ok">Error installing!</b>
				<?php } ?>
			</p>
			<h2>Changelog v1.21.10</h2>
			<ul class="version-history">
				<li><span class="version-fixed">Fix</span> The username now can contain UTF-8 characters.</li>
			</ul>
			<a class="com-rsmembership-button" href="index.php?option=com_rsmembership">Start using RSMembership!</a>
			<a class="com-rsmembership-button" href="http://www.rsjoomla.com/support/documentation/view-knowledgebase/74-rsmembership-user-guide.html" target="_blank">Read the RSMembership! User Guide</a>
			<a class="com-rsmembership-button" href="http://www.rsjoomla.com/support.html" target="_blank">Get Support!</a>
		</div>
		<div style="clear: both;"></div>
	<?php
	}
}