<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('text');

class JFormFieldDate extends JFormFieldText {
	protected $type = 'Date';

	public function getInput() {
		$doc = JFactory::getDocument();
		$db  = JFactory::getDbo();
		
		// jQuery UI JS
		$doc->addScript(JURI::root(true).'/administrator/components/com_rsmembership/assets/js/ui/core.js');
		$doc->addScript(JURI::root(true).'/administrator/components/com_rsmembership/assets/js/ui/widget.js');
		$doc->addScript(JURI::root(true).'/administrator/components/com_rsmembership/assets/js/ui/mouse.js');
		$doc->addScript(JURI::root(true).'/administrator/components/com_rsmembership/assets/js/ui/slider.js');
		$doc->addScript(JURI::root(true).'/administrator/components/com_rsmembership/assets/js/ui/datepicker.js');
		$doc->addScript(JURI::root(true).'/administrator/components/com_rsmembership/assets/js/ui/timepicker.js');
		
		// & CSS
		$doc->addStyleSheet(JURI::root(true).'/administrator/components/com_rsmembership/assets/css/ui/jquery.ui.all.css');
		$doc->addStyleSheet(JURI::root(true).'/administrator/components/com_rsmembership/assets/css/ui/jquery.ui.timepicker.css');
		
		// Initialize
		$doc->addScriptDeclaration("jQuery(document).ready(function($){
			$('#".$this->id."').datetimepicker({
				changeMonth: true,
				changeYear: true,
				dateFormat: 'yy-mm-dd',
				timeFormat: 'HH:mm:ss'
			});
		
			$('#".$this->id."_img').click(function(){
				$('#".$this->id."').datetimepicker('show');
			});

		});");
		
		if ($this->value == $db->getNullDate()) {
			$this->value = '';
		} else {
			$this->value = JHtml::_('date', $this->value, 'Y-m-d H:i:s');
		}
		
		$html[] = '<div class="input-append">';
		$html[] = parent::getInput();
		$html[] = '<span id="'.$this->id.'_img" class="add-on rsme_pointer"><i class="icon-calendar"></i></span>';
		$html[] = '</div>';
		return implode("\n", $html);
	}
}