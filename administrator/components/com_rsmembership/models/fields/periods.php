<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class JFormFieldPeriods extends JFormField {
	protected $type = 'Periods';

	public function getInput() 
	{
		$period_types  = array(
			JHtml::_('select.option', 'h', JText::_('COM_RSMEMBERSHIP_HOURS')),
			JHtml::_('select.option', 'd', JText::_('COM_RSMEMBERSHIP_DAYS')),
			JHtml::_('select.option', 'm', JText::_('COM_RSMEMBERSHIP_MONTHS')),
			JHtml::_('select.option', 'y', JText::_('COM_RSMEMBERSHIP_YEARS'))
		);

		$disabled	 = '';//(empty($this->value) ? 'disabled="disabled"' : '');
		if ( !is_array($this->value) ) {
			$this->value[0] = 'd';
			$this->value[1] = '';
		}
		
		// set the default length to days
		if (is_null($this->value[0])) {
			$this->value[0] = 'd';
		}

		return JHTML::_('select.genericlist', $period_types, $this->name.'[]', ' class="'.$this->element['class'].'" '.$disabled, 'value', 'text', $this->value[0], $this->id.'0').'<input type="text" name="'.$this->name.'[]'.'" id="'.$this->id.'1" value="'.$this->value[1].'" '.$disabled.' class="input-tiny" />';
	}
}