<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipViewExtraValue extends JViewLegacy
{
	public function display($tpl = null)
	{
		$this->item 		= $this->get('Item');
		$this->ordering 	= $this->get('SharedOrdering');
		$this->pagination 	= $this->get('sharedPagination');
		$this->currency 	= RSMembershipHelper::getConfig('currency');
		
		parent::display($tpl);
	}
}