<?php
/**
* @package RSMembership!
* @copyright (C) 2010 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted access');
?>

<?php if ( !empty($this->reports_data['data']) ) { ?>
<script type="text/javascript">
		// Load the Visualization API and the piechart package.
		google.load('visualization', '1.0', {'packages':['corechart']});

		// Set a callback to run when the Google Visualization API is loaded.
		google.setOnLoadCallback(RSMembershipdrawChart);

		// Callback that creates and populates a data table,
		// instantiates the pie chart, passes in the data and
		// draws it.
		function RSMembershipdrawChart() {
			
			var data 					= google.visualization.arrayToDataTable(<?php echo json_encode($this->reports_data['data']) ;?>);
			var response_options		= <?php echo json_encode($this->reports_data['options']) ;?>;

			// aditional options
			response_options['height'] = '300';
			response_options['legend'] = {position: 'right', textStyle: {color: 'blue', fontSize: 10}, height:110};
			
			var options = response_options;

			// Instantiate and draw our chart, passing in some options.
			var chart = new google.visualization.AreaChart(document.getElementById('rsmembership_chart_overview'));
			chart.draw(data, options);
		}

</script>
<?php } ?>
<div class="row-fluid">
	<div class="span<?php echo ( !empty($this->reports_data['data']) ? '6' : '9' );?> cpanel">

		<div class="dashboard-wraper">
			<div class="dashboard-content"> 
				<a class="icon hasTip" href="index.php?option=com_rsmembership&amp;view=transactions" title="<?php echo JText::_('COM_RSMEMBERSHIP_TRANSACTIONS_DESC'); ?>">
					<?php echo JHTML::_('image', 'administrator/components/com_rsmembership/assets/images/transactions.png', JText::_('COM_RSMEMBERSHIP_TRANSACTIONS')); ?>
					<span class="dashboard-title"><?php echo JText::_('COM_RSMEMBERSHIP_TRANSACTIONS'); ?></span>
				</a>
			</div>
		</div>

		<div class="dashboard-wraper">
			<div class="dashboard-content"> 
				<a class="icon hasTip" href="index.php?option=com_rsmembership&amp;view=memberships" title="<?php echo JText::_('COM_RSMEMBERSHIP_MEMBERSHIPS_DESC'); ?>">
					<?php echo JHTML::_('image', 'administrator/components/com_rsmembership/assets/images/memberships.png', JText::_('COM_RSMEMBERSHIP_MEMBERSHIPS')); ?>
					<span class="dashboard-title"><?php echo JText::_('COM_RSMEMBERSHIP_MEMBERSHIPS'); ?></span>
				</a>
			</div>
		</div>
		
		<div class="dashboard-wraper">
			<div class="dashboard-content"> 
				<a class="icon hasTip" href="index.php?option=com_rsmembership&amp;view=membership_fields" title="<?php echo JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_FIELDS_DESC'); ?>">
					<?php echo JHTML::_('image', 'administrator/components/com_rsmembership/assets/images/membership_fields.png', JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_FIELDS')); ?>
					<span class="dashboard-title"><?php echo JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_FIELDS'); ?></span>
				</a>
			</div>
		</div>
		
		<div class="dashboard-wraper">
			<div class="dashboard-content"> 
				<a class="icon hasTip" href="index.php?option=com_rsmembership&amp;view=categories" title="<?php echo JText::_('COM_RSMEMBERSHIP_CATEGORIES_DESC'); ?>">
					<?php echo JHTML::_('image', 'administrator/components/com_rsmembership/assets/images/categories.png', JText::_('COM_RSMEMBERSHIP_CATEGORIES')); ?>
					<span class="dashboard-title"><?php echo JText::_('COM_RSMEMBERSHIP_CATEGORIES'); ?></span>
				</a>
			</div>
		</div>
		
		<div class="dashboard-wraper">
			<div class="dashboard-content"> 
				<a class="icon hasTip" href="index.php?option=com_rsmembership&amp;view=extras" title="<?php echo JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_EXTRAS_DESC'); ?>">
					<?php echo JHTML::_('image', 'administrator/components/com_rsmembership/assets/images/extras.png', JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_EXTRAS')); ?>
					<span class="dashboard-title"><?php echo JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_EXTRAS'); ?></span>
				</a>
			</div>
		</div>
		
		<div class="dashboard-wraper">
			<div class="dashboard-content"> 
				<a class="icon hasTip" href="index.php?option=com_rsmembership&amp;view=upgrades" title="<?php echo JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_UPGRADES_DESC'); ?>">
					<?php echo JHTML::_('image', 'administrator/components/com_rsmembership/assets/images/upgrades.png', JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_UPGRADES')); ?>
					<span class="dashboard-title"><?php echo JText::_('COM_RSMEMBERSHIP_MEMBERSHIP_UPGRADES'); ?></span>
				</a>
			</div>
		</div>
		
		<div class="dashboard-wraper">
			<div class="dashboard-content"> 
				<a class="icon hasTip" href="index.php?option=com_rsmembership&amp;view=coupons" title="<?php echo JText::_('COM_RSMEMBERSHIP_COUPONS_DESC'); ?>">
					<?php echo JHTML::_('image', 'administrator/components/com_rsmembership/assets/images/coupons.png', JText::_('COM_RSMEMBERSHIP_COUPONS')); ?>
					<span class="dashboard-title"><?php echo JText::_('COM_RSMEMBERSHIP_COUPONS'); ?></span>
				</a>
			</div>
		</div>
		
		<div class="dashboard-wraper">
			<div class="dashboard-content"> 
				<a class="icon hasTip" href="index.php?option=com_rsmembership&amp;view=payments" title="<?php echo JText::_('COM_RSMEMBERSHIP_PAYMENT_INTEGRATIONS_DESC'); ?>">
					<?php echo JHTML::_('image', 'administrator/components/com_rsmembership/assets/images/payments.png', JText::_('COM_RSMEMBERSHIP_PAYMENT_INTEGRATIONS')); ?>
					<span class="dashboard-title"><?php echo JText::_('COM_RSMEMBERSHIP_PAYMENT_INTEGRATIONS'); ?></span>
				</a>
			</div>
		</div>
		
		<div class="dashboard-wraper">
			<div class="dashboard-content"> 
				<a class="icon hasTip" href="index.php?option=com_rsmembership&amp;view=files" title="<?php echo JText::_('COM_RSMEMBERSHIP_FILES_DESC'); ?>">
					<?php echo JHTML::_('image', 'administrator/components/com_rsmembership/assets/images/files.png', JText::_('COM_RSMEMBERSHIP_FILES')); ?>
					<span class="dashboard-title"><?php echo JText::_('COM_RSMEMBERSHIP_FILES'); ?></span>
				</a>
			</div>
		</div>
		
		<div class="dashboard-wraper">
			<div class="dashboard-content"> 
				<a class="icon hasTip" href="index.php?option=com_rsmembership&amp;view=terms" title="<?php echo JText::_('COM_RSMEMBERSHIP_FILE_TERMS_DESC'); ?>">
					<?php echo JHTML::_('image', 'administrator/components/com_rsmembership/assets/images/terms.png', JText::_('COM_RSMEMBERSHIP_FILE_TERMS')); ?>
					<span class="dashboard-title"><?php echo JText::_('COM_RSMEMBERSHIP_FILE_TERMS'); ?></span>
				</a>
			</div>
		</div>
		
		<div class="dashboard-wraper">
			<div class="dashboard-content"> 
				<a class="icon hasTip" href="index.php?option=com_rsmembership&amp;view=subscribers" title="<?php echo JText::_('COM_RSMEMBERSHIP_SUBSCRIBERS_DESC'); ?>">
					<?php echo JHTML::_('image', 'administrator/components/com_rsmembership/assets/images/subscribers.png', JText::_('COM_RSMEMBERSHIP_SUBSCRIBERS')); ?>
					<span class="dashboard-title"><?php echo JText::_('COM_RSMEMBERSHIP_SUBSCRIBERS'); ?></span>
				</a>
			</div>
		</div>
		
		<div class="dashboard-wraper">
			<div class="dashboard-content"> 
				<a class="icon hasTip" href="index.php?option=com_rsmembership&amp;view=subscriptions" title="<?php echo JText::_('COM_RSMEMBERSHIP_SUBSCRIPTIONS_DESC'); ?>">
					<?php echo JHTML::_('image', 'administrator/components/com_rsmembership/assets/images/subscriptions.png', JText::_('COM_RSMEMBERSHIP_SUBSCRIPTIONS')); ?>
					<span class="dashboard-title"><?php echo JText::_('COM_RSMEMBERSHIP_SUBSCRIPTIONS'); ?></span>
				</a>
			</div>
		</div>
		
		<div class="dashboard-wraper">
			<div class="dashboard-content"> 
				<a class="icon hasTip" href="index.php?option=com_rsmembership&amp;view=fields" title="<?php echo JText::_('COM_RSMEMBERSHIP_FIELDS_DESC'); ?>">
					<?php echo JHTML::_('image', 'administrator/components/com_rsmembership/assets/images/fields.png', JText::_('COM_RSMEMBERSHIP_FIELDS')); ?>
					<span class="dashboard-title"><?php echo JText::_('COM_RSMEMBERSHIP_FIELDS'); ?></span>
				</a>
			</div>
		</div>
		
		<div class="dashboard-wraper">
			<div class="dashboard-content"> 
				<a class="icon hasTip" href="index.php?option=com_rsmembership&amp;view=reports" title="<?php echo JText::_('COM_RSMEMBERSHIP_REPORTS_DESC'); ?>">
					<?php echo JHTML::_('image', 'administrator/components/com_rsmembership/assets/images/reports.png', JText::_('COM_RSMEMBERSHIP_REPORTS')); ?>
					<span class="dashboard-title"><?php echo JText::_('COM_RSMEMBERSHIP_REPORTS'); ?></span>
				</a>
			</div>
		</div>
		
		<div class="dashboard-wraper">
			<div class="dashboard-content"> 
				<a class="icon hasTip" href="index.php?option=com_rsmembership&amp;view=syslogs" title="<?php echo JText::_('COM_RSMEMBERSHIP_SYSLOGS_DESC'); ?>">
					<?php echo JHTML::_('image', 'administrator/components/com_rsmembership/assets/images/syslogs.png', JText::_('COM_RSMEMBERSHIP_SYSLOGS')); ?>
					<span class="dashboard-title"><?php echo JText::_('COM_RSMEMBERSHIP_SYSLOGS'); ?></span>
				</a>
			</div>
		</div>
		
		<div class="dashboard-wraper">
			<div class="dashboard-content"> 
				<a class="icon hasTip" href="index.php?option=com_rsmembership&amp;view=updates" title="<?php echo JText::_('COM_RSMEMBERSHIP_UPDATES_DESC'); ?>">
					<?php echo JHTML::_('image', 'administrator/components/com_rsmembership/assets/images/updates.png', JText::_('COM_RSMEMBERSHIP_UPDATES')); ?>
					<span class="dashboard-title"><?php echo JText::_('COM_RSMEMBERSHIP_UPDATES'); ?></span>
				</a>
			</div>
		</div>
		
		<div class="dashboard-wraper">
			<div class="dashboard-content"> 
				<a class="icon hasTip" href="index.php?option=com_rsmembership&amp;view=configuration" title="<?php echo JText::_('COM_RSMEMBERSHIP_CONFIGURATION_DESC'); ?>">
					<?php echo JHTML::_('image', 'administrator/components/com_rsmembership/assets/images/configuration.png', JText::_('COM_RSMEMBERSHIP_CONFIGURATION')); ?>
					<span class="dashboard-title"><?php echo JText::_('COM_RSMEMBERSHIP_CONFIGURATION'); ?></span>
				</a>
			</div>
		</div>
	</div>

	<?php if ( !empty($this->reports_data['data']) ) { ?>
	<div class="span3">
			<div id="rsmembership_chart_overview" class="dashboard-info"></div>
	</div>
	<?php } ?>

	<div class="span3">
		<div class="dashboard-container">
			<div class="dashboard-info">
				<span>
					<img src="../administrator/components/com_rsmembership/assets/images/rsmembership.jpg" alt="RSMembership! logo" >
				</span>
				<table class="dashboard-table">
					<tr>
						<td nowrap="nowrap" align="right"><strong><?php echo JText::_('COM_RSMEMBERSHIP_INSTALLED_VERSION') ?> </strong></td>
						<td colspan="2">RSMembership! <?php echo $this->version; ?></td>
					</tr>
					<tr>
						<td nowrap="nowrap" align="right"><strong><?php echo JText::_('COM_RSMEMBERSHIP_COPYRIGHT') ?> </strong></td>
						<td nowrap="nowrap">&copy; 2007 - <?php echo gmdate('Y'); ?> <a href="http://www.rsjoomla.com" target="_blank">RSJoomla.com</a></td>
					</tr>
					<tr>
						<td nowrap="nowrap" align="right"><strong><?php echo JText::_('COM_RSMEMBERSHIP_DOCUMENTATION') ?> </strong></td>
						<td nowrap="nowrap"><a href="http://www.rsjoomla.com/support/documentation/view-knowledgebase/74-rsmembership-user-guide.html" target="_blank"><?php echo JText::_('COM_RSMEMBERSHIP_GO_TO_DOCUMENTATION');?></a></td>
					</tr>
					<tr>
						<td nowrap="nowrap" align="right"><strong><?php echo JText::_('COM_RSMEMBERSHIP_LICENSE') ?> </strong></td>
						<td nowrap="nowrap"><a href="http://www.gnu.org/licenses/gpl.html" target="_blank">GPL Commercial License</a></td>
					</tr>
					<tr>
						<td nowrap="nowrap" align="right"><strong><?php echo JText::_('COM_RSMEMBERSHIP_AUTHOR') ?> </strong></td>
						<td nowrap="nowrap"><a href="http://www.rsjoomla.com" target="_blank">RSJoomla!</a></td>
					</tr>

					<tr>
						<td nowrap="nowrap" align="right"><strong><?php echo JText::_('COM_RSMEMBERSHIP_LICENSE_CODE') ?> </strong></td>
						<?php if (strlen($this->code) == 20) { ?>
						<td nowrap="nowrap" class="correct-code"><?php echo $this->escape($this->code); ?></td>
						<?php } elseif ($this->code) { ?>
						<td nowrap="nowrap" class="incorrect-code">
							<a href="<?php echo JRoute::_('index.php?option=com_rsmembership&view=configuration'); ?>"><strong><?php echo $this->escape($this->code);?></strong></a>
							<br />
<strong><a href="http://www.rsjoomla.com/support/documentation/view-article/767-where-do-i-find-my-license-code-.html" target="_blank"><?php echo JText::_('COM_RSMEMBERSHIP_WHERE_DO_I_FIND_THIS'); ?></a></strong>
						</td>
						<?php } else { ?>
						<td nowrap="nowrap" class="missing-code">
							<a href="<?php echo JRoute::_('index.php?option=com_rsmembership&view=configuration'); ?>"><?php echo JText::_('COM_RSMEMBERSHIP_LICENSE_CODE_DESC'); ?></a>
							<br />
<strong><a href="http://www.rsjoomla.com/support/documentation/view-article/767-where-do-i-find-my-license-code-.html" target="_blank"><?php echo JText::_('COM_RSMEMBERSHIP_WHERE_DO_I_FIND_THIS'); ?></a></strong>
						</td>
						<?php } ?>
					</tr>
				</table>
			</div>
		</div>
	</div> <!-- span6 -->
</div><!-- row-fluid -->