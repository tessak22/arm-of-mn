<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
$saveOrder	= $listOrder == 'date_added';
JHtml::_('behavior.framework');
?>
<form action="<?php echo JRoute::_('index.php?option=com_rsmembership&view=coupons'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<?php echo $this->filterbar->show(); ?>
		<table class="adminlist table table-striped">
			<thead>
			<tr>
				<th width="5"><?php echo JText::_( '#' ); ?></th>
				<th width="20"><input type="checkbox" name="checkall-toggle" value="" onclick="Joomla.checkAll(this);"/></th>
				<th width="120" nowrap="nowrap"><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_DATE_ADDED', 'date_added', $listDirn, $listOrder); ?></th>
				<th><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_COUPON_CODE', 'name', $listDirn, $listOrder); ?></th>
				<th width="120" nowrap="nowrap"><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_FROM', 'date_start', $listDirn, $listOrder); ?></th>
				<th width="120" nowrap="nowrap"><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_TO', 'date_end', $listDirn, $listOrder); ?></th>
				<th width="120" nowrap="nowrap"><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_DISCOUNT', 'discount_type, discount_price', $listDirn, $listOrder); ?></th>
				<th width="80" nowrap="nowrap"><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_PUBLISHED', 'published', $listDirn, $listOrder); ?></th>
			</tr>
			</thead>
			<?php
			$k = 0;
			foreach ($this->items as $i => $row)
			{
			?>
				<tr class="row<?php echo $k; ?>">
					<td><?php echo $this->pagination->getRowOffset($i); ?></td>
					<td><?php echo JHTML::_('grid.id', $i, $row->id); ?></td>
					<td nowrap="nowrap"><?php echo RSMembershipHelper::showDate($row->date_added); ?></td>
					<td><a href="<?php echo JRoute::_('index.php?option=com_rsmembership&task=coupon.edit&id='.$row->id); ?>"><?php echo $row->name != '' ? $this->escape($row->name) : JText::_('COM_RSMEMBERSHIP_NO_TITLE'); ?></a></td>
					<td nowrap="nowrap"><?php echo $row->date_start != '0000-00-00 00:00:00' ? RSMembershipHelper::showDate($row->date_start) : '-'; ?></td>
					<td nowrap="nowrap"><?php echo $row->date_end != '0000-00-00 00:00:00' ? RSMembershipHelper::showDate($row->date_end) : '-'; ?></td>
					<td><?php echo $row->discount_type ? RSMembershipHelper::getPriceFormat($this->escape($row->discount_price)) : $this->escape($row->discount_price).'%'; ?></td>
					<td width="1%" nowrap="nowrap" align="center"><?php echo JHtml::_('jgrid.published', $row->published, $i, 'coupons.');?></td>
				</tr>
			<?php
				$k=1-$k;
			}
			?>
			<tfoot>
				<tr>
					<td colspan="9"><?php echo $this->pagination->getListFooter(); ?></td>
				</tr>
			</tfoot>
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