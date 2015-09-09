<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view');

class RSMembershipViewCategories extends JViewLegacy
{
	public function display($tpl = null) 
	{
		$app = JFactory::getApplication();

		$this->params 		= clone($app->getParams('com_rsmembership'));
		$this->items 		= $this->get('Items');
		$this->pagination 	= $this->get('Pagination');
		$this->Itemid 		= $app->input->get('Itemid',0, 'int');
		$this->state 		= $this->get('State');
	
		// Description
		if ( $this->params->get('menu-meta_description') ) 
			$this->document->setDescription($this->params->get('menu-meta_description'));
		// Keywords
		if ( $this->params->get('menu-meta_keywords') ) 
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		// Robots
		if ( $this->params->get('robots') ) 
			$this->document->setMetadata('robots', $this->params->get('robots'));

		parent::display($tpl);
	}
}