<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view');

class RSMembershipViewMymemberships extends JViewLegacy
{
	function display($tpl = null)
	{
		$this->params 		= clone(JFactory::getApplication()->getParams('com_rsmembership'));
		$this->items 		= $this->get('Items');
		$this->pagination  	= $this->get('pagination');
		$this->total 		= $this->get('total');
		$this->action		= $this->escape(JRoute::_(JURI::getInstance(),false));
		$this->date_format	= RSMembershipHelper::getConfig('date_format');
		$this->transactions = $this->get('transactions');
		$this->limitstart	= JFactory::getApplication()->input->get('limitstart', 0, 'int');

		$Itemid = JFactory::getApplication()->input->get('Itemid',0, 'int');
		if ($Itemid > 0)
			$this->Itemid  	= '&Itemid='.$Itemid;
		else
			$this->Itemid	= '';

		// Description
		if ($this->params->get('menu-meta_description'))
			$this->document->setDescription($this->params->get('menu-meta_description'));
		// Keywords
		if ($this->params->get('menu-meta_keywords'))
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		// Robots
		if ($this->params->get('robots'))
			$this->document->setMetadata('robots', $this->params->get('robots'));

		parent::display();
	}
}