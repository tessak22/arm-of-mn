<?php
/**
* @package RSMembership!
* @copyright (C) 2009-2015 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('list');

class JFormFieldAuthorizeDataMap extends JFormFieldList
{
	protected $type = 'AuthorizeDataMap';
	
	protected function getOptions() {		
		// Initialize variables.
		$options 	= array();
		$options[] 	= JHtml::_('select.option', '', JText::_('PLG_SYSTEM_RSMEMBERSHIPAUTHORIZE_AUTHORIZE_DATA_MAP_UNUSED'));
		
		$db 	= JFactory::getDbo();
		$query 	= $db->getQuery(true);
		$query->select($db->qn('name'))
			  ->from('#__rsmembership_fields')
			  ->order($db->qn('ordering').' '.$db->escape('asc'));
		$db->setQuery($query);
		if ($fields = $db->loadObjectList()) {
			foreach ($fields as $field) {
				$tmp = JHtml::_('select.option', $field->name, $field->name);

				// Add the option object to the result set.
				$options[] = $tmp;
			}
		}

		reset($options);
		return $options;
	}
}