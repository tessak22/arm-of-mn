<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2011-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class mFieldType_corerating extends mFieldType_number
{
	var $name = 'link_rating';
	var $numOfInputFields = 0;

	function getOutput($view=1) {
		global $mtconf;

		$outputFormat = $this->getParam('outputFormat',1);

		// Output Stars
		if ($outputFormat ==1)
		{
			$star = round($this->getValue(), 0);
			$html = '';

			// Print stars
			for( $i=0; $i<$star; $i++) {
				$html .= '<img src="'.$mtconf->getjconf('live_site').$mtconf->get('relative_path_to_rating_image').'star_10.png" width="16" height="16" hspace="1" alt="Star10" />';
			}
			// Print blank star
			for( $i=$star; $i<5; $i++) {
				$html .= '<img src="'.$mtconf->getjconf('live_site').$mtconf->get('relative_path_to_rating_image').'star_00.png" width="16" height="16" hspace="1" alt="Star00" />';
			}

			return $html;
		}

		// Output the rating values
		return parent::getOutput($view);
	}

	function getJSValidation() {
		return null;
	}
}
?>