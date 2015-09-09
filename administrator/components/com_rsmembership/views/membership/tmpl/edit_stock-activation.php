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

$this->fields 	= $this->form->getFieldset('stock-activation');
foreach ($this->fields as $field) 
{
	if (strtolower($field->type) == 'spacer') 
	{
		echo '<div class="clr"></div>';
		echo '<h3>'.JText::_($field->label).'</h3>';
		echo '<div class="clr"></div>';
		if ($field->description) 
			echo '<div class="com-rsmembership-spacer-desc">'.JText::_($field->description).'</div>';
	} else {
		$this->field->showField($field->hidden ? '' : $field->label, $field->input);

		if($field->name == 'jform[no_renew]') 
			echo '<div class="clr"></div><strong class="rsmembership_critical">'.JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_USER_TYPE_WARNING').'</strong>';
		
	}
}
$this->field->endFieldset();
?>
<div class="alert alert-info">
	<p><?php echo JText::_('COM_RSMEMBERSHIP_SUPER_USER_GROUPS_RESTRICTION'); ?></p>
</div>