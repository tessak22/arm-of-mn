<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSOrdering
{
	public function showHead($listDirn, $listOrder, $orderField='ordering', $params=array()) {
		$items 		=& $params['items'];
		$saveOrder  = $listOrder == $orderField;
		$saveTask 	=& $params['saveTask'];
		?>
		<th align="center" class="rsm_order_th">
			<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ORDERING', $orderField, strtolower($listDirn), $listOrder); ?>
			<?php if ($saveOrder) { ?>
				<?php echo JHtml::_('grid.order',  $items, 'filesave.png', $saveTask); ?>
			<?php } ?>
		</th>
		<?php
	}
	
	public function showRow($saveOrder, $itemOrdering, $params=array()) {
		$context 	= $params['context'];
		$pagination =& $params['pagination'];
		$listDirn	= strtolower($params['listDirn']);
		$i			= $params['i'];

		$disabled = $saveOrder ?  '' : 'disabled="disabled"';
		?>
		<td class="order center">
		<?php if ($saveOrder) { ?>
			<?php if ($listDirn == 'asc') { ?>
				<span><?php echo $pagination->orderUpIcon($i, true, $context.'.orderup', 'JLIB_HTML_MOVE_UP', true); ?></span>
				<span><?php echo $pagination->orderDownIcon($i, $pagination->total, true, $context.'.orderdown', 'JLIB_HTML_MOVE_DOWN', true); ?></span>
			<?php } elseif ($listDirn == 'desc') { ?>
				<span><?php echo $pagination->orderUpIcon($i, true, $context.'.orderdown', 'JLIB_HTML_MOVE_UP', true); ?></span>
				<span><?php echo $pagination->orderDownIcon($i, $pagination->total, true, $context.'.orderup', 'JLIB_HTML_MOVE_DOWN', true); ?></span>
			<?php } ?>
		<?php } ?>
		<input type="text" name="order[]" size="5" value="<?php echo $itemOrdering; ?>" <?php echo $disabled ?> class="text-area-order" />
		<?php
	}
}