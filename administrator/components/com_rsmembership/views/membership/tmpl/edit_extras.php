<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

// set description if required
if (isset($this->fieldset->description) && !empty($this->fieldset->description)) { ?>
	<div class="com-rsmembership-tooltip"><?php echo JText::_($this->fieldset->description); ?></div>
<?php 
} 
$this->field->startFieldset(JText::_($this->fieldset->label), 'rs_fieldset adminform');

$this->fields 	= $this->form->getFieldset('extras');
foreach ($this->fields as $field) 
{
	$input = $field->input;
	$label = $field->label;
	if ($field->fieldname == 'extras' && !$field->hasValues) {
		echo JText::_('COM_RSMEMBERSHIP_PLEASE_ADD_EXTRA');
	} else {
		$this->field->showField($field->hidden ? '' : $label, $input);
	}
}

$this->field->endFieldset();