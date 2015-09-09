<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('list');

class JFormFieldExtraValues extends JFormFieldList {
	protected $type = 'ExtraValues';
	public $hasValues = false;

	protected function getOptions() {
		$db 	 = JFactory::getDBO();
		$query	 = $db->getQuery(true);
		$options = array();

		$query->select($db->qn('id'))
			  ->select($db->qn('name'))
			  ->from($db->qn('#__rsmembership_extras'))
			  ->where($db->qn('published').' = '.$db->q(1))
			  ->order($db->qn('ordering').' '.$db->escape('asc'));
		$db->setQuery($query);
		$extras = $db->loadObjectList();

		$all_extras = array();
		foreach ($extras as $extra)
			$all_extras[$extra->id] = $extra->name;
		
		$query->clear();
		$query->select('*')
			  ->from($db->qn('#__rsmembership_extra_values'))
			  ->order($db->qn('ordering').' ASC');
		$db->setQuery($query);
		$extra_values = $db->loadObjectList();
		
		$all_extravalues = array();
		foreach ($extra_values as $value)
			$all_extravalues[$value->extra_id][$value->id] = $value->name;

		//build options
		foreach ($all_extras as $extra_id => $extra_text) 
		{
			$options[] = (object) array( 'value' => '<OPTGROUP>', 'text' => $extra_text);

			if (isset($all_extravalues[$extra_id])) 
				foreach ($all_extravalues[$extra_id] as $extra_value_id => $extra_value_text) 
					$options[] = JHTML::_('select.option', $extra_value_id, $extra_value_text);

			$options[] = (object) array( 'value' => '</OPTGROUP>', 'text' => $extra_text);
		}
		
		reset($options);
		
		if ($options) {
			$this->hasValues = true;
		}

		return $options;
	}
}