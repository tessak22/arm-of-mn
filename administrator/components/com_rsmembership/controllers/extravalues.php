<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSMembershipControllerExtraValues extends JControllerAdmin
{
	protected $text_prefix = 'COM_RSMEMBERSHIP';

	public function __construct($config = array()) 
	{
		parent::__construct($config);
	}

	public function getModel($name = 'ExtraValue', $prefix = 'RSMembershipModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function delete()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to remove from the request.
		$cid 		= JFactory::getApplication()->input->get('cid', array(), 'array');
		$extra_id	= JFactory::getApplication()->input->get('extra_id', 0, 'int');
		// Get the model.
		$model = $this->getModel();

		// Make sure the item ids are integers
		jimport('joomla.utilities.arrayhelper');
		JArrayHelper::toInteger($cid);

		// Remove the items.
		if ($model->delete($cid)) 
			$this->setMessage(JText::plural($this->text_prefix . '_N_ITEMS_DELETED', count($cid)));
		else
			$this->setMessage($model->getError());

		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list.'&extra_id=' . $extra_id, false));
	}

	public function saveOrderAjax() 
	{
		$pks 	= $this->input->post->get('cid', array(), 'array');
		$order 	= $this->input->post->get('order', array(), 'array');

		// Sanitize the input
		JArrayHelper::toInteger($pks);
		JArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return)
			echo "1";

		// Close the application
		JFactory::getApplication()->close();
	}

	public function saveorder()
	{
		parent::saveorder();

		$extra_id = JFactory::getApplication()->input->get('extra_id', 0, 'int');
		$extra_id_url = (!empty($extra_id) ? '&extra_id='.$extra_id : '');
		
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $extra_id_url, false));
	}

	public function reorder()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$extra_id 		= JFactory::getApplication()->input->get('extra_id', 0, 'int');
		$extra_id_url 	= (!empty($extra_id) ? '&extra_id='.$extra_id : '');
		$return 		= parent::reorder();

		if ($return === false)
		{
			// Reorder failed.
			$message = JText::sprintf('JLIB_APPLICATION_ERROR_REORDER_FAILED', $model->getError());
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $extra_id_url, false), $message, 'error');
			return false;
		}
		else
		{
			// Reorder succeeded.
			$message = JText::_('JLIB_APPLICATION_SUCCESS_ITEM_REORDERED');
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $extra_id_url, false), $message);
			return true;
		}
	}

	
	public function publish()
	{
		parent::publish();

		$extra_id = JFactory::getApplication()->input->get('extra_id', 0, 'int');
		$extra_id_url = (!empty($extra_id) ? '&extra_id='.$extra_id : '');

		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $extra_id_url, false));
	}
}