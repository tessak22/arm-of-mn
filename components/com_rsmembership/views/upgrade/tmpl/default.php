<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.modal');
?> 

<script type="text/javascript">
function validate_upgrade()
{
	var form = document.membershipForm;
	var msg = new Array();
	
	<?php if (!empty($this->membershipterms)) { ?>
	if (!document.getElementById('rsm_checkbox_agree').checked)
		msg.push("<?php echo JText::_('COM_RSMEMBERSHIP_PLEASE_AGREE_MEMBERSHIP', true); ?>");
	
	<?php } ?>
	
	<?php foreach ($this->fields_validation as $validation) { ?>
		<?php echo $validation; ?>
	<?php } ?>
	
	if (msg.length > 0)
	{
		msg_container = new Element('div');
		msg_container.innerHTML = '<div class="rsm_modal_error"><?php echo JText::_('COM_RSMEMBERSHIP_THERE_WAS_AN_ERROR', true); ?></div><p>' + msg.join('</p><p>') + '</p>';
		msg_container.className = 'rsm_modal_error_container';
		
		try {
			SqueezeBox.open(msg_container, {
				handler: 'adopt',
				size: {x: 450, y: 350}
			});
		}
		catch (err) {
			alert(msg.join("\n"));
		}
		return false;
	}
	
	
	
	return true;
}
</script>


	<?php if ($this->params->get('show_page_heading', 1)) { ?>
	<div class="page-header">
		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	</div>
	<?php } ?>

	<form method="post" class="rsmembership_form" action="<?php echo JRoute::_('index.php?option=com_rsmembership&task=upgradepaymentredirect'); ?>" name="membershipForm" onsubmit="return validate_upgrade();" id="rsm_upgrade_form">
		<div class="item-page">
			<div class="page-header"><h3><?php echo JText::_('COM_RSMEMBERSHIP_PURCHASE_INFORMATION'); ?></h3></div>
			<table cellpadding="0" cellspacing="0" border="0" width="100%" class="rsmembership_form_table">
			<tr>
				<td width="30%" height="40"><?php echo JText::_('COM_RSMEMBERSHIP_UPGRADE'); ?>:</td>
				<td><?php echo $this->upgrade->fromname; ?> <?php echo JText::_('to'); ?> <?php echo $this->upgrade->toname; ?></td>
			</tr>
			<tr>
				<td width="30%" height="40"><?php echo JText::_('COM_RSMEMBERSHIP_TOTAL_COST'); ?>:</td>
				<td><?php echo $this->total; ?></td>
			</tr>
			</table>
		</div>

		<div class="item-page">
			<div class="page-header"><h3><?php echo JText::_('COM_RSMEMBERSHIP_ACCOUNT_INFORMATION'); ?></h3></div>
			<table cellpadding="0" cellspacing="0" border="0" width="100%" class="rsmembership_form_table">
			<tr>
				<td width="30%" height="40"><?php echo JText::_('COM_RSMEMBERSHIP_NAME'); ?>:</td>
				<td><?php echo $this->escape($this->user->get('name')); ?></td>
			</tr>
			<tr>
				<td height="40"><?php echo JText::_( 'COM_RSMEMBERSHIP_EMAIL' ); ?>:</td>
				<td><?php echo $this->escape($this->user->get('email')); ?></td>
			</tr>
			<?php foreach ($this->fields as $field) {  
				$hidden = (isset($field[2]) && $field[2] == 'hidden') ? true : false;
			?>
			<tr<?php echo ($hidden ? ' style="display:none"':'')?>>
				<td height="40"><?php echo $field[0]; ?></td>
				<td><?php echo $field[1]; ?></td>
			</tr>
			<?php } ?>
			</table>
		</div>
		
		<?php if (count($this->membership_fields)) { ?>
			<div class="item-page">
				<h3 class="page-header"><?php echo JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_INFORMATION'); ?></h3>
					<table cellpadding="0" cellspacing="0" border="0" width="100%" class="rsmembership_form_table">
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

	<?php if ($this->upgrade->price > 0) { ?>
		<div class="item-page">
			<div class="page-header"><h3><?php echo JText::_('COM_RSMEMBERSHIP_PAYMENT_INFORMATION'); ?></h3></div>
			<table cellpadding="0" cellspacing="0" border="0" width="100%" class="rsmembership_form_table">
			<tr>
				<td width="30%" height="40"><?php echo JText::_('COM_RSMEMBERSHIP_PAY_WITH'); ?>:</td>
				<td>
				<?php
				$i = 0;
				if (!empty($this->payments))
					foreach ($this->payments as $plugin => $paymentname) { $i++; ?>
					<p><input <?php echo $i == 1 ? 'checked="checked"' : ''; ?> type="radio" name="payment" value="<?php echo $this->escape($plugin); ?>" id="payment<?php echo $i; ?>" class="pull-left" /> <label for="payment<?php echo $i; ?>"><?php echo $this->escape($paymentname); ?></label></p>
				<?php } ?>
				</td>
			</tr>
			</table>
		</div>
	<?php } ?>

	<?php if (!empty($this->membershipterms)) { ?>
		<div class="item-page">
			<div class="page-header"><h3><?php echo JText::_('COM_RSMEMBERSHIP_TERM'); ?></h3></div>
			<table cellpadding="0" cellspacing="0" border="0" width="100%" class="rsmembership_form_table">
				<tr>
					<td><iframe border="1" style="border: solid 1px #c7c7c7; height: 200px;" width="100%" src="<?php echo JRoute::_('index.php?option=com_rsmembership&view=terms&cid='.$this->membershipterms->id.':'.JFilterOutput::stringURLSafe($this->membershipterms->name).'&tmpl=component'); ?>"></iframe></td>
				</tr>
				<tr>
					<td height="40" align="center"><input type="checkbox" id="rsm_checkbox_agree" /> <label for="rsm_checkbox_agree"><?php echo JText::_('COM_RSMEMBERSHIP_I_AGREE'); ?> (<?php echo $this->membershipterms->name; ?>)</label></td>
				</tr>
			</table>
		</div>
	<?php } ?>
		<div class="form-actions">
			<button type="button" class="button btn pull-left" onclick="document.location='<?php echo JRoute::_('index.php?option=com_rsmembership&view=mymembership&cid='.$this->cid); ?>'" name="Cancel"><?php echo JText::_('COM_RSMEMBERSHIP_BACK'); ?></button>
			<button type="submit" class="button btn btn-success pull-right"><?php echo JText::_('COM_RSMEMBERSHIP_UPGRADE'); ?></button>
		</div>
		<?php echo $this->token; ?>
		<input type="hidden" name="option" value="com_rsmembership" />
		<input type="hidden" name="view" value="upgrade" />
		<input type="hidden" name="task" value="upgradepaymentredirect" />
		<input type="hidden" name="cid" value="<?php echo $this->cid; ?>" />
		<?php if (count($this->membership_fields)) { ?>
			<input type="hidden" name="to_id" value="<?php echo $this->upgrade->membership_to_id ?>" />
		<?php } ?>
	</form> <!-- rsm_upgrade_form -->
