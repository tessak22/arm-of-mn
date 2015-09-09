<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSAccordion
{
	protected $id		= null;
	protected $titles 	= array();
	protected $contents = array();
	
	public function __construct($id) {
		$this->id = preg_replace('#[^A-Z0-9_\. -]#i', '', $id);
	}

	public function addTitle($label, $id) {
		$this->titles[] = (object) array('label' => $label, 'id' => $id);
	}

	public function addContent($content) {
		$this->contents[] = $content;
	}

	public function render() {
		echo JHtml::_('sliders.start', $this->id, array('useCookie' => 1));
		foreach ($this->titles as $i => $title) {
			echo JHtml::_('sliders.panel', JText::_($title->label), $title->id);
			echo $this->contents[$i];
		}
		echo JHtml::_('sliders.end');
	}
}