<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

// set description if required
if (isset($this->fieldset->description) && !empty($this->fieldset->description)) { ?>
	<div class="com-rsmembership-tooltip"><?php echo JText::_($this->fieldset->description); ?></div>
<?php 
}
	// Email Settings
	$this->field->startFieldset(JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_ADMIN_EMAIL'), 'rs_fieldset adminform');
	$this->field->showField( $this->form->getLabel('admin_email_mode') 		, $this->form->getInput('admin_email_mode') );
	$this->field->showField( $this->form->getLabel('admin_email_from_addr') 		, $this->form->getInput('admin_email_from_addr') );
	$this->field->showField( $this->form->getLabel('admin_email_to_addr') 	, $this->form->getInput('admin_email_to_addr') );
	$this->field->endFieldset();
	// End Email Settings

	// Subscribe Email
	// add the tab title
	$this->accordion_admin->addTitle( JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_ADMIN_EMAIL_NEW'), 'admin_email_new');

	$content = $this->field->startFieldset(JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_ADMIN_EMAIL_NEW_DESC'), 'rs_fieldset adminform', false);

	$content .= $this->field->showField( $this->form->getLabel('admin_email_new_subject') , $this->form->getInput('admin_email_new_subject'), false );
	$content .= $this->field->showField( $this->form->getLabel('admin_email_new_text') 	 , '<div class="rsmembership_clear">'.$this->form->getInput('admin_email_new_text').'</div>' , false);
	$content .= $this->field->showField(' ', JText::sprintf('COM_RSMEMBERSHIP_PLACEHOLDERS_CAN_BE_USED', $this->getPlaceholders('admin_email_new')), false);

	$content .= $this->field->endFieldset(false);
	// add the tab content
	$this->accordion_admin->addContent($content);
	// End Subscribe Email

	// Approved Email
	// add the tab title
	$this->accordion_admin->addTitle( JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_ADMIN_EMAIL_APPROVED'), 'admin_email_approved');

	$content = $this->field->startFieldset(JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_ADMIN_EMAIL_APPROVED_DESC'), 'rs_fieldset adminform', false);

	$content .= $this->field->showField( $this->form->getLabel('admin_email_approved_subject') , $this->form->getInput('admin_email_approved_subject'), false );
	$content .= $this->field->showField( $this->form->getLabel('admin_email_approved_text') 	 , '<div class="rsmembership_clear">'.$this->form->getInput('admin_email_approved_text').'</div>' , false);
	$content .= $this->field->showField(' ', JText::sprintf('COM_RSMEMBERSHIP_PLACEHOLDERS_CAN_BE_USED', $this->getPlaceholders('admin_email_approved')), false);

	$content .= $this->field->endFieldset(false);
	// add the tab content
	$this->accordion_admin->addContent($content);
	// End Approved Email
	
	// Denied Email
	// add the tab title
	$this->accordion_admin->addTitle( JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_ADMIN_EMAIL_DENIED'), 'admin_email_denied');

	$content = $this->field->startFieldset(JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_ADMIN_EMAIL_DENIED_DESC'), 'rs_fieldset adminform', false);

	$content .= $this->field->showField( $this->form->getLabel('admin_email_denied_subject') , $this->form->getInput('admin_email_denied_subject'), false );
	$content .= $this->field->showField( $this->form->getLabel('admin_email_denied_text') 	 , '<div class="rsmembership_clear">'.$this->form->getInput('admin_email_denied_text').'</div>' , false);
	$content .= $this->field->showField(' ', JText::sprintf('COM_RSMEMBERSHIP_PLACEHOLDERS_CAN_BE_USED', $this->getPlaceholders('admin_email_denied')), false);

	$content .= $this->field->endFieldset(false);
	// add the tab content
	$this->accordion_admin->addContent($content);
	// End Denied Email
	
	// Renew Email
	// add the tab title
	$this->accordion_admin->addTitle( JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_ADMIN_EMAIL_RENEW'), 'admin_email_renew');

	$content = $this->field->startFieldset(JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_ADMIN_EMAIL_RENEW_DESC'), 'rs_fieldset adminform', false);

	$content .= $this->field->showField( $this->form->getLabel('admin_email_renew_subject') , $this->form->getInput('admin_email_renew_subject'), false );
	$content .= $this->field->showField( $this->form->getLabel('admin_email_renew_text')  , '<div class="rsmembership_clear">'.$this->form->getInput('admin_email_renew_text').'</div>' , false);
	$content .= $this->field->showField(' ', JText::sprintf('COM_RSMEMBERSHIP_PLACEHOLDERS_CAN_BE_USED', $this->getPlaceholders('admin_email_renew')), false);

	$content .= $this->field->endFieldset(false);
	// add the tab content
	$this->accordion_admin->addContent($content);
	// End Renew Email
	
	// Upgrade Email
	// add the tab title
	$this->accordion_admin->addTitle( JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_ADMIN_EMAIL_UPGRADE'), 'admin_email_upgrade');

	$content = $this->field->startFieldset(JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_ADMIN_EMAIL_UPGRADE_DESC'), 'rs_fieldset adminform', false);

	$content .= $this->field->showField( $this->form->getLabel('admin_email_upgrade_subject') , $this->form->getInput('admin_email_upgrade_subject'), false );
	$content .= $this->field->showField( $this->form->getLabel('admin_email_upgrade_text')  , '<div class="rsmembership_clear">'.$this->form->getInput('admin_email_upgrade_text').'</div>' , false);
	$content .= $this->field->showField(' ', JText::sprintf('COM_RSMEMBERSHIP_PLACEHOLDERS_CAN_BE_USED', $this->getPlaceholders('admin_email_upgrade')), false);

	$content .= $this->field->endFieldset(false);
	// add the tab content
	$this->accordion_admin->addContent($content);
	// End Upgrade Email

	// Add Extra Email
	// add the tab title
	$this->accordion_admin->addTitle( JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_ADMIN_EMAIL_ADDEXTRA'), 'admin_email_addextra');

	$content = $this->field->startFieldset(JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_ADMIN_EMAIL_ADDEXTRA_DESC'), 'rs_fieldset adminform', false);

	$content .= $this->field->showField( $this->form->getLabel('admin_email_addextra_subject') , $this->form->getInput('admin_email_addextra_subject'), false );
	$content .= $this->field->showField( $this->form->getLabel('admin_email_addextra_text')    , '<div class="rsmembership_clear">'.$this->form->getInput('admin_email_addextra_text').'</div>' , false);
	$content .= $this->field->showField(' ', JText::sprintf('COM_RSMEMBERSHIP_PLACEHOLDERS_CAN_BE_USED', $this->getPlaceholders('admin_email_addextra')), false);

	$content .= $this->field->endFieldset(false);
	// add the tab content
	$this->accordion_admin->addContent($content);
	// End Add Extra Email
	
	// Expire Email
	// add the tab title
	$this->accordion_admin->addTitle( JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_ADMIN_EMAIL_EXPIRE'), 'admin_email_expire');

	$content = $this->field->startFieldset(JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_ADMIN_EMAIL_EXPIRE_DESC'), 'rs_fieldset adminform', false);

	$content .= $this->field->showField( $this->form->getLabel('admin_email_expire_subject') , $this->form->getInput('admin_email_expire_subject'), false );
	$content .= $this->field->showField( $this->form->getLabel('admin_email_expire_text')  , '<div class="rsmembership_clear">'.$this->form->getInput('admin_email_expire_text').'</div>' , false);
	$content .= $this->field->showField(' ', JText::sprintf('COM_RSMEMBERSHIP_PLACEHOLDERS_CAN_BE_USED', $this->getPlaceholders('admin_email_expire_text')), false);

	$content .= $this->field->endFieldset(false);
	// add the tab content
	$this->accordion_admin->addContent($content);
	// End Expire Email

	// render accordion
	$this->accordion_admin->render();