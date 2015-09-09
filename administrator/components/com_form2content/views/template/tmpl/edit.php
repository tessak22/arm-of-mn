<?php 
defined('JPATH_PLATFORM') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.keepalive');

require_once(JPATH_COMPONENT_SITE.DIRECTORY_SEPARATOR.'shared.form2content.php');
?>
<script type="text/javascript">
//<!--
jQuery(document).ready(function() {
	jQuery("#jform_template").width(750);
});

Joomla.submitbutton = function(task) 
{
	if (task == 'template.cancel') 
	{
		Joomla.submitform(task, document.getElementById('adminForm'));
		return true;
	}

	if(jQuery('#jform_id').val() == '')
	{
		var result = jQuery('#jform_title').val().match(new RegExp('^[A-Za-z0-9_]+$'));

		if (result == null)
		{
			alert('<?php echo $this->escape(JText::_('COM_FORM2CONTENT_ERROR_TEMPLATE_TITLE_INVALID_CHARS', true)); ?>');
			return false;
		}
	}
		
	Joomla.submitform(task, document.getElementById('adminForm'));
	return true;
}
//-->	
</script>
<form action="<?php echo JRoute::_('index.php?option=com_form2content&task=template.edit&layout=edit&id='.urlencode($this->item->id)); ?>" method="post" name="adminForm" id="adminForm">
<div class="row-fluid">
	<!-- Begin Content -->
	<div class="span12 form-horizontal">
		<div class="control-group">
			<div class="control-label"><?php echo $this->form->getLabel('title'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('title'); ?></div>
		</div>
		<div class="control-group">
			<div class="control-label"><?php echo $this->form->getLabel('template'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('template'); ?></div>
		</div>
	</div>
	<!--  End Content -->
</div>
<?php echo $this->form->getInput('id'); ?>
<input type="hidden" name="task" value="" />
<input type="hidden" name="return" value="<?php echo JFactory::getApplication()->input->getCmd('return');?>" />
<?php echo JHtml::_('form.token'); ?>
<?php echo F2cViewHelper::displayCredits(); ?>
</form>
