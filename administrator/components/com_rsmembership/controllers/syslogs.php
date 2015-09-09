<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipControllerSyslogs extends JControllerAdmin
{
	function __construct($config = array()) {
		parent::__construct($config);
		
		// delete
		$this->registerTask('trash', 'delete');
	}
	
	public function getModel($name = 'Syslog', $prefix = 'RSMembershipModel', $config = array('ignore_request' => true)) {
		return parent::getModel($name, $prefix, $config);
	}
}