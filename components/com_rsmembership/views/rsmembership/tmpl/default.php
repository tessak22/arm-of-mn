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
	<div class="row-fluid">
		<ul class="thumbnails">
<?php 
	$i = 1; 
	foreach ($this->items as $item) 
	{
		$link  		= JRoute::_( RSMembershipRoute::Membership($item->id, $this->Itemid) );
		$apply_link = JRoute::_( RSMembershipRoute::Subscribe( $item->category_id, $item->category_name, $item->id, $item->name, $this->Itemid ) );

		$price 		= RSMembershipHelper::getPriceFormat($item->price);
		$image 		= !empty($item->thumb) ? JHTML::_('image', 'components/com_rsmembership/assets/thumbs/'.$item->thumb, $item->name, 'class="span'.( (4 * $this->params->get('columns_no', 2)) ).' rsm_thumb'.$this->escape($this->params->get('pageclass_sfx')).'"') : '';

		$placeholders = array(
			'{price}' 	=> $price,
			'{buy}'		=> '',
			'{extras}'  => '',
			'{stock}'	=> ($item->stock > -1 ? ( $item->stock == 0 ? JText::_('COM_RSMEMBERSHIP_UNLIMITED') : $item->stock) : JText::_('COM_RSMEMBERSHIP_OUT_OF_STOCK_PLACEHOLDER')) ,
			'<hr id="system-readmore" />' => ''
		);
		
		// Trigger content plugins if enabled
		if (RSMembershipHelper::getConfig('trigger_content_plugins')) {
			$item->description = JHtml::_('content.prepare', $item->description);
		}
		
		$item->description = str_replace(array_keys($placeholders), array_values($placeholders), $item->description);
		?>
			<li class="span<?php echo (12 / $this->params->get('columns_no', 2)); ?> pull-left rsm_container<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
				<div class="thumbnail">
					<div class="caption">
						<div class="rsm_fixed_height">
							<div class="page-header">
								<h2 class="rsm_title contentheading<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>"><?php if ($this->params->get('show_category', 0)) { ?><?php echo $item->category_id ? $item->category_name : JText::_('COM_RSMEMBERSHIP_NO_CATEGORY'); ?> - <?php } ?><a href="<?php echo $link; ?>"><?php echo $item->name; ?></a>
								<small><?php echo $price; ?></small>
								</h2>
							</div>
							<?php echo $image; ?>
							<?php echo $item->description; ?>
						</div>
						<div class="clr clearfix"></div>
						<div class="row-fluid">
							<div class="btn-group pull-left span<?php echo ( ( 4 * $this->params->get('columns_no', 2) ) );?>">
								<?php if ($this->params->get('show_buttons', 2) == 1 || $this->params->get('show_buttons', 2) == 2) { ?>
									<a href="<?php echo $link; ?>" class="btn"><?php echo JText::_('COM_RSMEMBERSHIP_DETAILS'); ?></a>
								<?php } ?>

								<?php if (($this->params->get('show_buttons', 2) == 2 || $this->params->get('show_buttons', 2) == 3) && $item->stock != -1) { ?>
									<a href="<?php echo $apply_link; ?>" class="btn btn-success"><?php echo JText::_('COM_RSMEMBERSHIP_SUBSCRIBE'); ?></a>
								<?php } ?>
							</div>
						</div>
						<div class="clearfix"></div>
					</div>
				</div>
			</li>
	<?php if ( ($i % $this->params->get('columns_no', 2)) == 0 ) { ?>
		</ul><ul class="thumbnails">
	<?php } ?>
	<?php $i++; ?>
<?php } ?>
	</ul>
</div>
<?php if ($this->params->get('show_pagination', 0) && $this->pagination->get('pages.total') > 1) { ?>
	<div class="pagination">
		<?php echo $this->pagination->getPagesLinks(); ?>
	</div>
	<?php echo $this->pagination->getPagesCounter(); ?>
<?php } ?>
	<div class="clearfix"></div>
</div>