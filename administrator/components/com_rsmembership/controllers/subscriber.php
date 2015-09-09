<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class RSMembershipControllerSubscriber extends JControllerForm
{
	public function __construct() {
		parent::__construct();
	}

	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'user_id')
	{
		$append    = parent::getRedirectToItemAppend($recordId, $urlVar);
		$user_id   = JFactory::getApplication()->input->get('id', 0,'int');
		$temp_id   = JFactory::getApplication()->input->get('temp_id', 0,'int');

		if ($user_id) 
			$append .= '&user_id=' . $user_id;

		$model = $this->getModel();
		if ($temp_id = $model->getTempId()) {
			$append .= '&temp_id=' . $temp_id;
		}

		return $append;
	}

	public function cancel($key = null) 
	{
		parent::cancel($key);
		
		$model = $this->getModel();
		if ($model->getTempId()) {
			$this->setRedirect( JRoute::_('index.php?option=com_rsmembership&view=transactions', false) );
		}
	}
}