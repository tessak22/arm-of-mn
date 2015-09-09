<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');
JText::script('COM_RSMEMBERSHIP_TRANSACTION_STATUS_COMPLETED');
JText::script('COM_RSMEMBERSHIP_TRANSACTION_STATUS_PENDING');
JText::script('COM_RSMEMBERSHIP_TRANSACTION_STATUS_DENIED');
?>
<script type="text/javascript">
		// Load the Visualization API and the piechart package.
		google.load('visualization', '1', {'packages':['corechart']});

		// Set a callback to run when the Google Visualization API is loaded.
		// google.setOnLoadCallback(RSMembershipdrawChart);

		// Callback that creates and populates a data table,
		// instantiates the pie chart, passes in the data and
		// draws it.
		
		Object.size = function(obj) {
			var size = 0, key;
			for (key in obj) {
				if (obj.hasOwnProperty(key)) size++;
			}
			return size;
		};
		
		 function RSMembershipdrawChart( response_data, response_options ) {
			var data = google.visualization.arrayToDataTable(response_data);

			// aditional options
			response_options['height'] = '600'; //crosshair: { trigger: 'both' }
			
			var options = response_options;

			// Instantiate and draw our chart, passing in some options.
			var chart = new google.visualization.AreaChart(document.getElementById('rsmembership_chart_div'));
			chart.draw(data, options);
		}
		function RSMembershipdrawChartLabel( response_columns, response_rows, response_options ) {
			
			var dataTable  = new google.visualization.DataTable();
			
			jQuery.each(response_columns, function (index, item) {
				
					var countItem =  Object.size(item);//Object.keys(item).length;
					if (countItem == 1) {
						jQuery.each(item, function (scope, name) {
							if(scope != 'type') {	
								dataTable.addColumn(scope, name);
							}
						});
					}
					else {
						dataTable.addColumn({'type': item.type, 'role': item.role, 'p': {'html': true}});
					}
			});
			dataTable.addRows(response_rows);
			
			response_options['height'] = '600';
			var options = response_options;
			
			var chart = new google.visualization.AreaChart(document.getElementById('rsmembership_chart_div'));
			chart.draw(dataTable , options);
		}
		
		function RSMembershipDisplayTotals(type, info, unit, statuses) {
			switch(type) {
				case 'sales':
					jQuery('#rsmembership_chart_info').empty();
					var display='';
					jQuery.each(info.total, function(status, val){
						display += '<div class="rsmem_sales_info"><div style="float:left"><span class="rsmem_sales_total">Total <span class="rsme_text_color_'+status+'">'+Joomla.JText._('COM_RSMEMBERSHIP_TRANSACTION_STATUS_'+status.toUpperCase())+'</span></span>: <span class="rsmem_sales_total_value '+(val>0 ? 'rsmem_blue' : 'rsmem_red')+'">'+val+'</span> '+info.currency+'</div> <div style="float:right"> <span class="rsmem_sales_average">Average <span class="rsme_text_color_'+status+'">'+Joomla.JText._('COM_RSMEMBERSHIP_TRANSACTION_STATUS_'+status.toUpperCase())+'</span></span>: <span class="rsmem_sales_average_value '+(info.average[status]>0 ? 'rsmem_blue' : 'rsmem_red')+'">'+info.average[status]+'</span> '+info.currency+ ' / '+unit+'</div></div>';
					});
					if (display!='') {
						jQuery('#rsmembership_chart_info').append(display);
					}
				break;
			}		
		}

	jQuery(document).ready(function(){

		if (jQuery('#jform_report').find(":selected").val() == 'report_1') {
			jQuery('#jform_status_memberships').parents('li, div.control-group').show();
			jQuery('#jform_status_transactions, #jform_transaction_types, #jform_gateways').parents('li, div.control-group').hide();
			jQuery('.rsmg_filter-transactions').hide();
		} else {
			if (jQuery('#jform_report').find(":selected").val() == 'report_3') {
				jQuery('.rsmg_filter-price').hide();
			}
			else jQuery('.rsmg_filter-price').show();
			
			jQuery('#jform_status_memberships').parents('li, div.control-group').hide();
			jQuery('#jform_status_transactions, #jform_transaction_types, #jform_gateways').parents('li, div.control-group').show();
			jQuery('.rsmg_filter-transactions').show();
		}

		jQuery('#jform_report').change(function() {
			if (jQuery('#jform_report').find(":selected").val() == 'report_1') {
				jQuery('#jform_status_memberships').parents('li, div.control-group').show();
				jQuery('#jform_status_transactions, #jform_transaction_types, #jform_gateways').parents('li, div.control-group').hide();
				jQuery('.rsmg_filter-transactions').hide();
			} else {
				if (jQuery('#jform_report').find(":selected").val() == 'report_3') {
					jQuery('.rsmg_filter-price').hide();
				}
				else jQuery('.rsmg_filter-price').show();
				
				jQuery('#jform_status_memberships').parents('li, div.control-group').hide();
				jQuery('#jform_status_transactions, #jform_transaction_types, #jform_gateways').parents('li, div.control-group').show();
				jQuery('.rsmg_filter-transactions').show();
			}
		});
		
		jQuery('#rsmembership_refresh_reports').click(function(){
			var reportType = jQuery('#jform_report').find(":selected").val();
			var formdata = jQuery('#adminForm').serialize();
			var data 	 = '&task=reports.getdata&'+formdata;
			jQuery.ajax({
				dataType: 'json',
				type: 'POST',
				url: 'index.php?option=com_rsmembership',
				data: data,
				beforeSend :  function(){
					var loader = jQuery('<div>',{'class':'rsmem_loader'});
					jQuery('#rsmembership_chart_div').empty();
					jQuery('#rsmembership_chart_info').empty();
					jQuery('#rsmembership_chart_div').append(loader);
				},
				success: function(response) {
					jQuery('.rsmem_loader').remove();
					if ((typeof(response.data) != 'undefined' && response.data.length > 0 && reportType!='report_3') || (typeof(response.rows) != 'undefined' && response.rows.length > 0 && reportType=='report_3')) {
						jQuery('#rsmembership_chart_div').empty().show();
						if (reportType!='report_3') {
							RSMembershipdrawChart(response.data, response.options);
							jQuery('#rsmembership_chart_info').empty();
						}
						else {
							RSMembershipdrawChartLabel(response.columns, response.rows, response.options);
							RSMembershipDisplayTotals('sales',response.info, response.options.hAxis.title, response.columns);
						}
						jQuery('#rsmebership_warning_box').empty().hide();
					}
					else {
						jQuery('#rsmebership_warning_box').empty().html('<?php echo JText::_('COM_RSMEMBERSHIP_NO_DATA'); ?>').show();
						jQuery('#rsmembership_chart_div').empty().hide();
					}
				}
			});
		});

		jQuery('#rsmembership_refresh_reports').trigger('click');
});
</script>

		<div class="row-fluid" id="rsmembership_chart_container">
			<div class="span2">
				<form method="post" action="#" name="adminForm" id="adminForm">

					<?php
					foreach ($this->fieldsets as $name => $fieldset) 
					{
						$this->accordion->addTitle(JText::_($fieldset->label) , $fieldset->name);
						$content = $this->field->startFieldset('', 'rs_fieldset adminform', false);

						$this->fields 	= $this->form->getFieldset($fieldset->name);
						foreach ($this->fields as $field) {
							$content .= $this->field->showField($field->hidden ? '' : $field->label, $field->input, false);
						}
						$content .= $this->field->endFieldset(false);
						
						$this->accordion->addContent($content);
					}
					
					// render accordion
					$this->accordion->render();
					?>

					<div align="center"><button type="button" id="rsmembership_refresh_reports" class="btn btn-info"><?php echo JText::_('COM_RSMEMBERSHIP_REPORTS_REFRESH_GRAPH'); ?></button></div>

				</form>
			</div>
			<div class="span10">
				<div id="rsmebership_warning_box"></div>
				<div id="rsmembership_chart_info"></div>
				<div id="rsmembership_chart_div"></div>
			</div>
		</div>
<script type="text/javascript">
	jQuery('.rsmg_filter-transactions').hide();
</script>
<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
?>