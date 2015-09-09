<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('editor');
class JFormFieldgetEditor extends JFormFieldEditor
{
	public $type = 'getEditor'; 

	public function getLabel() 
	{
		$after_label = '';
		if ( isset($this->element['viewname']) && $this->element['viewname'] == 'membership' ) 
		{
			$after_label = '<div class="clr"></div><p class="rsmembership_after_label">'.JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_DESCRIPTION_PLACEHOLDERS').'<br /><br />'.
			'<span class="rsmembership_'.(strpos($this->value, '{price}') !== false ? 'green' : 'red').'">{price}</span> - '. JText::_('COM_RSMEMBERSHIP_PLACEHOLDER_PRICE').'</p>'.
			'<span class="rsmembership_'.(strpos($this->value, '{stock}') !== false ? 'green' : 'red').'">{stock}</span> - '. JText::_('COM_RSMEMBERSHIP_PLACEHOLDER_STOCK').'</p>';
		}

		return parent::getLabel().$after_label;
	}
}