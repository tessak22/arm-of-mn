<?php
/**
* @package RSEvents!Pro
* @copyright (C) 2015 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined( '_JEXEC' ) or die( 'Restricted access' ); 

abstract class JHTMLRSEventsPro
{
	/**
	 * Array containing information for loaded files
	 *
	 * @var    array
	 * @since  2.5
	 */
	protected static $loaded = array();
	
	/**
	 * Load calendar script
	 *
	 * @return void
	 */
	public static function loadCalendar() {
		// Only load once
		if (isset(static::$loaded[__METHOD__])) {
			return;
		}
		
		$document = JFactory::getDocument();
		$document->addStyleSheet(JURI::root(true).'/components/com_rseventspro/assets/css/bootstrap-datetimepicker.min.css');
		
		if ($document->getType() == 'html') {
			$document->addCustomTag('<script src="'.JURI::root(true).'/components/com_rseventspro/assets/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>');
			$document->addCustomTag('<script src="'.JURI::root(true).'/components/com_rseventspro/assets/js/bootstrap.fix.js?v='.RSEPRO_RS_REVISION.'" type="text/javascript"></script>');
		}
		
		static::$loaded[__METHOD__] = true;
	}
	
	/**
	 * Display the calendar
	 *
	 * @return html
	 */
	public static function rscalendar($name, $value = '', $allday = false, $time = true, $onchange = null, $attribs = null) {
		// Load scripts
		self::loadCalendar();
		
		$id		= self::createID($name);
		$h12	= rseventsproHelper::getConfig('time_format','int');
		$sec	= rseventsproHelper::getConfig('hideseconds','int',0);
		$format = $h12 ? 'yyyy-MM-dd HH:mm'.($sec ? '' : ':ss').' PP' : 'yyyy-MM-dd hh:mm'.($sec ? '' : ':ss');
		$format = $allday ? 'yyyy-MM-dd' : $format;
		$time	= $allday ? false : $time;
		$value	= htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
		$value	= $value == JFactory::getDbo()->getNullDate() ? '' : $value;
		$clear	= true;
		$dummy	= $h12 && !$allday;
		
		if ($id == 'jform_start' || $id == 'jform_end') {
			if ($h12 && $allday) {
				$dummy = true;
			}
		}
		
		if (is_array($attribs)) {
			$attribs['class'] = isset($attribs['class']) ? $attribs['class'] : 'input-medium';
			$attribs['class'] = trim($attribs['class']);
			
			if (array_key_exists('clear', $attribs)) {
				$clear = $attribs['clear'];
				unset($attribs['clear']);
			}

			$attribs = JArrayHelper::toString($attribs);
		}
		
		$html	= array();
		$script	= array();
		
		$script[] = 'jQuery(document).ready(function (){';
		$script[] = "\t".'jQuery("#'.$id.'_datetimepicker").datetimepicker({';
		
		// Trigger the custom function, if exist
		if ($onchange) {
			$script[] = "\t\t".'onChangeFnct: function() { '.$onchange.' },';
		}
		
		// Show/Hide the time selector area
		$script[] = "\t\t".'pickTime: '.($time ? 'true' : 'false').',';
		
		// Remove seconds from the calendar
		if ($sec) {
			$script[] = "\t\t".'pickSeconds: false,';
		}
		
		// Set the custom values for the 12h time period
		if ($dummy) {
			$script[] = "\t\t".'pick12HourFormat: true,';
			$script[] = "\t\t".'linkField: "'.$id.'",';
		}
		
		// Set the format of the date
		$script[] = "\t\t".'format: "'.$format.'"';
		
		$script[] = "\t".'});';
		$script[] = '});';
		
		// Add script declaration that initialize the calendar
		JFactory::getDocument()->addScriptDeclaration(implode("\n",$script));

		$calendarid		= $dummy ? $id.'_dummy' : $id;
		$calendarname	= $dummy ? $id.'_dummy' : $name;
		
		if ($value) {
			if ($value == 'today') {
				$thevalue = $value;
			} else {		
				if ($allday) {
					$thevalue = rseventsproHelper::showdate($value,'Y-m-d');
				} else {
					if ($h12) {
						$thevalue = rseventsproHelper::showdate($value,'Y-m-d h:i'.($sec ? '' : ':s').' A');
					} else {
						$thevalue = rseventsproHelper::showdate($value,'Y-m-d H:i'.($sec ? '' : ':s'));
					}
				}
			}
		} else {
			$thevalue = '';
		}
		
		$html[] = '<div id="'.$id.'_datetimepicker" class="input-append" data-date-weekstart="'.intval(JText::_('COM_RSEVENTSPRO_CALENDAR_START_DAY')).'">';
		$html[] = '<input type="text" name="'.$calendarname.'" id="'.$calendarid.'" value="'.$thevalue.'" '.$attribs.' />';
		$html[] = '<button class="btn" type="button">';
		$html[] = '<i class="icon-calendar"></i>';
		$html[] = '</button>';
		
		if ($clear) {
			$html[] = '<button class="btn" type="button">';
			$html[] = '<i class="icon-remove"></i>';
			$html[] = '</button>';
		}
		
		$html[] = '</div>';
		
		if ($dummy) {
			if ($value) {
				if ($value != 'today') {
					$value = rseventsproHelper::showdate($value,'Y-m-d H:i:s');
				}
			} else {
				$value = '';
			}
			$html[] = '<input type="hidden" id="'.$id.'" name="'.$name.'" value="'.$value.'" />';
		}
		
		return implode("\n",$html);
	}
	
	/**
	 *	Deprecated
	 */
	public static function calendar($value, $name, $id, $format = '%Y-%m-%d', $readonly = false, $js = false, $no12 = false, $allday = 0) {
		return self::rscalendar($name, $value, $allday);
	}
	
	/**
	 * @param   int $value	The state value
	 * @param   int $i
	 */
	public static function featured($value = 0, $i) {
		// Array of image, task, title, action
		$states	= array(
			0	=> array((rseventsproHelper::isJ3() ? 'star-empty' : 'disabled.png'),	'events.featured',		'COM_RSEVENTSPRO_UNFEATURED',	'COM_RSEVENTSPRO_TOGGLE_TO_FEATURE'),
			1	=> array((rseventsproHelper::isJ3() ? 'star' : 'featured.png'),			'events.unfeatured',	'COM_RSEVENTSPRO_FEATURED',		'COM_RSEVENTSPRO_TOGGLE_TO_UNFEATURE'),
		);
		$state	= JArrayHelper::getValue($states, (int) $value, $states[1]);
		$icon	= $state[0];
		$image 	= JHtml::_('image', 'admin/'.$state[0], JText::_($state[2]), NULL, true);
		
		if (rseventsproHelper::isJ3()) {
			$html	= '<a href="#" onclick="return listItemTask(\'cb'.$i.'\',\''.$state[1].'\')" class="btn btn-micro hasTooltip' . ($value == 1 ? ' active' : '') . '" title="'.JText::_($state[3]).'"><i class="icon-'
					. $icon.'"></i></a>';
		} else {
			$html	= '<a href="#" onclick="return listItemTask(\'cb'.$i.'\',\''.$state[1].'\')" class="'.rseventsproHelper::tooltipClass() . ($value == 1 ? ' active' : '') . '" title="'.rseventsproHelper::tooltipText(JText::_($state[3])).'">'
					. $image.'</a>';
		}

		return $html;
	}
	
	public static function chosen($selector = '.rsepro-chosen', $options = array()) {
		$doc = JFactory::getDocument();
		$doc->addStyleDeclaration('.rsepro-chosen {width: 220px;}');
		
		if (rseventsproHelper::isJ3()) {
			JHtml::_('formbehavior.chosen', $selector, null, $options);
		} else {
			if (isset(static::$loaded[__METHOD__][$selector])) {
				return;
			}
			
			// Default settings
			$options['disable_search_threshold']  = isset($options['disable_search_threshold']) ? $options['disable_search_threshold'] : 10;
			$options['allow_single_deselect']     = isset($options['allow_single_deselect']) ? $options['allow_single_deselect'] : true;
			$options['placeholder_text_multiple'] = isset($options['placeholder_text_multiple']) ? $options['placeholder_text_multiple']: JText::_('JGLOBAL_SELECT_SOME_OPTIONS');
			$options['placeholder_text_single']   = isset($options['placeholder_text_single']) ? $options['placeholder_text_single'] : JText::_('JGLOBAL_SELECT_AN_OPTION');
			$options['no_results_text']           = isset($options['no_results_text']) ? $options['no_results_text'] : JText::_('JGLOBAL_SELECT_NO_RESULTS_MATCH');

			// Options array to json options string
			$options_str = json_encode($options);
			
			if ($doc->getType() == 'html') {
				$doc->addCustomTag('<script src="'.JURI::root(true).'/components/com_rseventspro/assets/js/chosen.jquery.min.js" type="text/javascript"></script>');
			}
			$doc->addStyleSheet(JURI::root(true).'/components/com_rseventspro/assets/css/chosen.css');
			$doc->addScriptDeclaration("
					jQuery(document).ready(function (){
						jQuery('" . $selector . "').chosen(" . $options_str . ");
					});
				"
			);

			static::$loaded[__METHOD__][$selector] = true;

			return;
		}
	}
	
	
	public static function tags($selector, $options = array()) {
		
		$chosenAjaxSettings = new JRegistry(
			array(
				'selector'      => $selector,
				'type'          => array_key_exists('type',$options) ? $options['type'] : 'POST',
				'url'           => array_key_exists('url',$options) ? $options['url'] : JUri::base().'index.php?option=com_rseventspro&task=filter&type=tags&condition=contains&method=json&output=1',
				'dataType'      => array_key_exists('dataType',$options) ? $options['dataType'] :'json',
				'jsonTermKey'   => array_key_exists('jsonTermKey',$options) ? $options['jsonTermKey'] :'search',
				'minTermLength' => array_key_exists('minTermLength',$options) ? $options['minTermLength'] :'2'
			)
		);
		
		self::loadTags($selector, $chosenAjaxSettings);
		
		JText::script('JGLOBAL_KEEP_TYPING');
		JText::script('JGLOBAL_LOOKING_FOR');
		
		JFactory::getDocument()->addScriptDeclaration("
			(function($){
				$(document).ready(function () {

					var customTagPrefix = '';

					// Method to add tags pressing enter
					$('" . $selector . "_chzn input').keyup(function(event) {

						// Tag is greater than the minimum required chars and enter pressed
						if (this.value && this.value.length >= " . $chosenAjaxSettings->get('minTermLength',2) . " && (event.which === 13 || event.which === 188)) {

							// Search an highlighted result
							var highlighted = $('" . $selector . "_chzn').find('li.active-result.highlighted').first();

							// Add the highlighted option
							if (event.which === 13 && highlighted.text() !== '') {
							
								// Extra check. If we have added a custom tag with this text remove it
								var customOptionValue = customTagPrefix + highlighted.text();
								$('" . $selector . " option').filter(function () { return $(this).val() == customOptionValue; }).remove();

								// Select the highlighted result
								var tagOption = $('" . $selector . " option').filter(function () { return $(this).html() == highlighted.text(); });
								tagOption.attr('selected', 'selected');
							}
							// Add the custom tag option
							else {
								var customTag = this.value;

								// Extra check. Search if the custom tag already exists (typed faster than AJAX ready)
								var tagOption = $('" . $selector . " option').filter(function () { return $(this).html() == customTag; });
								if (tagOption.text() !== '') {
									tagOption.attr('selected', 'selected');
								} else {
									var option = $('<option>');
									option.text(this.value).val(customTagPrefix + this.value);
									option.attr('selected','selected');

									// Append the option an repopulate the chosen field
									$('" . $selector . "').append(option);
								}
							}

							this.value = '';
							$('" . $selector . "').trigger('liszt:updated');
							event.preventDefault();
						}
					});
				});
			})(jQuery);
			"
		);
	}
	
	protected static function loadTags($selector, $options) {
		// Retrieve options/defaults
		$selector       = $options->get('selector', '.tagfield');
		$type           = $options->get('type', 'POST');
		$url            = $options->get('url', null);
		$dataType       = $options->get('dataType', 'json');
		$jsonTermKey    = $options->get('jsonTermKey', 'search');
		$afterTypeDelay = $options->get('afterTypeDelay', '500');
		$minTermLength  = $options->get('minTermLength', '2');
		$document		= JFactory::getDocument();
		
		if (empty($url)) {
			return;
		}
		
		if (isset(static::$loaded[__METHOD__][$selector])) {
			return;
		}
		
		if ($document->getType() == 'html') {
			$document->addCustomTag('<script src="'.JURI::root(true).'/components/com_rseventspro/assets/js/chosen.ajax.jquery.min.js" type="text/javascript"></script>');
		}
		$document->addScriptDeclaration("
			(function($){
				$(document).ready(function () {
					$('" . $selector . "').ajaxChosen({
						type: '" . $type . "',
						url: '" . $url . "',
						dataType: '" . $dataType . "',
						jsonTermKey: '" . $jsonTermKey . "',
						afterTypeDelay: '" . $afterTypeDelay . "',
						minTermLength: '" . $minTermLength . "'
					}, function (data) {
						var results = [];

						$.each(data, function (i, val) {
							results.push({ value: val.value, text: val.text });
						});

						return results;
					});
				});
			})(jQuery);
			"
		);

		static::$loaded[__METHOD__][$selector] = true;
		return;
	}
	
	protected static function createID($name) {
		return str_replace(array('[]','[',']'),array('','_',''),$name);
	}
}