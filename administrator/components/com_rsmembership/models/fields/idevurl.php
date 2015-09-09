<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class JFormFieldIdevurl extends JFormField {
	protected $type = 'Idevurl';

	public function getInput() 
	{
		return '
		<input type="text" name="'.$this->name.'" value="'.$this->value.'" class="'.$this->element['class'].'" id="jform_idev_url" size="100" />
		<button type="button" class="fltlft btn btn-info btn-tiny" id="jform_idev_check_url">'.JText::_('COM_RSMEMBERSHIP_IDEV_CHECK_CONNECTION').'</button>';
	}
}