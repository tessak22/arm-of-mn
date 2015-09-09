<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view');

class RSMembershipViewThankYou extends JViewLegacy
{
	function display( $tpl = null )
	{
		// get parameters
		$this->params  = clone(JFactory::getApplication()->getParams('com_rsmembership'));
		$this->message = $this->get('message');
		
		parent::display();
	}
}