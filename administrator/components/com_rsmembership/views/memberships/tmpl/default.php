<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
$saveOrder	= $listOrder == 'm.ordering';

if (RSMembershipHelper::isJ3() && $saveOrder) 
	JHtml::_('sortablelist.sortable', 'sortTable', 'adminForm', strtolower($listDirn), 'index.php?option=com_rsmembership&task=memberships.saveOrderAjax&tmpl=component');

JHtml::_('behavior.framework');
?>

<form action="<?php echo JRoute::_('index.php?option=com_rsmembership&view=memberships'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<?php echo $this->filterbar->show(); ?>
		<table class="adminlist table table-striped" id="sortTable">
			<thead>
			<tr>
				<th width="5"><?php echo JText::_( '#' ); ?></th>
				<?php echo $this->ordering->showHead($listDirn, $listOrder, 'm.ordering', array('items' => $this->items, 'saveTask' => 'memberships.saveorder')); ?>
				<th width="1%" nowrap="nowrap"><input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" /></th>
				<th width="1%"><?php echo JHTML::_('grid.sort', 'JGRID_HEADING_ID', 'm.id', $listDirn, $listOrder); ?></th>
				<th><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_MEMBERSHIP', 'm.name', $listDirn, $listOrder); ?></th>
				<th><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_CATEGORY_NAME', 'category_name', $listDirn, $listOrder); ?></th>
				<th><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_MEMBERSHIP_LENGTH', 'm.period_type', $listDirn, $listOrder); ?></th>
				<th><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_MEMBERSHIP_PRICE', 'm.price', $listDirn, $listOrder); ?></th>
				<th width="1%" nowrap="nowrap"><?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'm.published', $listDirn, $listOrder); ?></th>
			</tr>
			</thead>
	<?php foreach ($this->items as $i => $item) { ?>
			<tr class="row<?php echo $i % 2; ?>">
				<td width="1%" nowrap="nowrap"><?php echo $this->pagination->getRowOffset($i); ?></td>
				<?php $this->ordering->showRow($saveOrder, $item->ordering, array('context' => 'memberships', 'pagination' => $this->pagination, 'listDirn' => $listDirn, 'i' => $i)); ?>
				<td width="1%" nowrap="nowrap"><?php echo JHTML::_('grid.id', $i, $item->id); ?></td>
				<td width="1%" nowrap="nowrap"><?php echo $item->id; ?></td>
				<td><a href="<?php echo JRoute::_('index.php?option=com_rsmembership&task=membership.edit&id='.$item->id); ?>"><?php echo $item->name != '' ? $this->escape($item->name) : '<em>'.JText::_('COM_RSMEMBERSHIP_NO_TITLE').'</em>'; ?></a>
				<a class="btn btn-small" href="<?php echo JRoute::_('index.php?option=com_rsmembership&view=membership_fields&filter_membership_id='.(int) $item->id); ?>"><small>[<?php echo JText::_('COM_RSMEMBERSHIP_FIELDS'); ?>]</small></a>
				</td>
				<td><?php echo $item->category_id ? $this->escape($item->category_name) : '<em>'.JText::_('COM_RSMEMBERSHIP_NO_CATEGORY').'</em>'; ?></td>
				<td>
					<?php if ($item->fixed_expiry) { ?>
						<?php echo JText::_('COM_RSMEMBERSHIP_FIXED_EXPIRY'); ?>
					<?php } else { ?>
						<?php if (!empty($item->period)) { ?>
							<?php echo $item->period; ?> <?php echo $item->period_type; ?>
						<?php } else { ?>
							<?php echo JText::_('COM_RSMEMBERSHIP_UNLIMITED'); ?>
						<?php } ?>
					<?php } ?>
				</td>
				<td><?php echo RSMembershipHelper::getPriceFormat($item->price); ?></td>
				<td width="1%" nowrap="nowrap" align="center">
				<?php echo JHtml::_('jgrid.published', $item->published, $i, 'memberships.');?>
				</td>
			</tr>
	<?php
	}
	?>
			<tr><td colspan="9" align="center" class="center"><?php echo $this->pagination->getListFooter(); ?></td></tr>
		</table>

		<?php echo JHTML::_( 'form.token' ); ?>
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="task" value="" />
		<?php if (!RSMembershipHelper::isJ3()) { ?>
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php } ?>
	</div>
</form>