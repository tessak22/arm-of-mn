<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipTableExtraValueShared extends JTable
{
	public $published 	= 1;

	public function __construct(& $db) 
	{
		parent::__construct('#__rsmembership_extra_value_shared', 'id', $db);
	}

	public function check()
	{
		if (!$this->id)
			$this->ordering = self::getNextOrder();

		return true;
	}
}