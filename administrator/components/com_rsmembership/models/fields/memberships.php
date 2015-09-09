<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class JFormFieldMemberships extends JFormField {
	protected $type = 'Memberships';

	public function getInput() 
	{
		$options 	= array();
		$multiple 	= ($this->element['multiple'] ? 'multiple="multiple"' : '');
		$size		= ($this->element['size'] ? 'size="'.$this->element['size'].'"' : '');
		$onchange	= ($this->element['onchange'] ? 'onchange="'.$this->element['onchange'].'"' : '');

		$all_membs  = RSMembershipHelper::getMembershipsList();
		$options = array_merge($options, $all_membs);

		return JHTML::_('select.genericlist', $options, $this->name, 'class="'.$this->element['class'].'" '.$onchange.' '.$multiple.' '.$size, 'value', 'text', $this->value, $this->id);
	}
}
		