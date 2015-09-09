<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

class RSMembershipViewTransaction extends JViewLegacy
{
	protected $item;

	public function display($tpl = null) {
		// get subscriber
		$this->item = $this->get('Item');

		parent::display($tpl);
	}
}