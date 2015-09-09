<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');
?>
<div class="row-fluid">
	<div class="item-page">
		<?php if ($this->params->get('show_page_heading', 1)) { ?>
		<div class="page-header">
			<h1><?php echo $this->escape($this->params->get('page_heading', $this->item->name)); ?></h1>
		</div>
		<?php } ?>

		<form method="post" action="<?php echo JRoute::_(RSMembershipRoute::Subscribe( $this->item->category_id,  $this->item->category_name, $this->item->id, $this->item->name, $this->Itemid ) ); ?>" id="rsm_membership_form">
			<?php if (!empty($this->item->thumb)) { ?>
					<?php echo JHTML::_('image', 'components/com_rsmembership/assets/thumbs/'.$this->item->thumb, $this->item->name, 'class="rsm_thumb"'); ?>
			<?php } ?>
			<?php
			// Trigger content plugins if enabled
			if (RSMembershipHelper::getConfig('trigger_content_plugins')) {
				$this->item->description = JHtml::_('content.prepare', $this->item->description);
			}
			echo $this->item->description;
			?>
			<div id="rsm_extras_container<?php echo $this->item->id; ?>">
				<?php echo $this->extras; ?>
			</div>

			<?php echo JHTML::_( 'form.token' ); ?>

			<input type="hidden" name="cid" value="<?php echo $this->item->id; ?>" />
			<input type="hidden" name="task" value="subscribe" />
		</form> <!-- rsm_membership_form -->
	</div>
</div>
<div class="clearfix"></div>