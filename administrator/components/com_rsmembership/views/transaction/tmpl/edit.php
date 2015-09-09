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
$this->tabs->addTitle(JText::_($this->fieldsets['main']->label), 'transaction-info');
// load content
$transaction_info_content = $this->loadTemplate('transaction_info');
// add the tab content
$this->tabs->addContent($transaction_info_content);


$this->tabs->addTitle(JText::_('COM_RSMEMBERSHIP_SUBSCRIBER_INFO'), 'user-info');
// load content
$user_info_content = $this->loadTemplate('user_info');
// add the tab content
$this->tabs->addContent($user_info_content);

$this->tabs->addTitle(JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_INFO'), 'membership-info');
// load content
$membership_info_content = $this->loadTemplate('membership_info');
// add the tab content
$this->tabs->addContent($membership_info_content);

$this->tabs->addTitle(JText::_('COM_RSMEMBERSHIP_PAYMENT_LOG'), 'payment-log');
// load content
$payment_log_content = $this->loadTemplate('payment_log');
// add the tab content
$this->tabs->addContent($payment_log_content);

$this->tabs->render();
?>