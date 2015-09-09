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

JHtml::_('behavior.framework');
?>

<form action="<?php echo JRoute::_('index.php?option=com_rsmembership&view=subscribers'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<div class="com-rsmembership-progress" id="com-rsmembership-joomla-configuration-progress" style="display:none"><div class="com-rsmembership-bar" style="width: 0%;">0%</div></div>
		<?php echo $this->filterbar->show(); ?>
		<table class="adminlist table table-striped">
			<thead>
				<tr>
					<th width="5"><?php echo JText::_( '#' ); ?></th>
					<th width="20"><input type="checkbox" name="checkall-toggle" value="" onclick="Joomla.checkAll(this);"/></th>
					<th width="70"><?php echo JHtml::_('grid.sort', 'COM_RSMEMBERSHIP_SUBSCRIBER_ID', 'mu.user_id', $listDirn, $listOrder); ?></th>
					<th width="20"><?php echo JText::_('COM_RSMEMBERSHIP_ENABLED'); ?></th>
					<th><?php echo JHtml::_('grid.sort', JText::_('COM_RSMEMBERSHIP_USERNAME'), 'u.username', $listDirn, $listOrder); ?></th>
					<th><?php echo JHtml::_('grid.sort', JText::_('COM_RSMEMBERSHIP_NAME'), 'u.name', $listDirn, $listOrder); ?></th>
					<th><?php echo JHtml::_('grid.sort', JText::_('COM_RSMEMBERSHIP_EMAIL'), 'u.email', $listDirn, $listOrder); ?></th>
					<?php
						if ($this->customFields) {
							foreach ($this->customFields as $id => $properties) { ?>
								<th><?php echo JHtml::_('grid.sort', ($properties->label ? JText::_($properties->label) : JText::_('COM_RSMEMBERSHIP_NO_TITLE')), 'mu.f'.$properties->id, $listDirn, $listOrder); ?></th>
								<?php
							}
						}
					?>
					<th><?php echo JHtml::_('grid.sort', JText::_('COM_RSMEMBERSHIP_ACTIVE_SUBSCRIPTIONS'), 'num_activesubs', $listDirn, $listOrder); ?></th>
					<th><?php echo JHtml::_('grid.sort', JText::_('COM_RSMEMBERSHIP_TOTAL_SUBSCRIPTIONS'), 'num_subs', $listDirn, $listOrder); ?></th>
				</tr>
			</thead>
			<?php
			foreach ( $this->items as $i => $row ) {
			?>
				<tr class="row<?php echo $i%2; ?>">
					<td><?php echo $this->pagination->getRowOffset($i); ?></td>
					<td><?php echo JHtml::_('grid.id', $i, $row->id); ?></td>
					<td><?php echo $row->id; ?></td>
					<td><?php echo JHtml::_('image', 'administrator/components/com_rsmembership/assets/images/'.($row->block ? 'disabled.png' : 'tick.png'), ''); ?></td>
					<td><a href="<?php echo JRoute::_('index.php?option=com_rsmembership&task=subscriber.edit&user_id='.$row->id); ?>"><?php echo $this->escape($row->username); ?></a></td>
					<td><?php echo $this->escape($row->name); ?></td>
					<td><a href="<?php echo JRoute::_('index.php?option=com_rsmembership&task=subscriber.edit&user_id='.$row->id); ?>"><?php echo $this->escape($row->email); ?></a></td>
					<?php
						if ($this->customFields) {
							foreach ( $this->customFields as $properties ) { ?>
								<td><?php echo $this->escape($row->{$properties->name}); ?></td>
							<?php
							}
						}
					?>
					<td><?php echo $row->num_activesubs; ?></td>
					<td><?php echo $row->num_subs; ?></td>
				</tr>
			<?php
			}
			?>
				<tr><td colspan="<?php echo count($this->customFields) + 8; ?>" align="center" class="center"><?php echo $this->pagination->getListFooter(); ?></td></tr>
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
	RSMembership.exportCSV.totalItems = <?php echo $this->totalItems;?>;
	RSMembership.exportCSV.view = 'subscribers';

	Joomla.submitbutton = function(pressbutton) {
		if (pressbutton == 'subscribers.exportcsv') {
			RSMembership.exportCSV.setCSV(0,'');
		}
		else 
		{
			Joomla.submitform(pressbutton);
		}
	}
</script>