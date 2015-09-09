<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

$post_extra = JFactory::getApplication()->input->get('rsmembership_extra', array(), 'array');
?>
<?php if ( $this->extras ) { ?>
	<?php foreach ( $this->extras as $extra ) { ?>
			<?php $isset_post = isset($post_extra[$extra->id]); ?>
			<div class="item-page">
				<div class="page-header"><h3 class="rsm_extra_title"><?php echo $extra->name; ?></h3></div>
				<?php
				if (RSMembershipHelper::getConfig('trigger_content_plugins')) {
					$extra->description = JHtml::_('content.prepare', $extra->description);
				}
				echo $extra->description;
				?>
				<?php $extra_values = $this->model->getExtraValues($extra->id); ?>
				<?php switch ( $extra->type ) { 
						case 'dropdown': ?>
						<?php $values = array(); ?>
							<select name="rsmembership_extra[<?php echo $extra->id; ?>]" class="rsm_extra">
								<option value="0"><?php echo JText::_('COM_RSMEMBERSHIP_PLEASE_SELECT_EXTRA'); ?></option>
							<?php foreach ($extra_values as $value) { ?>
								<option <?php echo ( ( !$isset_post && $value->checked ) || ( $isset_post && $value->id == $post_extra[$extra->id] ) ? 'selected="selected"' : '' ); ?> value="<?php echo $value->id; ?>"><?php echo $value->name; ?> - <?php echo RSMembershipHelper::getPriceFormat($value->price); ?></option>
							<?php } ?>
							</select>
						<?php break; ?>

					<?php case 'radio': ?>
						<?php $values = array(); ?>
						<?php foreach ($extra_values as $i => $value) { ?>
							<input type="radio" <?php echo ( ( !$isset_post && $value->checked ) || ( $isset_post && $value->id == $post_extra[$extra->id] ) ? 'checked="checked"' : '' ); ?> value="<?php echo $value->id; ?>" id="extras<?php echo $value->id; ?>" name="rsmembership_extra[<?php echo $extra->id; ?>]" class="rsm_extra pull-left" />
							<label for="extras<?php echo $value->id; ?>" class="rsm_extra"><?php echo $value->name; ?> - <?php echo RSMembershipHelper::getPriceFormat($value->price); ?></label>
						<?php } ?>
						<?php break; ?>

					<?php case 'checkbox': ?>
						<?php foreach ($extra_values as $i => $value) { ?>
							<input type="checkbox" <?php echo ( ( !$isset_post && $value->checked ) || ( $isset_post && in_array($value->id, $post_extra[$extra->id]) ) ? 'checked="checked"' : '' ); ?> value="<?php echo $value->id; ?>" id="extras<?php echo $value->id; ?>" name="rsmembership_extra[<?php echo $extra->id; ?>][]" class="rsm_extra pull-left" />
							<label for="extras<?php echo $value->id; ?>" class="rsm_extra"><?php echo $value->name; ?> - <?php echo RSMembershipHelper::getPriceFormat($value->price); ?></label>
						<?php } ?>
						<?php break; ?>
				<?php } ?>
				<div class="rsm_clear"></div>
			</div> <!-- end .item-page -->
	<?php } ?>
<?php } ?>

<?php if ( $this->show_subscribe_btn ) { ?>
		<div class="row-fluid">
			<div class="form-actions span12">
				<button type="submit" class="pull-right button btn btn-success"><?php echo JText::_('COM_RSMEMBERSHIP_SUBSCRIBE'); ?></button>
			</div>
		</div>
<?php } ?>
