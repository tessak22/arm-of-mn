<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.keepalive');
JHTML::_('behavior.tooltip');
JHTML::_('behavior.modal');
?>

<form action="<?php echo JRoute::_('index.php?option=com_rsmembership&task=extravalue.edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off" class="form-validate form-horizontal">
<?php
foreach ($this->fieldsets as $name => $fieldset) 
{
	// add the tab title
	$this->tabs->addTitle($fieldset->label, $fieldset->name);

	// prepare the content
	$this->fieldset = $fieldset;

	$this->fields 	= $this->form->getFieldset($fieldset->name);
	$content = $this->loadTemplate($fieldset->name);

	// add the tab content
	$this->tabs->addContent($content);
}

// render tabs
$this->tabs->render();
?>
<?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="task" value="" />
<!-- need this to for getRedirectToListAppend -->
<input type="hidden" name="extra_id" value="<?php echo JFactory::getApplication()->input->get('extra_id', 0, 'int'); ?>" />

</form>

<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
?>