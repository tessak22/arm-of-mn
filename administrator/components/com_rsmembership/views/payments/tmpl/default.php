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

if (RSMembershipHelper::isJ3() && $saveOrder) 
	JHtml::_('sortablelist.sortable', 'sortTable', 'adminForm', strtolower($listDirn), 'index.php?option=com_rsmembership&task=payments.saveOrderAjax&tmpl=component');

JError::raiseNotice(500, JText::_('COM_RSMEMBERSHIP_PAYMENT_TRANSLATE'));	
?>
	<form action="<?php echo JRoute::_('index.php?option=com_rsmembership&view=payments'); ?>" method="post" name="adminForm" id="adminForm">
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
			<?php echo $this->filterbar->show(); ?>
			<table class="adminlist table table-striped" id="sortTable">
				<thead>
					<tr>
						<th width="5"><?php echo JText::_( '#' ); ?></th>
						<?php echo $this->ordering->showHead($listDirn, $listOrder, 'ordering', array('items' => $this->payments, 'saveTask' => 'payments.saveorder')); ?>
						<th><?php echo JText::_('COM_RSMEMBERSHIP_PAYMENT_TYPE'); ?></th>
						<th><?php echo JText::_('COM_RSMEMBERSHIP_PAYMENT_LIMITATIONS'); ?></th>
						<th width="1"><?php echo JText::_('COM_RSMEMBERSHIP_CONFIGURE'); ?></th>
						<th width="1"><?php echo JText::_('JPUBLISHED'); ?></th>
					</tr>
				</thead>
			<?php
			$k = 0;
			$i = 0;
			$j = 0;
			$n = count($this->payments);
			foreach ($this->payments as $row) 
			{
				$is_wire = isset($row->id);
				if ($is_wire) {
				$link = JRoute::_('index.php?option=com_rsmembership&task=payment.edit&id='.$row->id);
				?>
				<tr class="row<?php echo $k; ?>">
					<td align="center"><?php echo JHTML::_('grid.id', $j, $row->id); ?></td>
					<?php $this->ordering->showRow($saveOrder, $row->ordering, array('context' => 'payments', 'pagination' => $this->pagination, 'listDirn' => $listDirn, 'i' => $i)); ?>
					<td><a href="<?php echo $link; ?>"><?php echo $this->getTranslation($this->escape($row->name)); ?></a></td>
					<td>&nbsp;</td>
					<td align="center"><a href="<?php echo $link; ?>"><?php echo JHTML::image('administrator/components/com_rsmembership/assets/images/config.png', JText::_('COM_RSMEMBERSHIP_CONFIGURE')); ?></a></td>
					<td width="1%" nowrap="nowrap" align="center"><?php echo JHtml::_('jgrid.published', $row->published, $i, 'payments.');?></td>
				</tr>
				<?php $j++; 
				} else {
				$link = JRoute::_('index.php?option=com_plugins&task=plugin.edit&extension_id='.$row->cid);
				?>
				<tr class="row<?php echo $k; ?>">
					<td>&nbsp;</td>
					<td align="center"></td>
					<td><a href="<?php echo $link; ?>"><?php echo $this->escape($row->name); ?></a></td>
					<td><?php echo $row->limitations; ?></td>
					<td align="center"><a href="<?php echo $link; ?>"><?php echo JHTML::image('administrator/components/com_rsmembership/assets/images/config.png', JText::_('COM_RSMEMBERSHIP_CONFIGURE')); ?></a></td>
					<td>&nbsp;</td>
				</tr>
			<?php
				}
				$i++;
				$k=1-$k;
			}
			?>
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