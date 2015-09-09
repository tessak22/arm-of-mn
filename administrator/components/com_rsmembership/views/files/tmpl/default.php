<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.framework');
?>
<?php if ($this->params->show_add) { ?>
<script type="text/javascript">
function submitbutton()
{
	var table = window.parent.document.getElementById('<?php echo $this->function; ?>');
	if (typeof(table)=='undefined' || table===null)
		return false;
		
	for (var i=<?php echo $this->start; ?>; i<<?php echo $this->count; ?>; i++)
	{
		var cb = document.getElementById('cb'+i);
		
		<?php if ($this->task == 'addfolder') { ?>
		var item = cb.value + '/';
		<?php } elseif ($this->task == 'addfile') { ?>
		var item = cb.value;
		<?php } ?>
		
		if (cb.checked)
		{
			if (table.innerHTML.indexOf('<td align="center">' + item + '</td>') != -1)
				alert('<?php echo JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_PATH_ALREADY'); ?>' + ' ' + item);
		}
	}
	
	<?php if ($this->params->show_add) { ?>
	$('folder_add_button').disabled = true;
	<?php } ?>

	rsmembership_submit_form_ajax('files.<?php echo $this->function; ?>');
	
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
	
	<?php if (!empty($this->membership_id)) { ?>
	var model = 'membership';
	var url = 'index.php?option=com_rsmembership&format=raw&task=ajax.<?php echo $this->function; ?>&id=<?php echo $this->membership_id; ?>&tmpl=component&sid=' + Math.random();
		<?php if (!empty($this->email_type)) { ?>
			url += '&email_type=<?php echo $this->escape($this->email_type); ?>';
		<?php } ?>
	<?php } elseif (!empty($this->extra_value_id)) { ?>
	var model = 'extravalue';
	var url = 'index.php?option=com_rsmembership&format=raw&task=ajax.<?php echo $this->function; ?>&id=<?php echo $this->extra_value_id; ?>&tmpl=component&sid=' + Math.random();
	<?php } ?>
	xmlHttp.onreadystatechange = function() {//Call a function when the state changes.
		if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete")
		{
			window.parent.document.getElementById('<?php echo $this->function; if (!empty($this->email_type)) echo $this->email_type; ?>_ajax').innerHTML = xmlHttp.responseText;
	<?php if (RSMembershipHelper::isJ3()) { ?>
			var sortableList = new window.parent.jQuery.JSortableList('#<?php echo $this->function; ?> tbody','adminForm','asc' , 'index.php?option=com_rsmembership&task='+model+'.saveOrderAjax&tmpl=component','','');
	<?php } ?>

			window.parent.SqueezeBox.close();
		}
	}
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function rsmembership_check_state_changed() 
{
	if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete")
	{
		document.getElementById('<?php echo $this->function ?>_ajax').innerHTML = xmlHttp.responseText;
		window.parent.SqueezeBox.close();
	}
}
</script>
<?php } ?>
<form action="<?php echo JRoute::_($this->link); ?>" method="post" name="adminForm" id="adminForm" class="form form-horizontal" enctype="multipart/form-data">
	<div id="j-sidebar-container" class="span2">
	<?php if ( !$this->tmpl ) { ?>
		<?php echo $this->sidebar; ?>
	<?php } ?>
	</div>
	<div id="j-main-container" class="span10">
		<div id="rsmembership_explorer">
				<?php if (!empty($this->function) && ($this->function == 'addmembershipshared' || $this->function == 'addextravalueshared' || $this->function == 'addextravaluefolders')) { ?>
					<button type="button" class="btn btn btn-small" onclick="document.location = '<?php echo JRoute::_('index.php?option=com_rsmembership&view=share&membership_id='.$this->membership_id.'&tmpl=component'); ?>'"><?php echo JText::_('Back'); ?></button>
				<?php } ?>
				<?php if ($this->params->show_add) { ?>
					<button id="folder_add_button" type="button" class="btn btn-info btn-small" onclick="if(document.adminForm.boxchecked.value==0){alert('<?php echo JText::sprintf('COM_RSMEMBERSHIP_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST_TO', JText::_('COM_RSMEMBERSHIP_ADD')); ?>');}else{submitbutton()}"><?php echo JText::_('COM_RSMEMBERSHIP_ADD_SELECTED_ITEMS'); ?></button>
				<?php } ?>
				<?php if ($this->params->show_upload) { ?>
					<?php if ($this->canUpload) { ?>
						<table class="adminform table table-striped table-bordered center" id="rsm_upload_table">
							<tr>
								<th width="100%" class="center">
									<input class="input_box" id="upload" name="upload" type="file" size="57" class="input input-normal" />
									<input class="button btn btn-info" type="button" value="<?php echo JText::_('Upload File'); ?>" onclick="submitbutton('files.upload')" />
								</th>
							</tr>
						</table>
					<?php } else { ?>
						<?php echo JText::_('COM_RSMEMBERSHIP_CANT_UPLOAD'); ?>
					<?php } ?>
				<?php } ?>

				<div id="editcell1">
					<table class="adminlist table table-striped">
						<thead>
						<tr>
							<th width="20" valign="middle"><input type="checkbox" name="checkall-toggle" value="" onclick="Joomla.checkAll(this);"/></th>
							<th class="center">
								<strong><?php echo JText::_('COM_RSMEMBERSHIP_CURRENT_LOCATION'); ?></strong>
							<?php foreach ($this->elements as $element) { ?>
									<a href="<?php echo JRoute::_($this->link.'&folder='.urlencode($this->escape($element->fullpath))); ?>"><?php echo $this->escape($element->name); ?></a> /
							<?php } ?>
							<?php if ($this->params->show_new_dir) { ?>
								<input type="text" name="dirname" value="" />
								<button type="button" class="btn btn-success" onclick="if (document.adminForm.dirname.value.length > 0) submitbutton('files.newdir'); else alert('<?php echo JText::_('COM_RSMEMBERSHIP_DIRECTORY_NAME_ERROR'); ?>');"><?php echo JText::_('COM_RSMEMBERSHIP_NEW_DIRECTORY'); ?></button>
							<?php } ?>
							</th>
						</tr>
						</thead>
						<tr>
							<td>&nbsp;</td>
							<td><a class="folder" href="<?php echo JRoute::_($this->link.'&folder='.urlencode($this->previous)); ?>">..<?php echo JHTML::_('image', 'administrator/components/com_rsmembership/assets/images/up.gif', JText::_('COM_RSMEMBERSHIP_BACK')); ?></a></td>
						</tr>
				<?php
				$j = 0;
				foreach ($this->folders as $folder)
				{
				?>
					<tr>
						<?php if ($this->params->show_folders) { ?>
						<td><?php echo JHTML::_('grid.id', $j, $folder->fullpath); ?></td>
						<?php } else { ?>
						<td>&nbsp;</td>
						<?php } ?>
						<td><a class="folder" href="<?php echo JRoute::_($this->link.'&folder='.urlencode($folder->fullpath)); ?>"><?php echo $this->escape($folder->name); ?></a> <a href="<?php echo JRoute::_('index.php?option=com_rsmembership&task=file.edit&cid='.urlencode($folder->fullpath.'/')); ?>"><?php if ($this->params->show_edit) { ?>[<?php echo JText::_('Edit'); ?>]</a><?php } ?></td>
					</tr>
				<?php
					$j++;
				}

				if ($this->params->show_files) {
					$i = $j;
					foreach ($this->files as $file)
					{
					?>
						<tr>
							<td><?php echo JHTML::_('grid.id', $i, $file->fullpath); ?></td>
							<td><a class="file" href="<?php echo $this->params->show_edit ? JRoute::_('index.php?option=com_rsmembership&task=file.edit&cid='.urlencode($file->fullpath)) : 'javascript: void(0)'; ?>"><?php echo $file->name; ?></a> <a href="<?php echo JRoute::_('index.php?option=com_rsmembership&task=file.edit&cid='.urlencode($file->fullpath)); ?>"><?php if ($this->params->show_edit) { ?>[<?php echo JText::_('Edit'); ?>]</a><?php } ?></td>
						</tr>
					<?php
						$i++;
					}
				}
				?>
					</table>
				</div>

				<?php echo JHTML::_( 'form.token' ); ?>
				<input type="hidden" name="boxchecked" value="0" />

				<input type="hidden" name="folder" value="<?php echo $this->current; ?>" />
				<?php if (!empty($this->membership_id)) { ?>
				<input type="hidden" name="membership_id" value="<?php echo $this->membership_id; ?>" />
				<?php } ?>
				<?php if (!empty($this->extra_value_id)) { ?>
				<input type="hidden" name="extra_value_id" value="<?php echo $this->extra_value_id; ?>" />
				<?php } ?>
				<?php if (!empty($this->email_type)) { ?>
				<input type="hidden" name="email_type" value="<?php echo $this->escape($this->email_type); ?>" />
				<?php } ?>
				<input type="hidden" name="function" value="<?php echo $this->function; ?>" />
				<input type="hidden" name="task" value="" />
		</div>
	</div>
</form>
