<?php
/**
 * Install script
 *
 * @package         NoNumber Framework
 * @version
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2015 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/script.install.helper.php';

class PlgSystemNnframeworkInstallerScript extends PlgSystemNnframeworkInstallerScriptHelper
{
	public $name = 'NONUMBER_FRAMEWORK';
	public $alias = 'nnframework';
	public $extension_type = 'plugin';

	public function onBeforeInstall()
	{
		if (!$this->isNewer())
		{
			return false;
		}
	}
}
