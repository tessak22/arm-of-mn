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
Joomla.tableOrdering = function(order, dir, task) 
{
	var form = document.adminForm;

	form.filter_order.value		= order;
	form.filter_order_Dir.value	= dir;
	form.submit(task);
}
</script>

<div class="item-page">
	<?php if ($this->params->get('show_page_heading', 1)) { ?>
	<div class="page-header">
		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	</div>
	<?php } ?>

	<form action="<?php echo JRoute::_( RSMembershipRoute::Memberships( JFactory::getApplication()->input->get('catid', 0, 'int'), $this->Itemid , 'list') ); ?>" method="post" name="adminForm" id="rsm_rsmembership_form_list">
	<table width="99%" class="table<?php echo $this->escape($this->params->get('pageclass_sfx')); ?> table-stripped table-bordered table-hover">
	<?php if ($this->params->get('show_headings', 1)) { ?>
		<tr>
			<th class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>" align="right" width="5%"><?php echo JText::_('#'); ?></th>
			<th class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>"><?php echo JHTML::_('grid.sort',  JText::_('COM_RSMEMBERSHIP_MEMBERSHIP'), 'm.name', $listDirn, $listOrder); ?></th>
			<?php if ($this->params->get('show_category', 0)) { ?>
			<th class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>"><?php echo JHTML::_('grid.sort',  JText::_('COM_RSMEMBERSHIP_CATEGORY'), 'c.name', $listDirn, $listOrder); ?></th>
			<?php } ?>
			<th class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>"><?php echo JHTML::_('grid.sort',  JText::_('COM_RSMEMBERSHIP_PRICE'), 'price', $listDirn, $listOrder); ?></th>
		</tr>
	<?php } ?>

	<?php $k = 1; ?>
	<?php $i = 0; ?>
	<?php foreach ($this->items as $item) {
		$catid = $item->category_id ? '&catid='.$item->category_id.':'.JFilterOutput::stringURLSafe($item->category_name) : ''; ?>
		<tr class="sectiontableentry<?php echo $k . $this->escape($this->params->get('pageclass_sfx')); ?>" >
			<td align="right"><?php echo $this->pagination->getRowOffset($i); ?></td>
			<td><a href="<?php echo JRoute::_(RSMembershipRoute::Membership($item->id, $this->Itemid)); ?>"><?php echo $this->escape($item->name); ?></a></td>
			<?php if ($this->params->get('show_category', 0)) { ?>
			<td><?php echo $item->category_id ? $item->category_name : JText::_('COM_RSMEMBERSHIP_NO_CATEGORY'); ?></td>
			<?php } ?>
			<td><?php echo RSMembershipHelper::getPriceFormat($item->price); ?></td>
		</tr>
	<?php $k = $k == 1 ? 2 : 1; ?>
	<?php $i++; ?>
	<?php } ?>
	<?php if ($this->params->get('show_pagination', 0) && $this->pagination->get('pages.total') > 1) { ?>
		<tr><td colspan="4" align="center" class="center"><?php echo $this->pagination->getListFooter(); ?></td></tr>
	<?php } ?>
	</table>

	<input type="hidden" name="filter_order" value="" />
	<input type="hidden" name="filter_order_Dir" value="" />
	<input type="hidden" name="limitstart" value="<?php echo $this->limitstart; ?>" />
</form>
</div>