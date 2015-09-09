<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
$saveOrder	= $listOrder == 'ordering';

$selectedMemberships = $this->state->get($this->context.'.filter.filter_memberships');
$resetSelected 		 = $this->state->get($this->context.'.filter.filter_resetselected');

JHtml::_('behavior.framework');
JHtml::_('behavior.modal');
?>
<script type="text/javascript">
function resetMemberships() {
	var memberships = document.getElementsByName("filter_memberships[]").length;
	for (var i = 0; i < memberships; i++) {
		document.getElementsByName("filter_memberships[]")[i].checked = false; 
	}
	document.getElementById('filter_resetselected').value = 1;
	document.adminForm.submit();
}

function showHideContainer(container, force) {
	var status 		= document.getElementById(container).style.display;
	var newStatus 	= force || status == 'none' ? 'block' : 'none';
	
	document.getElementById(container).style.display = newStatus;
}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_rsmembership&view=subscriptions'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<div class="com-rsmembership-progress" id="com-rsmembership-joomla-configuration-progress" style="display:none"><div class="com-rsmembership-bar" style="width: 0%;">0%</div></div>
		<?php echo $this->filterbar->show(); ?>
		<?php if (count($this->memberships) > 0) {?>
			<div class="rsme_memberships_outercontainer">
				<button class="btn" type="button" onclick="showHideContainer('rsme_memberships',false)" title="<?php echo JText::_('COM_RSMEMBERSHIP_SELECT_MEMBERSHIPS_DESC');?>"><?php echo JText::_('COM_RSMEMBERSHIP_SELECT_MEMBERSHIPS');?></button>
				<div id="rsme_memberships" style="display:none">
					<ul>
						<?php foreach($this->memberships as $membership) { ?>
							<li><input type="checkbox" <?php echo (isset($selectedMemberships) && (in_array($membership->id,$selectedMemberships) && !$resetSelected) ? 'checked="checked"' : '');?> value="<?php echo $membership->id; ?>" id="membership<?php echo $membership->id; ?>" name="filter_memberships[]"/><label for="membership<?php echo $membership->id; ?>"><?php echo $this->escape($membership->name); ?></label></li>
						<?php } ?>
					</ul>
					<button title="<?php echo JText::_('COM_RSMEMBERSHIP_APPLY_FILTER');?>" onclick="document.getElementById('filter_resetselected').value = 0; this.form.submit();" type="button" class="btn"><?php echo JText::_('COM_RSMEMBERSHIP_APPLY_FILTER');?></button>
					<button title="<?php echo JText::_('COM_RSMEMBERSHIP_RESET_MEMBERSHIPS_FILTER');?>" onclick="resetMemberships();" type="button" class="btn"><?php echo JText::_('JSEARCH_RESET');?></button>
					<input type="hidden" id="filter_resetselected" name="filter_resetselected" value="<?php echo ($resetSelected!=null ? $resetSelected : '0')?>"/>
				</div>
			</div>
		<?php } ?>
		<table class="adminlist table table-striped rsme_table_vtop">
			<thead>
				<tr>
					<th width="5"><?php echo JText::_( '#' ); ?></th>
					<th width="20"><input type="checkbox" name="checkall-toggle" value="" onclick="Joomla.checkAll(this);"/></th>
					<th><?php echo JHtml::_('grid.sort', JText::_('COM_RSMEMBERSHIP_MEMBERSHIP'), 'm.name', $listDirn, $listOrder); ?></th>
					<th><?php echo JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_EXTRAS'); ?></th>
					<th><?php echo JHtml::_('grid.sort', JText::_('COM_RSMEMBERSHIP_USERNAME'), 'u.username', $listDirn, $listOrder); ?></th>
					<th><?php echo JHtml::_('grid.sort', JText::_('COM_RSMEMBERSHIP_EMAIL'), 'u.email', $listDirn, $listOrder); ?></th>
					<th><?php echo JHtml::_('grid.sort', JText::_('COM_RSMEMBERSHIP_STATUS'), 'ms.status', $listDirn, $listOrder); ?></th>
					<th><?php echo JHtml::_('grid.sort', JText::_('COM_RSMEMBERSHIP_NOTIFIED'), 'ms.notified', $listDirn, $listOrder); ?></th>
					<th><?php echo JHtml::_('grid.sort', JText::_('COM_RSMEMBERSHIP_START_DATE'), 'ms.membership_start', $listDirn, $listOrder); ?></th>
					<th><?php echo JHtml::_('grid.sort', JText::_('COM_RSMEMBERSHIP_START_END'), 'ms.membership_end', $listDirn, $listOrder); ?></th>
					<th width="80"><?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'ms.published', $listDirn, $listOrder); ?></th>
				</tr>
			</thead>
			<?php
			foreach ( $this->items as $i => $row ) {
			?>
				<tr class="row<?php echo $i%2; ?>">
					<td><?php echo $this->pagination->getRowOffset($i); ?></td>
					<td><?php echo JHtml::_('grid.id', $i, $row->id); ?></td>
					<td><a href="<?php echo JRoute::_('index.php?option=com_rsmembership&task=membership_subscriber.edit&tmpl=component&id='.$row->id); ?>" class="modal" rel="{handler: 'iframe', size: {x: 800, y: 600}}"><?php echo $row->name != '' ? $this->escape($row->name) : JText::_('COM_RSMEMBERSHIP_NO_TITLE'); ?></a></td>
					<td>
					<?php if ($row->extras) {
						$extras = explode(',', $row->extras);
						foreach ($extras as $value) {
							if (isset($this->extraValues[$value])) {
								?>
								<p><?php echo $this->escape($this->extraValues[$value]); ?></p>
								<?php
							}
						}
					} else { ?>
						<em><?php echo JText::_('COM_RSMEMBERSHIP_NONE'); ?></em>
					<?php } ?>	
					</td>
					<td><a href="<?php echo JRoute::_('index.php?option=com_rsmembership&task=subscriber.edit&user_id='.$row->user_id); ?>"><?php echo $this->escape($row->username); ?></a></td>
					<td><a href="<?php echo JRoute::_('index.php?option=com_rsmembership&task=subscriber.edit&user_id='.$row->user_id); ?>"><?php echo $this->escape($row->email); ?></a></td>
					<td><?php echo JText::_('COM_RSMEMBERSHIP_STATUS_'.$row->status); ?></td>
					<td>
						<?php if ($this->isNullDate($row->notified)) { ?>
							<span class="rsme_notified rsme_danger"><?php echo JText::_('COM_RSMEMBERSHIP_NOT_NOTIFIED'); ?></span>
						<?php } else { ?>
							<?php echo RSMembershipHelper::showDate($row->notified); ?>
							<?php if ($row->status == MEMBERSHIP_STATUS_ACTIVE) { ?>
								<button type="button" class="btn btn-primary" onclick="return listItemTask('cb<?php echo $i;?>','subscriptions.notify')"><?php echo JText::_('COM_RSMEMBERSHIP_NOTIFY'); ?></button>
							<?php } ?>
						<?php } ?>
					</td>					
					<td><?php echo $this->isNullDate($row->membership_start) ? '-' : RSMembershipHelper::showDate($row->membership_start); ?></td>
					<td><?php echo $this->isNullDate($row->membership_end) ? JText::_('COM_RSMEMBERSHIP_UNLIMITED') : RSMembershipHelper::showDate($row->membership_end); ?></td>
					<td width="1%" nowrap="nowrap" align="center"><?php echo JHtml::_('jgrid.published', $row->published, $i, 'subscriptions.');?></td>
				</tr>
			<?php
			}
			?>
				<tr><td colspan="11" align="center" class="center"><?php echo $this->pagination->getListFooter(); ?></td></tr>
			</table>

			<?php echo JHtml::_( 'form.token' ); ?>
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="task" value="" />
		<?php if (!RSMembershipHelper::isJ3()) { ?>
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php } ?>
	</div>
</form>
<script type="text/javascript">
<?php if (isset($selectedMemberships) && count($selectedMemberships) > 0 && (is_null($resetSelected) || !$resetSelected)) { ?>
	showHideContainer('rsme_memberships', true);
<?php } ?>

	RSMembership.exportCSV.totalItems = <?php echo $this->totalItems;?>;
	RSMembership.exportCSV.view = 'subscriptions';
	
	Joomla.submitbutton = function(pressbutton) {
		if (pressbutton == 'subscriptions.exportcsv') {
			RSMembership.exportCSV.setCSV(0,'');
		} else {
			Joomla.submitform(pressbutton);
		}
	}
</script>