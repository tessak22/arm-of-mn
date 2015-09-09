<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.tooltip');
$css_status = ( $this->membership->status == 0 ? 'success' : ( $this->membership->status == 1 ? 'warning' : 'important' ) );
?>
<div class="item-page" id="rsm_mymembership_container">
	<div class="page-header">
		<h1>
			<span class="rsme_faded"><?php echo JText::_('COM_RSMEMBERSHIP_MEMBERSHIP'); ?>:</span> <?php echo $this->escape($this->membership->name); ?>
			<?php if ($this->params->get('show_status', 1)) { ?>
				<sup class="label label-<?php echo $css_status; ?>"><?php echo JText::_('COM_RSMEMBERSHIP_STATUS_'.$this->membership->status); ?></sup>
			<?php } ?>
		</h1>
	</div>

	<form method="post" action="<?php echo JRoute::_('index.php?option=com_rsmembership&task=upgrade&cid='.$this->membership->id); ?>" name="membershipForm" id="rsm_membership_form">
		<!-- Membership Info -->
		<div id="rsme_membership_info">
			<!-- Membership Start - End -->
			<?php if ($this->params->get('show_expire', 1)) { ?>
			<div class="row-fluid">
				<div class="span6 pull-left">
					<span class="pull-left rsme_faded"><?php echo JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_START'); ?>: </span> 
					<strong><?php echo RSMembershipHelper::showDate($this->membership->membership_start); ?></strong>
				</div>
				<div class="span6 pull-left">	
					<span class="pull-left rsme_faded"><?php echo JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_END'); ?>: </span> 
					<strong><?php echo $this->membership->membership_end != '0000-00-00 00:00:00' ? RSMembershipHelper::showDate($this->membership->membership_end) : JText::_('COM_RSMEMBERSHIP_UNLIMITED'); ?></strong>
				</div>
			</div>
			<?php } ?>
			<!-- Membership Renew -->
			<div class="row-fluid">
			<?php if (!$this->membership->no_renew) { ?>
					<div class="pull-left span6">
						<span class="rsme_faded rsme_vcenter"><?php echo JText::_('COM_RSMEMBERSHIP_RENEW'); ?>: </span>

						<?php if ($this->params->get('show_price', 1)) { ?>
							<div id="rsme_renewal_price" class="rsme_vcenter">
							<?php if ( $this->membership->use_renewal_price ) { ?>
								<?php echo  RSMembershipHelper::getPriceFormat($this->membership->renewal_price); ?>
							<?php } else { ?>
								<?php echo  RSMembershipHelper::getPriceFormat($this->membership->price); ?>
							<?php } ?>
							</div>
						<?php } ?>

						<?php $renew_link = JRoute::_('index.php?option=com_rsmembership&task=renew&cid='.$this->membership->id.':'.JFilterOutput::stringURLSafe($this->membership->name)); ?>
						<?php if ($this->membership->status == 2 || $this->membership->status == 3) { ?>
								<a class="btn btn-small btn-success rsme_vcenter" href="<?php echo $renew_link; ?>"><i class="icon-white icon-refresh"></i> <?php echo JText::_('COM_RSMEMBERSHIP_RENEW'); ?></a>
						<?php } elseif ($this->membership->status == 0) { ?>
								<a class="btn btn-small btn-success rsme_vcenter" href="<?php echo $renew_link; ?>"><i class="icon-white icon-refresh"></i> <?php echo JText::_('COM_RSMEMBERSHIP_RENEW_IN_ADVANCE'); ?></a>
						<?php } ?>
					</div>
			<?php } ?>

			<!-- Membership Cancel Subscriptions -->
			<?php if ($this->params->get('show_cancel_subscription', 1)) { ?>
				<?php if ($this->membership->status == 0) { ?>
					<div class="pull-right span6">
						<a class="btn btn-small btn-danger pull-left rsme_vcenter" onclick="return confirm('<?php echo JText::_('COM_RSMEMBERSHIP_CONFIRM_CANCEL'); ?>')" href="<?php echo JRoute::_('index.php?option=com_rsmembership&task=cancel&cid='.$this->membership->id); ?>"><i class="icon-white icon-trash"></i> <?php echo JText::_('COM_RSMEMBERSHIP_CANCEL'); ?></a>
					</div>
				<?php } ?>
			<?php } ?>
			</div>
			
			<!-- Membership Fields -->
			<?php if (count($this->membership_fields)) { ?>
				<div class="row-fluid">
					<h3 class="page-header"><?php echo JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_INFORMATION'); ?></h3>
						<table cellpadding="0" cellspacing="0" border="0" width="100%" class="rsmembership_show_table">
						<?php foreach ($this->membership_fields as $field) {  
							$hidden = (isset($field[2]) && $field[2] == 'hidden') ? true : false;
						?>
						<tr<?php echo ($hidden ? ' style="display:none"':'')?>>
							<td width="30%" height="40"><?php echo $field[0]; ?></td>
							<td><?php echo $field[1]; ?></td>
						</tr>
						<?php } ?>
						</table>
				</div>
			<?php } ?>
			<!-- Membership Upgrade -->
			<?php if ($this->has_upgrades && $this->membership->status == MEMBERSHIP_STATUS_ACTIVE) { ?>
			<div class="row-fluid">
				<div class="pull-left span6" id="rsme_upgrade_box">
					<span class="rsme_vcenter rsme_faded"><label for="to_id"><?php echo JText::_('COM_RSMEMBRSHIP_UPGRADE_TO'); ?></label></span>
					<span class="rsme_vcenter"><?php echo $this->lists['upgrades']; ?> </span>
					<span class="rsme_vcenter"><button type="submit" class="btn btn-success"><?php echo JText::_('COM_RSMEMBERSHIP_UPGRADE'); ?></button></span>
				</div>
			</div>
			<?php } ?>

		<!-- Bought Extras -->
		<?php if (!empty($this->boughtextras)) { ?>
			<div class="row-fluid rsme_extrab_container">
				<div class="span12">
					<p class="lead"><?php echo JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_EXTRA_BOUGHT'); ?></p>
					<div class="pull-left">
						<?php foreach ($this->boughtextras as $bought_extra) { ?>
							<?php foreach ($bought_extra as $id => $extraname) { ?>
								<span class="label label-success"><i class="icon-white icon-check icon-ok"></i> <?php echo $extraname; ?></span>
							<?php } ?>
						<?php } ?>
					</div>
				</div>
			</div>
		<?php } ?>
		<!-- Available Extras -->
		<?php if (!empty($this->extras)) { ?>
			<div class="row-fluid rsme_extra_container">
				<div class="span12">
					<p class="lead"><?php echo JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_EXTRA'); ?>:</p>
					<div>
					<?php foreach ( $this->extras as $extra ) { ?>
						<?php if ( $extra->type != 'checkbox' && isset($this->boughtextras[$extra->extra_id]) ) continue; ?>
							 <a class="btn btn-small rsme_extra_btn" href="<?php echo JRoute::_('index.php?option=com_rsmembership&task=addextra&cid='.$this->membership->id.':'.JFilterOutput::stringURLSafe($this->membership->name).'&extra_id='.$extra->id); ?>"><span class="icon icon-plus"></span> <?php echo JText::sprintf('COM_RSMEMBERSHIP_PURCHASE_EXTRA', $extra->name); ?></a>
					<?php } ?>
					</div>
				</div>
			</div>
		<?php } ?>
		
		<!-- Terms & Conditions -->
		<?php if (!empty($this->membershipterms->id)) { ?>
			<p class="lead"><?php echo JText::_('COM_RSMEMBERSHIP_TERM'); ?></p>
			<a class="btn btn-info" href="<?php echo JRoute::_('index.php?option=com_rsmembership&view=terms&cid='.$this->membershipterms->id.':'.JFilterOutput::stringURLSafe($this->membershipterms->name)); ?>"><i class="icon-white icon-eye"></i> <?php echo $this->membershipterms->name; ?></a>
		<?php } ?>
		<!-- Not active reason -->
		<?php if ($this->membership->status > 0) { ?>
		<p><?php echo JText::sprintf('COM_RSMEMBERSHIP_NOT_ACTIVE', JText::_('COM_RSMEMBERSHIP_STATUS_'.$this->membership->status)); ?></p>
		<?php } ?>
		<div class="clearfix"></div>

		<?php if ($this->previous !== false || !empty($this->folders) || !empty($this->files)) { ?>
		<p class="lead"><?php echo JText::_('COM_RSMEMBERSHIP_FILES_AVAILABLE'); ?></p>
		<table class="table table-striped">
		<?php if ($this->params->get('show_headings', 1)) { ?>
			<tr>
				<th width="1%" class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">&nbsp;</th>
				<th class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>"><?php echo JText::_('COM_RSMEMBERSHIP_NAME'); ?></th>
			</tr>
		<?php } ?>
		<?php if ($this->previous !== false) { ?>
			<tr class="sectiontableentry1<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>" >
				<td align="center" valign="top"><?php echo '<i class="icon-folder-open"></i>'; ?></td>
				<td><a href="<?php echo JRoute::_('index.php?option=com_rsmembership&view=mymembership&cid='.$this->cid.($this->previous != '' ? '&path='.$this->previous.'&from='.$this->from : '')); ?>">..</a></td>
			</tr>
		<?php } ?>
		<?php foreach ($this->folders as $folder) {
			if (RSMembershipHelper::getConfig('trigger_content_plugins') && isset($folder->description)) {
				$folder->description = JHtml::_('content.prepare', $folder->description);
			}
			$image = !empty($folder->thumb) ? '<img src="'.JURI::root().'components/com_rsmembership/assets/thumbs/files/'.$folder->thumb.'" width="'.$folder->thumb_w.'" />' : '<i class="icon-folder-close"></i>';
			?>
			<tr class="sectiontableentry1<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>" >
				<td align="center" valign="top"><?php echo $image; ?></td>
				<td><a href="<?php echo JRoute::_('index.php?option=com_rsmembership&view=mymembership&cid='.$this->cid.'&path='.$folder->fullpath.'&from='.$folder->from); ?>"><?php echo !empty($folder->name) ? $folder->name : $folder->fullpath; ?></a><?php if (!empty($folder->description)) { ?><p><?php echo $folder->description; ?></p><?php } ?></td>
			</tr>
		<?php } ?>
		<?php foreach ($this->files as $file) {
			if (RSMembershipHelper::getConfig('trigger_content_plugins') && isset($file->description)) {
				$file->description = JHtml::_('content.prepare', $file->description);
			}
			$image = !empty($file->thumb) ? '<img src="'.JURI::root().'components/com_rsmembership/assets/thumbs/files/'.$file->thumb.'" width="'.$file->thumb_w.'" />' : '<i class="icon-file"></i>'; ?>
			<tr class="sectiontableentry1<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>" >
				<td align="center" valign="top"><?php echo $image; ?></td>
				<td><a href="<?php echo JRoute::_('index.php?option=com_rsmembership&task=download&cid='.$this->cid.'&path='.$file->fullpath.'&from='.$file->from); ?>"><?php echo !empty($file->name) ? $file->name : $file->fullpath; ?></a><?php if (!empty($file->description)) { ?><p><?php echo $file->description; ?></p><?php } ?></td>
			</tr>
		<?php } ?>
		</table>
		<?php } ?>

		</div>
		<?php echo JHtml::_('form.token'); ?>
		<input type="hidden" name="option" value="com_rsmembership" />
		<input type="hidden" name="view" value="mymembership" />
		<input type="hidden" name="task" value="upgrade" />
		<input type="hidden" name="cid" value="<?php echo $this->membership->id; ?>" />
	</form>
</div>