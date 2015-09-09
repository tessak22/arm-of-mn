<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipModelMymembership extends JModelItem
{
	var $_data = null;
	var $_folder = null;
	var $_parents = array();
	var $_extra_parents = array();
	var $_parent = 0;
	
	var $db_files;
	var $terms;
	
	protected $user;
	protected $isWindows;
	
	public function __construct()
	{
		parent::__construct();
		jimport('joomla.filesystem.folder');

		// Some workarounds are needed for Windows
		$this->isWindows = DIRECTORY_SEPARATOR == '\\';
		
		// Get logged in user
		$this->user = JFactory::getUser();
		
		$app 	= JFactory::getApplication();
		$jinput = $app->input;
		
		$db 	= JFactory::getDbo();
		$query	= $db->getQuery(true);

		// Not logged in - must redirect to login.
		if ($this->user->guest) {
			$link = base64_encode((string) JUri::getInstance());
			$app->redirect(JRoute::_('index.php?option=com_users&view=login&return='.$link, false));
		}
		
		// Membership doesn't match - redirect back to My Memberships page.
		if (!$this->_getMembership()) {
			$app = JFactory::getApplication();
			$app->redirect(JRoute::_(RSMembershipRoute::MyMemberships(), false));
		}
		
		$this->getParentFolders();
		$this->getExtraParentFolders();
		
		// Let's see if the membership is active
		if ($this->_data->status > 0) {
			return;
		}
		
		// let's get the path
		$path = $jinput->get('path', '', 'string');
		if (!empty($path)) 
		{
			$path = explode("|", $path);
			// extract the parent folder's id
			$parent_id = (int) $path[0];

			if (empty($parent_id)) 
				$app->redirect(JRoute::_(RSMembershipRoute::MyMemberships(), false));

			// extract the path within the parent
			$path = !empty($path[1]) ? $path[1] : '';

			// check where are we looking
			$from = $this->getFrom();
			if ( $from == 'membership' ) 
				$parent = $this->_parents[$parent_id];
			elseif ( $from == 'extra' ) 
				$parent = $this->_extra_parents[$parent_id];

			// check if the parent is within the allowed parents list
			if (empty($parent)) 
				$app->redirect(JRoute::_(RSMembershipRoute::MyMemberships(), false));

			$this->_parent = $parent_id;

			// compute the full path: parent + path
			$path 	= realpath($parent.'/'.$path);
			$parent = realpath($parent);

			// check if we are trying to access a path that's not within the parent
			if (strpos($path, $parent) !== 0)
				$app->redirect(JRoute::_(RSMembershipRoute::MyMemberships(), false));
			
			// let's see if we've requested a download
			$task = $jinput->get('task', '', 'cmd');

			if ($task == 'download')
			{
				// check if path exists and is a file
				if ( is_file($path) ) 
				{
					// check if we need to agree to terms first
					$query
						->select($db->qn('term_id'))
						->from($db->qn('#__rsmembership_files'))
						->where($db->qn('path').' = '.$db->q($path));
					$db->setQuery($query);
					$term_id = $db->loadResult();

					if ( !empty($term_id) ) 
					{
						$row = JTable::getInstance('Term','RSMembershipTable');
						$row->load($term_id);
						if (!$row->published)
							$term_id = 0;
					}

					$agree = $jinput->get('agree', '', 'string');
					if (!empty($term_id) && empty($agree))
					{
						$this->terms = $row->description;
					}
					else
					{
						@ob_end_clean();
						$filename = basename($path);
						header("Cache-Control: public, must-revalidate");
						header('Cache-Control: pre-check=0, post-check=0, max-age=0');
						header("Pragma: no-cache");
						header("Expires: 0"); 
						header("Content-Description: File Transfer");
						header("Expires: Sat, 01 Jan 2000 01:00:00 GMT");
						if (preg_match('#Opera#', $_SERVER['HTTP_USER_AGENT']))
							header("Content-Type: application/octetstream");
						else
							header("Content-Type: application/octet-stream");

						header("Content-Length: ".(string) filesize($path));
						header('Content-Disposition: attachment; filename="'.$filename.'"');
						header("Content-Transfer-Encoding: binary\n");
						@readfile($path);
						$row 			= JTable::getInstance('Log','RSMembershipTable');
						$row->date 		= JFactory::getDate()->toSql();
						$row->user_id 	= $this->user->id;
						$row->path 		= '[DWN] '.$path;
						$row->ip 		= $_SERVER['REMOTE_ADDR'];
						$row->store();
						exit();
					}
				}
				else
					$app->redirect(JRoute::_(RSMembershipRoute::MyMemberships(), false));
			}
			else 
			{
				// check if the path exists and is a folder
				if ( is_dir($path) ) 
				{
					$this->_folder = $path;
					if ( substr($this->_folder, -1) == '/' ) 
						$this->_folder = substr($this->_folder, 0, -1);
				}
				else 
					$app->redirect(JRoute::_(RSMembershipRoute::MyMemberships(), false));
			}
		}
	}

	protected function setNiceName($path, &$element) {
		static $cache;
		if (!is_array($cache)) {
			$db 	= JFactory::getDbo();
			$query 	= $db->getQuery(true);
			
			$query->select('*')
			  ->from($db->qn('#__rsmembership_files'));
			$db->setQuery($query);
			$cache = $db->loadObjectList('path');
		}
		
		if (!empty($cache[$path])) {
			$found					= &$cache[$path];
			$element->name 			= $found->name;
			$element->description 	= $found->description;
			$element->thumb 		= $found->thumb;
			$element->thumb_w 		= $found->thumb_w;
		}
	}

	protected function _getMembership() {
		$id 	= $this->user->id;
		$cid 	= $this->getCid();
		$db 	= JFactory::getDbo();
		$query	= $db->getQuery(true);
		
		$query
			->select('ms.*')
			->select($db->qn('m.name'))
			->select($db->qn('m.term_id'))
			->select($db->qn('m.no_renew'))
			->select($db->qn('m.use_renewal_price'))
			->select($db->qn('m.renewal_price'))
			->select($db->qn('m.price'))
			->from($db->qn('#__rsmembership_membership_subscribers', 'ms'))
			->join('left', $db->qn('#__rsmembership_memberships', 'm').' ON '.$db->qn('ms.membership_id').' = '.$db->qn('m.id'))
			->where($db->qn('ms.id').' = '.$db->q($cid))
			->where($db->qn('ms.user_id').' = '.$db->q($id))
			->where($db->qn('m.published').' = '.$db->q(1));
		$db->setQuery($query);
		$this->_data = $db->loadObject();
		
		if (!$this->_data) {
			return false;
		}
		
		return true;
	}

	function getBoughtExtras()
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$return = array();

		if (!empty($this->_data->extras))
		{
			$query
				->select($db->qn('id'))
				->select($db->qn('extra_id'))
				->select($db->qn('name'))
				->from($db->qn('#__rsmembership_extra_values'))
				->where($db->qn('id').' IN ('.$this->_data->extras.')')
				->where($db->qn('published') .' = '. $db->q('1'))
				->order($db->qn('extra_id').' ASC, '.$db->qn('ordering').' ASC');
			$db->setQuery($query);
			$extravalues = $db->loadObjectList();

			foreach ( $extravalues as $extravalue ) 
				$return[$extravalue->extra_id][$extravalue->id] = $extravalue->name;
		}

		return $return;
	}

	function getExtras()
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$return = array();

		$query
			->select('e.*')
			->from($db->qn('#__rsmembership_membership_extras', 'me'))
			->join('left', $db->qn('#__rsmembership_extras', 'e').' ON '.$db->qn('me.extra_id') .' = '.$db->qn('e.id'))
			->where($db->qn('me.membership_id').' = '.$db->q($this->_data->membership_id))
			->where($db->qn('e.published').' = '.$db->q('1'));

		$db->setQuery($query);
		$extras = $db->loadObjectList();

		foreach ( $extras as $extra ) 
		{
			$query->clear();
			$query
				->select('*')
				->from($db->qn('#__rsmembership_extra_values'))
				->where($db->qn('extra_id').' = '.$db->q($extra->id))
				->where($db->qn('published').' = '.$db->q('1'))
				->order($db->qn('ordering').' ASC');

			if ( !empty($this->_data->extras) ) 
				$query->where($db->qn('id').' NOT IN ('.$this->_data->extras.')');

			$db->setQuery($query);
			$values 		= $db->loadObjectList();

			if ( !empty($values) ) 
				foreach ( $values as $value ) 
					$value->type 	= $extra->type;

			$return 		= array_merge($return, $values);
		}

		return $return;
	}

	function getUpgrades()
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);

		$query
			->select('u.*')
			->select($db->qn('m.name'))
			->from($db->qn('#__rsmembership_membership_upgrades', 'u'))
			->join('left', $db->qn('#__rsmembership_memberships', 'm').' ON '.$db->qn('u.membership_to_id').' = '.$db->qn('m.id'))
			->where($db->qn('u.membership_from_id').' = '.$db->q($this->_data->membership_id))
			->where($db->qn('m.published').' = '.$db->q('1'))
			->where($db->qn('u.published').' = '.$db->q('1'));
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	function getTerms()
	{
		return $this->terms;
	}

	function getMembership()
	{
		return $this->_data;
	}

	function getMembershipTerms()
	{
		if (!empty($this->_data->term_id))
		{
			$row = JTable::getInstance('Term','RSMembershipTable');
			$row->load($this->_data->term_id);
			if (!$row->published)
				return false;

			return $row;
		}

		return false;
	}

	function getCid()
	{
		return JFactory::getApplication()->input->get('cid', 0, 'int');
	}

	function getFrom()
	{
		return JFactory::getApplication()->input->get('from', 'membership', 'word');
	}

	function getParentFolders()
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);

		// let's see if the membership is active
		if ($this->_data->status > 0)
			return $this->_parents;

		$query
			->select($db->qn('id'))
			->select($db->qn('params', 'path'))
			->from($db->qn('#__rsmembership_membership_shared'))
			->where($db->qn('membership_id').' = '.$db->q($this->_data->membership_id))
			->where($db->qn('type').' = '.$db->q('folder'))
			->where($db->qn('published').' = '.$db->q('1'))
			->order($db->qn('ordering').' ASC');
		$db->setQuery($query);

		$parents = $db->loadObjectList();
		foreach ($parents as $parent)
			$this->_parents[$parent->id] = $this->cleanPath($parent->path);
		
		return $this->_parents;
	}

	function getExtraParentFolders()
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);

		// let's see if the membership is active
		if ($this->_data->status > 0)
			return $this->_extra_parents;

		if (empty($this->_data->extras)) 
			return $this->_extra_parents;


		$extras = explode(',', $this->_data->extras);

		$query
			->select($db->qn('id'))
			->select($db->qn('params'))
			->from($db->qn('#__rsmembership_extra_value_shared'))
			->where($db->qn('extra_value_id').' IN (\''.implode($db->q(','), $extras).'\')')
			->where($db->qn('type').' = '.$db->q('folder'))
			->where($db->qn('published').' = '.$db->q('1'))
			->order($db->qn('ordering').' ASC');
		$db->setQuery($query);

		$parents = $db->loadObjectList();

		foreach ( $parents as $parent ) 
			$this->_extra_parents[$parent->id] = $this->cleanPath($parent->params);

		return $this->_extra_parents;
	}

	protected function cleanPath($path) {
		$path = realpath($path);
		$path = rtrim($path, '\\/');
		
		if ($this->isWindows) {
			$path = str_replace('\\', '/', $path);
		}
		
		return $path;
	}
	
	public function getFolders()
	{
		$folders 		= array();
		$all_folders 	= array();
		
		// let's see if the membership is active
		if ($this->_data->status > 0)
			return $folders;
		
		// Check if we are not browsing a folder
		if (is_null($this->_folder)) {
			// Show all the folders associated with this membership
			foreach ($this->_parents as $folder) {
				$all_folders[] = (object) array(
					'name' => $folder,
					'from' => 'membership'
				);
			}
			
			// Show all the folders associated with the extra values of this membership
			foreach ($this->_extra_parents as $folder) {
				$all_folders[] = (object) array(
					'name' => $folder,
					'from' => 'extra'
				);
			}
				
			// We don't need a parent since we have the full path in the database
			$parent = '';
		} else {
			// Show the folders in the current folder
			$subfolders = JFolder::folders($this->_folder);
			$from		= $this->getFrom();
			foreach ($subfolders as $folder) {
				$all_folders[] = (object) array(
					'name' => $folder,
					'from' => $from
				);
			}
			
			// We need the parent to be set as the current folder
			$parent = $this->_folder.'/';
		}
		
		// prepare our folders
		foreach ($all_folders as $folder) {
			// Membership or extra ?
			$from 	= $folder->from;
			// Get the folder's name
			$folder = $parent.$folder->name;
			// Clean it
			$folder = $this->cleanPath($folder);
			// Set folder name as default
			$name = strrchr($folder, '/');
			if ($name) {
				$name = ltrim($name, '/');
			} else {
				$name = $folder;
			}
			
			$element = (object) array(
				'from' => $from,
				'name' => $name,
				'description',
				'thumb',
				'thumb_w',
				'fullpath'
			);
			
			// Try to find the element name from the db
			// It's a folder so we need to append a slash
			$this->setNiceName($folder.'/', $element);
			
			// Select the array, defaults to memberships.
			$parents = $from == 'extra' ? $this->_extra_parents : $this->_parents;

			// Let's see if we are browsing the parent
			$pos = array_search($folder, $parents);
			if ($pos !== false) {
				// We are listing the available shared folders so we need the id of the parent as the path
				$element->fullpath = $pos;
			} else {
				// We are browsing through the parent so we need the subpath along with the id of the parent
				$element->fullpath = $this->_parent.'|'.substr_replace($folder, '', 0, strlen($parents[$this->_parent]) + 1);
			}
			
			$folders[] = $element;
		}
		
		return $folders;
	}
	
	function getFiles()
	{
		$files = array();
		
		// let's see if the membership is active
		if ($this->_data->status > 0) 
			return $files;

		if (!is_null($this->_folder)) {
			$all_files = JFolder::files($this->_folder);
			$folder	   = $this->cleanPath($this->_folder);
			$from	   = $this->getFrom();
			
			foreach ($all_files as $file) {
				$element = (object) array(
					'from' => $from,
					'name' => $file,
					'description',
					'thumb',
					'thumb_w',
					'fullpath',
					'published' => 1
				);
				
				// Try to find the element name from the db
				$this->setNiceName($folder.'/'.$file, $element);
				
				// Select the array, defaults to memberships.
				$parents = $from == 'extra' ? $this->_extra_parents : $this->_parents;

				$element->fullpath = $this->_parent.'|'.substr_replace($folder.'/'.$file, '', 0, strlen($parents[$this->_parent]) + 1);
				
				$files[] = $element;
			}
		}
		
		return $files;
	}
	
	public function getCurrent() {
		return $this->_folder;
	}

	public function getPrevious() 
	{
		$from 		= $this->getFrom();
		$parents 	= $from == 'extra' ? $this->_extra_parents : $this->_parents;
		
		if (in_array($this->cleanPath($this->_folder), $parents)) { 
			return '';
		}

		if (!empty($this->_parent)) {
			$parts = explode('/', $this->cleanPath($this->_folder));
			if (count($parts) > 1) {
				array_pop($parts);
			}
			
			$folder = implode('/', $parts);
			$folder = substr_replace($folder, '', 0, strlen($parents[$this->_parent]) + 1);

			return $this->_parent.'|'.$folder;
		}

		return false;
	}

	public function cancel() {
		$db 	= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$id  	= $this->user->id;
		$cid 	= $this->getCid();

		$transaction = JTable::getInstance('Transaction', 'RSMembershiptable');
		$transaction->load($this->_data->from_transaction_id);
		
		$membership  = JTable::getInstance('Membership', 'RSMembershiptable');
		$membership->load($this->_data->membership_id);

		$plugins = RSMembership::getPlugins();
		
		// Keep a legacy mode for Authorize.net
		if (in_array($transaction->gateway, $plugins) || $transaction->gateway == 'Authorize.Net') {
			$plugin = array_search($transaction->gateway, $plugins);
			if ($plugin === false) {
				$plugin = 'rsmembershipauthorize';
			}
			
			$args = array(
				'plugin' 		=> $plugin,
				'data' 			=> &$this->_data,
				'membership' 	=> $membership,
				'transaction' 	=> &$transaction
			);
			JFactory::getApplication()->triggerEvent('onMembershipCancelPayment', $args);
		}

		$query->clear();
		$query
			->update($db->qn('#__rsmembership_membership_subscribers'))
			->set($db->qn('status').' = '.$db->q('3'))
			->where($db->qn('id').' = '.$db->q($cid));
		$db->setQuery($query);
		$db->execute();

		if (!is_array($membership->gid_expire)) 
			$membership->gid_expire = explode(',', $membership->gid_expire);

		if ( $membership->gid_enable ) {
			RSMembership::updateGid($id, $membership->gid_expire, false, 'remove');
		}

		if ($membership->disable_expired_account)
		{
			list($memberships, $extras) = RSMembershipHelper::getUserSubscriptions($id);
				if (!$memberships) {
					RSMembership::disableUser($id);
					$app = JFactory::getApplication();
					$app->logout();
				}
		}
	}
}