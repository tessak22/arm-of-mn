<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');
?>

<div class="item-page">
	<?php if ($this->params->get('show_page_heading', 1)) { ?>
	<div class="page-header">
		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	</div>
	<?php } ?>

	<div id="rsm_thankyou_container">
		<?php
		if (RSMembershipHelper::getConfig('trigger_content_plugins')) {
			$this->message = JHtml::_('content.prepare', $this->message);
		}
		echo $this->message;
		?>
	</div><!-- rsm_thankyou_container -->
</div><!-- item-page -->