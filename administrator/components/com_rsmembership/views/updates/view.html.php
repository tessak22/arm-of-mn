<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class RSMembershipViewUpdates extends JViewLegacy
{
	public function display($tpl = null)
	{
		$this->addToolbar();

		$this->hash 	= $this->get('hash');
		$this->jversion = $this->get('joomlaVersion');
		$this->revision = (string) new RSMembershipVersion();
		$this->sidebar	= $this->get('SideBar');

		parent::display($tpl);
	}

	protected function addToolbar() 
	{
		JToolBarHelper::title(JText::_('COM_RSMEMBERSHIP_UPDATES'), 'updates');

		// add Menu in sidebar
		require_once JPATH_COMPONENT.'/helpers/toolbar.php';

		RSMembershipToolbarHelper::addToolbar('updates');
	}
}