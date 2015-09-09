<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
JHtml::_('behavior.framework');
?>
<form action="<?php echo JRoute::_('index.php?option=com_rsmembership&view=upgrades'); ?>" method="post" name="adminForm" id="adminForm">
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
				<th><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_UPGRADE', 'from_name', $listDirn, $listOrder); ?></th>
				<th><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_UPGRADE_PRICE', 'price', $listDirn, $listOrder); ?></th>
				<th><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_PUBLISHED', 'published', $listDirn, $listOrder); ?></th>
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
					<td>
						<a href="<?php echo JRoute::_('index.php?option=com_rsmembership&task=upgrade.edit&id='.$row->id); ?>">
							<?php echo JText::_('COM_RSMEMBERSHIP_FROM');?> <?php echo $row->from_name != '' ? $this->escape($row->from_name) : JText::_('COM_RSMEMBERSHIP_NO_TITLE'); ?> 
							<?php echo JText::_('COM_RSMEMBERSHIP_TO');?> <?php echo $row->to_name != '' ? $this->escape($row->to_name) : JText::_('COM_RSMEMBERSHIP_NO_TITLE'); ?>
						</a>
					</td>
					<td>
						<?php echo RSMembershipHelper::getPriceFormat($this->escape($row->price)); ?>
					</td>
					<td width="1%" nowrap="nowrap" align="center"><?php echo JHtml::_('jgrid.published', $row->published, $i, 'upgrades.');?></td>
				</tr>
			<?php
				$k=1-$k;
			}
			?>
			<tfoot>
				<tr>
					<td colspan="6"><?php echo $this->pagination->getListFooter(); ?></td>
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