<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');
?>
<h2><?php echo JText::_('COM_RSMEMBERSHIP_VIEWING_TRANSACTION_LOG'); ?></h2>
<div>
	<?php echo $this->item->transaction_data->response_log ? nl2br($this->escape($this->item->transaction_data->response_log)) : JText::_('COM_RSMEMBERSHIP_LOG_IS_EMPTY'); ?>
</div>