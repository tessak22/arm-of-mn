<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class RSMembershipControllerReports extends JControllerForm
{
	public function getdata()
	{
		$jinput   = JFactory::getApplication()->input;
		$filters  = $jinput->get('jform', array(), 'array');
		
		$model 	  = $this->getModel('Reports', 'RSMembershipModel');
		$response = $model->getReportData($filters);

		echo json_encode($response);
		exit;
	}
}