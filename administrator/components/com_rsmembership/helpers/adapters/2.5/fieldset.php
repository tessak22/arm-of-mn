<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
class RSFieldset {
	public function startFieldset($legend='', $class='adminform', $display = true) {
		$return = '<fieldset class="' . $class . '">';
			if ($legend) { 
				$return .= '<legend>'. $legend .'</legend>';
			}
			$return .= '<ul class="config-option-list">';

			if ($display)
				echo $return;
			else 
				return $return;
	}

	public function showField($label, $input, $display = true) {
		$return = '<li>' . $label . $input . '</li>';

		if ($display) 
			echo $return;
		else 
			return $return;
	}
	
	public function endFieldset($display = true) {
		$return = '</ul></fieldset><div class="clr"></div>';

		if ($display) echo $return;
		else return $return;
	}
}