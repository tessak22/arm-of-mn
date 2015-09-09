<?php
defined('JPATH_PLATFORM') or die('Restricted acccess');

define("MAXCROPSIZE", 600);
define("MAXPREVIEWSIZE", 250);

JHtml::_('bootstrap.framework');
JHtml::script('com_form2content/jquery.Jcrop.min.js', false, true);
JHtml::script('com_form2content/jquery.blockUI.js', false, true);
JHtml::stylesheet('com_form2content/jquery.Jcrop.min.css', array(), true);

$imageDir 		= Path::Combine(JUri::root(true), F2cFactory::getConfig()->get('images_path'));
$imageRoot 		= Path::Combine(JPATH_BASE, F2cFactory::getConfig()->get('images_path'));
$srcImage		= Path::Combine($imageRoot, JFactory::getApplication()->input->getString('image'));
$tmpImage		= JFactory::getApplication()->input->getString('image');
$fieldId 		= JFactory::getApplication()->input->getInt('fieldid');
$contentTypeId 	= JFactory::getApplication()->input->getInt('contenttypeid');
$contentType	= F2cFactory::getContentType($contentTypeId);
$field			= $contentType->fields[$fieldId];
$row			= JFactory::getApplication()->input->getString('row', -1);
$scaleFactor	= 1; // no scaling
$prefix 		= $field->getPrefix();
$aspectWidth	= $field->settings->get($prefix.'_crop_aspect_width');
$aspectHeight	= $field->settings->get($prefix.'_crop_aspect_height');
$cropThumbOnly	= $field->settings->get($prefix.'_crop_thumb_only', 0);
$jImage 		= new JImage($srcImage);
$imageWidth 	= $jImage->getWidth();
$imageHeight 	= $jImage->getHeight();

if($imageWidth > MAXCROPSIZE || $imageHeight > MAXCROPSIZE)
{
	// rescale image to fit in viewport boundaries
	$scaleFactor 	= MAXCROPSIZE / max(array($imageWidth, $imageHeight));
	$imageWidth 	= round($imageWidth * $scaleFactor,0 , PHP_ROUND_HALF_UP);
	$imageHeight 	= round($imageHeight * $scaleFactor,0 , PHP_ROUND_HALF_UP);
}

if(empty($aspectWidth) || empty($aspectHeight))
{
	$previewHeight 	= MAXPREVIEWSIZE;
	$previewWidth 	= MAXPREVIEWSIZE;
}
else 
{
	if($aspectWidth > $aspectHeight)
	{
		$previewWidth 	= MAXPREVIEWSIZE;
		$previewHeight 	= round(MAXPREVIEWSIZE * $aspectHeight/$aspectWidth, 0 , PHP_ROUND_HALF_UP);
	}
	else 
	{
		$previewHeight 	= MAXPREVIEWSIZE;
		$previewWidth 	= round(MAXPREVIEWSIZE * $aspectWidth/$aspectHeight, 0 , PHP_ROUND_HALF_UP);
	}
}

// calculate top and height for centering crop image
$top 	= round((MAXCROPSIZE - $imageHeight) / 2.0, 0 , PHP_ROUND_HALF_UP);
$left 	= round((MAXCROPSIZE - $imageWidth) / 2.0, 0 , PHP_ROUND_HALF_UP);
?>
<script type="text/javascript">
	var tmpImage = '<?php echo $tmpImage; ?>';
	var jBusyCroppingImage = '<p class="blockUI"><img src="<?php echo JURI::root(true).'/media/com_form2content/images/'; ?>busy.gif" /> <?php echo JText::_('COM_FORM2CONTENT_BUSY_CROPPING_IMAGE', true)?></p>';
	var previewInit = false;
	
  jQuery(function($)
  {
	$('#cropimage').attr("src", "<?php echo Jtext::_($imageDir, true); ?>/" + tmpImage);
	
	// Create variables (in this scope) to hold the API and image size
    var jcrop_api,
        boundx,
        boundy,

        // Grab some information about the preview pane
        $preview = $('#preview-pane'),
        $pcnt = $('#preview-pane .preview-container'),
        $pimg = $('#preview-pane .preview-container img'),

        xsize = $pcnt.width(),
        ysize = $pcnt.height();
    
    $('#cropimage').Jcrop({
      onChange: updatePreview,
      onSelect: updatePreview
      <?php 
    	      if($aspectWidth != '')
    	      {
    	      	echo ', aspectRatio: '.$aspectWidth.' / ' . $aspectHeight;
    	      }
      ?>
    },function(){
      // Use the API to get the real image size
      var bounds = this.getBounds();
      boundx = bounds[0];
      boundy = bounds[1];

      // Store the API in the jcrop_api variable
      jcrop_api = this;

      // Move the preview into the jcrop container for css positioning
      $preview.appendTo(jcrop_api.ui.holder);
      // center the image
      jcrop_api.ui.holder.css('top', '<?php echo $top; ?>px');
      jcrop_api.ui.holder.css('left', '<?php echo $left; ?>px');
    });

    function updatePreview(c)
    {
      if(!previewInit)
      {
    	jQuery('#preview').attr("src", "<?php echo Jtext::_($imageDir, true); ?>/" + tmpImage);
      	previewInit = true;
      }
      
      if (parseInt(c.w) > 0)
      {
		// recalculate preview image boundaries
		var prv = jQuery('.preview-container');
		var previewWidth = <?php echo MAXPREVIEWSIZE;?>;
		var previewHeight = <?php echo MAXPREVIEWSIZE;?>;
		
		if(c.w > c.h)
		{
			// landscape
			var aspectRatio = c.w / c.h;
			previewHeight = parseInt(<?php echo MAXPREVIEWSIZE; ?>/ aspectRatio);
		}
		else
		{
			// portrait
			var aspectRatio = c.h / c.w;
			previewWidth = parseInt(<?php echo MAXPREVIEWSIZE; ?>/ aspectRatio);
		}

		prv.css('height',previewHeight+'px');
		prv.css('width',previewWidth+'px');
		
        var rx = previewWidth / c.w;
        var ry = previewHeight / c.h;
          
        $pimg.css({
          width: Math.round(rx * boundx) + 'px',
          height: Math.round(ry * boundy) + 'px',
          marginLeft: '-' + Math.round(rx * c.x) + 'px',
          marginTop: '-' + Math.round(ry * c.y) + 'px'
        });
      }

      	// update coordinates
	  	jQuery('#x').val(c.x);
		jQuery('#y').val(c.y);
		jQuery('#w').val(c.w);
		jQuery('#h').val(c.h);		
    };
  });

  function checkCoords()
  {
    if(!parseInt(jQuery('#w').val()))
    { 
    	alert('<?php echo JText::_('COM_FORM2CONTENT_ERROR_CROPPING_EMPTY_REGION', true); ?>');
    	return false;
    }

	<?php 
	if(!$cropThumbOnly)
	{
	    $minSelectionWidth 		= round($scaleFactor * $field->settings->get($field->getPrefix().'_min_width'), 0 , PHP_ROUND_HALF_UP);
	    $minSelectionHeight 	= round($scaleFactor * $field->settings->get($field->getPrefix().'_min_height'), 0 , PHP_ROUND_HALF_UP);

        if($minSelectionWidth > 0)
        {
			?> 
			if(jQuery('#w').val() < <?php echo $minSelectionWidth; ?>)
			{
		    	alert('<?php echo JText::_('COM_FORM2CONTENT_ERROR_IMAGE_CROP_MIN_WIDTH', true); ?>');
		    	return false;
			}          
			<?php 
        }
        
        if($minSelectionHeight > 0)
        {
			?> 
			if(jQuery('#h').val() < <?php echo $minSelectionHeight; ?>)
			{
		    	alert('<?php echo JText::_('COM_FORM2CONTENT_ERROR_IMAGE_CROP_MIN_HEIGHT', true); ?>');
		    	return false;
			}          
			<?php 
        }
   	} 
   	?>
   	
    return true;
  };
   
  	function crop()
  	{
		if(checkCoords())
		{
			jQuery(document).ajaxStop(jQuery.unblockUI); 
			jQuery.blockUI({message: jBusyCroppingImage});
					
			var contentTypeFieldId = <?php echo $fieldId;?>;
			var row = <?php echo $row; ?>;
			var url = 	'index.php?option=com_form2content&task=form.imagecrop&format=raw' +
						'&x='+jQuery('#x').val()+'&y='+jQuery('#y').val()+
						'&w='+jQuery('#w').val()+'&h='+jQuery('#h').val()+
						'&filename='+tmpImage +
						'&fieldid='+jQuery('#fieldid').val() +
						'&contenttypeid='+jQuery('#contenttypeid').val() + 
						'&cropthumbonly=<?php echo $cropThumbOnly; ?>';

			jQuery.ajax({
			    type: 'POST',
			    dataType: 'JSON',
			    data: null,
			    url: url,
			    cache: false,
			    contentType: false,
			    processData: false,
			    success: function(data)
			    {
			    	if(data['error'] == '')
			    	{
			        	
			        	keyValue = '#t'+contentTypeFieldId;

			        	if(row >= 0)
			        	{
			        		keyValue += '_'+row;
			        	} 

			        	// Set the preview image
		        		window.parent.jQuery(keyValue+'_preview').attr("src", data['thumbnail']);
			        	window.parent.jQuery(keyValue+'_preview').css('display', 'block');
			        	// Indicate that cropping was performed
			        	window.parent.jQuery(keyValue+'_cropped').val('1');

			        	if(row >= 0)
			        	{
				        	window.parent.jQuery('#t'+contentTypeFieldId+'_'+row+'filename').val(data['filename']);			
				        	// Set the correct url for the cropping window
				        	window.parent.jQuery('#t'+contentTypeFieldId+'_'+row+'_crop').attr("href", "index.php?option=com_form2content&task=form.cropdisplay&tmpl=component&view=crop&fieldid="+contentTypeFieldId+"&contenttypeid="+jQuery('#contenttypeid').val()+"&row="+row+"&image="+data['filename']);
			        	}
			        	else
			        	{
				        	window.parent.jQuery('#t'+contentTypeFieldId+'_tmpfilename').val(data['filename']);			        	
				        	// Set the correct url for the cropping window
				        	window.parent.jQuery('#t'+contentTypeFieldId+'_crop').attr("href", "index.php?option=com_form2content&task=form.cropdisplay&tmpl=component&view=crop&fieldid="+contentTypeFieldId+"&contenttypeid="+jQuery('#contenttypeid').val()+"&image="+data['filename']);
			        	}
			        	
			        	window.parent.SqueezeBox.close();
			    	}
			    	else
			    	{
			    		alert(data['error']);
			    	}
			    },
			});
		}
	}
  
</script>
<style type="text/css">
.jcrop-holder #preview-pane {
  display: block;
  z-index: 2000;
  position: absolute;
  top: <?php echo -$top; ?>px;
  left: <?php echo 10 + MAXCROPSIZE - $left; ?>px;  
  padding: 6px;
  border: 1px rgba(0,0,0,.4) solid;
  background-color: white;

  -webkit-border-radius: 6px;
  -moz-border-radius: 6px;
  border-radius: 6px;

  -webkit-box-shadow: 1px 1px 5px 2px rgba(0, 0, 0, 0.2);
  -moz-box-shadow: 1px 1px 5px 2px rgba(0, 0, 0, 0.2);
  box-shadow: 1px 1px 5px 2px rgba(0, 0, 0, 0.2);
}


/* The Javascript code will set the aspect ratio of the crop
   area based on the size of the thumbnail preview,
   specified here */
#preview-pane .preview-container {
  width: <?php echo $previewWidth; ?>px;
  height: <?php echo $previewHeight; ?>px;
  overflow: hidden;
}

#cropcontainer
{
	width: <?php echo MAXCROPSIZE; ?>px; 
	height: <?php echo MAXCROPSIZE; ?>px; 
	float: left;
}

#buttonbar
{
	float: right;
	position: relative;
	top: 400px;
}

#crop_instructions
{
	float: right;
	width: 240px;
	position: relative;
	top: 270px;
}
</style>
<form action="" method="post" name="adminForm" id="adminForm">
	<div id="outer">
		<div id="cropcontainer">
			<img id="cropimage" src="" width="<?php echo $imageWidth; ?>" height="<?php echo $imageHeight; ?>" />
		</div>
		<div id="preview-pane">
		    <div class="preview-container">
		    	<img src="<?php echo JURI::root(true).'/media/com_form2content/images/1x1_transparent.png'; ?>" class="jcrop-preview" alt="" id="preview" />
		    </div>
		</div>	
		<div id="crop_instructions">
			<h2><?php echo JText::_('COM_FORM2CONTENT_CROP_IMAGE'); ?></h2>
			<p><?php echo JText::_('COM_FORM2CONTENT_CROP_INSTRUCTIONS'); ?></p>
		</div>	
		<div id="buttonbar">
			<input type="button" class="btn" onclick="crop();" value="<?php echo JText::_('COM_FORM2CONTENT_CROP'); ?>" />
			<input type="button" class="btn" onclick="window.parent.SqueezeBox.close();" value="<?php echo JText::_('JCANCEL'); ?>" />
		</div>			
	</div>
	<input id="x" type="hidden" name="x">
	<input id="y" type="hidden" name="y">
	<input id="w" type="hidden" name="w">
	<input id="h" type="hidden" name="h">
	<input id="fieldid" type="hidden" name="fieldid" value="<?php echo $fieldId; ?>">
	<input id="contenttypeid" type="hidden" name="contenttypeid" value="<?php echo $contentTypeId; ?>">
	<input id="row" type="hidden" name="row" value="<?php echo $row; ?>">
</form>
