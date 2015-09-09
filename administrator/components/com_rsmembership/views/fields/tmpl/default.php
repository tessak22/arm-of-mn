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

if ( RSMembershipHelper::isJ3() && $saveOrder ) 
	JHtml::_('sortablelist.sortable', 'sortTable', 'adminForm', strtolower($listDirn), 'index.php?option=com_rsmembership&task=fields.saveOrderAjax&tmpl=component');

JHtml::_('behavior.framework');
JError::raiseNotice(500, JText::_('COM_RSMEMBERSHIP_FIELD_TRANSLATE'));
?>
<form action="<?php echo JRoute::_('index.php?option=com_rsmembership&view=fields'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<?php echo $this->filterbar->show(); ?>
		<table class="adminlist table table-striped" id="sortTable">
			<thead>
				<tr>
					<th width="5"><?php echo JText::_( '#' ); ?></th>
					<?php echo $this->ordering->showHead($listDirn, $listOrder, 'ordering', array('items' => $this->items, 'saveTask' => 'fields.saveorder')); ?>
					<th width="20"><input type="checkbox" name="checkall-toggle" value="" onclick="Joomla.checkAll(this);"/></th>
					<th><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_FIELD', 'name', $listDirn, $listOrder); ?></th>
					<th><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_LABEL', 'label', $listDirn, $listOrder); ?></th>
					<th><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_TYPE', 'type', $listDirn, $listOrder); ?></th>
					<th><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_VALIDATION_RULE', 'rule', $listDirn, $listOrder); ?></th>
					<th width="80"><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_REQUIRED', 'required', $listDirn, $listOrder); ?></th>
					<th width="160"><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_SHOW_IN_SUBSCRIBERS', 'showfield', $listDirn, $listOrder); ?></th>
					<th width="80"><?php echo JHTML::_('grid.sort', 'JPUBLISHED', 'published', $listDirn, $listOrder); ?></th>
				</tr>
			</thead>
			<?php
			$k = 0;
			foreach ($this->items as $i => $row)
			{
			?>
				<tr class="row<?php echo $k; ?>">
					<td><?php echo $this->pagination->getRowOffset($i); ?></td>
					<?php $this->ordering->showRow($saveOrder, $row->ordering, array('context' => 'fields', 'pagination' => $this->pagination, 'listDirn' => $listDirn, 'i' => $i)); ?>
					<td><?php echo JHTML::_('grid.id', $i, $row->id); ?></td>
					<td><a href="<?php echo JRoute::_('index.php?option=com_rsmembership&task=field.edit&id='.$row->id); ?>"><?php echo $row->name != '' ? $this->escape($row->name) : JText::_('COM_RSMEMBERSHIP_NO_TITLE'); ?></a></td>
					<td><a href="<?php echo JRoute::_('index.php?option=com_rsmembership&task=field.edit&id='.$row->id); ?>"><?php echo $row->label != '' ? JText::_($row->label) : JText::_('COM_RSMEMBERSHIP_NO_TITLE'); ?></a></td>
					<td><?php echo JText::_('COM_RSMEMBERSHIP_'.strtoupper($this->escape($row->type))); ?></td>
					<td><?php echo !empty($row->rule) ? $this->escape($row->rule) : '<em>'.JText::_('NONE').'</em>'; ?></td>
					<td align="center" class="center">
					<?php 
						echo JHtml::_('jgrid.state', array(
									0 => array('setrequired', 'JYES', '', '', false, 'unpublish', 'unpublish'),
									1 => array('unsetrequired', 'JNO', '', '', false, 'publish', 'publish')
									), $row->required, $i, 'fields.');
					?>
					</td>
					<td align="center" class="center">
					<?php 
						echo JHtml::_('jgrid.state', array(
									0 => array('showinsubscribers', 'JYES', '', '', false, 'unpublish', 'unpublish'),
									1 => array('hideinsubscribers', 'JNO', '', '', false, 'publish', 'publish')
									), $row->showinsubscribers, $i, 'fields.');
					?>
					</td>
					<td width="1%" nowrap="nowrap" align="center"><?php echo JHtml::_('jgrid.published', $row->published, $i, 'fields.');?></td>
				</tr>
			<?php
				$k=1-$k;
			}
			?>
			<tfoot>
				<tr>
					<td colspan="10"><?php echo $this->pagination->getListFooter(); ?></td>
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