jQuery.noConflict();

var RSMembership = {}

RSMembership.$ = jQuery;

RSMembership.exportCSV = {
	
	totalItems : 0,
	view : '',

	setProgress: function (current) {
		if (RSMembership.$('#com-rsmembership-joomla-configuration-progress .com-rsmembership-bar').length > 0) {
			var currentProgress = (current * 100) / this.totalItems;
			RSMembership.$('#com-rsmembership-joomla-configuration-progress .com-rsmembership-bar').css('width', currentProgress + '%').text(parseInt(currentProgress) + '%');
		}
	},
	
	setCSV : function(from, fileHash) {
		if (this.totalItems > 0 && from == this.totalItems) {	
			RSMembership.$('#com-rsmembership-joomla-configuration-progress').hide();
			window.location.assign('index.php?option=com_rsmembership&task='+this.view+'.exportcsv&filehash='+fileHash);
		}
		else 
		{
			RSMembership.$.ajax({
				dataType: 'json',
				type: 'POST',
				url: 'index.php',
				data: {
					option: 'com_rsmembership',
					task: this.view+'.writecsv',
					start: from,
					filehash: fileHash 
				},
				beforeSend: function() {
					RSMembership.$('#j-main-container').find('.alert').remove();
					RSMembership.$('#com-rsmembership-joomla-configuration-progress').show();
				},
				success: function(data) {
					if (data.success == true) {
						from = data.response.newFrom;
						fileHash = data.response.fileHash; 
						
						RSMembership.exportCSV.setProgress(from);
						
						setTimeout(function(){
							RSMembership.exportCSV.setCSV(from,fileHash);
						},700);
					} else {
						RSMembership.$('#com-rsmembership-joomla-configuration-progress').hide();
						RSMembership.$('#j-main-container').prepend(RSMembership.$('<div class="alert alert-error"></div>').text(data.response));
					}
				}
			});
		}
	}
}
