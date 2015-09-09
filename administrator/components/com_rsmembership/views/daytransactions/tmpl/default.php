<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');

JHTML::_('behavior.calendar');
JHTML::_('behavior.modal');
?>
<form action="<?php echo JRoute::_('index.php?option=com_rsmembership&view=sales_report'); ?>" method="post" name="adminForm" id="adminForm">

	<div id="j-sidebar-container" class="span2"><?php echo $this->sidebar; ?></div>
	<div id="j-main-container" class="span10">
	<?php echo $this->filterbar->show(); ?>
		<table class="table adminlist table-hovered">
			<thead>
				<tr>
					<th width="5">#</th>
					<th><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_DATE','date', $listDirn, $listOrder); ?></th>
					<th><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_STATUS','t.status', $listDirn, $listOrder); ?></th>
					<th width="150" class="rsme_align_right"><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_DAYSUM','daysum', $listDirn, $listOrder); ?></th>
					<th width="30" class="rsme_align_right"><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_CURRENCY','t.currency', $listDirn, $listOrder); ?></th>
				</tr>
			</thead>
		<?php
		foreach ($this->items as $i => $row) 
		{
			$css_status = ( $row->status == 'completed' ? 'success' : ( $row->status == 'pending' ? 'warning' : 'error' ) );
		?>
			<tr class="row<?php echo $i%2; ?> <?php echo $css_status; ?>">
				<td width="1%" nowrap="nowrap"><?php echo $this->pagination->getRowOffset($i); ?></td>
				<td nowrap="nowrap"><?php echo JHtml::date($row->date); ?></td>
				<td nowrap="nowrap"><?php echo JText::_('COM_RSMEMBERSHIP_TRANSACTION_STATUS_'.strtoupper($row->status)); ?></td>
				<td nowrap="nowrap" class="rsme_align_right"><?php echo $row->daysum; ?></td>
				<td nowrap="nowrap" class="rsme_align_right"><?php echo $row->currency; ?></td>
			</tr>
		<?php
		}
		?>
			<?php
			if (count($this->total) > 0) {
				foreach($this->total as $total) {
			?>
				<tr>
					<td colspan="3">&nbsp;</td>
					<td align="right" class="rsme_align_right">
						<?php echo JText::_('COM_RSMEMBERSHIP_TRANSACTION_TOTAL_STATUS_'.strtoupper($total->status)).': <strong class="rsme_text_color_'.$total->status.'">'.$total->total.'</strong>'; ?>
					</td>
					<td align="right" class="rsme_align_right">
						<span class="rsme_text_color_<?php echo $total->status; ?>"><?php echo $total->currency;?></span>
					</td>
				</tr>
			<?php
				}
			}
			?>
			<tr><td colspan="5" align="center" class="center"><?php echo $this->pagination->getListFooter(); ?></td></tr>
		</table>
	</div>

		<?php echo JHTML::_( 'form.token' ); ?>
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="task" value="" />
		<?php if ( !RSMembershipHelper::isJ3() ) { ?>
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php } ?>
</form>