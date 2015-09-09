<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
?>
<script type="text/javascript">
// refresh the results from the parent window
function rsmembership_refresh_results()
{
	if (!window.parent || !window.parent.document.getElementById('addmemberships_ajax')) {
		return;
	}
	
	var xmlHttp = rsmembership_get_xml_http_object();
	if (xmlHttp==null)
	{
	  alert ("Browser does not support HTTP Request");
	  return;
	}
	
	var url = 'index.php?option=com_rsmembership&view=subscriber&layout=edit_memberships&user_id=<?php echo $this->item->user_id; ?>&tmpl=component&format=raw&sid=' + Math.random();
	xmlHttp.onreadystatechange = function() {
		if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete")
		{
			window.parent.document.getElementById('addmemberships_ajax').innerHTML = xmlHttp.responseText;
			window.parent.reloadSqueezeBox();
		}
	}

	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function rsmembership_change_unlimited()
{
	document.getElementById('jform_membership_end').disabled = document.getElementById('jform_unlimited').checked == true;
}

function rsmembership_change_membership()
{
	var xmlHttp = rsmembership_get_xml_http_object();
	if (xmlHttp==null)
	{
	  alert ("Browser does not support HTTP Request");
	  return;
	}

	url = 'index.php?option=com_rsmembership';
	var params = [
		'option=com_rsmembership',
		'task=ajax.date',
		'format=raw',
		'membership_id=' + encodeURIComponent(document.getElementById('jform_membership_id').value),
		'membership_start=' + encodeURIComponent(document.getElementById('jform_membership_start').value)
	];
	
	xmlHttp.onreadystatechange = function() { // Call a function when the state changes.
		if (xmlHttp.readyState == 4 || xmlHttp.readyState == "complete") {
			var response = xmlHttp.responseText;
			if (response == '<?php echo $this->escape(JFactory::getDbo()->getNullDate()); ?>') {
				document.getElementById('jform_unlimited').checked = true;
			} else {
				document.getElementById('jform_membership_end').value = xmlHttp.responseText;
				document.getElementById('jform_unlimited').checked = false;
			}
			rsmembership_change_unlimited();
			rsmembership_calculate_price();
		}
	}

	xmlHttp.open('POST', url, true);
	xmlHttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xmlHttp.send(params.join('&'));
}

function rsmembership_calculate_price() {
	<?php if (!$this->item->id) { ?>
		var memberships = {};
		var extras		= {};
		<?php foreach ($this->prices['memberships'] as $membership_id => $price) { ?>
			memberships[<?php echo (int) $membership_id; ?>] = <?php echo $this->jsEscape($price); ?>;
		<?php } ?>
		<?php foreach ($this->prices['extras'] as $extra_id => $price) { ?>
			extras[<?php echo (int) $extra_id; ?>] = <?php echo $this->jsEscape($price); ?>;
		<?php } ?>
		var total = 0;
		total += memberships[document.getElementById('jform_membership_id').value];
		for (var i=0; i<document.getElementById('jform_extras').options.length; i++) {
			var option = document.getElementById('jform_extras').options[i];
			if (option.selected) {
				total += extras[option.value];
			}
		}
		document.getElementById('jform_price').value = total;
	<?php } ?>
}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_rsmembership'); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off" class="form form-validate form-horizontal rsm_subscriber_membership_form">
<div class="pull-right">
	<button type="button" class="btn btn-success" id="membership_save_button" onclick="submitbutton('membership_subscriber.apply')"><?php echo JText::_('COM_RSMEMBERSHIP_SAVE');?></button>
	<button type="button" class="btn btn-warning" onclick="window.parent.SqueezeBox.close();"><?php echo JText::_('COM_RSMEMBERSHIP_CANCEL');?></button>
</div>

<?php
	$this->tabs->addTitle(JText::_($this->fieldsets['main']->label), 'membership-details');
	// load content
	$tmembership_details_content = $this->loadTemplate('membership_details');
	// add the tab content
	$this->tabs->addContent($tmembership_details_content);
	
	$this->tabs->addTitle(JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_INFO'), 'membership-info');
	// load content
	$membership_info_content = $this->loadTemplate('membership_info');
	// add the tab content
	$this->tabs->addContent($membership_info_content);
	
	$this->tabs->render();
?>
<?php echo JHtml::_('form.token'); ?>
	<input type="hidden" name="option" value="com_rsmembership" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="last_transaction_id" value="<?php echo $this->item->last_transaction_id; ?>" />
	<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
</form>
<script type="text/javascript">
rsmembership_change_unlimited();
<?php if (!$this->item->id) { ?>
rsmembership_change_membership();
rsmembership_calculate_price();
<?php } ?>

// Refresh the results on page reload
rsmembership_refresh_results();
</script>