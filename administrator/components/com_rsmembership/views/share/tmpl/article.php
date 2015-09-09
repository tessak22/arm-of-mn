<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');
?>
<script type="text/javascript">
function submitbutton()
{
	var table = window.parent.document.getElementById('<?php echo $this->function; ?>');
	if (typeof(table)=='undefined' || table===null)
		return false;

	$('membership_save_button').disabled = true;

	<?php if ($this->what == 'membership_id') { ?>
	rsmembership_submit_form_ajax('share.addmembershiparticles');
	<?php } else { ?>
	rsmembership_submit_form_ajax('share.addextravaluearticles');
	<?php } ?>

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
		
		params.push(document.adminForm.elements[i].name + '=' + document.adminForm.elements[i].value);
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

<form action="<?php echo JRoute::_('index.php?option=com_rsmembership&view=share&layout=article&tmpl=component&'.$this->what.'='.$this->id); ?>" method="post" name="adminForm" id="adminForm" class="form form-horizontal">
	<button type="button" class="btn btn-small" onclick="document.location = '<?php echo JRoute::_('index.php?option=com_rsmembership&view=share&'.$this->what.'='.$this->id.'&tmpl=component'); ?>'"><?php echo JText::_('Back'); ?></button>
	<button type="button" id="membership_save_button" class="btn btn-small btn-info" onclick="if(document.adminForm.boxchecked.value==0){alert('<?php echo JText::sprintf('COM_RSMEMBERSHIP_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST_TO', JText::_('COM_RSMEMBERSHIP_ADD')); ?>');}else{submitbutton()}"><?php echo JText::_('COM_RSMEMBERSHIP_ADD_SELECTED_ITEMS'); ?></button>
	<br /><br />
	<table class="adminform">
		<tr>
			<td width="100%">
			<?php echo JText::_( 'SEARCH' ); ?>
			<input type="text" name="search" id="search" value="<?php echo $this->filter_word; ?>" class="text_area input input-normal" onChange="document.adminForm.submit();" />
			<button onclick="this.form.submit();" class="btn btn-medium"><i class="icon-search"></i><?php echo ( !RSMembershipHelper::isJ3() ? JText::_( 'Go' ) : '' ); ?></button>
			<button onclick="this.form.getElementById('search').value='';this.form.submit();" class="btn btn-medium btn-warning" ><i class="icon-remove"></i><?php echo ( !RSMembershipHelper::isJ3() ? JText::_( 'Reset' ) : '' ); ?></button>
			</td>
			<td nowrap="nowrap"></td>
		</tr>
	</table>
	<div id="editcell1">
		<table class="adminlist table table-striped">
			<thead>
			<tr>
				<th width="5"><?php echo JText::_( '#' ); ?></th>
				<th width="20"><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);"/></th>
				<th><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_TITLE', 'title', $this->sortOrder, $this->sortColumn); ?></th>
				<th><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_CATEGORY', 'categorytitle', $this->sortOrder, $this->sortColumn); ?></th>
				<th width="5"><?php echo JHTML::_('grid.sort', 'JGRID_HEADING_ID', 'id', $this->sortOrder, $this->sortColumn); ?></th>
			</tr>
			</thead>
	<?php
	$k = 0;
	$i = 0;
	$n = count($this->items);
	if ( !empty($this->items) ) 
	{
		foreach ( $this->items as $row ) 
		{
	?>
		<tr class="row<?php echo $k; ?>">
			<td><?php echo $this->pagination->getRowOffset($i); ?></td>
			<td><?php echo JHTML::_('grid.id', $i, $row->id); ?></td>
			<td><?php echo $this->escape($row->title); ?></td>
			<td><?php echo $this->escape($row->categorytitle); ?></td>
			<td><?php echo $row->id; ?></td>
		</tr>
	<?php
		$i++;
		$k=1-$k;
		}
	}
	?>
		<tfoot>
			<tr>
				<td colspan="6"><?php echo $this->pagination->getListFooter(); ?></td>
			</tr>
		</tfoot>
		</table>
	</div>
	
	<?php echo JHTML::_( 'form.token' ); ?>
	
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="<?php echo $this->what; ?>" value="<?php echo $this->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortOrder; ?>" />
</form>

<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
?>