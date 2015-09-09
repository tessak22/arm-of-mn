<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class RSMembershipVersion
{
	public $version = '1.21.10';
	public $key		= 'MB86SH10F3';
	// Unused
	public $revision = null;
	
	public function __toString() {
		return $this->version;
	}
	
	// Legacy, keep revision
	public function __construct() {
		list($j, $revision, $bugfix) = explode('.', $this->version);
		$this->revision = $revision;
	}
}