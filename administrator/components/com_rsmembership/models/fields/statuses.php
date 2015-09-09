<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class JFormFieldStatuses extends JFormField {
	protected $type = 'Statuses';

	public function getInput() 
	{
		$multiple 	  = ($this->element['multiple'] ? 'multiple="multiple"' : '');
		$size		  = ($this->element['size'] ? 'size="'.$this->element['size'].'"' : '');
		$all_statuses = RSMembershipHelper::getStatusesList();

		return JHTML::_('select.genericlist', $all_statuses, $this->name, 'class="'.$this->element['class'].'" '.$multiple.' '.$size, 'value', 'text', $this->value);
	}
}
		