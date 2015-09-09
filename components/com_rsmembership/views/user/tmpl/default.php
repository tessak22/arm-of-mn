<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');
?> 
<script type="text/javascript">
function validate_user()
{
	var form = document.membershipForm;
	var msg = new Array();
	
	<?php foreach ($this->fields_validation as $validation) { ?>
		<?php echo $validation; ?>
	<?php } ?>
	
	if (msg.length > 0)
	{
		alert(msg.join("\n"));
		return false;
	}
	
	return true;
}
</script>
<div class="item-page">
	<?php if ($this->params->get('show_page_heading', 1)) { ?>
	<div class="page-header">
		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	</div>
	<?php } ?>

	<form method="post" class="rsmembership_form form form-horizontal" action="<?php echo JRoute::_('index.php?option=com_rsmembership&task=validateuser'); ?>" name="membershipForm" onsubmit="return validate_user();" id="rsm_user_form">
		<?php $this->field->startFieldset('', 'rsmembership_form_table input'); ?>
			<?php foreach ($this->fields as $field) { ?>
				<?php echo  $this->field->showField($field[0], $field[1]); ?>
			<?php } ?>
		<div class="form-actions">
			<button type="submit" class="button btn btn-success pull-right"><?php echo JText::_('COM_RSMEMBERSHIP_SAVE'); ?></button>
		</div>
		<?php echo $this->field->endFieldset(); ?>
	<?php echo JHTML::_('form.token'); ?>
	<input type="hidden" name="task" value="validateuser" />
	</form><!-- rsm_user_form -->
</div>