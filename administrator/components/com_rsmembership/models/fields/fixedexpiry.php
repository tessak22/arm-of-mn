<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class JFormFieldFixedExpiry extends JFormField {
	protected $type = 'FixedExpiry';

	public function getInput() 
	{
		$disabled	 = (empty($this->value[0]) ? ' disabled="disabled"' : '');
		$checked	 = (isset($this->value[3]) && $this->value[3] == 1   ? ' checked="checked"' : '');
		$days		 = $this->getDays();
		$months		 = $this->getMonths();
		$years		 = $this->getYears();
		$date 		= JFactory::getDate()->toUnix();

		// setting the default value
		if( empty($this->value) ) 
			$this->value = array(0, date('d', $date), date('m', $date), date('Y', $date) );

		return 
			JHTML::_('select.genericlist', $days,   $this->name.'[]', ' class="'.$this->element['class'].'" '.$disabled, 'value', 'text', $this->value[0], $this->id.'0').
			JHTML::_('select.genericlist', $months, $this->name.'[]', ' class="'.$this->element['class'].'" '.$disabled, 'value', 'text', $this->value[1], $this->id.'1').
			JHTML::_('select.genericlist', $years,  $this->name.'[]', ' class="'.$this->element['class'].'" '.$disabled, 'value', 'text', $this->value[2], $this->id.'2').' 
			<label for="'.$this->id.'3" class="rsmembership_after_input checkbox"><input type="checkbox" name="'.$this->name.'[]'.'" id="'.$this->id.'3" value="1" '.$checked.' class="input-tiny" />'.JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_ENABLE_FIXED_EXPIRY').'</label>';
	}

	function getDays()
	{
		$return = array();

		$tmp = new stdClass();
		$tmp->value = 0;
		$tmp->text  = JText::_('COM_RSMEMBERSHIP_EVERY_DAY');

		$return[] = $tmp;
		
		for ($i=1; $i<=31; $i++)
		{
			$tmp = new stdClass();
			$tmp->value = $i;
			$tmp->text  = $i;

			$return[] = $tmp;
		}

		return $return;
	}
	
	function getMonths()
	{
		$return = array();
		
		$tmp = new stdClass();
		$tmp->value = 0;
		$tmp->text  = JText::_('COM_RSMEMBERSHIP_EVERY_MONTH');
		
		$return[] = $tmp;
		
		for ($i=1; $i<=12; $i++)
		{
			$tmp = new stdClass();
			$tmp->value = $i;
			$tmp->text  = JText::_('COM_RSMEMBERSHIP_MONTH_'.$i);
			
			$return[] = $tmp;
		}
		
		return $return;
	}
	
	function getYears()
	{
		$return = array();
		
		$tmp = new stdClass();
		$tmp->value = 0;
		$tmp->text  = JText::_('COM_RSMEMBERSHIP_EVERY_YEAR');
		
		$return[] = $tmp;
		
		// $max = date('Y', RSMembershipHelper::getCurrentDate());
		$max = RSMembershipHelper::showDate(time(),'Y');
		
		for ($i=$max; $i<=$max+50; $i++)
		{
			$tmp = new stdClass();
			$tmp->value = $i;
			$tmp->text  = $i;
			
			$return[] = $tmp;
		}
		
		return $return;
	}
}