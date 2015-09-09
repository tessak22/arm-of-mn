<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');
?>
<div class="item-page">
	<?php if ($this->params->get('show_page_heading', 1)) { ?>
	<div class="page-header">
		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	</div>
	<?php } ?>

<form action="<?php echo $this->action; ?>" method="post" name="adminForm" id="rsm_mymemberships_form">
<?php if ( !empty($this->items) ) { ?>
<table class="rsmembershiptable <?php echo $this->escape($this->params->get('pageclass_sfx')); ?> table table-stripped table-hovered">
<?php if ($this->params->get('show_headings', 1)) { ?>
<tr>
	<th class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>" align="right" width="5%"><?php echo JText::_('#'); ?></th>
 	<th class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>"><?php echo JText::_('COM_RSMEMBERSHIP_MEMBERSHIP'); ?></th>
	<th class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>"><?php echo JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_START'); ?></th>
	<th class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>"><?php echo JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_END'); ?></th>
	<th class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>"><?php echo JText::_('COM_RSMEMBERSHIP_STATUS'); ?></th>
</tr>
<?php } ?>

<?php $k = 1; ?>
<?php $i = 0; ?>
<?php foreach ($this->items as $item) { 
	$css_status = ( $item->status == 0 ? 'success' : ( $item->status == 1 ? 'warning' : 'error' ) );
?>
<tr class="rsmesectiontableentry<?php echo $k . $this->escape($this->params->get('pageclass_sfx')); ?> <?php echo $css_status;?>" >
	<td align="right"><?php echo $this->pagination->getRowOffset($i); ?></td>
	<td><a href="<?php echo JRoute::_('index.php?option=com_rsmembership&view=mymembership&cid='.$item->id.$this->Itemid); ?>"><?php echo $this->escape($item->name); ?></a></td>
	<td><i class="icon icon-clock"></i> <?php echo RSMembershipHelper::showDate($item->membership_start); ?></td>
	<td><i class="icon icon-clock"></i> <?php echo ( $item->membership_end == '0000-00-00 00:00:00' ? JText::_('COM_RSMEMBERSHIP_UNLIMITED') : RSMembershipHelper::showDate($item->membership_end)); ?></td>
	<td><?php echo JText::_('COM_RSMEMBERSHIP_STATUS_'.$item->status); ?></td>
</tr>
<?php $k = $k == 1 ? 2 : 1; ?>
<?php $i++; ?>
<?php } ?>

<?php if ($this->params->get('show_pagination', 1) && $this->pagination->get('pages.total') > 1) { ?>
<tr>
	<td align="center" colspan="5" class="center pagination sectiontablefooter<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
		<?php echo $this->pagination->getPagesLinks(); ?>
	</td>
</tr>
<tr>
	<td colspan="5" align="right"><?php echo $this->pagination->getPagesCounter(); ?></td>
</tr>
<?php } ?>
</table>
<input type="hidden" name="limitstart" value="<?php echo $this->limitstart; ?>" />

<?php } ?>
</form>

<?php if (!empty($this->transactions)) { ?>
<p><?php echo JText::sprintf('COM_RSMEMBERSHIP_HAVE_PENDING_TRANSACTIONS', count($this->transactions)); ?></p>
<table class="rsmembershiptable <?php echo $this->escape($this->params->get('pageclass_sfx')); ?> table table-stripped table-hovered" id="rsm_transactions_tbl">
<?php if ($this->params->get('show_headings', 1)) { ?>
<tr>
	<th class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>" align="right" width="5%"><?php echo JText::_('#'); ?></th>
 	<th class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>"><?php echo JText::_('COM_RSMEMBERSHIP_TRANSACTION'); ?></th>
	<th class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>"><?php echo JText::_('COM_RSMEMBERSHIP_DATE'); ?></th>
	<th class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>"><?php echo JText::_('COM_RSMEMBERSHIP_PRICE'); ?></th>
	<th class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>"><?php echo JText::_('COM_RSMEMBERSHIP_GATEWAY'); ?></th>
	<th class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>"><?php echo JText::_('COM_RSMEMBERSHIP_STATUS'); ?></th>
</tr>
<?php } ?>
<?php $k = 1; ?>
<?php foreach ($this->transactions as $i => $item) { 
	 $css_status = ( $item->status == 'active' ? 'success' : ( $item->status == 'pending' ? 'warning' : 'error' ) );
?>
<tr class="sectiontableentry<?php echo $k . $this->escape($this->params->get('pageclass_sfx')); ?> <?php echo $css_status;?> " >
	<td align="right"><?php echo $i; ?></td>
	<td><?php echo JText::_('COM_RSMEMBERSHIP_TRANSACTION_'.strtoupper($item->type)); ?></td>
	<td><?php echo RSMembershipHelper::showDate($item->date); ?></td>
	<td><?php echo RSMembershipHelper::getPriceFormat($item->price); ?></td>
	<td><?php echo $item->gateway; ?></td>
	<td><?php echo JText::_('COM_RSMEMBERSHIP_TRANSACTION_STATUS_'.strtoupper($item->status)); ?></td>
</tr>
<?php $k = $k == 1 ? 2 : 1; ?>
<?php $i++; ?>
<?php } ?>
</table>
<?php } ?>
</div>