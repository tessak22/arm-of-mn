<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>
<script language="javascript" type="text/javascript">
function tableOrdering(order, dir, task)
{
	var form = document.adminForm;

	form.filter_order.value	= order;
	form.filter_order_Dir.value	= dir;
	document.adminForm.submit(task);
}
</script>
<div class="item-page">
	<?php if ($this->params->get('show_page_heading', 1)) { ?>
	<div class="page-header">
		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	</div>
	<?php } ?>

	<form action="<?php echo JRoute::_( RSMembershipRoute::Categories('list') ); ?>" method="post" name="adminForm" id="adminForm">
		<table id="sortTable" width="99%" class="<?php echo $this->escape($this->params->get('pageclass_sfx')); ?> table table-stripped table-hover table-bordered">
		<?php if ($this->params->get('show_headings', 1)) { ?>
			<thead>
				<tr>
					<th class="<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>" align="right" width="5%"><?php echo JText::_('#'); ?></th>
					<th class="<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
					<?php echo JText::_('COM_RSMEMBERSHIP_TITLE'); ?>
					</th>
				</tr>
			</thead>
		<?php } ?>
			<?php $k = 1; ?>
			<?php foreach ( $this->items as $i => $item ) { ?>
			<tr class="sectiontableentry<?php echo $k . $this->escape($this->params->get('pageclass_sfx')); ?>" >
				<td align="right"><?php echo $this->pagination->getRowOffset($i); ?></td>
				<td><a href="<?php echo JRoute::_(RSMembershipRoute::Memberships($item->id, $this->Itemid, 'list')); ?>"><?php echo $this->escape($item->name); ?></a><?php if ($this->params->get('show_memberships', 0)) { ?> (<?php echo $item->memberships; ?>)<?php } ?></td>
			</tr>
			<?php $k = $k == 1 ? 2 : 1; ?>
			<?php } ?>
		</table>
		
		<?php if ($this->params->get('show_pagination', 1)) { ?>
		<div class="sectiontablefooter<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>" align="center">
			<div class="pagination">
				<?php echo $this->pagination->getPagesLinks(); ?>
			</div>
			<?php echo $this->pagination->getPagesCounter(); ?>
		</div>
		<?php } ?>

		<input type="hidden" name="filter_order" value="" />
		<input type="hidden" name="filter_order_Dir" value="" />
		<input type="hidden" name="task" value="" />
	
	</form>
</div>