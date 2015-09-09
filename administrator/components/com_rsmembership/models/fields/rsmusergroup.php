<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('usergroup');
class JFormFieldRSMUsergroup extends JFormFieldUsergroup
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'RSMUsergroup';

	protected function getInput() 
	{
		// JFormFieldUsergroup generates the value as an object e.g: (object(JObject)#247 (4) { ["_errors"rotected]=> array(0) {} [0] => 12, [1] => ,18)
		// we force the value to be an array
		$this->value = is_array($this->value) ? $this->value : explode(',', $this->value);
		
		$input = parent::getInput();
		
		if ($groups = self::getAdminGroups()) {
			$replacements = array();
			foreach ($groups as $group) {
				$replacements['value="'.$group.'"'] = 'value="" disabled="disabled"';
			}
			
			$input = str_replace(array_keys($replacements), array_values($replacements), $input);
		}
		
		return $input;
	}
	
	public static function getAdminGroups() {
		static $groups;
		if (!$groups) {
			require_once JPATH_COMPONENT.'/helpers/users.php';
			$groups = RSMembershipUsersHelper::getAdminGroups();
		}
		return $groups;
	}
}