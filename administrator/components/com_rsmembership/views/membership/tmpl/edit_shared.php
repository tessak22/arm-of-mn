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
?>

<?php if (!empty($this->item->id)) { ?>
		<div class="button2-left"><div class="blank"><a class="modal btn btn-info btn-small" title="Select the path" rel="{handler: 'iframe', size: {x: 660, y: 475}}" href="<?php echo JRoute::_('index.php?option=com_rsmembership&view=share&membership_id='.$this->item->id.'&tmpl=component'); ?>"><?php echo JText::_('COM_RSMEMBERSHIP_ADD_CONTENT'); ?></a></div></div>
		<span class="rsmembership_clear" style="margin-bottom: 10px;"></span>
		<div id="addmembershipshared_ajax">
			<?php echo $this->loadTemplate('shared_list'); ?>
		</div>
<?php } else { ?>
		<?php echo JText::_('COM_RSMEMBERSHIP_SHARED_SAVE_FIRST'); ?>
<?php } ?>
<div class="clearfix clr"></div>
<?php
$this->fields 	= $this->form->getFieldset('shared');
foreach ($this->fields as $field) {
	$this->field->showField($field->hidden ? '' : $field->label, $field->input);
}
$this->field->endFieldset();
?>