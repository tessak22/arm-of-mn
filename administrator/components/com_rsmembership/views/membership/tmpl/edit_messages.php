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
<?php } ?>
<?php
$this->field->startFieldset(JText::_($this->fieldset->label), 'rs_fieldset adminform');

$this->fields 	= $this->form->getFieldset('messages');
foreach ($this->fields as $field) 
	$this->field->showField($field->hidden ? '' : $field->label, ( ( strtolower($field->type) == 'editor' )  ? '<div class="rsmembership_clear">'.$field->input.'</div>' : $field->input) );

$this->field->showField(' ', JText::sprintf('COM_RSMEMBERSHIP_PLACEHOLDERS_CAN_BE_USED', $this->getPlaceholders('thankyou')));
	
$this->field->endFieldset();
?>