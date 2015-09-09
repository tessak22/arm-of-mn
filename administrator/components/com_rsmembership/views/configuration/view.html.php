<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class RSMembershipViewConfiguration extends JViewLegacy
{
	public function display($tpl = null) 
	{
		$user = JFactory::getUser();
		if (!$user->authorise('core.admin', 'com_rsmembership')) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->redirect(JRoute::_('index.php?option=com_rsmembership', false));
		}
		
		$this->addToolBar();

		// tabs
		$this->tabs		 = $this->get('RSTabs');
		$this->field	 = $this->get('RSFieldset');

		// form
		$this->form		 = $this->get('Form');
		$this->fieldsets = $this->form->getFieldsets();
		$this->sidebar	 = $this->get('SideBar');

		parent::display($tpl);
	}
	
	protected function addToolbar() {
		// set title
		JToolBarHelper::title('RSMembership!', 'rsmembership');
		
		require_once JPATH_COMPONENT.'/helpers/toolbar.php';
		RSMembershipToolbarHelper::addToolbar('configuration');

		JToolBarHelper::apply('configuration.apply');
		JToolBarHelper::save('configuration.save');
		JToolBarHelper::cancel('configuration.cancel');
	}
}