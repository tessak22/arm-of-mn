<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

//keep session alive while editing
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
if ( RSMembershipHelper::isJ3() ) 
	JHtml::_('formbehavior.chosen', 'select');
?>
<script type="text/javascript">
	function rsm_enable_registration() 
	{
		if ( jQuery("#jform_disable_registration1").is(":checked") ) 
			jQuery("#jform_registration_page").prop("disabled", false);
		else
			jQuery("#jform_registration_page").prop("disabled", true);
	}

	function rsm_captcha_enable()
	{
		var captchaFields 		= jQuery('#jform_captcha_enabled_for0, #jform_captcha_enabled_for1, #jform_captcha_characters, #jform_captcha_lines0, #jform_captcha_lines1, #jform_captcha_case_sensitive0, #jform_captcha_case_sensitive1'),
			recaptchaFields 	= jQuery('#jform_captcha_enabled_for0, #jform_captcha_enabled_for1, #jform_recaptcha_public_key, #jform_recaptcha_private_key, #jform_recaptcha_theme'),
			recaptchaNewFields 	= jQuery('#jform_captcha_enabled_for0, #jform_captcha_enabled_for1, #jform_recaptcha_new_site_key, #jform_recaptcha_new_secret_key, #jform_recaptcha_new_theme, #jform_recaptcha_new_type');
		
		captchaFields.prop('disabled', true);
		recaptchaFields.prop('disabled', true);
		recaptchaNewFields.prop('disabled', true);
		
		switch (parseInt(jQuery('#jform_captcha_enabled option:selected').val())) {
			case 1: // CAPTCHA
				captchaFields.prop('disabled', false);
			break;
			
			case 2: // reCAPTCHA Legacy
				recaptchaFields.prop('disabled', false);
			break;
			
			case 3: // reCAPTCHA New
				recaptchaNewFields.prop('disabled', false);
			break;
		}
	}

	function rsm_idev_enable(what)
	{
		if ( jQuery("#jform_idev_enable1").is(":checked") ) 
			jQuery("#jform_idev_url, #jform_idev_track_renewals0, #jform_idev_track_renewals1, #jform_idev_check_url").prop("disabled", false);
		else 
			jQuery("#jform_idev_url, #jform_idev_track_renewals0, #jform_idev_track_renewals1, #jform_idev_check_url ").prop("disabled", true);
	}

	function rsm_idev_check_connection()
	{
		submitbutton("configuration.idevCheckConnection");
	}

	function submitbutton(pressbutton)
	{
		var form = document.adminForm;
		
		if (pressbutton == "cancel")
		{
			submitform(pressbutton);
			return;
		}
		
		if (jQuery("#jform_currency").val().length == 0)
		{
			alert("<?php echo JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_CURRENCY_ERROR', true); ?>");
			return;
		}
		
		var dt = jQuery("#config-tabs-com_rsmembership_configuration").children("dt");
		for (var i=0; i<dt.length; i++)
		{
			if (dt[i].className.indexOf("open") != -1)
				jQuery("#jform_tabposition").val(i);
		}
		submitform(pressbutton);
	}

	Joomla.submitbutton = submitbutton;
	
	jQuery(document).ready(function(){

		rsm_enable_registration();
		rsm_captcha_enable();
		rsm_idev_enable();

		jQuery("#jform_disable_registration0, #jform_disable_registration1").click(function(){ 
			rsm_enable_registration();
		});
		jQuery("#jform_captcha_enabled").change(function(){ 
			rsm_captcha_enable();
		});
		jQuery("#jform_idev_enable0, #jform_idev_enable1").click(function(){ 
			rsm_idev_enable();
		});
		jQuery("#jform_idev_check_url").click(function(){ 
			rsm_idev_check_connection();
		});
	});
</script>
<form action="<?php echo JRoute::_('index.php?option=com_rsmembership&view=configuration'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate form form-horizontal" enctype="multipart/form-data" autocomplete="off">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
	<?php

	foreach ($this->fieldsets as $name => $fieldset) 
	{
		// add the tab title
		$this->tabs->addTitle($fieldset->label, $fieldset->name);

		$content = $this->field->startFieldset(JText::_($this->fieldsets[$fieldset->name]->label), 'adminform form', false);

		$this->fields 	= $this->form->getFieldset( $fieldset->name );
		foreach ($this->fields as $field) {
			$content .= $this->field->showField( $field->hidden || $fieldset->name == 'permissions' ? '' : $field->label, $field->input, false);
		}
		$content .= $this->field->endFieldset(false);

		// add the tab content
		$this->tabs->addContent($content);
	}

	// render tabs
	$this->tabs->render();
	?>
		<div>
		<?php echo JHtml::_('form.token'); ?>
		<input type="hidden" name="option" value="com_rsmembership" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="controller" value="configuration" />
		</div>
	</div>
</form>