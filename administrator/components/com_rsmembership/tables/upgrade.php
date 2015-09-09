<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipTableUpgrade extends JTable
{
	public function __construct(& $db) 
	{
		parent::__construct('#__rsmembership_membership_upgrades', 'id', $db);
	}

	public function check()
	{
		if ($this->membership_from_id == $this->membership_to_id) {
			$this->setError(JText::_('COM_RSMEMBERSHIP_UPGRADE_SAME_ERROR'));
			return false;
		}

		return true;
	}
}