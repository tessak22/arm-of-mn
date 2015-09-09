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
	JHtml::_('sortablelist.sortable', 'sortTable', 'adminForm', strtolower($listDirn), 'index.php?option=com_rsmembership&task=categories.saveOrderAjax&tmpl=component');
?>

<form action="<?php echo JRoute::_('index.php?option=com_rsmembership&view=categories'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-sidebar-container" class="span2"><?php echo $this->sidebar; ?></div>
	<div id="j-main-container" class="span10">
		<?php echo $this->filterbar->show(); ?>
		<table class="adminlist table table-striped" id="sortTable">
			<thead>
				<tr>
					<th width="5"><?php echo JText::_( '#' ); ?></th>
					<?php echo $this->ordering->showHead($listDirn, $listOrder, 'm.ordering', array('items' => $this->items, 'saveTask' => 'categories.saveorder')); ?>
					<th width="20"><input type="checkbox" name="checkall-toggle" value="" onclick="Joomla.checkAll(this);"/></th>
					<th width="5"><?php echo JHTML::_('grid.sort', 'JGRID_HEADING_ID', 'id', $listDirn, $listOrder); ?></th>
					<th><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_CATEGORY_NAME', 'name', $listDirn, $listOrder); ?></th>
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
				<?php $this->ordering->showRow($saveOrder, $row->ordering, array('context' => 'categories', 'pagination' => $this->pagination, 'listDirn' => $listDirn, 'i' => $i)); ?>
				<td><?php echo JHTML::_('grid.id', $i, $row->id); ?></td>
				<td><?php echo $row->id; ?></td>
				<td><a href="<?php echo JRoute::_('index.php?option=com_rsmembership&task=category.edit&id='.$row->id); ?>"><?php echo $row->name != '' ? $this->escape($row->name) : JText::_('COM_RSMEMBERSHIP_NO_TITLE'); ?></a></td>
				<td width="1%" nowrap="nowrap" align="center"><?php echo JHtml::_('jgrid.published', $row->published, $i, 'categories.');?></td>
			</tr>
		<?php
			$i++;
			$k=1-$k;
		}
		?>
			<tr><td colspan="6" align="center" class="center"><?php echo $this->pagination->getListFooter(); ?></td></tr>
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