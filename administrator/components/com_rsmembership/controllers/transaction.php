<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class RSMembershipControllerTransaction extends JControllerForm
{
	public function __construct() {
		parent::__construct();
	}

	public function cancel($key = null) 
	{
		parent::cancel($key);
		
	}

}