<?php
/**
* @package RSEvents!Pro
* @copyright (C) 2015 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<ul class="nav nav-tabs">
	<li><a href="javascript:void(0);" data-toggle="modal" data-target="#rsepro-edit-event-photo" class="center" onclick="rsepro_reset_frame();"><?php echo $this->loadTemplate('icon'); ?></a></li>
	
	<li class="active"><a href="javascript:void(0);" data-target="#rsepro-edit-tab1" data-toggle="tab"><?php echo JText::_('COM_RSEVENTSPRO_EVENT_TAB_CREATE'); ?> <span class="icon icon-flag"></span></a></li>
	
	<li><a href="javascript:void(0);" data-target="#rsepro-edit-tab2" data-toggle="tab"><?php echo JText::_('COM_RSEVENTSPRO_EVENT_TAB_CATEGORIES'); ?> <span class="icon icon-tag"></span></a></li>
	
	<li class="rsepro-hide"<?php echo $this->item->registration ? ' style="display:block;"' : ''; ?>><a href="javascript:void(0);" data-target="#rsepro-edit-tab3" data-toggle="tab"><?php echo JText::_('COM_RSEVENTSPRO_EVENT_TAB_REGISTRATION'); ?>  <span class="icon icon-calendar"></span></a></li>
	
	<li class="rsepro-hide"<?php echo $this->item->registration ? ' style="display:block;"' : ''; ?>><a href="javascript:void(0);" data-target="#rsepro-edit-tab4" data-toggle="tab"><?php echo JText::_('COM_RSEVENTSPRO_EVENT_TAB_NEWTICKET'); ?> <span class="icon icon-plus"></span></a></li>
	
	<?php if ($this->tickets) { ?>
	<?php foreach ($this->tickets as $ticket) { ?>
	<li class="rsepro-hide"<?php echo $this->item->registration ? ' style="display:block;"' : ''; ?>><a href="javascript:void(0);" data-target="#rsepro-edit-ticket<?php echo $ticket->id; ?>" data-toggle="tab"><?php echo $ticket->name; ?></a></li>
	<?php }} ?>
	
	<?php if (rseventsproHelper::pdf()) { ?>
	<li class="rsepro-hide"<?php echo $this->item->registration ? ' style="display:block;"' : ''; ?>><a href="javascript:void(0);" data-target="#rsepro-edit-tab5" data-toggle="tab"><?php echo JText::_('COM_RSEVENTSPRO_TICKET_PDF'); ?> <span class="icon icon-file"></span></a></li>
	<?php } ?>
	
	<?php JFactory::getApplication()->triggerEvent('rsepro_addMenuOptionRegistration'); ?>
	
	<li class="rsepro-hide"<?php echo $this->item->discounts && $this->item->registration ? ' style="display:block;"' : ''; ?>><a href="javascript:void(0);" data-target="#rsepro-edit-tab6" data-toggle="tab"><?php echo JText::_('COM_RSEVENTSPRO_EVENT_TAB_DISCOUNTS'); ?> <span class="icon icon-cog"></span></a></li>
	
	<li class="rsepro-hide"<?php echo $this->item->discounts && $this->item->registration ? ' style="display:block;"' : ''; ?>><a href="javascript:void(0);" data-target="#rsepro-edit-tab7" data-toggle="tab"><?php echo JText::_('COM_RSEVENTSPRO_EVENT_TAB_NEWCOUPON'); ?> <span class="icon icon-plus"></span></a></li>
	
	<?php if ($this->coupons) { ?>
	<?php foreach ($this->coupons as $coupon) { ?>
	<li class="rsepro-hide"<?php echo $this->item->discounts && $this->item->registration ? ' style="display:block;"' : ''; ?>><a href="javascript:void(0);" data-target="#rsepro-edit-coupon<?php echo $coupon->id; ?>" data-toggle="tab"><?php echo $coupon->name; ?></a></li>
	<?php }} ?>
	
	<?php if (empty($this->item->parent) && (!empty($this->permissions['can_repeat_events']) || $this->admin)) { ?>
	<li class="rsepro-hide"<?php echo $this->item->recurring ? ' style="display:block;"' : ''; ?>><a href="javascript:void(0);" data-target="#rsepro-edit-tab8" data-toggle="tab"><?php echo JText::_('COM_RSEVENTSPRO_EVENT_TAB_RECURRING'); ?> <span class="icon icon-refresh"></span></a></li>
	<?php } ?>
	
	<?php if (!empty($this->permissions['can_upload']) || $this->admin) { ?>
	<li><a href="javascript:void(0);" data-target="#rsepro-edit-tab9" data-toggle="tab"><?php echo JText::_('COM_RSEVENTSPRO_EVENT_TAB_FILES'); ?> <span class="icon icon-file"></span></a></li>
	<?php } ?>
	
	<li><a href="javascript:void(0);" data-target="#rsepro-edit-tab10" data-toggle="tab"><?php echo JText::_('COM_RSEVENTSPRO_EVENT_TAB_CONTACT'); ?> <span class="icon icon-user"></span></a></li>
	
	<li><a href="javascript:void(0);" data-target="#rsepro-edit-tab11" data-toggle="tab"><?php echo JText::_('COM_RSEVENTSPRO_EVENT_TAB_META'); ?> <span class="icon icon-list"></span></a></li>
	
	<li><a href="javascript:void(0);" data-target="#rsepro-edit-tab12" data-toggle="tab"><?php echo JText::_('COM_RSEVENTSPRO_EVENT_TAB_FRONTEND'); ?> <span class="icon icon-home"></span></a></li>
	
	<?php if (rseventsproHelper::isGallery()) { ?>
	<li><a href="javascript:void(0);" data-target="#rsepro-edit-tab13" data-toggle="tab"><?php echo JText::_('COM_RSEVENTSPRO_EVENT_TAB_GALLERY'); ?> <span class="icon icon-picture"></span></a></li>
	<?php } ?>
	
	<?php JFactory::getApplication()->triggerEvent('rsepro_addMenuOption'); ?>
</ul>