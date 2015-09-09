<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class RSMembershipModelMembership extends JModelAdmin
{
	protected $text_prefix = 'COM_RSMEMBERSHIP';

	protected $attachments 		   	 = array();
	protected $attachmentsPagination = null;

	public function getTable($type = 'Membership', $prefix = 'RSMembershipTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_rsmembership.membership', 'membership', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
			return false;

		return $form;
	}

	protected function loadFormData() 
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_rsmembership.edit.membership.data', '');

		if (empty($data)) 
			$data = $this->getItem();

		return $data;
	}

	public function getItem($pk = null) 
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$item 	= parent::getItem($pk);

		$item->period_values[] = $item->period_type;
		$item->period_values[] = $item->period;

		$item->trial_period_values[] = $item->trial_period_type;
		$item->trial_period_values[] = $item->trial_period;

		$item->fixed_expiry_values[] = $item->fixed_day;
		$item->fixed_expiry_values[] = $item->fixed_month;
		$item->fixed_expiry_values[] = $item->fixed_year;
		$item->fixed_expiry_values[] = $item->fixed_expiry;

		$item->thumb_resize = $item->thumb_w;
		
		$query->select($db->qn('extra_id'))->from($db->qn('#__rsmembership_membership_extras'))->where($db->qn('membership_id').' = '.$db->q($item->id));
		$db->setQuery($query);
		$item->extras = $db->loadColumn();
		
		$instances 	  = RSMembership::getSharedContentPlugins();
		$query->clear();
		$query->select('*')->from($db->qn('#__rsmembership_membership_shared'))->where($db->qn('membership_id').' = '.$db->q($item->id))->order($db->qn('ordering').' ASC');
		$db->setQuery($query);
		$item->shared = $db->loadObjectList();

		foreach ($item->shared as $s => $shared)
			switch ($shared->type)
			{
				default:
					foreach ($instances as $instance) 
						if (method_exists($instance, 'showUserFriendlyParams')) 
							$instance->showUserFriendlyParams($shared);

					$item->shared[$s] = $shared;
				break;

				case 'article':
					$query->clear();
					$query->select('title')->from($db->qn('#__content'))->where($db->qn('id').' = '.$db->q($shared->params));
					$db->setQuery($query);
					$item->shared[$s]->params = $db->loadResult();
				break;

				case 'category':
					$query->clear();
					$query->select('title')->from($db->qn('#__categories'))->where($db->qn('id').' = '.$db->q($shared->params));
					$db->setQuery($query);
					$item->shared[$s]->params = $db->loadResult();
				break;

				case 'module':
					$query->clear();
					$query->select($db->qn('title').', '.$db->qn('module'))->from($db->qn('#__modules'))->where($db->qn('id').' = '.$db->q($shared->params));
					$db->execute();
					$module = $db->loadObject();
					$item->shared[$s]->params = '('.$module->module.') '.$module->title;
				break;

				case 'menu':
					$query->clear();
					$query->select($db->qn('title','name').', '.$db->qn('menutype'))->from($db->qn('#__menu'))->where($db->qn('id').' = '.$db->q($shared->params));
					$db->setQuery($query);
					$menu = $db->loadObject();
					$item->shared[$s]->params = '('.$menu->menutype.') '.$menu->name;
				break;
			}

		jimport('joomla.html.pagination');
		$this->sharedPagination = new JPagination(count($item->shared), 0, 0);

		// attachments
		$query->clear();
		$query->select('*')->from($db->qn('#__rsmembership_membership_attachments'))->where($db->qn('membership_id').' = '.$db->q($item->id))->order($db->qn('ordering').' ASC');
		$db->setQuery($query);
		$attachments = $db->loadObjectList();

		$this->attachments = array();
		$this->attachmentsPagination = null;

		foreach ($attachments as $attachment)
			$this->attachments[$attachment->email_type][] = $attachment;
		foreach ($this->attachments as $email_type => $attachments)
			$this->attachmentsPagination[$email_type] = new JPagination(count($attachments), 0, 0);

		return $item;
	}
	
	function getAttachments()
	{
		return $this->attachments;
	}
	
	function getAttachmentsPagination()
	{
		return $this->attachmentsPagination;
	}

	public function save($data) 
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$post 	= JFactory::getApplication()->input->get('jform', array(), 'array');

		if ( isset($post['period_values']) ) {
			$data['period_type'] = $post['period_values'][0]; 
			$data['period'] 	 = $post['period_values'][1]; 
		}

		if ( isset($post['trial_period_values']) ) {
			$data['trial_period_type']  = $post['trial_period_values'][0]; 
			$data['trial_period'] 		= $post['trial_period_values'][1]; 
		}

		if ( isset($post['fixed_expiry_values'][3]) ) {
			$data['fixed_day'] 	  = $post['fixed_expiry_values'][0];
			$data['fixed_month']  = $post['fixed_expiry_values'][1];
			$data['fixed_year']   = $post['fixed_expiry_values'][2];
			$data['fixed_expiry'] = $post['fixed_expiry_values'][3];
		}
		else {
			$data['fixed_day'] 	  = 0;
			$data['fixed_month']  = 0;
			$data['fixed_year']   = 0;
			$data['fixed_expiry'] = 0;
		}	

		if ( isset($post['thumb_delete']) )
			$data['thumb'] = '';

		$data['thumb_w'] = (int) $post['thumb_w'];
		if ($data['thumb_w'] <= 0)
			$data['thumb_w'] = 48;

		if ( parent::save($data) ) 
		{
			$data['id'] = $this->getState($this->getName() . '.id');

			// Trigger event
			JFactory::getApplication()->triggerEvent('rsm_onMembershipSave', array(&$data));

			// Process the thumbnail
			$files = JFactory::getApplication()->input->files->get('jform');
			$thumb = $files['thumb'];
			jimport('joomla.filesystem.file');

			$thumb['db_name'] = JPATH_ROOT.'/components/com_rsmembership/assets/thumbs/'.$data['id'];

			// Delete it if requested
			if ( isset($post['thumb_delete']) ) 
			{
				JFile::delete($thumb['db_name'].'.jpg');
				$query->clear();
				$query->update($db->qn('#__rsmembership_memberships'))->set($db->qn('thumb').' = '.$db->q(''))->where($db->qn('id').' = '.$data['id']);
				$db->setQuery($query);
				$db->execute();
			}

			// Add the thumbnail if uploaded
			if (!$thumb['error'] && !empty($thumb['tmp_name']))
			{
				// Resize the thumb if requested
				if (isset($post['thumb_resize'])) 
					$success = RSMembershipHelper::createThumb($thumb['tmp_name'], $thumb['db_name'], $data['thumb_w']);
				else
					$success = JFile::upload($thumb['tmp_name'], $thumb['db_name'].'.jpg');

				// Add to database only if upload successful
				if ($success)
				{
					$query->clear();
					$query->update($db->qn('#__rsmembership_memberships'))->set($db->qn('thumb').' = '.$db->q(JFile::getName($thumb['db_name'].'.jpg')))->where($db->qn('id').' = '.$data['id']);
					$db->setQuery($query);
					$db->execute();
				}
			}

			// Process the extras
			$extras = $data['extras'];
			JArrayHelper::toInteger($extras, array(0));

			$query->clear();
			$query->delete()->from($db->qn('#__rsmembership_membership_extras'))->where($db->qn('membership_id').' = '.$db->q($data['id']));
			$db->setQuery($query);
			$db->execute();

			foreach ($extras as $extra)
			{
				if (empty($extra)) continue;
				
				$query->clear();
				$query->insert($db->qn('#__rsmembership_membership_extras'))->columns(array('membership_id','extra_id'))->values($db->q($data['id']).', '.$db->q($extra));
				$db->setQuery($query);
				$db->execute();
			}

			return true;

		} else 
		{
			JError::raiseWarning(500, $this->getError());
			return false;
		}
	}

	public function delete(&$cids)
	{
		$db		 = JFactory::getDBO();
		$query	 = $db->getQuery(true);
		$in_cids = "'".implode($db->q(','), $cids)."'";

		jimport('joomla.filesystem.file');
		foreach ( $cids as $cid ) 
			if ( JFile::exists( JPATH_ROOT.'/components/com_rsmembership/assets/thumbs/'.$cid.'.jpg' ) ) 
				JFile::delete( JPATH_ROOT.'/components/com_rsmembership/assets/thumbs/'.$cid.'.jpg' );

		// delete memberships
		$query->delete()->from($db->qn('#__rsmembership_memberships'))->where($db->qn('id').' IN ('.$in_cids.')');
		$db->setQuery($query);
		$db->execute();

		$query->clear();
		$query->delete()->from($db->qn('#__rsmembership_membership_attachments'))->where($db->qn('membership_id').' IN ('.$in_cids.')');
		$db->setQuery($query);
		$db->execute();

		$query->clear();
		$query->delete()->from($db->qn('#__rsmembership_membership_extras'))->where($db->qn('membership_id').' IN ('.$in_cids.')');
		$db->setQuery($query);
		$db->execute();

		$query->clear();
		$query->delete()->from($db->qn('#__rsmembership_membership_shared'))->where($db->qn('membership_id').' IN ('.$in_cids.')');
		$db->setQuery($query);
		$db->execute();

		$query->clear();
		$query->delete()->from($db->qn('#__rsmembership_membership_subscribers'))->where($db->qn('membership_id').' IN ('.$in_cids.')');
		$db->setQuery($query);
		$db->execute();

		return true;
	}

	public function getRSFieldset() 
	{
		require_once JPATH_COMPONENT.'/helpers/adapters/fieldset.php';

		$fieldset = new RSFieldset();

		return $fieldset;
	}

	public function getRSTabs() 
	{
		require_once JPATH_COMPONENT.'/helpers/adapters/tabs.php';

		$tabs = new RSTabs('com-rsmembership-membership');
		return $tabs;
	}

	public function getRSAccordion() 
	{
		require_once JPATH_COMPONENT.'/helpers/adapters/accordion.php';

		$accordion = new RSAccordion('com-rsmembership-accordion-membership');
		return $accordion;
	}

	/**
	 * Folder Tasks
	 */
	
	// Folder - Publish
	public function foldersPublish($cid=array(), $publish=1)
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);

		if (!is_array($cid) || count($cid) > 0)
		{
			$publish = (int) $publish;
			$cids 	 = implode(',', $cid);

			$query->update($db->qn('#__rsmembership_membership_shared'))->set($db->qn('published').' = '.$db->q($publish))->where($db->qn('id').' IN (\''.$cids.'\')');
			$db->setQuery($query);

			if (!$db->execute())
			{
				$this->setError($db->getErrorMsg());
				return false;
			}
		}

		return $cid;
	}

	// Folder - Remove
	public function foldersRemove($cids) 
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$cids 	= implode(',', $cids);

		$query->delete()->from($db->qn('#__rsmembership_membership_shared'))->where($db->qn('id').' IN (\''.$cids.'\')');
		$db->setQuery($query);
		$db->execute();

		return true;
	}

	/**
	 * Attachment Tasks
	*/
	// Attachment - Publish
	function attachmentsPublish($cid=array(), $publish=1)
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);

		if (!is_array($cid) || count($cid) > 0) 
		{
			$publish = (int) $publish;
			$cids 	 = implode(',', $cid);

			$query->clear();
			$query->update($db->qn('#__rsmembership_membership_attachments'))->set($db->qn('published').' = '.$db->q($publish))->where($db->qn('id').' IN (\''.$cids.'\')');
			$db->setQuery($query);

			if (!$db->execute())
			{
				$this->setError($db->getErrorMsg());
				return false;
			}
		}

		return $cid;
	}
	
	// Attachment - Remove
	function attachmentsRemove($cids) 
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$cids = implode(',', $cids);

		$query->delete()->from($db->qn('#__rsmembership_membership_attachments'))->where($db->qn('id').' IN (\''.$cids.'\')');
		$db->setQuery($query);
		$db->execute();

		return true;
	}

	public function getSharedOrdering() {
		require_once JPATH_COMPONENT.'/helpers/adapters/ordering.php';

		$ordering = new RSOrdering();
		return $ordering;
	}
	
	public function getSharedPagination() {
		return $this->sharedPagination;
	}
}