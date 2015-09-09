<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.keepalive');
JHTML::_('behavior.tooltip');
?>

<script type="text/javascript">
function reloadSqueezeBox()
{
	var $modals = false;
	if (typeof jQuery != 'undefined') {
		$modals = jQuery('a.modal').get();
	} else if (typeof MooTools != 'undefined') {
		$modals = $$('a.modal');
	}
		
	if ($modals) {
		SqueezeBox.assign($modals, {
			parse: 'rel'
		});
	}
}
</script>

<?php
// add the tab title
$this->tabs->addTitle(JText::_($this->fieldsets['main']->label), 'user-info');
// load content
$user_info_content = $this->loadTemplate('user_info');
// add the tab content
$this->tabs->addContent($user_info_content);

if ( !$this->temp ) 
{
	$this->tabs->addTitle(JText::_('COM_RSMEMBERSHIP_MEMBERSHIPS'), 'memberships');
	$memberships_content = $this->loadTemplate('memberships');
	$this->tabs->addContent($memberships_content);

	$this->tabs->addTitle(JText::_('COM_RSMEMBERSHIP_TRANSACTION_HISTORY'), 'transactions');
	$transactions_content = $this->loadTemplate('transactions');
	$this->tabs->addContent($transactions_content);

	$this->tabs->addTitle(JText::_('COM_RSMEMBERSHIP_ACCESS_LOGS'), 'logs');
	$logs_content = $this->loadTemplate('logs');
	$this->tabs->addContent($logs_content);
}
// render tabs
$this->tabs->render();
?>