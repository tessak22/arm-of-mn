<?php
/**
* @package RSEvents!Pro
* @copyright (C) 2015 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

class rseventsproViewRseventspro extends JViewLegacy
{
	public function display($tpl = null) {
		$lists = array();
		$db			= JFactory::getDbo();
		$query		= $db->getQuery(true);
		$app		= JFactory::getApplication();
		$user		= JFactory::getUser();
		$layout		= $this->getLayout();
		$pathway	= $app->getPathWay();
		$menus		= $app->getMenu();
		$menu		= $menus->getActive();
		
		// Get menu parameters , user permission etc.
		$this->user			= $this->get('User');
		$this->admin		= rseventsproHelper::admin();
		$this->params		= rseventsproHelper::getParams();
		$this->permissions	= rseventsproHelper::permissions();
		$this->pdf			= rseventsproHelper::pdf();
		$this->config		= rseventsproHelper::getConfig();
		$this->operator		= $this->get('Operator');
		$this->document		= JFactory::getDocument();
		
		// Load RSEvents!Pro plugins
		rseventsproHelper::loadPlugins();
		
		// Add Joomla! 2.5 menu metadata
		if ($this->params->get('menu-meta_description'))
			$this->document->setDescription($this->params->get('menu-meta_description'));

		if ($this->params->get('menu-meta_keywords'))
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));

		if ($this->params->get('robots'))
			$this->document->setMetadata('robots', $this->params->get('robots'));
		
		// Add search bar
		if ($this->params->get('search',1)) {
			if ($this->document->getType() == 'html') {
				$this->document->addCustomTag('<script src="'.JURI::root(true).'/components/com_rseventspro/assets/js/jquery.filter.js?v='.RSEPRO_RS_REVISION.'" type="text/javascript"></script>');
			}
		}
		
		if ($layout == 'default') {
			// Get events
			$this->events	= $this->get('events');
			$this->total	= $this->get('total');
			$this->fid		= $this->get('FilterId');
			
			// Add feed
			if ($this->params->get('rss',1)) {
				$link	= '&format=feed';
				$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
				$this->document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
				$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
				$this->document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);
			}
			
			$filters			= $this->get('filters');
			$this->columns		= $filters[0];
			$this->operators	= $filters[1];
			$this->values		= $filters[2];
			$this->category		= $this->get('EventCategory');
			
			//set the pathway
			if (!$menu) 
				$pathway->addItem(JText::_('COM_RSEVENTSPRO_BC_EVENTS'));
			
		} elseif ($layout == 'edit') {
			// Check for permission
			$id				= $app->input->getInt('id');
			$this->owner	= $this->get('owner');
			$this->states	= array('published' => true, 'unpublished' => true, 'archived' => false, 'trash' => false, 'all' => false);
			
			$this->tab	= $app->input->getInt('tab');
			$this->item	= $this->get('Event');
			
			$permission_denied = false;
			if (!$this->admin) {
				if (!$id) {
					if (empty($this->permissions['can_post_events']))
						$permission_denied = true;
				} else {
					if (($this->user != $this->owner && empty($this->permissions['can_edit_events'])) || empty($this->user))
						$permission_denied = true;
				}
			}
			
			if ($permission_denied) {
				if ($user->get('guest')) {
					$app->enqueueMessage(JText::_('COM_RSEVENTSPRO_PLEASE_LOGIN_TO_CREATE_EVENT'));
					$app->redirect(JRoute::_('index.php?option=com_users&view=login&return='.base64_encode(JURI::getInstance()),false));
				} else {
					if (!$id) {
						rseventsproHelper::error(JText::_('COM_RSEVENTSPRO_ERROR_NO_CREATION_PERMISSION'), rseventsproHelper::route('index.php?option=com_rseventspro&layout=default',false, RseventsproHelperRoute::getEventsItemid('999999')));
					} else {
						rseventsproHelper::error(JText::_('COM_RSEVENTSPRO_ERROR_NO_EDIT_PERMISSION'), rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($this->item->id,$this->item->name),false));
					}
				}
			}
			
			if ($id == 0) {
				if ($date = $app->input->get('date')) {
					$time = JFactory::getDate()->format('H:i:s');
					$start = JFactory::getDate($date.' '.$time);
					$end = clone($start);
					$end->modify('+2 hours');
					
					$array = array('start' => $start->toSql(), 'end' => $end->toSql());
					$app->input->set('jform', $array);
				}
				
				// Set new state
				$app->input->set('new',1);
				// Get the model
				$model = $this->getModel();
				// Save event
				$model->save();
				// Redirect
				$app->redirect(rseventsproHelper::route('index.php?option=com_rseventspro&layout=edit&id='.rseventsproHelper::sef($model->getState('eventid'),$model->getState('eventname')),false));
			}
			
			// Load scripts
			JHtml::_('rseventspro.chosen');
			if ($this->document->getType() == 'html') {
				$this->document->addCustomTag('<script src="'.JURI::root(true).'/components/com_rseventspro/assets/js/edit.js?v='.RSEPRO_RS_REVISION.'" type="text/javascript"></script>');
			}
			$this->document->addStyleSheet(JURI::root(true).'/components/com_rseventspro/assets/css/edit.css?v='.RSEPRO_RS_REVISION);
			
			// Load custom scripts
			$app->triggerEvent('rsepro_addCustomScripts');
			
			if (rseventsproHelper::getConfig('enable_google_maps')) {
				$this->document->addScript('https://maps.google.com/maps/api/js?sensor=false&libraries=geometry');
				if ($this->document->getType() == 'html') {
					$this->document->addCustomTag('<script src="'.JURI::root(true).'/components/com_rseventspro/assets/js/jquery.map.js?v='.RSEPRO_RS_REVISION.'" type="text/javascript"></script>');
				}
			}
			
			require_once JPATH_SITE.'/components/com_rseventspro/helpers/events.php';
			$this->eventClass	= RSEvent::getInstance($this->item->id);
			$this->tickets		= $this->eventClass->getTickets();
			$this->coupons		= $this->eventClass->getCoupons();
			$this->files		= $this->eventClass->getFiles();
			$this->repeats		= $this->eventClass->getRepeats();
			$this->categories	= $this->eventClass->getCategoriesOptions();
			
			//set the pathway
			$theview = isset($menu->query['layout']) ? $menu->query['layout'] : 'rseventspro';
			if (($menu && $theview != 'show') || !$menu)
				$pathway->addItem($this->item->name,rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($this->item->id,$this->item->name),false));
			
			$pathway->addItem(JText::_('COM_RSEVENTSPRO_BC_EDIT_EVENT'));
			
		} elseif ($layout == 'file') {
		
			$app->input->set('from','file');
			$this->owner = $this->get('owner');
			$permission_denied = false;
			
			if (!$this->admin) {
				if (($this->user != $this->owner && empty($this->permissions['can_edit_events'])) || empty($this->user))
					$permission_denied = true;
			}
			
			if ($permission_denied) {
				rseventsproHelper::error(JText::_('COM_RSEVENTSPRO_ERROR_EDIT_FILE'), rseventsproHelper::route('index.php?option=com_rseventspro&layout=default',false));
			}
			
			$this->row = $this->get('file');
			
		} elseif ($layout == 'upload') {
			
			$this->owner = $this->get('owner');
			$permission_denied = false;
			
			if (!$this->admin) {
				if (($this->user != $this->owner && empty($this->permissions['can_edit_events'])) || empty($this->user))
					$permission_denied = true;
			}
			
			if ($permission_denied) {
				rseventsproHelper::error(JText::_('COM_RSEVENTSPRO_ERROR_UPLOAD_ICON'), rseventsproHelper::route('index.php?option=com_rseventspro&layout=default',false));
			}
			
			// Load scripts
			if ($this->document->getType() == 'html') {
				$this->document->addCustomTag('<script src="'.JURI::root(true).'/components/com_rseventspro/assets/js/jquery.imgareaselect.pack.js?v='.RSEPRO_RS_REVISION.'" type="text/javascript"></script>');
			}
			$this->document->addStyleSheet(JURI::root(true).'/components/com_rseventspro/assets/css/imgareaselect-animated.css');
			
			$this->item			= $this->get('Event');
			$this->icon			= $this->get('icon');
			
			$image				= @getimagesize(JPATH_SITE.'/components/com_rseventspro/assets/images/events/'.$this->item->icon);
			$this->width		= isset($image[0]) ? $image[0] : 800;
			$this->height		= isset($image[1]) ? $image[1] : 380;
			$this->customheight	= round(($this->height * ($this->width < 380 ? $this->width : 380)) / $this->width) + 100;

			if ($this->height > $this->width) {
				$this->divwidth		= $this->width < 380 ? $this->width : 380;
			} else {
				if ($this->width < 600) {
					$this->divwidth = $this->width;
				} else {
					$ratio = $this->height / $this->width;
					$newHeight = (int) (600 * $ratio);
					$this->divwidth = $newHeight > 400 ? 400 : 600;
				}
			}
			
			$this->left_crop	= isset($this->item->properties['left']) ? $this->item->properties['left'] : 0;
			$this->top_crop		= isset($this->item->properties['top']) ? $this->item->properties['top'] : 0;
			$this->width_crop	= isset($this->item->properties['width']) ? $this->item->properties['width'] : $this->width;
			$this->height_crop	= isset($this->item->properties['height']) ? $this->item->properties['height'] : $this->height;
			
			if (!empty($this->item->icon) && !file_exists(JPATH_SITE.'/components/com_rseventspro/assets/images/events/'.$this->item->icon)) {
				$this->item->icon = '';
				$this->icon = '';
			}
			
		} elseif ($layout == 'forms') {
			
			$this->owner = $this->get('owner');
			$permission_denied = false;
			
			if (!$this->admin) {
				if (($this->user != $this->owner && empty($this->permissions['can_edit_events'])) || empty($this->user))
					$permission_denied = true;
			}
			
			if ($permission_denied) {
				rseventsproHelper::error(JText::_('COM_RSEVENTSPRO_GLOBAL_PERMISSION_DENIED'), rseventsproHelper::route('index.php?option=com_rseventspro&layout=default',false));
			}
			
			$this->forms		= $this->get('forms');
			$this->pagination	= $this->get('formspagination');
			
		} elseif ($layout == 'location') {
			
			if (rseventsproHelper::getConfig('enable_google_maps')) {
				$this->document->addScript('https://maps.google.com/maps/api/js?sensor=false&libraries=geometry');
				if ($this->document->getType() == 'html') {
					$this->document->addCustomTag('<script src="'.JURI::root(true).'/components/com_rseventspro/assets/js/jquery.map.js?v='.RSEPRO_RS_REVISION.'" type="text/javascript"></script>');
				}
			}
			
			$this->row = $this->get('location');
			
			//set the pathway
			$pathway->addItem(JText::_('COM_RSEVENTSPRO_BC_LOCATION'));
			
		} elseif ($layout == 'locations') {
		
			$this->locations	= $this->get('locations');
			$this->total		= $this->get('totallocations');
			
		} elseif ($layout == 'editlocation') {
			
			if (empty($this->permissions['can_edit_locations']) && !$this->admin) {
				rseventsproHelper::error(JText::_('COM_RSEVENTSPRO_ERROR_EDIT_LOCATION'), rseventsproHelper::route('index.php?option=com_rseventspro&layout=locations',false));
			}
			
			if (rseventsproHelper::getConfig('enable_google_maps','int')) {
				$this->document->addScript('https://maps.google.com/maps/api/js?sensor=false&libraries=geometry');
				if ($this->document->getType() == 'html') {
					$this->document->addCustomTag('<script src="'.JURI::root(true).'/components/com_rseventspro/assets/js/jquery.map.js?v='.RSEPRO_RS_REVISION.'" type="text/javascript"></script>');
				}
			}
			
			JHtml::_('rseventspro.chosen','.rschosen');
			
			// Load custom scripts
			$app->triggerEvent('rsepro_addCustomScripts');
			
			$this->row = $this->get('location');
			
			//set the pathway
			$pathway->addItem(JText::_('COM_RSEVENTSPRO_BC_EDIT_LOCATION'));
			
		} elseif ($layout == 'categories') {
		
			$this->categories	= $this->get('categories');
			$this->total		= $this->get('totalcategories');
		
		} elseif ($layout == 'map') {
		
			if (rseventsproHelper::getConfig('enable_google_maps')) {
				$this->document->addScript('https://maps.google.com/maps/api/js?sensor=false&libraries=geometry');
				if ($this->document->getType() == 'html') {
					$this->document->addCustomTag('<script src="'.JURI::root(true).'/components/com_rseventspro/assets/js/jquery.map.js?v='.RSEPRO_RS_REVISION.'" type="text/javascript"></script>');
				}
			}
			
			$filters			= $this->get('filters');
			$this->columns		= $filters[0];
			$this->operators	= $filters[1];
			$this->values		= $filters[2];
			
			if (!$this->params->get('enable_radius',0)) {
				$this->events	= $this->get('eventsmap');
			}
			
			$this->location	= $this->params->get('default_location', 'Statue of Liberty National Monument, New York, NY 10004, United States');
			$this->radius	= (int) $this->params->get('default_radius', '100');
			$width 			= $this->params->get('map_width', '100%');
			$height 		= $this->params->get('map_height', '400px');
			
			if (strpos($width, '%') !== false) {
				$this->width = $width;
			} elseif (strpos($width, 'px') !== false) {
				$this->width = $width;
			} else {
				$this->width = (int) $width.'px';
			}
			
			if (strpos($height, '%') !== false) {
				$this->height = $height;
			} elseif (strpos($height, 'px') !== false) {
				$this->height = $height;
			} else {
				$this->height = (int) $height.'px';
			}
			
		} elseif ($layout == 'show' || $layout == 'print') {
			if (!rseventsproHelper::check(JFactory::getApplication()->input->getInt('id')))	{			
				rseventsproHelper::error(JText::_('COM_RSEVENTSPRO_ERROR_INVALID_EVENT'), rseventsproHelper::route('index.php?option=com_rseventspro&layout=default',false));
			}
			
			// Get the event
			$this->event			= $this->get('event');
			$this->cansubscribe		= $this->get('cansubscribe');
			$this->issubscribed		= $this->get('issubscribed');
			$this->canunsubscribe	= $this->get('canunsubscribe');
			$this->report			= $this->get('canreport');
			
			if (!rseventsproHelper::canview($this->event->id) && $this->event->owner != $this->user && !$this->admin) {
				rseventsproHelper::error(JText::_('COM_RSEVENTSPRO_ERROR_VIEW_EVENT'), rseventsproHelper::route('index.php?option=com_rseventspro&layout=default',false));
			}
			
			// Load event metadata
			rseventsproHelper::metas($this->event);
			
			if ($layout == 'show') {
				// Set hits
				rseventsproHelper::hits($this->event->id);
				
				// Load maps
				if (rseventsproHelper::getConfig('enable_google_maps','int')) {
					$this->document->addScript('https://maps.google.com/maps/api/js?sensor=false&libraries=geometry');
					if ($this->document->getType() == 'html') {
						$this->document->addCustomTag('<script src="'.JURI::root(true).'/components/com_rseventspro/assets/js/jquery.map.js?v='.RSEPRO_RS_REVISION.'" type="text/javascript"></script>');
					}
				}
			}
			
			$this->options	= rseventsproHelper::options($this->event->id);
			$this->guests	= $this->get('guests');
			
			// Load jQuery Colorbox modal
			if (rseventsproHelper::getConfig('modal','int') == 1 && $layout == 'show') {
				$this->document->addScript(JURI::root(true).'/components/com_rseventspro/assets/js/jquery.colorbox.min.js');
				$this->document->addStyleSheet(JURI::root(true).'/components/com_rseventspro/assets/css/colorbox.css');
				$this->document->addCustomTag('<script type="text/javascript">jQuery(document).ready(function(){
					jQuery("a[rel=\'rs_subscribe\']").colorbox({iframe:true, innerWidth:800, innerHeight:500 , title:\''.JText::_('COM_RSEVENTSPRO_EVENT_JOIN',true).'\'});
					jQuery("a[rel=\'rs_invite\']").colorbox({iframe:true, innerWidth:530, innerHeight:500, title:\''.JText::_('COM_RSEVENTSPRO_EVENT_INVITE',true).'\'});
					jQuery("a[rel=\'rs_message\']").colorbox({iframe:true, innerWidth:650, innerHeight:400, title:\''.JText::_('COM_RSEVENTSPRO_EVENT_MESSAGE_TO_GUESTS',true).'\'});
					jQuery("a[rel=\'rs_unsubscribe\']").colorbox({iframe:true, innerWidth:550, innerHeight:400, title:\''.JText::_('COM_RSEVENTSPRO_UNSUBSCRIBE_UNSUBSCRIBE',true).'\'});
					});</script>
				');
			}
			
			//set the pathway
			$theview = isset($menu->query['layout']) ? $menu->query['layout'] : 'rseventspro';
			if (($menu && $theview != 'show') || !$menu)
				$pathway->addItem($this->event->name);
			
			// date helping functions
			$nowunix = JFactory::getDate()->toUnix();
			
			if ($this->event->allday) {
				$date = JFactory::getDate($this->event->start);
				$date->modify('+1 days');
				$endunix = $date->toUnix();
			} else {
				$endunix = JFactory::getDate($this->event->end)->toUnix();
			}
			$this->eventended = $endunix < $nowunix;
			
		} elseif ($layout == 'subscribe') {
			$app->input->set('cid',$app->input->getInt('id'));
			
			$this->event		= $this->get('event');
			$this->cansubscribe	= $this->get('cansubscribe');
			
			$this->js = $this->config->multi_tickets ? 'rs_get_ticket(this);' : 'rsepro_add_single_ticket(this);';
			
			if ($this->event->ticketsconfig) {
				$this->updatefunction = 'rsepro_multi_seats_total();';
			} else {
				if ($this->config->multi_tickets) {
					$this->updatefunction = 'rsepro_multi_total();';
				} else {
					$this->updatefunction = 'rsepro_single_total();';
				}
			}
			
			$thankyou = false;
			if ($this->event->form) {
				$formparams = JFactory::getSession()->get('com_rsform.formparams.'.$this->event->form);
				if (isset($formparams->formProcessed)) 
					$thankyou = true;
			}
			
			$this->thankyou = $thankyou;
			
			// If the Force login option is enabled and the current user is not logged in redirect the user
			if (rseventsproHelper::getConfig('must_login','int') && $user->get('id') == 0) {
				$link = rseventsproHelper::getConfig('modal','int');
				if ($link == 0) {
					$app->redirect(rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($this->event->id,$this->event->name),false),JText::_('COM_RSEVENTSPRO_PLEASE_LOGIN'));
				} else {
					echo rseventsproHelper::redirect(true,JText::_('COM_RSEVENTSPRO_PLEASE_LOGIN'),rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($this->event->id,$this->event->name),false),false,true);
					return;
				}
			}
			
			// If the user cannot view this event or there is no registration to the event -> redirect
			if (!rseventsproHelper::canview($this->event->id) && $this->event->owner != $this->user && !$this->admin) {
				rseventsproHelper::error(JText::_('COM_RSEVENTSPRO_ERROR_VIEW_EVENT'), rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($this->event->id,$this->event->name),false));
			}
			
			// Can the current user view the subscription form
			if (!$this->cansubscribe['status'] && !$this->thankyou) {
				rseventsproHelper::error($this->cansubscribe['err'], rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($this->event->id,$this->event->name),false));
			}
			
			if (rseventsproHelper::getConfig('modal','int') == 1 || rseventsproHelper::getConfig('modal','int') == 2)
				$app->input->set('tmpl','component');
			
			$this->form = rseventsproHelper::rsform();
			
			// There are no tickets left
			$tickets = $this->get('eventtickets');
			
			$query->clear()
				->select('COUNT('.$db->qn('id').')')
				->from($db->qn('#__rseventspro_tickets'))
				->where($db->qn('ide').' = '.(int) $this->event->id);
			
			$db->setQuery($query);
			$eventtickets = $db->loadResult();
			
			if (!empty($eventtickets) && empty($tickets) && $this->event->form == 0) {
				rseventsproHelper::error(JText::_('COM_RSEVENTSPRO_ERROR_NO_TICKETS_LEFT'), rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($this->event->id,$this->event->name),false));
			}
			
			$payments = rseventsproHelper::getPayments(false,$this->event->payments);
			if (!empty($payments)) {
				if (rseventsproHelper::getConfig('payment_type','int'))
					$lists['payments']      = JHTML::_('select.genericlist', $payments, 'payment', 'class="input-large" onchange="'.$this->updatefunction.'"', 'value' ,'text',rseventsproHelper::getConfig('default_payment'));
				else
					$lists['payments']      = JHTML::_('select.radiolist', $payments, 'payment', 'class="inputbox" onchange="'.$this->updatefunction.'"', 'value' ,'text',rseventsproHelper::getConfig('default_payment'));
			}
			
			$this->user			= $user;
			$this->tickets		= $tickets;
			$this->payment		= $this->get('ticketpayment');
			$this->payments		= !empty($payments);
			
			//set the pathway
			$theview = isset($menu->query['layout']) ? $menu->query['layout'] : 'rseventspro';
			if (($menu && $theview != 'show') || !$menu)
				$pathway->addItem($this->event->name,rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($this->event->id,$this->event->name),false));
			
			$pathway->addItem(JText::_('COM_RSEVENTSPRO_BC_SUBSCRIBE'));
			
		} elseif ($layout == 'wire') {
			
			$this->data		= $this->get('subscriber');
			$this->payment  = $this->get('payment');
			
			//set the pathway
			$theview = isset($menu->query['layout']) ? $menu->query['layout'] : 'rseventspro';
			if (($menu && $theview != 'show') || !$menu)
				$pathway->addItem($this->data['event']->name,rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($this->data['event']->id,$this->data['event']->name),false));
			
			$pathway->addItem(JText::_('COM_RSEVENTSPRO_BC_WIRE'));
			
		} elseif ($layout == 'subscriptions') {
			
			if ($user->get('guest')) {
				$app->enqueueMessage(JText::_('COM_RSEVENTSPRO_PLEASE_LOGIN_TO_VIEW_SUBSCRIPTIONS'));
				$app->redirect(JRoute::_('index.php?option=com_users&view=login&return='.base64_encode(JURI::getInstance()),false));
			} else {
				$this->subscriptions = $this->get('subscriptions');
			}
			
		} elseif ($layout == 'subscribers') {
			
			$this->row = $this->get('event');
			
			if ($this->admin || $this->row->owner == $user->get('id')) {
				$states = array_merge(array(JHTML::_('select.option', '-', JText::_('COM_RSEVENTSPRO_GLOBAL_SELECT_STATE'))), $this->get('statuses'));
				$lists['state'] = JHTML::_('select.genericlist', $states, 'state', 'size="1" onchange="document.adminForm.submit();" class="input-small"','value','text',$app->getUserState('com_rseventspro.subscriptions.state.frontend'));
				
				$lists['tickets'] = JHTML::_('select.genericlist', $this->get('ticketsfromevent'), 'ticket', 'size="1" onchange="document.adminForm.submit();" class="input-large"','value','text',$app->getUserState('com_rseventspro.subscriptions.ticket.frontend'));
				
				$this->data			= $this->get('subscribers');
				$this->total		= $this->get('totalsubscribers');
				$this->filter_word	= $app->getUserState('com_rseventspro.subscriptions.search_frontend');
				
				//set the pathway
				$theview = isset($menu->query['layout']) ? $menu->query['layout'] : 'rseventspro';
				if (($menu && $theview != 'show') || !$menu)
					$pathway->addItem($this->row->name,rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($this->row->id,$this->row->name),false));
				
				$pathway->addItem(JText::_('COM_RSEVENTSPRO_BC_SUBSCRIBERS'));
				
			} else {
				rseventsproHelper::error(JText::_('COM_RSEVENTSPRO_ERROR_SUBSCRIBERS_VIEW'), rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($this->row->id,$this->row->name),false));
			}
		
		} elseif ($layout == 'editsubscriber') {
			
			$this->data = $this->get('subscriber');
			if ($this->admin || $this->data['event']->owner == $user->get('id')) {
				$lists['status'] = JHTML::_('select.genericlist', $this->get('statuses'), 'jform[state]', 'size="1" class="input-small"','value','text', $this->data['data']->state);
				$lists['confirmed'] = JHTML::_('select.genericlist', $this->get('YesNo'), 'jform[confirmed]', 'size="1" class="input-small"','value','text', $this->data['data']->confirmed);
				
				$this->fields	= $this->get('fields');
				$tparams = $this->data['data']->gateway == 'offline' ? $this->get('card') : $this->data['data']->params;
				$this->tparams = $tparams;
				
				//set the pathway
				$theview = isset($menu->query['layout']) ? $menu->query['layout'] : 'rseventspro';
				if (($menu && $theview != 'show') || !$menu)
					$pathway->addItem($this->data['event']->name,rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($this->data['event']->id,$this->data['event']->name),false));
				
				$pathway->addItem(JText::_('COM_RSEVENTSPRO_BC_EDIT_SUBSCRIBER'));
				
			} else {
				rseventsproHelper::error(JText::_('COM_RSEVENTSPRO_ERROR_EDIT_SUBSCRIBER'), rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($this->data['event']->id,$this->data['event']->name),false));
			}
		
		} elseif ($layout == 'message') {
			
			$this->event = $this->get('event');
			
			if ($this->admin || $this->event->owner == $user->get('id')) {
				$this->subscribers = $this->get('people');
				
				//set the pathway
				$theview = isset($menu->query['layout']) ? $menu->query['layout'] : 'rseventspro';
				if (($menu && $theview != 'show') || !$menu)
					$pathway->addItem($this->event->name,rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($this->event->id,$this->event->name),false));
					
				$pathway->addItem(JText::_('COM_RSEVENTSPRO_BC_SEND_MESSAGE'));
			} else {
				rseventsproHelper::error(JText::_('COM_RSEVENTSPRO_ERROR_SEND_MESSAGE_GUESTS'), rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($this->event->id,$this->event->name),false));
			}
			
		} elseif ($layout == 'invite') {
		
			$this->event	= $this->get('event');
			$options = rseventsproHelper::options($this->event->id);
			
			if (!rseventsproHelper::check($this->event->id)) {
				rseventsproHelper::error(JText::_('COM_RSEVENTSPRO_GLOBAL_PERMISSION_DENIED'), rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($this->event->id,$this->event->name),false));
			}
			
			// date helping functions
			$nowunix 	= JFactory::getDate()->toUnix();
			
			if ($this->event->allday) {
				$date = JFactory::getDate($this->event->start);
				$date->modify('+1 days');
				$endunix = $date->toUnix();
			} else {
				$endunix 	= JFactory::getDate($this->event->end)->toUnix();
			}
			$eventended = $endunix < $nowunix;
			
			if ($eventended) {
				rseventsproHelper::error(JText::_('COM_RSEVENTSPRO_ERROR_INVITE_1'), rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($this->event->id,$this->event->name),false));
			}
			
			if (empty($options['show_invite'])) {
				rseventsproHelper::error(JText::_('COM_RSEVENTSPRO_ERROR_INVITE_2'), rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($this->event->id,$this->event->name),false));
			}
			
			require_once JPATH_SITE.'/components/com_rseventspro/helpers/invite.php';
			$root			= JUri::getInstance()->toString(array('scheme','host','port'));
			$callback		= $root.rseventsproHelper::route('index.php?option=com_rseventspro&layout=invite&id='.rseventsproHelper::sef($this->event->id,$this->event->name));
			$this->auth		= RSYahoo::auth($callback);
			$this->contacts	= RSYahoo::getContacts();
			
			if (!empty($this->config->google_client_id)) {
				$this->document->addScript('https://apis.google.com/js/client.js');
			}
			
			//set the pathway
			$theview = isset($menu->query['layout']) ? $menu->query['layout'] : 'rseventspro';
			if (($menu && $theview != 'show') || !$menu)
				$pathway->addItem($this->event->name,rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($this->event->id,$this->event->name),false));
				
			$pathway->addItem(JText::_('COM_RSEVENTSPRO_BC_INVITE'));
		
		} elseif ($layout == 'unsubscribe') {
			
			$this->event		= $this->get('event');
			$this->issubscribed	= $this->get('issubscribed');
			
			if ($this->issubscribed)
				$this->subscriptions = $this->get('usersubscriptions');
			else {
				rseventsproHelper::error(JText::_('COM_RSEVENTSPRO_GLOBAL_PERMISSION_DENIED'), rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($this->event->id,$this->event->name),false));
			}
				
		} elseif ($layout == 'search') {
		
			$this->events	= $this->get('results');
			$this->total	= $this->get('totalresults');
			$this->search	= $app->getUserStateFromRequest('rsepro.search.search', 'rskeyword');
			
		} elseif ($layout == 'tickets') {
			
			$this->event		= $this->get('event');
			$this->cansubscribe	= $this->get('cansubscribe');
			
			// If the user cannot view this event or there is no registration to the event -> redirect
			if (!rseventsproHelper::canview($this->event->id) && $this->event->owner != $this->user && !$this->admin) {
				rseventsproHelper::error(JText::_('COM_RSEVENTSPRO_ERROR_VIEW_EVENT'), rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($this->event->id,$this->event->name),false));
			}
			
			// Can the current user view the subscription form
			if (!$this->cansubscribe['status']) {
				rseventsproHelper::error($this->cansubscribe['err'], rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($this->event->id,$this->event->name),false));
			}
			
			// There are no tickets left
			$tickets = $this->get('eventtickets');
			
			$query->clear()
				->select('COUNT('.$db->qn('id').')')
				->from($db->qn('#__rseventspro_tickets'))
				->where($db->qn('ide').' = '.(int) $this->event->id);
			
			$db->setQuery($query);
			$eventtickets = $db->loadResult();
			
			if (!empty($eventtickets) && empty($tickets) && $this->event->form == 0) {
				rseventsproHelper::error(JText::_('COM_RSEVENTSPRO_ERROR_NO_TICKETS_LEFT'), rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($this->event->id,$this->event->name),false));
			}
			
			$this->document->addStyleSheet(JURI::root(true).'/components/com_rseventspro/assets/css/tickets.css?v='.RSEPRO_RS_REVISION);
			
			$this->tickets = rseventsproHelper::getTickets(JFactory::getApplication()->input->getInt('id',0));
		} elseif ($layout == 'seats') {
			
			$permission_denied = false;
			if (!$this->admin) {
				if (!$id) {
					if (empty($this->permissions['can_post_events']))
						$permission_denied = true;
				} else {
					if (($this->user != $this->owner && empty($this->permissions['can_edit_events'])) || empty($this->user))
						$permission_denied = true;
				}
			}
			
			if ($permission_denied) {
				rseventsproHelper::error(JText::_('COM_RSEVENTSPRO_GLOBAL_PERMISSION_DENIED'), rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($this->row->id,$this->row->name),false));
			}
			
			// Load scripts
			$this->document->addScript(JURI::root(true).'/components/com_rseventspro/assets/js/jquery-ui.min.js?v='.RSEPRO_RS_REVISION);
			$this->document->addStyleSheet(JURI::root(true).'/components/com_rseventspro/assets/css/tickets.css?v='.RSEPRO_RS_REVISION);
			$this->tickets = rseventsproHelper::getTickets($app->input->getInt('id',0));
		} elseif ($layout == 'report') {
			$this->report			= $this->get('canreport');
			
			if (!$this->report) {
				rseventsproHelper::error(JText::_('COM_RSEVENTSPRO_ERROR_REPORT'), rseventsproHelper::route('index.php?option=com_rseventspro&layout=default',false));
			}
		} elseif ($layout == 'reports') {
			$this->report		= $this->get('canreport');
			$this->event		= $this->get('event');
			
			if ($this->admin || $this->event->owner == $user->get('id')) {
				$this->reports	= rseventsproHelper::getReports($this->event->id);
			} else {
				rseventsproHelper::error(JText::_('COM_RSEVENTSPRO_ERROR_REPORTS'), rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($this->event->id,$this->event->name),false));
			}
			
			if (!rseventsproHelper::check($this->event->id) || !$this->report) {
				rseventsproHelper::error(JText::_('COM_RSEVENTSPRO_GLOBAL_PERMISSION_DENIED'), rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($this->event->id,$this->event->name),false));
			}
		} elseif ($layout == 'scan') {
			$this->event		= $this->get('event');
			
			if ($this->admin || $this->event->owner == $user->get('id')) {
				$this->scan			= rseventsproHelper::getScan();
				
				//set the pathway
				$theview = isset($menu->query['layout']) ? $menu->query['layout'] : 'rseventspro';
				if (($menu && $theview != 'show') || !$menu)
					$pathway->addItem($this->event->name,rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($this->event->id,$this->event->name),false));
				
				$pathway->addItem(JText::_('COM_RSEVENTSPRO_BC_SCAN'));
				
			} else {
				rseventsproHelper::error(JText::_('COM_RSEVENTSPRO_GLOBAL_PERMISSION_DENIED'), rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($this->event->id,$this->event->name),false));
			}
		} elseif ($layout == 'userseats') {
			$this->data = $this->get('subscriber');
			if ($this->admin || $this->data['event']->owner == $user->get('id')) {
				$this->document->addStyleSheet(JURI::root(true).'/components/com_rseventspro/assets/css/tickets.css?v='.RSEPRO_RS_REVISION);
				
				$eventId		= $this->data['event']->id;
				$this->tickets	= rseventsproHelper::getTickets($eventId);
				$this->id		= JFactory::getApplication()->input->getInt('id',0);
			} else {
				rseventsproHelper::error(JText::_('COM_RSEVENTSPRO_GLOBAL_PERMISSION_DENIED'), rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($this->data['event']->id,$this->data['event']->name),false));
			}
		} elseif ($layout == 'placeholders') {
			require_once JPATH_SITE.'/components/com_rseventspro/helpers/placeholders.php';
			
			$type 				= JFactory::getApplication()->input->getCmd('type','');
			$this->placeholders = RSEventsProPlaceholders::get($type);
		}
		
		$this->lists	= $lists;
		
		parent::display($tpl);
	}
	
	public function getStatus($state) {
		if ($state == 0) {
			return '<font color="blue">'.JText::_('COM_RSEVENTSPRO_GLOBAL_STATUS_INCOMPLETE').'</font>';
		} elseif ($state == 1) {
			return '<font color="green">'.JText::_('COM_RSEVENTSPRO_GLOBAL_STATUS_COMPLETED').'</font>';
		} elseif ($state == 2) {
			return '<font color="red">'.JText::_('COM_RSEVENTSPRO_GLOBAL_STATUS_DENIED').'</font>';
		}
	}
	
	public function getUser($id) {
		if ($id > 0) {
			return JFactory::getUser($id)->get('username');
		} else return JText::_('COM_RSEVENTSPRO_GLOBAL_GUEST');
	}
	
	public function getNumberEvents($id, $type) {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$events	= 0;
		
		if ($type == 'categories') {
			$query->clear()
				->select($db->qn('e.id'))
				->from($db->qn('#__rseventspro_events','e'))
				->join('left', $db->qn('#__rseventspro_taxonomy','t').' ON '.$db->qn('e.id').' = '.$db->qn('t.ide'))
				->where($db->qn('t.type').' = '.$db->q('category'))
				->where($db->qn('t.id').' = '.(int) $id)
				->where($db->qn('e.completed').' = 1')
				->where($db->qn('e.published').' = 1');
			
			$db->setQuery($query);
			$eventids = $db->loadColumn();
			
			if (!empty($eventids)) {
				foreach ($eventids as $eid) {
					if (!rseventsproHelper::canview($eid)) 
						continue;
					$events++;
				}
			}
		} else if ($type == 'locations') {
			$query->clear()
				->select($db->qn('id'))
				->from($db->qn('#__rseventspro_events'))
				->where($db->qn('location').' = '.(int) $id)
				->where($db->qn('completed').' = 1')
				->where($db->qn('published').' = 1');
			
			
			$db->setQuery($query);
			$eventids = $db->loadColumn();
			
			if (!empty($eventids)) {
				foreach ($eventids as $eid) {
					if (!rseventsproHelper::canview($eid)) 
						continue;
					$events++;
				}
			}
		}
		
		if (!$events) return;
		return $events.' '.JText::plural('COM_RSEVENTSPRO_CALENDAR_EVENTS',$events);
	}
}