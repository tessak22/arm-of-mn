<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');
$listOrder = 'ordering';
$listDirn  = 'ASC';
$saveOrder	= true;

if (RSMembershipHelper::isJ3()) 
	JHtml::_('sortablelist.sortable', 'addmembershipshared', 'adminForm', strtolower($listDirn), 'index.php?option=com_rsmembership&task=membership.saveOrderAjax&tmpl=component');
?>

<table class="adminlist table table-striped" id="addmembershipshared">
	<thead>
	<tr>
		<th width="5"><?php echo JText::_( '#' ); ?></th>
		<th width="20">&nbsp;</th>
		<?php echo $this->ordering->showHead($listDirn, $listOrder, 'ordering', array('items' => $this->item->shared, 'saveTask' => 'membership.saveorder')); ?>
		<th width="20"><?php echo JText::_('COM_RSMEMBERSHIP_DELETE'); ?></th>
		<th width="200"><?php echo JText::_('COM_RSMEMBERSHIP_TYPE'); ?></th>
		<th><?php echo JText::_('COM_RSMEMBERSHIP_PARAMS'); ?></th>
		<th width="80"><?php echo JText::_('COM_RSMEMBERSHIP_PUBLISHED'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	$k = 0;
	foreach ($this->item->shared as $i => $row) 
	{
	?>
		<tr class="row<?php echo $k; ?>">
			<td><?php echo $this->sharedPagination->getRowOffset($i); ?></td>
			<td><?php echo JHTML::_('grid.id', $i, $row->id, false, 'cid_folders'); ?></td>
			<?php $this->ordering->showRow($saveOrder, $row->ordering, array('context' => 'membership', 'pagination' => $this->sharedPagination, 'listDirn' => $listDirn, 'i' => $i)); ?>
			<td align="center">
			<a class="delete-item" onclick="return confirm('<?php echo JText::_('COM_RSMEMBERSHIP_CONFIRM_DELETE'); ?>')" href="<?php echo JRoute::_('index.php?option=com_rsmembership&task=membership.foldersremove&cid_folders[]='.$row->id.'&'.JSession::getFormToken().'=1&tabposition=3'); ?>"><?php echo JHTML::_('image', 'administrator/components/com_rsmembership/assets/images/remove.png', JText::_('Delete')); ?></a>
			</td>
			<td><?php echo JText::_('COM_RSMEMBERSHIP_TYPE_'.strtoupper($row->type)); ?></td>
			<td>
			<?php if ($row->type == 'folder') { ?>
				<a href="<?php echo JRoute::_('index.php?option=com_rsmembership&task=file.edit&cid='.urlencode($row->params)); ?>" target="_blank"><?php echo $this->escape($row->params); ?></a>
			<?php } elseif ($row->type == 'backendurl' || $row->type == 'frontendurl') { ?>
				<a class="modal" rel="{handler: 'iframe', size: {x: 660, y: 475}}" href="<?php echo JRoute::_('index.php?option=com_rsmembership&view=share&layout=url&tmpl=component&membership_id='.$this->item->id.'&cid='.$row->id); ?>">index.php?option=<?php echo $this->escape($row->params); ?></a>
			<?php } else { ?>
				<?php echo $this->escape($row->params); ?>
			<?php } ?>
			</td>
			<td align="center"><?php echo JHtml::_('jgrid.published', $row->published, $i, 'membership.folders');?></td>
		</tr>
	<?php
		$k=1-$k;
	}
	?>
	</tbody>
</table>