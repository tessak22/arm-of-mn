<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');
$num = 1;
?>
<table class="adminlist table table-striped">
	<thead>
		<tr>
			<th width="5"><?php echo JText::_( '#' ); ?></th>
			<th><?php echo JText::_('TITLE'); ?></th>
		</tr>
	</thead>
		<tr class="row0">
			<td><?php echo $num++; ?></td>
			<td><a href="<?php echo JRoute::_('index.php?option=com_rsmembership&view=files&task=addfolder&tmpl=component&'.$this->what.'='.$this->id.'&function='.$this->function); ?>"><?php echo JText::_('COM_RSMEMBERSHIP_TYPE_FOLDER'); ?></a></td>
		</tr>
		<tr class="row1">
			<td><?php echo $num++; ?></td>
			<td><a href="<?php echo JRoute::_('index.php?option=com_rsmembership&view=share&layout=url&tmpl=component&'.$this->what.'='.$this->id); ?>"><?php echo JText::_('COM_RSMEMBERSHIP_TYPE_URL'); ?></a></td>
		</tr>
		<tr class="row0">
			<td><?php echo $num++; ?></td>
			<td><a href="<?php echo JRoute::_('index.php?option=com_rsmembership&view=share&layout=module&tmpl=component&'.$this->what.'='.$this->id); ?>"><?php echo JText::_('COM_RSMEMBERSHIP_TYPE_MODULE'); ?></a></td>
		</tr>
		<tr class="row1">
			<td><?php echo $num++; ?></td>
			<td><a href="<?php echo JRoute::_('index.php?option=com_rsmembership&view=share&layout=menu&tmpl=component&'.$this->what.'='.$this->id); ?>"><?php echo JText::_('COM_RSMEMBERSHIP_TYPE_MENU'); ?></a></td>
		</tr>
		<tr class="row0">
			<td><?php echo $num++; ?></td>
			<td><a href="<?php echo JRoute::_('index.php?option=com_rsmembership&view=share&layout=article&tmpl=component&'.$this->what.'='.$this->id); ?>"><?php echo JText::_('COM_RSMEMBERSHIP_TYPE_ARTICLE'); ?></a></td>
		</tr>
		<tr class="row0">
			<td><?php echo $num++; ?></td>
			<td><a href="<?php echo JRoute::_('index.php?option=com_rsmembership&view=share&layout=category&tmpl=component&'.$this->what.'='.$this->id); ?>"><?php echo JText::_('COM_RSMEMBERSHIP_TYPE_CATEGORY'); ?></a></td>
		</tr>
	<?php
	$k = 1;
	$i = $num;
	foreach ($this->pluginShareTypes as $row) { ?>
	<tr class="row<?php echo $k; ?>">
		<td><?php echo $i; ?></td>
		<td><a href="<?php echo JRoute::_('index.php?option=com_rsmembership&view=share&layout=plugin&tmpl=component&'.$this->what.'='.$this->id.'&share_type='.$row); ?>"><?php echo JText::_('PLG_RSMEMBERSHIP_TYPE_'.strtoupper($row)); ?></a></td>
	</tr>
	<?php
		$i++;
		$k=1-$k;
	}
	?>
</table>

<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
?>