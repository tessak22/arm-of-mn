<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.tooltip');
JHtml::_('behavior.framework');
?>

<script type="text/javascript">
function submitbutton()
{
	var container = window.parent.document.getElementById('<?php echo $this->function; ?>');

	if (typeof(container)=='undefined' || container===null)
		return false;

	var form = document.adminForm;

	// do field validation
	if (document.getElementById('jform_params').value.length == 0)
		alert('<?php echo JText::_('COM_RSMEMBERSHIP_URL_ERROR', true); ?>');
	else
	{
		$('membership_save_button').disabled = true;
		$('membership_save_button2').disabled = true;

		<?php if ($this->what == 'membership_id') { ?>
		rsmembership_submit_form_ajax('share_url.addmembershipurl');
		<?php } else { ?>
		rsmembership_submit_form_ajax('share_url.addextravalueurl');
		<?php } ?>

	}
	
	return false;
}

// submit the form through ajax
function rsmembership_submit_form_ajax(pressbutton)
{
	if (pressbutton)
		document.adminForm.task.value=pressbutton;

	xmlHttp = rsmembership_get_xml_http_object();
	
	var url = document.adminForm.action;
	
	var params = new Array();
	for (i=0; i<document.adminForm.elements.length; i++)
	{
		// don't send an empty value
		if (!document.adminForm.elements[i].name) continue;
		if (document.adminForm.elements[i].name.length == 0) continue;
		// check if the checkbox is checked
		if (document.adminForm.elements[i].type == 'checkbox' && document.adminForm.elements[i].checked == false) continue;
		// check if the radio is selected
		if (document.adminForm.elements[i].type == 'radio' && document.adminForm.elements[i].checked == false) continue;
		
		params.push(document.adminForm.elements[i].name + '=' + escape(document.adminForm.elements[i].value));
	}
	
	params = params.join('&');
	
	xmlHttp.open("POST", url, true);

	//Send the proper header information along with the request
	xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlHttp.setRequestHeader("Content-length", params.length);
	xmlHttp.setRequestHeader("Connection", "close");

	xmlHttp.onreadystatechange = function() {//Call a function when the state changes.
	if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete")
		rsmembership_refresh_results();
	}
	xmlHttp.send(params);
}

// refresh the results from the parent window
function rsmembership_refresh_results()
{
	xmlHttp = rsmembership_get_xml_http_object();
	if (xmlHttp==null)
	{
	  alert ("Browser does not support HTTP Request");
	  return;
	}

	var url = 'index.php?option=com_rsmembership&task=ajax.<?php echo $this->function; ?>&id=<?php echo $this->id; ?>&tmpl=component&format=raw&sid=' + Math.random();

	xmlHttp.onreadystatechange = function() {//Call a function when the state changes.
		if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete")
		{
			window.parent.document.getElementById('<?php echo $this->function; ?>_ajax').innerHTML = xmlHttp.responseText;
	<?php if (RSMembershipHelper::isJ3()) { ?>
		<?php if ($this->what == 'membership_id') { ?>
			var model = 'membership';
		<?php } else { ?>
			var model = 'extravalue';
		<?php } ?>
			var sortableList = new window.parent.jQuery.JSortableList('#<?php echo $this->function; ?> tbody','adminForm','asc' , 'index.php?option=com_rsmembership&task='+model+'.saveOrderAjax&tmpl=component','','');
	<?php } ?>
			
			window.parent.SqueezeBox.close();
		}
	}
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}
</script>

<div id="<?php echo $this->function; ?>" class="row-fluid">
	<form action="<?php echo JRoute::_('index.php?option=com_rsmembership&view=share&layout=url&tmpl=component&'.$this->what.'='.$this->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">

	<button type="button" class="btn btn-small pull-left" onclick="document.location = '<?php echo JRoute::_('index.php?option=com_rsmembership&view=share&'.$this->what.'='.$this->id.'&tmpl=component'); ?>'"><?php echo JText::_('Back'); ?></button>
	<button id="membership_save_button" class="btn btn-small btn-info pull-left" type="button" onclick="submitbutton();"><?php echo $this->item->id > 0 ? JText::_('COM_RSMEMBERSHIP_UPDATE_URL') : JText::_('COM_RSMEMBERSHIP_ADD_URL'); ?></button>
		<div class="clearfix"></div><br />

		<?php
		$this->fields 	= $this->form->getFieldset('main');

		$this->field->startFieldset(JText::_($this->fieldsets['main']->label), 'adminform form');

		foreach ($this->fields as $field) 
		{
			$this->field->showField( $field->hidden ? '' : $field->label, $field->name == 'jform[params]' ? '<span id="rsme_url_addr">index.php?option=</span>'.$field->input : $field->input);
		}

		$this->field->endFieldset();
		?>

	<button id="membership_save_button2" class="btn btn-small btn-info pull-left" type="button" onclick="submitbutton();"><?php echo $this->item->id > 0 ? JText::_('COM_RSMEMBERSHIP_UPDATE_URL') : JText::_('COM_RSMEMBERSHIP_ADD_URL'); ?></button>

	<?php echo JHTML::_( 'form.token' ); ?>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="<?php echo $this->what; ?>" value="<?php echo $this->id; ?>" />

	<input type="hidden" name="filter_order" value="ordering" />
	<input type="hidden" name="filter_order_Dir" value="ASC" />
</form>
</div>
<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
?>