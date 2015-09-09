<?php
defined('JPATH_PLATFORM') or die('Restricted acccess');

class F2cFieldImageGallery extends F2cFieldBase
{	
	public $createThumbnail = true;
	public $baseDir; 
	
	function __construct($field)
	{
		$this->reset();
		parent::__construct($field);
		$this->baseDir = Path::Combine(JPATH_SITE, $this->f2cConfig->get('images_path'));
	}
	
	public function getPrefix()
	{
		return 'igl';
	}
	
	public function reset()
	{
		$this->values['VALUE']				= array();
		$this->internal['fieldcontentid']	= null;
	}
	
	public function render($translatedFields, $contentTypeSettings, $parms = array(), $form, $formId)
	{
		$html 				= '';
		$fieldValue 		= $this->values['VALUE'];
		$attributesTable	= $this->settings->get('igl_attributes_table') ? $this->settings->get('igl_attributes_table') : 'border="1"';
		$uploadAttribs 		= $this->settings->get('igl_attributes_image', 'class="inputbox f2c_upload_control"');
		$thumbsgalleryUrl	= self::getUrl($formId, $this->projectid, $this->id, true).'thumbs/';
		$rowcount 			= 0;		
		$colspan 			= $this->settings->get('igl_show_title_tag') && $this->settings->get('igl_show_alt_tag') ? 5 : 4;
		$imagesPath 		= $this->f2cConfig->get('images_path');
		
		if($imagesPath != $this->convertToRelativePath($imagesPath))
		{
			$thumbsgalleryUrl = 'images/stories/'.$thumbsgalleryUrl;
		}
		
		$thumbsgalleryUrl 		= JUri::root(true).'/'.$thumbsgalleryUrl;
		
		// Add some gallery settings to the front-end script
		$document 						= JFactory::getDocument();
		$feSettings 					= array();
		$feSettings['show_title_tag'] 	= $this->settings->get('igl_show_title_tag');
		$feSettings['show_alt_tag'] 	= $this->settings->get('igl_show_alt_tag');
		$feSettings['jTitleTag']		= JText::_('COM_FORM2CONTENT_TITLE');
		$feSettings['jAltTag']			= JText::_('COM_FORM2CONTENT_ALT_TEXT');
		
		// Make sure the modal script is loaded when cropping is allowed
		if($this->settings->get('igl_cropping', F2C_FIELD_IMAGE_CROP_NOT_ALLOWED) != F2C_FIELD_IMAGE_CROP_NOT_ALLOWED)
		{
			JHTML::_('behavior.modal', 'a.F2cModal');
		}		
		
		$document->addScriptDeclaration('var t' . $this->id . '_settings = \'' . json_encode($feSettings) . '\';');

		$html .= '<table id="'.$this->elementId.'" class="f2c_image_gallery_tbl">';
		
		if($fieldValue && count($fieldValue) > 0)
		{
			foreach($fieldValue as $value)
			{
				$rowId 		= $this->elementId.'_'.$rowcount;
				$state		= $value['STATE'];
				$rowStyle	= $value['STATE'] == 2 ? 'style="display: none;"' : '';	
				$rowcount++;
					
				if(array_key_exists('TMPFILENAME', $value) && $value['TMPFILENAME'])
				{
					$imgSrc = JUri::root(true).Path::Combine($this->f2cConfig->get('images_path'), 'thumb_'.$value['TMPFILENAME']);	
				}
				else 
				{
					$imgSrc = $thumbsgalleryUrl . $value['FILENAME'];	
				}

				$html .= '<tr id="'.$rowId.'" '.$rowStyle.'>
							<td>
								<img src="' . $imgSrc . '" />
								<input type="hidden" name="'.$this->elementId.'RowKey[]" value="'.$rowId.'"/>';
								
	  			$html .= $this->renderHiddenField($rowId.'width', $value['WIDTH']);
	  			$html .= $this->renderHiddenField($rowId.'height', $value['HEIGHT']);
	  			$html .= $this->renderHiddenField($rowId.'thumbwidth', $value['WIDTH_THUMB']);
	  			$html .= $this->renderHiddenField($rowId.'thumbheight', $value['HEIGHT_THUMB']);
	  			$html .= $this->renderHiddenField($rowId.'_cropped', 1);	
	  			$html .= $this->renderHiddenField($rowId.'state', $state);  			
	  			
	  			if(array_key_exists('TMPFILENAME', $value) && $value['TMPFILENAME'])
	  			{
	  				$html .= $this->renderHiddenField($rowId.'filename', $value['TMPFILENAME']);
	  				$html .= $this->renderHiddenField($rowId.'originalfilename', $value['FILENAME']);
	  			}
	  			else 
	  			{
	  				$html .= $this->renderHiddenField($rowId.'filename', $value['FILENAME']);
	  			}
	  			
				$html .= '	</td>';
						
				if($this->settings->get('igl_show_alt_tag') || $this->settings->get('igl_show_title_tag')) 
				{
					$html.=	'<td>
								<table class="f2c_image_gallery_tbl_alt_title">';
					
					if($this->settings->get('igl_show_alt_tag'))
					{
						$html .= '<tr>
									<td>'.JText::_('COM_FORM2CONTENT_ALT_TEXT').'</td>
							   	  	<td><input type="text" id="'.$rowId.'alt" name="'.$rowId.'alt" size="40" value="' . htmlspecialchars($value['ALT']) . '" maxlength="255" '.$this->settings->get('igl_attributes_item_text').' /></td>
							   	  </tr>';
					}
					
					if($this->settings->get('igl_show_title_tag'))
					{
						$html .= '<tr>
									<td>'.JText::_('COM_FORM2CONTENT_TITLE').'</td>
									<td><input type="text" id="'.$rowId.'title" name="'.$rowId.'title" size="40" value="' . htmlspecialchars($value['TITLE']) . '" maxlength="255" '.$this->settings->get('igl_attributes_item_text').' /></td>
							   	  </tr>';
					}
					
					$html.=	'	</table>
							 </td>';
							 
				}

				$html .= 	'<td nowrap>';
				$html .=		'<a href="javascript:moveRowUp(\''.$rowId.'\');"><i class="icon-f2carrow-up f2c_row_button" title="' . JText::_('COM_FORM2CONTENT_UP') . '"></i></a>';
				$html .= 		'<a href="javascript:moveRowDown(\''.$rowId.'\');"><i class="icon-f2carrow-down f2c_row_button" title="' . JText::_('COM_FORM2CONTENT_DOWN') . '"></i></a>';
				$html .=		'<a href="javascript:deleteImageGalleryRow(\''.$rowId.'\');"><i class="icon-f2cminus f2c_row_button" title="' . JText::_('COM_FORM2CONTENT_DELETE') . '"></i></a>';
				$html .=	'</td>
						  </tr>';
			}
		}
		
		$html .= '</table>';
		
		// Div to surround the upload controls. This can be hidden when the max. number of uploads has been reached.
		$html .= '<div id="'.$this->elementId.'_upload_area">';
		
		if($this->settings->get('igl_input_type', F2C_FIELD_IMAGE_UPLOAD) == F2C_FIELD_IMAGE_BROWSE)
		{
			$html .= $form->getInput($this->elementId.'_browse');
		}
		else
		{
			// Render the upload field. For older browsers an iFrame upload will be rendered
			$html .= '<script type="text/javascript">';
			
			if($this->f2cConfig->get('force_iframe_upload', 0))
			{
				$html .= 'formDataSupport = false;';
			}
			
			// See which file types we may upload
			switch($this->settings->get('allow_filetype', 0))
			{
				case 0: // jpg, png and gif
					$extensions = array('jpg', 'jpeg', 'png', 'gif');
					break;
				case 1: // jpg
					$extensions = array('jpg', 'jpeg');
					break;
				case 2: // png
					$extensions = array('png');
					break;
				case 3: // gif
					$extensions = array('gif');
					break;
			}
			
			$jsExtensionsArray = $this->createJsExtensionsArray($extensions);
			
			$html .= 'if (formDataSupport){';
			$html .= 'document.write("'.$this->renderUploadControl($this->elementId,'uploadGalleryImage('.$this->projectid.','.$this->id.','.$jsExtensionsArray.');', $extensions).'")';		
			$html .= '} else {';
			$html .= 'document.write("<iframe id=\"t'.$this->id.'_iframe\" src=\"'.Path::Combine(JUri::root(true), 'index.php?option=com_form2content&view=iframeupload&task=form.renderiframeupload&format=raw&fieldid='.$this->id.'&contenttypeid='.$this->projectid).'\" frameborder=\"0\" height=\"18\" width=\"220\" scrolling=\"no\"></iframe>");';
			$html .= '}';
			$html .= '</script>&nbsp;';
		}
		
		$html .= '</div>';
		
		$html .= $this->renderRequiredText($contentTypeSettings);
		$html .= $this->renderHiddenField('hid'.$this->elementId, $this->internal['fieldcontentid']);
  		$html .= $this->renderHiddenField($this->elementId.'MaxKey', $rowcount);
  		$html .= $this->renderHiddenField($this->elementId.'Cropping', $this->settings->get('igl_cropping', 0));
		$html .= $this->renderHiddenField($this->elementId.'MaxUploads', $this->settings->get('igl_max_num_images'));
		
		return $html;
	}
	
	public function prepareSubmittedData($formId)
	{
		$jinput = JFactory::getApplication()->input;
		
		$this->internal['fieldcontentid'] 	= $jinput->getInt('hid'.$this->elementId);
		$this->values['VALUE'] 				= array();
		$rowKeys 							= $jinput->get($this->elementId.'RowKey', array(), 'ARRAY');		
		$galleryDir 						= self::getPath($formId, $this->projectid, $this->id, false);

		if(count($rowKeys))
		{
			foreach($rowKeys as $rowKey)
			{
				$arrImage 						= array();
				$arrImage['ALT'] 				= $jinput->getString($rowKey . 'alt');
				$arrImage['TITLE'] 				= $jinput->getString($rowKey . 'title');
				$arrImage['STATE']				= $jinput->getInt($rowKey . 'state');	
			
				if($arrImage['STATE'] == 0)
				{
					// new image
					$arrImage['FILEPATH'] 		= $this->baseDir;
					$arrImage['FILENAME'] 		= $jinput->getString($rowKey . 'originalfilename');	
					$arrImage['TMPFILENAME']	= $jinput->getString($rowKey . 'filename');
					$fullImageProps 			= JImage::getImageFileProperties(Path::Combine($this->baseDir, $arrImage['TMPFILENAME']));
					$thumbImageProps			= JImage::getImageFileProperties(Path::Combine($this->baseDir, 'thumb_'.$arrImage['TMPFILENAME'])); 	
					$arrImage['WIDTH'] 			= $fullImageProps->width;
					$arrImage['HEIGHT'] 		= $fullImageProps->height;
					$arrImage['WIDTH_THUMB'] 	= $thumbImageProps->width;
					$arrImage['HEIGHT_THUMB'] 	= $thumbImageProps->height;					
				}
				else 
				{
					// existing image
					$arrImage['FILEPATH'] 		= $galleryDir;
					$arrImage['FILENAME'] 		= $jinput->getString($rowKey . 'filename');
					$arrImage['TMPFILENAME']	= '';
					$arrImage['WIDTH'] 			= $jinput->getInt($rowKey . 'width');
					$arrImage['HEIGHT'] 		= $jinput->getInt($rowKey . 'height');
					$arrImage['WIDTH_THUMB'] 	= $jinput->getInt($rowKey . 'thumbwidth');
					$arrImage['HEIGHT_THUMB'] 	= $jinput->getInt($rowKey . 'thumbheight');
					
				}
				
				$this->values['VALUE'][] = $arrImage;
			}
		}

		return $this;
	}
	
	public function store($formId)
	{
		$defaultThumbWidth 		= $this->f2cConfig->get('default_thumbnail_width', F2C_DEFAULT_THUMBNAIL_WIDTH);
		$defaultThumbHeight 	= $this->f2cConfig->get('default_thumbnail_height', F2C_DEFAULT_THUMBNAIL_HEIGHT);
		$content				= array();
		$fileNames				= array();					
		$fieldId 				= $this->internal['fieldcontentid'];
		$listNew 				= null;
		$valueList				= new JRegistry();
		$galleryDir 			= self::getPath($formId, $this->projectid, $this->id, false);
		$galleryDirThumbs		= Path::Combine($galleryDir, 'thumbs');
		$maxImageWidth 			= $this->settings->get('igl_max_width', 10000);
		$maxImageHeight 		= $this->settings->get('igl_max_height', 10000);
		$imageQuality			= $this->settings->get('igl_image_quality', $this->f2cConfig->get('jpeg_quality', 75));
		$thumbnailWidth			= $this->settings->get('igl_thumb_width', $defaultThumbWidth);
		$thumbnailHeight		= $this->settings->get('igl_thumb_heigth', $defaultThumbHeight);
		$thumbQuality			= $this->settings->get('igl_thumb_quality', $this->f2cConfig->get('jpeg_quality', 75));		
		
		if(!JFolder::exists($galleryDirThumbs))
		{
			JFolder::create($galleryDirThumbs);
		}

		if(count($this->values['VALUE']))
		{
			foreach($this->values['VALUE'] as $imageInfo)
			{ 
				$fileNames[$imageInfo['FILENAME']] 	= $imageInfo['FILENAME'];
				
				switch((int)$imageInfo['STATE'])
				{
					case 0: // new image
						// create a unique filename, prevent duplicates in the gallery directory
						$imageInfo['FILENAME'] 	= $this->createUniqueFilename($galleryDir, $imageInfo['FILENAME']);
						$srcImage 				= Path::Combine($imageInfo['FILEPATH'], $imageInfo['TMPFILENAME']);
						$dstImage 				= Path::Combine($galleryDir, $imageInfo['FILENAME']);
						
						// Resize image and store in gallery directory
						$tmpMaxWidth = $maxImageWidth;
						$tmpMaxHeight = $maxImageHeight;
						
						if(!ImageHelper::ResizeImage($srcImage, $dstImage, $tmpMaxWidth, $tmpMaxHeight, $imageQuality))
						{
							throw new Exception(JText::_('COM_FORM2CONTENT_ERROR_IMAGE_RESIZE_FAILED'));
						}
						
						// Store the resized width and height
						$imageInfo['WIDTH'] = $tmpMaxWidth;
						$imageInfo['HEIGHT'] = $tmpMaxHeight;
						
						// clean-up tmp image
						JFile::delete($srcImage);
						
						JFile::move(Path::Combine($imageInfo['FILEPATH'], 'thumb_'.$imageInfo['TMPFILENAME']), Path::Combine($galleryDirThumbs, $imageInfo['FILENAME']));
						
						// Remove the tmp image before saving
						$imageInfo['TMPFILENAME'] = '';
						
						$listNew[] = $imageInfo;
						break;
						
					case 1: // existing image
						$listNew[] = $imageInfo;
						break;
						
					case 2: // deleted image
						if($imageInfo['FILEPATH'] != $galleryDir)
						{
							// new image in tmp location
							JFile::delete(Path::Combine($imageInfo['FILEPATH'], $imageInfo['TMPFILENAME']));
							JFile::delete(Path::Combine($imageInfo['FILEPATH'], 'thumb_'.$imageInfo['TMPFILENAME']));
						}
						else 
						{		
							// existing image in gallery dir				
							JFile::delete(Path::Combine($galleryDir, $imageInfo['FILENAME']));
							JFile::delete(Path::Combine($galleryDirThumbs, $imageInfo['FILENAME']));
						}
						break;
						
					case 3: // url/remote import
						$tmpImage 	= Path::Combine($galleryDir, uniqid('f2c', true) . '.' . JFile::getExt($imageInfo['FILENAME']));
						$tmpThumb 	= Path::Combine($galleryDir, uniqid('f2c', true) . '.' . JFile::getExt($imageInfo['FILENAME']));						
						$dstImage 	= Path::Combine($galleryDir, $imageInfo['FILENAME']);
						$dstThumb	= Path::Combine($galleryDir.'thumbs', $imageInfo['FILENAME']);

						$this->downloadFile($imageInfo['imagelocation'], $tmpImage);
						// resize image
						$tmpMaxWidth = $maxImageWidth;
						$tmpMaxHeight = $maxImageHeight;
						
						if(!ImageHelper::ResizeImage($tmpImage, $dstImage, $tmpMaxWidth, $tmpMaxHeight, $imageQuality))
						{
							throw new Exception(JText::_('COM_FORM2CONTENT_ERROR_IMAGE_RESIZE_FAILED'));
						}

						// Store the resized width and height
						$imageInfo['WIDTH'] = $tmpMaxWidth;
						$imageInfo['HEIGHT'] = $tmpMaxHeight;
						
						if($imageInfo['thumblocation'])
						{
							$this->downloadFile($imageInfo['thumblocation'], $dstThumb);
						}
						else 
						{
							// create thumbnail image
							if(!ImageHelper::ResizeImage($tmpImage, $dstThumb, $thumbnailWidth, $thumbnailHeight, $thumbQuality))
							{
								throw new Exception(JText::_('COM_FORM2CONTENT_ERROR_IMAGE_RESIZE_FAILED'));
							}
						}

						// Store the resized width and height
						$imageInfo['WIDTH_THUMB'] = $thumbnailWidth;
						$imageInfo['HEIGHT_THUMB'] = $thumbnailHeight;
						
						JFile::delete($tmpImage);
					
						$listNew[] = $imageInfo;				
						break;
						
					case 4: // copy import
						JFile::copy($imageInfo['imagelocation'], Path::Combine($galleryDir, $imageInfo['FILENAME']));						
						JFile::copy($imageInfo['thumblocation'], Path::Combine($galleryDir.'thumbs', $imageInfo['FILENAME']));
						$listNew[] = $imageInfo;
						break;
						
					case 5: // included import
						JFile::move($imageInfo['imagelocation'], Path::Combine($galleryDir, $imageInfo['FILENAME']));
						JFile::move($imageInfo['thumblocation'], Path::Combine($galleryDir.'thumbs', $imageInfo['FILENAME']));
						$listNew[] = $imageInfo;
						break;
				}
			}
		}		
		
		// reset all state values to existing
		if(count($listNew))
		{
			foreach($listNew as &$imageInfo)
			{
				$imageInfo['STATE'] = 1;
			}	
		}
		
		$valueList->loadArray($listNew);
				
		$value 		= $valueList->toString();		
		$action 	= ($value) ? (($fieldId) ? 'UPDATE' : 'INSERT') : (($fieldId) ? 'DELETE' : '');
		$content[] 	= new F2cFieldHelperContent($fieldId, 'VALUE', $value, $action);
		
		return $content;
	}
	
	public function validate()
	{
		$numImages = 0;
		
		// Count all the images except the deleted ones
		if(count($this->values['VALUE']))
		{
			foreach($this->values['VALUE'] as $image)
			{
				if((int)$image['STATE'] != 2) // Deleted
				{
					$numImages++;
				}
			}
		}
		
		if($this->settings->get('requiredfield') && $numImages == 0)
		{
			throw new Exception($this->getRequiredFieldErrorMessage());
		}
		
		$maxNumImages = $this->settings->get('igl_max_num_images', -1);
		
		if($numImages > $maxNumImages && $maxNumImages != -1)
		{
			throw new Exception(sprintf(JText::_('COM_FORM2CONTENT_ERROR_IMAGEGALLERY_MAX_NUM_IMAGES_EXCEEDED'), $maxNumImages, $this->fieldname));
		}
	}
	
	public function getClientSideInitializationScript()
	{
		return "jQuery(document).ready(function(){setUploadState('$this->elementId');});";
	}
	
	public function getClientSideValidationScript(&$validationCounter)
	{
		$script = parent::getClientSideValidationScript($validationCounter);
		
		if($this->settings->get('igl_cropping', F2C_FIELD_IMAGE_CROP_NOT_ALLOWED) == F2C_FIELD_IMAGE_CROP_MANDATORY)
		{
			$script .= 'if(!F2C_ValReqImageGalleryCropping("t'.$this->id.'"))';
			$script .= '{ ';
			$script .= 'alert(\'' . sprintf(JText::_('COM_FORM2CONTENT_ERROR_IMAGE_NOT_CROPPED', true), $this->title) . '\'); ';
			$script .= 'return false; } ';
		}
		
		return $script;
	}
	
	public function copy($formId)
	{
		$galleryPath = self::getPath($formId, $this->projectid, $this->id);
		
		$this->internal['fieldcontentid'] = null;
	
		if(count($this->values['VALUE']))
		{
			foreach($this->values['VALUE'] as &$value)
			{
				// set state to copy
				$value['STATE'] 		= 4;
				$value['imagelocation'] = $value['FILENAME'] ? Path::Combine($galleryPath, $value['FILENAME']) : '';									
				$value['thumblocation'] = $value['FILENAME'] ? Path::Combine(Path::Combine($galleryPath, 'thumbs'), $value['FILENAME']) : '';									
			}
		}
	}
	
	public function getCssClass()
	{
		return 'f2c_field_image_gallery'.htmlspecialchars($this->settings->get('igl_fieldclass_sfx'));
	}
	
	public function export($xmlFields, $formId)
	{
		
      	$xmlField = $xmlFields->addChild('field');
      	$xmlField->fieldname = $this->fieldname;

      	$galleryPath 		= self::getPath($formId, $this->projectid, $this->id);
      	$galleryUrl			= self::getUrl($formId, $this->projectid, $this->id);
      	$exportImageMode	= $this->f2cConfig->get('export_images_mode', 0);					
     	$xmlFieldContent 	= $xmlField->addChild('contentImageGallery');
      						
      	if(is_array($this->values['VALUE']) && count($this->values['VALUE']))
      	{
      		foreach($this->values['VALUE'] as $galleryImage)
      		{
      			$xmlGalleryImage = $xmlFieldContent->addChild('galleryImage');
      			$xmlGalleryImage->filename = $galleryImage['FILENAME'];
      			$xmlGalleryImage->alt = $galleryImage['ALT'];
		      	$xmlGalleryImage->title = $galleryImage['TITLE'];
		      	$xmlGalleryImage->width = $galleryImage['WIDTH'];
		      	$xmlGalleryImage->height = $galleryImage['HEIGHT'];
		      	$xmlGalleryImage->width_thumbnail = $galleryImage['WIDTH_THUMB'];
		      	$xmlGalleryImage->height_thumbnail = $galleryImage['HEIGHT_THUMB'];
      								
		      	switch($exportImageMode)
		      	{
		      		case F2C_EXPORT_FILEMODE_ENCAPSULATE:
		      			$imageLocation 	= Path::Combine($galleryPath, $galleryImage['FILENAME']);
		      			$thumbLocation 	= Path::Combine($galleryPath.'thumbs', $galleryImage['FILENAME']);
		      			$xmlImage 		= $xmlGalleryImage->addChild('image');
						$xmlThumb 		= $xmlGalleryImage->addChild('thumbnail');
		      			$xmlImage->addCData(base64_encode($this->getFileContents($imageLocation)));  
						$xmlImage->addAttribute('includemode', 'include');								      	
						$xmlThumb->addCData(base64_encode($this->getFileContents($thumbLocation)));      							
		      			break;
		      								
		      		case F2C_EXPORT_FILEMODE_LOCAL:
		      			$imageLocation 	= Path::Combine($galleryPath, $galleryImage['FILENAME']);
		      			$thumbLocation 	= Path::Combine(Path::Combine($galleryPath, 'thumbs'), $galleryImage['FILENAME']);
		      			$xmlImage 		= $xmlGalleryImage->addChild('image', self::valueReplace($imageLocation));
		      			$xmlThumb 		= $xmlGalleryImage->addChild('thumbnail', self::valueReplace($thumbLocation));
		      			$xmlImage->addAttribute('includemode', 'path');
		      			break;
		      								
		      		case F2C_EXPORT_FILEMODE_REMOTE:
		      			$imageLocation 	= Path::Combine($galleryUrl, $galleryImage['FILENAME']);
		      			$thumbLocation 	= Path::Combine($galleryUrl.'thumbs', $galleryImage['FILENAME']);
		      			$xmlImage 		= $xmlGalleryImage->addChild('image', self::valueReplace($imageLocation));
		      			$xmlThumb		= $xmlGalleryImage->addChild('thumbnail', self::valueReplace($thumbLocation));
		      			$xmlImage->addAttribute('includemode', 'url');
		      			break;
		      	}
      		}
      	}
	}
	
	public function import($xmlField, $existingInternalData, $formId)
	{
		$this->internal['fieldcontentid'] 	= $formId ? $existingInternalData['fieldcontentid'] : 0;
		$this->internal['method'] 			= 'copy';
		$this->internal['delete']			= 0;
		$this->values['VALUE']				= array();
		
		foreach ($xmlField->contentImageGallery->children() as $xmlGalleryImage) 
		{
			$arrImage 								= array();
			$arrImage['FILENAME'] 			= (string)$xmlGalleryImage->filename;
			$arrImage['ALT'] 				= (string)$xmlGalleryImage->alt;
			$arrImage['TITLE'] 				= (string)$xmlGalleryImage->title;
			$arrImage['WIDTH'] 				= (string)$xmlGalleryImage->width;
			$arrImage['HEIGHT'] 			= (string)$xmlGalleryImage->height;
			$arrImage['WIDTH_THUMB'] 		= (string)$xmlGalleryImage->width_thumbnail;
			$arrImage['HEIGHT_THUMB'] 		= (string)$xmlGalleryImage->height_thumbnail;	      						
			
			switch((string)$xmlGalleryImage->image->attributes()->includemode)
			{
				case 'url':
					$arrImage['imagelocation'] 	= (string)$xmlGalleryImage->image;
					$arrImage['thumblocation'] 	= (string)$xmlGalleryImage->thumbnail;
					$arrImage['STATE'] 			= 3;
					break;
					
				case 'path':
					$arrImage['imagelocation'] 	= (string)$xmlGalleryImage->image;
					$arrImage['thumblocation'] 	= (string)$xmlGalleryImage->thumbnail;
					$arrImage['STATE'] 			= 4;
					break;
				case 'include':
					// encapsulated image
					$imagesPath		= Path::Combine(JPATH_SITE, $f2cConfig->get('images_path'));		      							
					$tmpImage 		= Path::Combine($imagesPath, uniqid('f2c', true) . '.' . JFile::getExt($arrImage['FILENAME']));
					$tmpThumb 		= Path::Combine($imagesPath, uniqid('f2c', true) . '.' . JFile::getExt($arrImage['FILENAME']));
					$decodedImage 	= base64_decode((string)$xmlGalleryImage->image);
					$decodedThumb	= base64_decode((string)$xmlGalleryImage->thumbnail);
					
					JFile::write($tmpImage, $decodedImage);
					JFile::write($tmpThumb, $decodedThumb);
												
					$arrImage['imagelocation'] 	= $tmpImage;
					$arrImage['thumblocation'] 	= $tmpThumb;
					$arrImage['STATE'] 			= 5;
					break;
			}
			
			$this->values['VALUE'][] = $arrImage;
		}
	}
	
	public function addTemplateVar($templateEngine, $form)
	{
		$images 		= array();
		
		if(count($this->values['VALUE']))
		{
			foreach ($this->values['VALUE'] as $imageData) 
			{
				$image 					= array();
				$image['ALT'] 			= $imageData['ALT'];
				$image['TITLE']			= $imageData['TITLE'];
				$image['FILENAME']		= $imageData['FILENAME'];
				$image['WIDTH']			= $imageData['WIDTH'];
				$image['HEIGHT']		= $imageData['HEIGHT'];
				$image['WIDTH_THUMB']	= $imageData['WIDTH_THUMB'];
				$image['HEIGHT_THUMB']	= $imageData['HEIGHT_THUMB'];
				$images[]				= $image;
			}
		}
		
		$templateEngine->addVar($this->fieldname, self::getPath($form->id, $this->projectid, $this->id));		
		$templateEngine->addVar($this->fieldname.'_PATH_ABSOLUTE', self::getPath($form->id, $this->projectid, $this->id));
		$templateEngine->addVar($this->fieldname.'_PATH_RELATIVE', self::getPath($form->id, $this->projectid, $this->id, true));		
		$templateEngine->addVar($this->fieldname.'_URL_ABSOLUTE', self::getUrl($form->id, $this->projectid, $this->id));
		$templateEngine->addVar($this->fieldname.'_URL_RELATIVE', self::getUrl($form->id, $this->projectid, $this->id, true));
		$templateEngine->addVar($this->fieldname.'_IMAGES', $images);		
	}
	
	public function getTemplateParameterNames()
	{
		$names = array(	strtoupper($this->fieldname).'_PATH_ABSOLUTE',
						strtoupper($this->fieldname).'_PATH_RELATIVE',
						strtoupper($this->fieldname).'_URL_ABSOLUTE',
						strtoupper($this->fieldname).'_URL_RELATIVE',
						strtoupper($this->fieldname).'_IMAGES');
		
		return array_merge($names, parent::getTemplateParameterNames());
	}

	public function setData($data)
	{
		$values 							= new JRegistry($data->content);
		$this->values['VALUE'] 				= $values->toArray();
		$this->internal['fieldcontentid']	= $data->fieldcontentid;
		$this->internal['basedir']			= $this->baseDir.DIRECTORY_SEPARATOR.'p'.$data->projectid.DIRECTORY_SEPARATOR.'f'.$data->formid.DIRECTORY_SEPARATOR.'gallery'.$data->id;
		
		// Fix for upgrade from earlier versions: state was incorrectly stored
		foreach($this->values['VALUE'] as &$value)
		{
			if(array_key_exists('STATE', $value))
			{
				$value['STATE'] = '1';
				$value['TMPFILENAME'] = '';
 			}
		}
	}
	
	public function preprocessForm(JForm $form)
	{
		// required setting will not be handled at this level
		$required 	= 'false';
		$rootdir	= $this->settings->get('igl_browseserver_root');
		$xml 		= '<field name="t'.$this->id.'_browse" type="F2cFileBrowser" label="" description="" preview="false" selectionrequired="'.$required.'" directory="'.$rootdir.'" onchange="transferGalleryImage('.$this->projectid.','.$this->id.');" />';
		$xmlElement = new SimpleXMLElement($xml);
		
		$form->setField($xmlElement);
	}
	
	public function cancel()
	{
		$jinput 	= JFactory::getApplication()->input;
		$imgBaseDir = Path::Combine(JPATH_SITE, $this->f2cConfig->get('images_path'));
		
		// check if temporary images were uploaded
		$rowKeys = $jinput->get('t'.$this->id.'RowKey', array(), 'ARRAY');
		
		if(count($rowKeys))
		{
			foreach($rowKeys as $rowKey)
			{
				if($jinput->getString($rowKey . 'originalfilename') != '')
				{
					$tmpImage = $jinput->getString($rowKey . 'filename');
					JFile::delete(Path::Combine($imgBaseDir, $tmpImage));
					JFile::delete(Path::Combine($imgBaseDir, 'thumb_'.$tmpImage));
				}
			}
		}
	}
	
	public function clearFile()
	{
		$baseFile	= JFactory::getApplication()->input->get('file');
		$image 		= Path::Combine($this->baseDir, $baseFile);
		$thumbnail 	= Path::Combine($this->baseDir, 'thumb_'.$baseFile);
		
		if(JFile::exists($image))
		{
			JFile::delete($image);
		}

		if(JFile::exists($thumbnail))
		{
			JFile::delete($thumbnail);
		}
	}
	
	public function postUploadCheck(&$resultInfo, $file)
	{
		if($this->f2cConfig->get('filename_restriction', 1) == 0)
		{
			// Check the filename, according to Joomla's (Media Manager) standards
			$file = $resultInfo['originalfilename'];
			
			if (str_replace(' ', '', $file) != $file || $file !== JFile::makeSafe($file))
			{
				$resultInfo['error'] = JText::_('COM_FORM2CONTENT_ERROR_WARNFILENAME');		
				return false;
			}
		}
		
		// Check if the image dimensions are within the minimum dimensions
		$minWidth 	= (int)$this->settings->get($this->getPrefix().'_min_width');
		$minHeight 	= (int)$this->settings->get($this->getPrefix().'_min_height');

		if($minWidth > 0 || $minHeight > 0)
		{
			$image = new JImage($file['tmp_name']);
			
			if($minWidth > 0 && $image->getWidth() < $minWidth)
			{
				$resultInfo['error'] = sprintf(JText::_('COM_FORM2CONTENT_ERROR_IMAGE_MIN_WIDTH'), $image->getWidth(), $minWidth);
				return false;
			}
			
			if($minHeight > 0 && $image->getHeight() < $minHeight)
			{
				$resultInfo['error'] = sprintf(JText::_('COM_FORM2CONTENT_ERROR_IMAGE_MIN_HEIGHT'), $image->getHeight(), $minHeight);
				return false;
			}
		}
		
		// Auto-convert the filename to Joomla's (Media Manager) standards
		$resultInfo['originalfilename'] = JFile::makeSafe(str_replace(' ', '_', $resultInfo['originalfilename']));
		return true;		
	}
	
	public function deleteContentType()
	{
		// remove the base image dir
		$baseDir = Path::Combine(self::getBase(), 'p'.$this->projectid);
		
		if(JFolder::exists($baseDir))
		{
			JFolder::delete($baseDir);
		}
	}
	
	public function deleteArticle($formId)
	{
		Path::Remove((Path::Combine(self::getBase(), 'p'.$this->projectid.'/f'.$formId)));
	}
	
	public static function getPath($formId, $projectId, $fieldId, $relative = false)
	{
		$base = self::getBase(false, $relative);

		if($formId)
		{
			$base .= 'p'.$projectId.'/f'.$formId.'/gallery'.$fieldId;
		}
		
		return $base . '/';
	}
	
	public static function getUrl($formId, $projectId, $fieldId, $relative = false)
	{
		$base = self::getBase(true, $relative);
		
		if($formId)
		{
			$base .= 'p'.$projectId.'/f'.$formId.'/gallery'.$fieldId;
		}
		
		return $base . '/';
	}
	
	private static function getBase($url, $relative = false)
	{
		$imagesPath = F2cFactory::getConfig()->get('images_path');
		
		if($relative)
		{
			return self::convertToRelativePath($imagesPath).'/';
		}
		else
		{
			return ($url ? F2cUri::GetClientRoot() : JPATH_SITE.'/').$imagesPath.'/';
		}				
	}
	
	/* For backward compatibility purposes the path is relative to images/stories when the image path is images/stories/xxxxxxxxxxx.
	 * In all other cases the relative path is relative to the website root
	 */		
	private static function convertToRelativePath($path)
	{
		$search = 'images/stories/';

		if(stripos($path, $search) === 0)
		{
			return substr($path, strlen($search));
		}
		else
		{
			return $path;
		}
	}
}
?>