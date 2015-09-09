<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.modal');
?>
<div id="addmemberships_ajax">
	<div class="button2-left">
		<div class="blank">
			<a class="modal btn btn-success" title="<?php echo JText::_('COM_RSMEMBERSHIP_NEW_MEMBERSHIP');?>" href="<?php echo JRoute::_('index.php?option=com_rsmembership&task=membership_subscriber.add&user_id='.$this->item->user_id.'&tmpl=component'); ?>" rel="{handler: 'iframe', size: {x: 800, y: 600}}" id="valuesChange"><?php echo JText::_('COM_RSMEMBERSHIP_NEW_MEMBERSHIP'); ?></a>
		</div>

	</div>
	<span class="rsmembership_clear"></span>
	<table class="adminlist table table-striped" id="addmemberships">
		<thead>
		<tr>
			<th width="5"><?php echo JText::_( '#' ); ?></th>
			<th width="20"><?php echo JText::_('Delete'); ?></th>
			<th><?php echo JText::_('COM_RSMEMBERSHIP_MEMBERSHIP'); ?></th>
			<th><?php echo JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_PRICE'); ?></th>
			<th><?php echo JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_START'); ?></th>
			<th><?php echo JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_END'); ?></th>
			<th colspan="2"><?php echo JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_STATUS'); ?></th>
			<th width="80"><?php echo JText::_('JPUBLISHED'); ?></th>
		</tr>
		</thead>
		<?php
		$k = 0;
		foreach ($this->item->memberships as $i => $row)
		{
			if ($row->status == 0) // active
				$image = 'images/legacy/publish_g.png';
			elseif ($row->status == 1) // pending
				$image = 'images/legacy/publish_y.png';
			elseif ($row->status == 2) // expired
				$image = 'images/legacy/publish_r.png';
			elseif ($row->status == 3) // cancelled
				$image = 'images/legacy/publish_x.png';
		?>
			<tr class="row<?php echo $k; ?>">
				<td><?php echo $i+1; ?></td>
				<td align="center">
				<a class="delete-item" onclick="return confirm('<?php echo JText::_('COM_RSMEMBERSHIP_CONFIRM_DELETE'); ?>')" href="<?php echo JRoute::_('index.php?option=com_rsmembership&task=membership_subscriber.remove&cids[]='.$row->id.'&'.JSession::getFormToken().'=1&user_id='.$row->user_id.'&tabposition=1'); ?>"><?php echo JHTML::_('image', 'administrator/components/com_rsmembership/assets/images/remove.png', JText::_('Delete')); ?></a>
				</td>
				<td><a href="<?php echo JRoute::_('index.php?option=com_rsmembership&task=membership_subscriber.edit&tmpl=component&id='.$row->id); ?>" class="modal" rel="{handler: 'iframe', size: {x: 800, y: 600}}"><?php echo $row->name != '' ? $this->escape($row->name) : JText::_('COM_RSMEMBERSHIP_NO_TITLE'); ?></a></td>
				<td>
					<?php echo RSMembershipHelper::getPriceFormat($row->price, $row->currency); ?>
				</td>
				<td><?php echo RSMembershipHelper::showDate( JFactory::getDate($row->membership_start)->toUnix() ); ?></td>
				<td><?php echo $row->membership_end != '0000-00-00 00:00:00' ? RSMembershipHelper::showDate( JFactory::getDate($row->membership_end)->toUnix() ) : JText::_('COM_RSMEMBERSHIP_UNLIMITED'); ?></td>
				<td><?php echo JText::_('COM_RSMEMBERSHIP_STATUS_'.$row->status); ?></td>
				<td align="center"><?php echo JHTML::_('image', JURI::root().'administrator/components/com_rsmembership/assets/'.$image, JText::_('COM_RSMEMBERSHIP_STATUS')); ?></td>
				<td align="center">
					<a href="<?php echo JRoute::_('index.php?option=com_rsmembership&task=membership_subscriber.'.( $row->published ? 'unpublish' : 'publish').'&cids[]='.$row->id.'&'.JSession::getFormToken().'=1&user_id='.$row->user_id.'&tabposition=1'); ?>"><?php echo JHTML::_('image', 'administrator/components/com_rsmembership/assets/images/'.($row->published ? 'tick' : 'disabled').'.png', JText::_(($row->published ? 'JUNPUBLISH' : 'JPUBLISH'))); ?></a>
				</td>
			</tr>
		<?php
			$k=1-$k;
		}
		?>
	</table>
</div>