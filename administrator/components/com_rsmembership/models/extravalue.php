<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class RSMembershipModelExtraValue extends JModelAdmin
{
	protected $text_prefix = 'COM_RSMEMBERSHIP';

	public function getTable($type = 'ExtraValue', $prefix = 'RSMembershipTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_rsmembership.extravalue', 'extravalue', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
			return false;

		return $form;
	}

	protected function loadFormData() 
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_rsmembership.edit.extravalue.data', array());

		if (empty($data))
			$data = $this->getItem();

		return $data;
	}

	public function getItem($pk = null)
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);

		$item = parent::getItem();
		
		$query->select('*')->from($db->qn('#__rsmembership_extra_value_shared'))->where($db->qn('extra_value_id').' = '.$db->q($item->id))->order($db->qn('ordering').' ASC');
		$db->setQuery($query);
		$item->shared = $db->loadObjectList();

		foreach ($item->shared as $s => $shared)
			switch ($shared->type)
			{
				default:
					$instances = RSMembership::getSharedContentPlugins();
					foreach ($instances as $instance)
						if (method_exists($instance, 'showUserFriendlyParams'))
							$instance->showUserFriendlyParams($shared);

					$item->shared[$s] = $shared;
				break;

				case 'article':
					$query->clear();
					$query->select($db->qn('title'))->from($db->qn('#__content'))->where($db->qn('id').' = '.$db->q( (int) $shared->params));
					$db->setQuery($query);
					$item->shared[$s]->params = $db->loadResult();
				break;

				case 'module':
					$query->clear();
					$query->select($db->qn('title').', '.$db->qn('module'))->from($db->qn('#__modules'))->where($db->qn('id').' = '.$db->q((int) $shared->params));
					$db->setQuery($query);
					$module = $db->loadObject();
					$item->shared[$s]->params = '('.$module->module.') '.$module->title;
				break;

				case 'menu':
					$query->clear();
					$query->select($db->qn('title','name').', '.$db->qn('menutype'))->from($db->qn('#__menu'))->where($db->qn('id').' = '.$db->q((int) $shared->params));
					$db->setQuery($query);
					$menu = $db->loadObject();
					$item->shared[$s]->params = '('.$menu->menutype.') '.$menu->name;
				break;

				case 'section':
					$query->clear();
					$query->select($db->qn('title'))->from($db->qn('#__sections'))->where($db->qn('id').' = '.$db->q((int) $shared->params));
					$db->setQuery($query);
					$item->shared[$s]->params = $db->loadResult();
				break;

				case 'category':
					$query->clear();
					$query->select($db->qn('title'))->from($db->qn('#__categories'))->where($db->qn('id').' = '.$db->q((int) $shared->params));
					$db->setQuery($query);
					$item->shared[$s]->params = $db->loadResult();
				break;
			}

		jimport('joomla.html.pagination');
		$this->sharedPagination = new JPagination(count($item->shared), 0, 0);

		return $item;
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

		$tabs = new RSTabs('com-rsmembership-extra-values');
		return $tabs;
	}

	// folder - Publish
	function foldersPublish($cid=array(), $publish=1)
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);

		if (!is_array($cid) || count($cid) > 0)
		{
			$publish = (int) $publish;
			$cids 	 = implode(',', $cid);

			$query->update($db->qn('#__rsmembership_extra_value_shared'))->set($db->qn('published').' = '.$db->q($publish))->where($db->qn('id').' IN (\''.$cids.'\')');
			$db->setQuery($query);
			$db->execute();
		}
		return $cid;
	}

	// Folder - Remove
	function foldersRemove($cids)
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$cids 	= implode("', '", $cids);

		$query->delete()->from($db->qn('#__rsmembership_extra_value_shared'))->where($db->qn('id').' IN (\''.$cids.'\')');
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