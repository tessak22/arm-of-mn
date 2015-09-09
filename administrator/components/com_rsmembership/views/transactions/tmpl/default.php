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
<form action="<?php echo JRoute::_('index.php?option=com_rsmembership&view=transactions'); ?>" method="post" name="adminForm" id="adminForm">

	<div id="j-sidebar-container" class="span2"><?php echo $this->sidebar; ?></div>
	<div id="j-main-container" class="span10">
	<?php echo $this->filterbar->show(); ?>
		<table class="table adminlist table-hovered">
			<thead>
				<tr>
					<th width="5">#</th>
					<th width="5"><?php echo JHTML::_('grid.sort', 'JGRID_HEADING_ID','t.id', $listDirn, $listOrder); ?></th>
					<th width="20"><input type="checkbox" name="checkall-toggle" value="" onclick="Joomla.checkAll(this);"/></th>
					<th width="70"><?php echo JText::_('COM_RSMEMBERSHIP_TRANSACTION'); ?></th>
					<th width="140"><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_DATE','t.date', $listDirn, $listOrder); ?></th>
					<th><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_EMAIL','email', $listDirn, $listOrder); ?></th>
					<th width="140">
					<?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_TYPE','t.type', $listDirn, $listOrder); ?>
					</th>
					<th><?php echo JText::_('COM_RSMEMBERSHIP_DETAILS'); ?></th>
					<th><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_PRICE','t.price', $listDirn, $listOrder); ?></th>
					<th><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_MEMBERSHIP_COUPON','t.coupon', $listDirn, $listOrder); ?></th>
					<th><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_STATUS','t.status', $listDirn, $listOrder); ?></th>
					<th><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_GATEWAY','t.gateway', $listDirn, $listOrder); ?></th>
					<th width="110"><?php echo JHTML::_('grid.sort', 'COM_RSMEMBERSHIP_IP','t.ip', $listDirn, $listOrder); ?></th>
					<th><?php echo JText::_('COM_RSMEMBERSHIP_HASH'); ?></th>
				</tr>
			</thead>
		<?php
		$k = 0;
		foreach ($this->items as $i => $row) 
		{
		$css_status = ( $row->status == 'completed' ? 'success' : ( $row->status == 'pending' ? 'warning' : 'error' ) );
		?>
			<tr class="row<?php echo $k; ?> <?php echo $css_status; ?>">
				<td width="1%" nowrap="nowrap"><?php echo $this->pagination->getRowOffset($i); ?></td>
				<td><?php echo $row->id; ?></td>
				<td><?php echo JHTML::_('grid.id', $i, $row->id); ?></td>
				<td><a href="index.php?option=com_rsmembership&task=transaction.edit&id=<?php echo $row->id;?>"><?php echo JText::_('COM_RSMEMBERSHIP_VIEW'); ?></a></td>
				<td width="1%" nowrap="nowrap"><?php echo RSMembershipHelper::showDate($row->date); ?></td>
				<td><?php echo !empty($row->email) ? '<a href="index.php?option=com_rsmembership&task=subscriber.edit&id='.$row->user_id.(!$row->user_id ? '&temp_id='.$row->id : '').'">'.$this->escape($row->email).'</a>' : '<em>'.JText::_('COM_RSMEMBERSHIP_NO_EMAIL').'</em>'; ?></td>
				<td width="1%" nowrap="nowrap"><?php echo JText::_('COM_RSMEMBERSHIP_TRANSACTION_'.strtoupper($row->type)); ?></td>
				<td><?php
				$params = RSMembershipHelper::parseParams($row->params);
				switch ($row->type)
				{
					case 'new':
						if (!empty($params['membership_id']))
							echo isset($this->cache->memberships[$params['membership_id']]) ? $this->cache->memberships[$params['membership_id']] : JText::_('COM_RSMEMBERSHIP_COULD_NOT_FIND_MEMBERSHIP');
						if (!empty($params['extras']))
							foreach ($params['extras'] as $extra)
								if (!empty($extra))
									echo '<br />- '.$this->cache->extra_values[$extra];
					break;
					
					case 'upgrade':
						if (!empty($params['from_id']) && !empty($params['to_id']))
							echo $this->cache->memberships[$params['from_id']].' -&gt; '.$this->cache->memberships[$params['to_id']];
					break;
					
					case 'addextra':
						if (!empty($params['extras']))
							foreach ($params['extras'] as $extra)
								echo $this->cache->extra_values[$extra].'<br />';
					break;
					
					case 'renew':
						if (!empty($params['membership_id']))
							echo $this->cache->memberships[$params['membership_id']];
					break;
				}
				?>
				</td>
				<td class="text-right"><?php echo $this->escape(RSMembershipHelper::getPriceFormat($row->price, $row->currency)); ?></td>
				<td><?php echo strlen($row->coupon) == 0 ? '<em>'.JText::_('COM_RSMEMBERSHIP_NO_COUPON').'</em>' : $this->escape($row->coupon); ?></td>
				<td><?php echo JText::_('COM_RSMEMBERSHIP_TRANSACTION_STATUS_'.strtoupper($row->status)); ?> <a href="<?php echo JRoute::_('index.php?option=com_rsmembership&view=transactions&layout=log&cid='.$row->id.'&tmpl=component'); ?>" class="modal" rel="{handler: 'iframe', size: {x: 660, y: 475}}"><?php echo JText::_('COM_RSMEMBERSHIP_TRANSACTION_VIEW_LOG'); ?></a></td>
				<td><?php echo $this->escape($row->gateway); ?></td>
				<td width="1%" nowrap="nowrap"><?php echo $this->escape($row->ip); ?></td>
				<td><?php echo !strlen($row->hash) ? '<em>'.JText::_('COM_RSMEMBERSHIP_NO_HASH').'</em>' : $this->escape($row->hash); ?></td>
			</tr>
		<?php
			$k=1-$k;
		}
		?>
			<tr><td colspan="14" align="center" class="center"><?php echo $this->pagination->getListFooter(); ?></td></tr>
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