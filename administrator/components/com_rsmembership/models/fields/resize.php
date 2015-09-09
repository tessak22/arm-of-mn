<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class JFormFieldResize extends JFormField {
	protected $type = 'Resize';

	public function getInput() 
	{
		$input = '<div class="rsmembership_resize"><input type="checkbox" value="1" name="'.$this->name.'" /> <span>'. JText::_('COM_RSMEMBERSHIP_RESIZE_TO').' </span> 
		<input type="text" name="jform[thumb_w]" value="'.$this->value.'" id="'.$this->id.'" size="10" maxlength="255" /> <span>'.JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_PX').'</span></div>';

		return $input;
	}
}