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
	$this->field->startFieldset(JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_EMAIL_SETTINGS'), 'rs_fieldset adminform');
	$this->field->showField( $this->form->getLabel('user_email_use_global') , $this->form->getInput('user_email_use_global') );
	$this->field->showField( $this->form->getLabel('user_email_mode') 		, $this->form->getInput('user_email_mode') );
	$this->field->showField( $this->form->getLabel('user_email_from') 		, $this->form->getInput('user_email_from') );
	$this->field->showField( $this->form->getLabel('user_email_from_addr')  , $this->form->getInput('user_email_from_addr') );
	$this->field->endFieldset();
	// End Email Settings

	// Subscribe Email
	// add the tab title
	$this->accordion_user->addTitle( JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_USER_EMAIL_NEW'), 'user_email_new');

	$content = $this->field->startFieldset(JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_USER_EMAIL_NEW_DESC'), 'rs_fieldset adminform', false);

	$content .= $this->field->showField( $this->form->getLabel('user_email_new_subject') , $this->form->getInput('user_email_new_subject'), false );
	$content .= $this->field->showField( $this->form->getLabel('user_email_new_text') 	 , '<div class="rsmembership_clear">'.$this->form->getInput('user_email_new_text').'</div>' , false);
	$content .= $this->field->showField(' ', JText::sprintf('COM_RSMEMBERSHIP_PLACEHOLDERS_CAN_BE_USED', $this->getPlaceholders('user_email_new')), false);

	$content .= '
			<span class="hasTip" title="'.JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_ATTACHMENTS_DESC').'">
				<label for="user_email_file_id">'.JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_ATTACHMENTS').'</label>
			</span>
			<div class="clr clearfix" style="margin-bottom: 10px;"></div>';
	
	if (!empty($this->item->id)) 
	{
		$this->email_type = 'user_email_new';
		$content .= '<div class="clr clearfix" style="margin-bottom: 10px;"></div><div class="clr clearfix" style="margin-bottom: 5px;"></div><div class="button2-left btn btn-small "><div class="blank"><a class="modal" title="Select the path" rel="{handler: \'iframe\', size: {x: 660, y: 475}}" href="'. JRoute::_('index.php?option=com_rsmembership&controller=files&view=files&task=addfile&tmpl=component&membership_id='.$this->item->id.'&function=addsubscriberfiles&email_type='.$this->email_type).'">'. JText::_('COM_RSMEMBERSHIP_ADD_FILES') .'</a></div></div>
			<div class="clr clearfix" style="margin-bottom: 10px;"></div>
			<div id="addsubscriberfiles'.$this->email_type.'_ajax">';
			$this->item->attachments 		  = isset($this->attachments[$this->email_type]) ? $this->attachments[$this->email_type] : array();
			$this->item->attachmentsPagination = isset($this->attachmentsPagination[$this->email_type]) ? $this->attachmentsPagination[$this->email_type]   : null;
		$content .= $this->loadTemplate('files');
		$content .= '</div>';
	} else 
		 $content .= JText::_('COM_RSMEMBERSHIP_ATTACHMENT_FILES_SAVE_FIRST');

	$content .= $this->field->endFieldset(false);

	// add the tab content
	$this->accordion_user->addContent($content);
	// End Subscribe Email

	// Approved Email
	// add the tab title
	$this->accordion_user->addTitle( JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_USER_EMAIL_APPROVED'), 'user_email_approved');
	
	$content = $this->field->startFieldset(JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_USER_EMAIL_APPROVED_DESC'), 'rs_fieldset adminform', false);

	$content .= $this->field->showField( $this->form->getLabel('user_email_approved_subject') , $this->form->getInput('user_email_approved_subject'), false );
	$content .= $this->field->showField( $this->form->getLabel('user_email_approved_text') 	 , '<div class="rsmembership_clear">'.$this->form->getInput('user_email_approved_text').'</div>' , false);
	$content .= $this->field->showField(' ', JText::sprintf('COM_RSMEMBERSHIP_PLACEHOLDERS_CAN_BE_USED', $this->getPlaceholders('user_email_approved')), false);

	$content .= '<span class="hasTip" title="'.JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_ATTACHMENTS_DESC').'"><label for="user_email_file_id">'.JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_ATTACHMENTS').'</label></span><div class="clr clearfix" style="margin-bottom: 10px;"></div>';

	if (!empty($this->item->id)) 
	{
		$this->email_type = 'user_email_approved';
		$content .= '<div class="clr clearfix" style="margin-bottom: 5px;"></div><div class="button2-left btn btn-small "><div class="blank"><a class="modal" title="Select the path" rel="{handler: \'iframe\', size: {x: 660, y: 475}}" href="'. JRoute::_('index.php?option=com_rsmembership&controller=files&view=files&task=addfile&tmpl=component&membership_id='.$this->item->id.'&function=addsubscriberfiles&email_type='.$this->email_type).'">'. JText::_('COM_RSMEMBERSHIP_ADD_FILES') .'</a></div></div>
			<div class="clr clearfix" style="margin-bottom: 10px;"></div>
			<div id="addsubscriberfiles'.$this->email_type.'_ajax">';

			$this->item->attachments 		   = isset($this->attachments[$this->email_type]) ? $this->attachments[$this->email_type] : array();
			$this->item->attachmentsPagination = isset($this->attachmentsPagination[$this->email_type]) ? $this->attachmentsPagination[$this->email_type]   : null;

		$content .= $this->loadTemplate('files');
		$content .= '</div>';
	} else 
		 $content .= JText::_('COM_RSMEMBERSHIP_ATTACHMENT_FILES_SAVE_FIRST');

	$content .= $this->field->endFieldset(false);

	// add the tab content
	$this->accordion_user->addContent($content);
	// End Approved Email
	
	// Denied Email
	// add the tab title
	$this->accordion_user->addTitle( JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_USER_EMAIL_DENIED'), 'user_email_denied');
	
	$content = $this->field->startFieldset(JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_USER_EMAIL_DENIED_DESC'), 'rs_fieldset adminform', false);

	$content .= $this->field->showField( $this->form->getLabel('user_email_denied_subject') , $this->form->getInput('user_email_denied_subject'), false );
	$content .= $this->field->showField( $this->form->getLabel('user_email_denied_text') 	 , '<div class="rsmembership_clear">'.$this->form->getInput('user_email_denied_text').'</div>' , false);
	$content .= $this->field->showField(' ', JText::sprintf('COM_RSMEMBERSHIP_PLACEHOLDERS_CAN_BE_USED', $this->getPlaceholders('user_email_denied')), false);

	$content .= $this->field->endFieldset(false);

	// add the tab content
	$this->accordion_user->addContent($content);
	// End Denied Email

	// Renew Email
	// add the tab title
	$this->accordion_user->addTitle( JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_USER_EMAIL_RENEW'), 'user_email_renew');
	
	$content = $this->field->startFieldset(JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_USER_EMAIL_RENEW_DESC'), 'rs_fieldset adminform', false);

	$content .= $this->field->showField( $this->form->getLabel('user_email_renew_subject') , $this->form->getInput('user_email_renew_subject'), false );
	$content .= $this->field->showField( $this->form->getLabel('user_email_renew_text') 	 , '<div class="rsmembership_clear">'.$this->form->getInput('user_email_renew_text').'</div>' , false);
	$content .= $this->field->showField(' ', JText::sprintf('COM_RSMEMBERSHIP_PLACEHOLDERS_CAN_BE_USED', $this->getPlaceholders('user_email_renew')), false);

	$content .= '<span class="hasTip" title="'.JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_ATTACHMENTS_DESC').'"><label for="user_email_file_id">'.JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_ATTACHMENTS').'</label></span><div class="clr clearfix" style="margin-bottom: 10px;"></div>';

	if (!empty($this->item->id)) 
	{
		$this->email_type = 'user_email_renew';
		$content .= '<div class="clr clearfix" style="margin-bottom: 5px;"></div><div class="button2-left btn btn-small "><div class="blank"><a class="modal" title="Select the path" rel="{handler: \'iframe\', size: {x: 660, y: 475}}" href="'. JRoute::_('index.php?option=com_rsmembership&controller=files&view=files&task=addfile&tmpl=component&membership_id='.$this->item->id.'&function=addsubscriberfiles&email_type='.$this->email_type).'">'. JText::_('COM_RSMEMBERSHIP_ADD_FILES') .'</a></div></div>
			<div class="clr clearfix" style="margin-bottom: 10px;"></div>
			<div id="addsubscriberfiles'.$this->email_type.'_ajax">';

			$this->item->attachments 		   = isset($this->attachments[$this->email_type]) ? $this->attachments[$this->email_type] : array();
			$this->item->attachmentsPagination = isset($this->attachmentsPagination[$this->email_type]) ? $this->attachmentsPagination[$this->email_type]   : null;

		$content .= $this->loadTemplate('files');
		$content .= '</div>';
	} else 
		 $content .= JText::_('COM_RSMEMBERSHIP_ATTACHMENT_FILES_SAVE_FIRST');

	$content .= $this->field->endFieldset(false);

	// add the tab content
	$this->accordion_user->addContent($content);
	// End Renew Email

	// Upgrade Email
	// add the tab title
	$this->accordion_user->addTitle( JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_USER_EMAIL_UPGRADE'), 'user_email_upgrade');
	
	$content = $this->field->startFieldset(JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_USER_EMAIL_UPGRADE_DESC'), 'rs_fieldset adminform', false);

	$content .= $this->field->showField( $this->form->getLabel('user_email_upgrade_subject') , $this->form->getInput('user_email_upgrade_subject'), false );
	$content .= $this->field->showField( $this->form->getLabel('user_email_upgrade_text') 	 , '<div class="rsmembership_clear">'.$this->form->getInput('user_email_upgrade_text').'</div>' , false);
	$content .= $this->field->showField(' ', JText::sprintf('COM_RSMEMBERSHIP_PLACEHOLDERS_CAN_BE_USED', $this->getPlaceholders('user_email_upgrade')), false);

	$content .= '<span class="hasTip" title="'.JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_ATTACHMENTS_DESC').'"><label for="user_email_file_id">'.JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_ATTACHMENTS').'</label></span><div class="clr clearfix" style="margin-bottom: 10px;"></div>';

	if (!empty($this->item->id)) 
	{
		$this->email_type = 'user_email_upgrade';
		$content .= '<div class="clr clearfix" style="margin-bottom: 5px;"></div><div class="button2-left btn btn-small "><div class="blank"><a class="modal" title="Select the path" rel="{handler: \'iframe\', size: {x: 660, y: 475}}" href="'. JRoute::_('index.php?option=com_rsmembership&controller=files&view=files&task=addfile&tmpl=component&membership_id='.$this->item->id.'&function=addsubscriberfiles&email_type='.$this->email_type).'">'. JText::_('COM_RSMEMBERSHIP_ADD_FILES') .'</a></div></div>
			<div class="clr clearfix" style="margin-bottom: 10px;"></div>
			<div id="addsubscriberfiles'.$this->email_type.'_ajax">';

			$this->item->attachments 		   = isset($this->attachments[$this->email_type]) ? $this->attachments[$this->email_type] : array();
			$this->item->attachmentsPagination = isset($this->attachmentsPagination[$this->email_type]) ? $this->attachmentsPagination[$this->email_type]   : null;

		$content .= $this->loadTemplate('files');
		$content .= '</div>';
	} else 
		 $content .= JText::_('COM_RSMEMBERSHIP_ATTACHMENT_FILES_SAVE_FIRST');

	$content .= $this->field->endFieldset(false);

	// add the tab content
	$this->accordion_user->addContent($content);
	// End Upgrade Email
	
	// Add Extra Email
	// add the tab title
	$this->accordion_user->addTitle( JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_USER_EMAIL_ADDEXTRA'), 'user_email_addextra');

	$content = $this->field->startFieldset(JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_USER_EMAIL_ADDEXTRA_DESC'), 'rs_fieldset adminform', false);

	$content .= $this->field->showField( $this->form->getLabel('user_email_addextra_subject') , $this->form->getInput('user_email_addextra_subject'), false );
	$content .= $this->field->showField( $this->form->getLabel('user_email_addextra_text') 	 , '<div class="rsmembership_clear">'.$this->form->getInput('user_email_addextra_text').'</div>' , false);
	$content .= $this->field->showField(' ', JText::sprintf('COM_RSMEMBERSHIP_PLACEHOLDERS_CAN_BE_USED', $this->getPlaceholders('user_email_addextra')), false);

	$content .= '<span class="hasTip" title="'.JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_ATTACHMENTS_DESC').'"><label for="user_email_file_id">'.JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_ATTACHMENTS').'</label></span><div class="clr clearfix" style="margin-bottom: 10px;"></div>';

	if (!empty($this->item->id)) 
	{
		$this->email_type = 'user_email_addextra';
		$content .= '<div class="clr clearfix" style="margin-bottom: 5px;"></div><div class="button2-left btn btn-small "><div class="blank"><a class="modal" title="Select the path" rel="{handler: \'iframe\', size: {x: 660, y: 475}}" href="'. JRoute::_('index.php?option=com_rsmembership&controller=files&view=files&task=addfile&tmpl=component&membership_id='.$this->item->id.'&function=addsubscriberfiles&email_type='.$this->email_type).'">'. JText::_('COM_RSMEMBERSHIP_ADD_FILES') .'</a></div></div>
			<div class="clr clearfix" style="margin-bottom: 10px;"></div>
			<div id="addsubscriberfiles'.$this->email_type.'_ajax">';

			$this->item->attachments 		   = isset($this->attachments[$this->email_type]) ? $this->attachments[$this->email_type] : array();
			$this->item->attachmentsPagination = isset($this->attachmentsPagination[$this->email_type]) ? $this->attachmentsPagination[$this->email_type]   : null;

		$content .= $this->loadTemplate('files');
		$content .= '</div>';
	} else 
		 $content .= JText::_('COM_RSMEMBERSHIP_ATTACHMENT_FILES_SAVE_FIRST');

	$content .= $this->field->endFieldset(false);

	// add the tab content
	$this->accordion_user->addContent($content);
	// End Add Extra Email
	
	// Expire Email
	// add the tab title
	$this->accordion_user->addTitle( JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_USER_EMAIL_EXPIRE'), 'user_email_expire');
	
	$content = $this->field->startFieldset(JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_USER_EMAIL_EXPIRE_DESC'), 'rs_fieldset adminform', false);

	$content .= $this->field->showField( $this->form->getLabel('expire_notify_interval') , $this->form->getInput('expire_notify_interval').'<span class="rsmembership_after_input">'.JText::_('COM_RSMEMBERSHIP_DAYS').'</span>' , false );
	$content .= $this->field->showField( $this->form->getLabel('user_email_expire_subject') , $this->form->getInput('user_email_expire_subject'), false );
	$content .= $this->field->showField( $this->form->getLabel('user_email_expire_text') 	, '<div class="rsmembership_clear">'.$this->form->getInput('user_email_expire_text').'</div>' , false);
	$content .= $this->field->showField(' ', JText::sprintf('COM_RSMEMBERSHIP_PLACEHOLDERS_CAN_BE_USED', $this->getPlaceholders('user_email_expire')), false);

	$content .= '<span class="hasTip" title="'.JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_ATTACHMENTS_DESC').'"><label for="user_email_file_id">'.JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_ATTACHMENTS').'</label></span><div class="clr clearfix" style="margin-bottom: 10px;"></div>';

	if (!empty($this->item->id)) 
	{
		$this->email_type = 'user_email_expire';
		$content .= '<div class="clr clearfix" style="margin-bottom: 5px;"></div><div class="button2-left btn btn-small "><div class="blank"><a class="modal" title="Select the path" rel="{handler: \'iframe\', size: {x: 660, y: 475}}" href="'. JRoute::_('index.php?option=com_rsmembership&controller=files&view=files&task=addfile&tmpl=component&membership_id='.$this->item->id.'&function=addsubscriberfiles&email_type='.$this->email_type).'">'. JText::_('COM_RSMEMBERSHIP_ADD_FILES') .'</a></div></div>
			<div class="clr clearfix" style="margin-bottom: 10px;"></div>
			<div id="addsubscriberfiles'.$this->email_type.'_ajax">';

			$this->item->attachments 		   = isset($this->attachments[$this->email_type]) ? $this->attachments[$this->email_type] : array();
			$this->item->attachmentsPagination = isset($this->attachmentsPagination[$this->email_type]) ? $this->attachmentsPagination[$this->email_type]   : null;

		$content .= $this->loadTemplate('files');
		$content .= '</div>';
	} else 
		 $content .= JText::_('COM_RSMEMBERSHIP_ATTACHMENT_FILES_SAVE_FIRST');

	$content .= $this->field->endFieldset(false);

	// add the tab content
	$this->accordion_user->addContent($content);
	// End Expire Email

	// render accordion
	$this->accordion_user->render();
?>