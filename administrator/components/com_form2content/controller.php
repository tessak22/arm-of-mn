<?php
defined('JPATH_PLATFORM') or die;

jimport('joomla.application.component.controller');

class Form2ContentController extends JControllerLegacy
{
	protected $default_view = 'forms';

	public function display($cachable = false, $urlparams = false)
	{
		parent::display();
		return $this;
	}
}