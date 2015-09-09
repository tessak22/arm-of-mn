<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipTableCoupon extends JTable
{
	public function __construct(& $db) 
	{
		parent::__construct('#__rsmembership_coupons', 'id', $db);
	}
}