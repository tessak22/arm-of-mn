<?php
/**
* @package RSEvents!Pro
* @copyright (C) 2015 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');?>
<?php echo 'RS_DELIMITER0'; ?>
<?php if (!empty($this->events)) { ?>
<?php $eventIds = rseventsproHelper::getEventIds($this->events, 'id'); ?>
<?php $this->events = rseventsproHelper::details($eventIds); ?>
<?php foreach($this->events as $details) { ?>
<?php if (isset($details['event']) && !empty($details['event'])) $event = $details['event']; else continue; ?>
<?php $full = rseventsproHelper::eventisfull($event->id); ?>
<?php $ongoing = rseventsproHelper::ongoing($event->id); ?>
<?php $categories = (isset($details['categories']) && !empty($details['categories'])) ? JText::_('COM_RSEVENTSPRO_GLOBAL_CATEGORIES').': '.$details['categories'] : '';  ?>
<?php $tags = (isset($details['tags']) && !empty($details['tags'])) ? JText::_('COM_RSEVENTSPRO_GLOBAL_TAGS').': '.$details['tags'] : '';  ?>
<?php $incomplete = !$event->completed ? ' rs_incomplete' : ''; ?>
<?php $featured = $event->featured ? ' rs_featured' : ''; ?>
<li class="rs_event_detail<?php echo $incomplete.$featured; ?>" id="rs_event<?php echo $event->id; ?>" itemscope itemtype="http://schema.org/Event">
	
	<div class="rs_options" style="display:none;">
		<?php if ((!empty($this->permissions['can_edit_events']) || $event->owner == $this->user || $event->sid == $this->user || $this->admin) && !empty($this->user)) { ?>
			<a href="<?php echo rseventsproHelper::route('index.php?option=com_rseventspro&layout=edit&id='.rseventsproHelper::sef($event->id,$event->name)); ?>">
				<img src="<?php echo JURI::root(); ?>components/com_rseventspro/assets/images/edit.png" alt="<?php echo JText::_('COM_RSEVENTSPRO_GLOBAL_EDIT'); ?>" />
			</a>
		<?php } ?>
		<?php if ((!empty($this->permissions['can_delete_events']) || $event->owner == $this->user || $event->sid == $this->user || $this->admin) && !empty($this->user)) { ?>
			<a href="<?php echo rseventsproHelper::route('index.php?option=com_rseventspro&task=rseventspro.remove&id='.rseventsproHelper::sef($event->id,$event->name)); ?>"  onclick="return confirm('<?php echo JText::_('COM_RSEVENTSPRO_GLOBAL_DELETE_CONFIRMATION'); ?>');">
				<img src="<?php echo JURI::root(); ?>components/com_rseventspro/assets/images/delete.png" alt="<?php echo JText::_('COM_RSEVENTSPRO_GLOBAL_DELETE'); ?>" />
			</a>
		<?php } ?>
	</div>
	
	<?php if (!empty($event->options['show_icon_list'])) { ?>
	<div class="rs_event_image" itemprop="image">
		<a href="<?php echo rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($event->id,$event->name)); ?>" class="rs_event_link thumbnail">
			<img src="<?php echo JRoute::_('index.php?option=com_rseventspro&task=image&id='.rseventsproHelper::sef($event->id,$event->name), false); ?>" alt="" width="<?php echo $this->config->icon_small_width; ?>" />
		</a>
	</div>
	<?php } ?>
	
	<div class="rs_event_details">
		<div itemprop="name">
			<a itemprop="url" href="<?php echo rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($event->id,$event->name)); ?>" class="rs_event_link<?php echo $full ? ' rs_event_full' : ''; ?><?php echo $ongoing ? ' rs_event_ongoing' : ''; ?>"><?php echo $event->name; ?></a> <?php if (!$event->completed) echo JText::_('COM_RSEVENTSPRO_GLOBAL_INCOMPLETE_EVENT'); ?> <?php if (!$event->published) echo JText::_('COM_RSEVENTSPRO_GLOBAL_UNPUBLISHED_EVENT'); ?>
		</div>
		<div>
			<?php if ($event->allday) { ?>
			<?php if (!empty($event->options['start_date_list'])) { ?>
			<span class="rsepro-event-on-block">
			<?php echo JText::_('COM_RSEVENTSPRO_GLOBAL_ON'); ?> <b><?php echo rseventsproHelper::showdate($event->start,$this->config->global_date,true); ?></b>
			</span>
			<?php } ?>
			<?php } else { ?>
			
			<?php if (!empty($event->options['start_date_list']) || !empty($event->options['start_time_list']) || !empty($event->options['end_date_list']) || !empty($event->options['end_time_list'])) { ?>
			<?php if (!empty($event->options['start_date_list']) || !empty($event->options['start_time_list'])) { ?>
			<?php if ((!empty($event->options['start_date_list']) || !empty($event->options['start_time_list'])) && empty($event->options['end_date_list']) && empty($event->options['end_time_list'])) { ?>
			<span class="rsepro-event-starting-block">
			<?php echo JText::_('COM_RSEVENTSPRO_EVENT_STARTING_ON'); ?>
			<?php } else { ?>
			<span class="rsepro-event-from-block">
			<?php echo JText::_('COM_RSEVENTSPRO_EVENT_FROM'); ?> 
			<?php } ?>
			<b><?php echo rseventsproHelper::showdate($event->start,rseventsproHelper::showMask('list_start',$event->options),true); ?></b>
			</span>
			<?php } ?>
			<?php if (!empty($event->options['end_date_list']) || !empty($event->options['end_time_list'])) { ?>
			<?php if ((!empty($event->options['end_date_list']) || !empty($event->options['end_time_list'])) && empty($event->options['start_date_list']) && empty($event->options['start_time_list'])) { ?>
			<span class="rsepro-event-ending-block">
			<?php echo JText::_('COM_RSEVENTSPRO_EVENT_ENDING_ON'); ?>
			<?php } else { ?>
			<span class="rsepro-event-until-block">
			<?php echo JText::_('COM_RSEVENTSPRO_EVENT_UNTIL'); ?>
			<?php } ?>
			<b><?php echo rseventsproHelper::showdate($event->end,rseventsproHelper::showMask('list_end',$event->options),true); ?></b>
			</span>
			<?php } ?>
			<?php } ?>
			
			<?php } ?>
		</div>
		
		<?php if (!empty($event->options['show_location_list']) || !empty($event->options['show_categories_list']) || !empty($event->options['show_tags_list'])) { ?>
		<div>
			<?php if ($event->locationid && $event->lpublished && !empty($event->options['show_location_list'])) { ?>
			<span class="rsepro-event-location-block"><?php echo JText::_('COM_RSEVENTSPRO_GLOBAL_AT'); ?> <a href="<?php echo rseventsproHelper::route('index.php?option=com_rseventspro&layout=location&id='.rseventsproHelper::sef($event->locationid,$event->location)); ?>"><?php echo $event->location; ?></a>
			</span> 
			<?php } ?>
			<?php if (!empty($event->options['show_categories_list'])) { ?>
			<span class="rsepro-event-categories-block"><?php echo $categories; ?></span> 
			<?php } ?>
			<?php if (!empty($event->options['show_tags_list'])) { ?>
			<span class="rsepro-event-tags-block"><?php echo $tags; ?></span> 
			<?php } ?>
		</div>
		<?php } ?>
	</div>
	
	<meta content="<?php echo rseventsproHelper::showdate($event->start,'Y-m-d H:i:s'); ?>" itemprop="startDate" />
	<meta content="<?php echo rseventsproHelper::showdate($event->end,'Y-m-d H:i:s'); ?>" itemprop="endDate" />
</li>
<?php } ?>
<?php } ?>
<?php echo 'RS_DELIMITER1'; ?>