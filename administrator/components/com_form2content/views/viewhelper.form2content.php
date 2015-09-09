<?php
defined('JPATH_PLATFORM') or die('Restricted acccess');

function DisplayCredits()
{
	if($data = JInstaller::parseXMLInstallFile(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'manifest.xml')) 
	{
		$version = $data['version'];
	}
	else
	{
		$version = 'undefined';
	}
	?>
	 	<br/>
		<div align="center">
			<span class="smallgrey"><?php echo JText::_('COM_FORM2CONTENT_FORM2CONTENT') . ' ' . JText::_('COM_FORM2CONTENT_VERSION') . ' ' . $version; ?> (<a href="http://www.form2content.com/changelog/pro-joomla3" target="_blank"><?php echo JText::_('COM_FORM2CONTENT_CHECK_VERSION'); ?></a>), &copy; 2008 - <?php echo Date("Y"); ?> - Copyright by <a href="http://www.opensourcedesign.nl" target="_blank">Open Source Design</a> - e-mail: <a href="mailto:support@opensourcedesign.nl">support@opensourcedesign.nl</a></span>
		</div>
	<?php		
}

function displayArticleStats(&$row)
{
	$db = JFactory::getDBO();

	$create_date 	= null;
	$nullDate 		= $db->getNullDate();

	// used to hide "Reset Hits" when hits = 0
	if ( !$row->hits ) {
		$visibility = 'style="display: none; visibility: hidden;"';
	} else {
		$visibility = '';
	}

	?>
	<table width="100%" style="border: 1px dashed silver; padding: 5px; margin-bottom: 10px;">
	<?php
	if ( $row->id ) {
	?>
	<tr>
		<td>
			<strong><?php echo JText::_('JOOMLA_ARTICLE_ID'); ?>:</strong>
		</td>
		<td>
			<?php echo $row->id; ?>
		</td>
	</tr>
	<?php
	}
	?>
	<tr>
		<td>
			<strong><?php echo JText::_('STATE'); ?></strong>
		</td>
		<td>
			<?php
			if($row->id)
			{
				echo $row->state > 0 ? JText::_('PUBLISHED') : ($row->state < 0 ? JText::_('ARCHIVED') : JText::_('UNPUBLISHED') );
			}
			else
			{
				echo $row->state > 0 ? JText::_('DRAFT_TO_BE_PUBLISHED') : JText::_('DRAFT_UNPUBLISHED');
			}
			?>
		</td>
	</tr>
	<tr>
		<td>
			<strong><?php echo JText::_('HITS'); ?></strong>
		</td>
		<td>
			<?php echo $row->hits;?>
		</td>
	</tr>
	<tr>
		<td>
			<strong><?php echo JText::_('REVISED'); ?></strong>
		</td>
		<td>
			<?php echo $row->version;?> <?php echo JText::_('TIMES'); ?>
		</td>
	</tr>
	<tr>
		<td>
			<strong><?php echo JText::_('CREATED'); ?></strong>
		</td>
		<td>
			<?php
			if ( $row->created == $nullDate ) {
				echo JText::_('NEW_DOCUMENT');
			} else {
				echo JHTML::_('date',  $row->created,  JText::_('DATE_FORMAT_LC2') );
			}
			?>
		</td>
	</tr>
	<tr>
		<td>
			<strong><?php echo JText::_('MODIFIED'); ?></strong>
		</td>
		<td>
			<?php
				if ( $row->modified == $nullDate ) {
					echo JText::_('NOT_MODIFIED');
				} else {
					echo JHTML::_('date',  $row->modified, JText::_('DATE_FORMAT_LC2'));
				}
			?>
		</td>
	</tr>
	</table>
	<?php
}
?>