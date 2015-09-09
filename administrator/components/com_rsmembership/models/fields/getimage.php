<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

// Thumb Image type  used in membership and file

class JFormFieldGetImage extends JFormField {
	protected $type = 'GetImage';

	public function getInput() 
	{
		$input  = '<div style="float:left">';
		$folder = (isset($this->element['folder_location']) ? $this->element['folder_location'].'/' : '');

		if ( !empty($this->value) ) {
			$input .= JHTML::_('image', JURI::root().'components/com_rsmembership/assets/thumbs/'.$folder.$this->value, '').
			'<div class="clr"></div><input type="checkbox" value="1" name="jform[thumb_delete]" /> '.JText::_('COM_RSMEMBERSHIP_DELETE_THUMB').'<br />';
		}

		$input .= '<input type="file" name="'.$this->name.'" id="'.$this->id.'" value="" /></div>';

		return $input;
	}
}