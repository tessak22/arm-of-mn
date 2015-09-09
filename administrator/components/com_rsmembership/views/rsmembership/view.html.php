<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class RSMembershipViewRSMembership extends JViewLegacy
{
	public function display($tpl = null)
	{
		$this->addToolbar();

		$this->sidebar	= $this->get('SideBar');

		$this->code			= $this->get('code');
		$this->version		= (string) new RSMembershipVersion();

		// loading Google Charts
		$this->document->addScript('https://www.google.com/jsapi');
		$this->reports_data = $this->get('ReportData');
		
		parent::display($tpl);
	}

	protected function addToolbar() 
	{
		JToolBarHelper::title(JText::_('COM_RSMEMBERSHIP_OVERVIEW'), 'rsmembership');

		// add Menu in sidebar
		require_once JPATH_COMPONENT.'/helpers/toolbar.php';

		RSMembershipToolbarHelper::addToolbar('rsmembership');
	}
}