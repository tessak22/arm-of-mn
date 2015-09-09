<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');
?>

<form action="<?php echo JRoute::_('index.php?option=com_rsmembership&task=transaction.edit&id='.(int) $this->item->transaction); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off" class="form-validate form-horizontal">
<div class="rsmem_transaction_userinfo">
<?php
$this->fields 	= $this->form->getFieldset('main');

$this->field->startFieldset('', 'adminform form');

$username_label = '<label id="u_username-lbl" for="username" class="hasTip" title="'.JText::_('Username').'">'.JText::_('Username').'</label>';
$username_field = '<span class="rsm_no_edit">'.(!$this->temp ? '<input type="text" name="u[username]" value="'.$this->escape($this->item->username).'" id="username" size="40" />' : $this->escape($this->item->username));
$this->field->showField($username_label, $username_field).'</span>';

$name_label = '<label id="u_name-lbl" for="name" class="hasTip" title="'.JText::_('Name').'">'.JText::_('Name').'</label>';
$name_field = '<span class="rsm_no_edit">'.(!$this->temp ? '<input type="text" name="u[name]" value="'.$this->escape($this->item->name).'" id="name" size="40" />' : $this->escape($this->item->name));
$this->field->showField($name_label, $name_field).'</span>';

$email_label = '<label id="u_email-lbl" for="email" class="hasTip" title="'.JText::_('Email').'">'.JText::_('Email').'</label>';
$email_field = '<span class="rsm_no_edit">'.(!$this->temp ? '<input type="text" name="u[email]" value="'.$this->escape($this->item->email).'" id="email" size="40" />' : $this->escape($this->item->email));
$this->field->showField($email_label, $email_field).'</span>';
?>

<?php foreach ($this->custom_fields as $cfield) { ?>
		<?php 
		$this->field->showField($cfield[0], '<span class="rsm_no_edit">'.$cfield[1].'</span>');
		?>
<?php } ?>
<?php $this->field->endFieldset();?>

<?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="task" value="" />
</div>
</form>

