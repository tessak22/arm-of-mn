<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class RSMembershipModelReports extends JModelAdmin
{
	public $_memberships;
	public $_membership_names;

	function __construct()
	{
		parent::__construct();

		// large databases need lots of memory
		ini_set('memory_limit', '128M');

		$this->_getMemberships();
		
		$db 	= JFactory::getDBO();
		$db->setQuery("SET SQL_BIG_SELECTS=1");
		$db->execute();
	}
	
	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_rsmembership.reports', 'reports', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
			return false;

		return $form;
	}

	protected function loadFormData() 
	{
		return $this->getItem();
	}
	
	public function getItem($pk = null)
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$item 	= new stdClass();

		// set the default values for fields in xml
		$item->report 				= '';
		$item->from_date 			= '';
		$item->user_id 				= '';
		$item->to_date 				= RSMembershipHelper::showDate(JFactory::getDate()->toUnix(), 'Y-m-d');
		$item->memberships 			= array_keys($this->_membership_names);
		$item->status_memberships 	= array(0,1,2,3);
		$item->status_transactions 	= array('pending', 'completed', 'denied');
		$item->price_from 			= 0;
		$item->transaction_types 	= array('new', 'upgrade', 'addextra', 'renew');
		
		$query->select('DISTINCT(gateway)')->from($db->qn('#__rsmembership_transactions'))->order($db->qn('gateway').' ASC');
		$db->setQuery($query);
		$item->gateways 			= $db->loadColumn();
		
		return $item;
	}

	public function getRSFieldset() 
	{
		require_once JPATH_COMPONENT.'/helpers/adapters/fieldset.php';

		$fieldset = new RSFieldset();

		return $fieldset;
	}
	
	public function getRSAccordion() 
	{
		require_once JPATH_COMPONENT.'/helpers/adapters/accordion.php';

		$accordion = new RSAccordion('com-rsmembership-accordion-reports');
		return $accordion;
	}
	
	public function _getMemberships()
	{
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		
		$query->select('*')->from($db->qn('#__rsmembership_memberships'))->order($db->qn('ordering').' ASC');
		$db->setQuery($query);
		$this->_memberships = $db->loadObjectList();

		$this->_membership_names = array();
		foreach ( $this->_memberships as $membership ) 
			$this->_membership_names[$membership->id] = $membership->name;
	}

	function getCustomer() 
	{
		return '<em>'.JText::_('COM_RSMEMBERSHIP_NO_USER_SELECTED').'</em>';
	}

	public function getReportData($post_filters) 
	{
		$db 		 = JFactory::getDBO();
		$query		 = $db->getQuery(true);
		$return 	 = array();
		$sql_filters = array();
		$output		 = array();
		$data 		 = array();
		$from_date 	 = $post_filters['from_date'];
		$to_date 	 = $post_filters['to_date'];

		$report 	 = $post_filters['report'];
		$date_column = (($report == 'report_2' || $report == 'report_3')  ? 'date' : 'membership_start');
		
		$memberships = ( isset($post_filters['memberships']) ? $post_filters['memberships'] : array() );
		JArrayHelper::toInteger($memberships, array());

		if ( $from_date || $to_date ) 
		{
			$start = (!empty($from_date) ? JFactory::getDate($from_date)->toSql() : '');
			
			// check if the to_date has the time attatched and remove it
			$to_date = trim(str_replace('00:00:00', '', $to_date));
			
			// set the current date to the current date and time for catching data even the current day
			if ($to_date == JHtml::date('now','Y-m-d')) {
				$to_date = JHtml::date('now','Y-m-d H:i:s');
			}
			
			$stop  = JFactory::getDate($to_date)->toSql();
		
			if ( $start && $stop ) 
			{
				$query->where( $db->qn($date_column) . ' >= ' . $db->q($start) . ' AND ' . $db->qn($date_column) . ' <= ' . $db->q($stop) );
			}
			elseif ( $start )
			{
				$query->where( $db->qn($date_column) . ' >= ' . $db->q($start) );
			}
			elseif ( $stop )
			{
				$query->where( $db->qn($date_column) . ' <= ' . $db->q($stop) );
			}
		}

		$unit = $post_filters['unit'];
		$format = 'Y-m-d';
		if ( $unit == 'day' ) 
			$format = 'Y-m-d';
		elseif ( $unit == 'month' ) 
			$format = 'Y-m';
		elseif ( $unit == 'year' ) 
			$format = 'Y';
		elseif ( $unit == 'quarter' ) 
			$format = 'Y-m';

		$user_id = $post_filters['user_id'];
		if ( !empty($user_id) ) 
		{
			$query->where( $db->qn('user_id') . ' = ' . $db->q($user_id) );
		}
		
		if( $report == 'report_2' || $report == 'report_3' ) 
		{
			$transaction_types = $post_filters['transaction_types'];

			if ( !empty($transaction_types) ) {
				$query->where($db->qn('type') . ' IN (\''.implode($db->q(','), $transaction_types).'\')');
			}

			$gateways = ( isset($post_filters['gateways']) ? $post_filters['gateways'] : array() );
			if ( !empty($gateways) ) {
				$query->where($db->qn('gateway') . ' IN (\''.implode($db->q(','), $gateways).'\')');
			}

			if ( !empty($memberships) && !empty($transaction_types) ) 
			{
				$where_membership_id = "(";

				foreach( $memberships as $mem )
				{
					$where_membership_id .= $db->qn('params')." LIKE ".$db->q("membership_id=".$mem);
					$where_membership_id .= " OR ".$db->qn('params')." LIKE ".$db->q("%;membership_id=".$mem);
					$where_membership_id .= " OR ".$db->qn('params')." LIKE ".$db->q("membership_id=".$mem.";%");
					$where_membership_id .= " OR ".$db->qn('params')." LIKE ".$db->q("%;membership_id=".$mem.";%");
					$where_membership_id .= " OR ".$db->qn('params')." LIKE ".$db->q("%;from_id=".$mem.";%");
					$where_membership_id .= " OR ".$db->qn('params')." LIKE ".$db->q("%;to_id=".$mem."");
					if($mem != end($memberships)) $where_membership_id .= " OR ";
				}
				$where_membership_id .= ")";

				$query->where($where_membership_id);
			}

			$status = $post_filters['status_transactions'];
			
			if ( !empty($status) ) {
				$query->where( $db->qn('status') . ' IN (\''.implode($db->q(','), $status).'\')' );
			}
		}
		else 
		{
			if ( !empty($memberships) ) 
				$query->where( $db->qn('membership_id') . ' IN (\''.implode($db->q(','), $memberships).'\')' );

			$status = $post_filters['status_memberships'];
			if ( !empty($status) ) 
				$query->where( $db->qn('status') . ' IN (\''.implode($db->q(','), $status).'\')' );
		}

		if ($report == 'report_1' || $report == 'report_2') {
			$price_from = $post_filters['price_from'];
			if ( !empty($price_from) ) {
				$query->where($db->qn('price') . ' >= (' . $price_from . ') ');
			}

			$price_to = $post_filters['price_to'];
			if ( !empty($price_to) ) {
				$query->where($db->qn('price') . ' <= (' . $price_to . ') ');
			}
		}

		// ordering
		$query->order( $db->qn($date_column) . ' ASC' );

		switch ($report)
		{
			case 'report_1':
				// query
				$query->select( $db->qn('membership_id') . ', ' . $db->qn($date_column) )->from($db->qn('#__rsmembership_membership_subscribers'));
				$db->setQuery($query);
				$subscribers = $db->loadObjectList();
				$query->clear();

				if ( !empty($subscribers) ) 
				{
					foreach ( $subscribers as $subscriber ) 
					{
						if ($unit == 'quarter') 
							$format = $this->getQuarter( JFactory::getDate($subscriber->membership_start)->toUnix() );

						$date 		= RSMembershipHelper::showDate( $subscriber->membership_start, $format );
						$membership = $this->getMembershipName( $subscriber->membership_id );
						@$return['units'][$date] = $date;
						@$return['memberships'][$date][$membership] += 1;
						@$return['totals'][$date] += 1;
					}
				}

				if (!empty($return['totals'])) 
				{
					foreach ($return['units'] as $date) 
					{
						foreach ($memberships as $membership) 
						{
							$membership = $this->getMembershipName($membership);
							if (empty($return['memberships'][$date][$membership])) 
							{
								$return['memberships'][$date][$membership] = 0;
							}
						}
					}

					// Building the header data response
					foreach ( $memberships as $mem) {
						$data[0][] = $this->getMembershipName($mem);
					}
					asort($data[0]);
					array_unshift( $data[0], JText::_('COM_RSMEMBERSHIP_REPORTS_PERIOD') );

					foreach ($return['memberships'] as $return_date => $return_values)
						ksort($return['memberships'][$return_date]);

					// adding data values
					foreach ($return['memberships'] as $date => $memberships) 
					{
						$membership_values = array_values( $memberships );
						array_unshift( $membership_values, $date );
						$data[] = $membership_values;
					}
				}

				$output['data'] 	= $data;
				$output['options']  = new stdClass();
				$output['options']->title 					= JText::_('COM_RSMEMBERSHIP_REPORT_1');
				$output['options']->hAxis					= new stdClass();
				$output['options']->hAxis->title 			= JText::_('COM_RSMEMBERSHIP_'.$unit); 
				$output['options']->hAxis->titleTextStyle 	= "color: 'red'"; 
				$output['options']->crosshair = new stdClass;
				$output['options']->crosshair->trigger = 'both';
				$output['options']->pointSize = '5';

			break;

			case 'report_2':
				// query
				$query->select($db->qn('id') . ', ' . $db->qn('type') . ', ' . $db->qn('params') . ', ' . $db->qn($date_column) )->from($db->qn('#__rsmembership_transactions'));
				$db->setQuery($query);
				$transactions = $db->loadObjectList();
				$query->clear();

				if(!empty($transactions)) {
					foreach ($transactions as $i => $transaction)
					{
						if ( $unit == 'quarter' ) 
							$format = $this->getQuarter( JFactory::getDate($transaction->$date_column)->toUnix() );

						$date 	= RSMembershipHelper::showDate( $transaction->$date_column, $format );
						@$return['units'][$date] = $date;
						@$return['transactions'][$date][JText::_('COM_RSMEMBERSHIP_TRANSACTION_'.strtoupper($transaction->type))] += 1;
						@$return['totals'][$date] += 1;
					}
				}

				if ( !empty($return['totals']) )
				{
					foreach ( $return['units'] as $date )
					{
						foreach ( $transaction_types as $transaction ) 
						{
							if ( empty($return['transactions'][$date][JText::_('COM_RSMEMBERSHIP_TRANSACTION_'.strtoupper($transaction))]) ) 
							{
								$return['transactions'][$date][JText::_('COM_RSMEMBERSHIP_TRANSACTION_'.strtoupper($transaction))] = 0;
							}
						}
					}

					// Building the header data response
					foreach ( $transaction_types as $trans) {
						$data[0][] = JText::_('COM_RSMEMBERSHIP_TRANSACTION_'.strtoupper($trans));
					}
					asort($data[0]);
					array_unshift( $data[0], JText::_('COM_RSMEMBERSHIP_REPORTS_PERIOD') );

					foreach ( $return['transactions'] as $return_transaction => $return_values ) 
						ksort( $return['transactions'][$date] );

					// adding data values
					foreach ( $return['transactions'] as $date => $trans ) 
					{
						$transaction_values = array_values( $trans );
						array_unshift( $transaction_values, $date );
						$data[] = $transaction_values;
					}
				}

				$output['data'] 	= $data;
				$output['options']  = new stdClass();
				$output['options']->title 					= JText::_('COM_RSMEMBERSHIP_REPORT_2');
				$output['options']->hAxis					= new stdClass();
				$output['options']->hAxis->title 			= JText::_('COM_RSMEMBERSHIP_'.$unit); 
				$output['options']->hAxis->titleTextStyle 	= "color: 'red'"; 
				$output['options']->crosshair = new stdClass;
				$output['options']->crosshair->trigger = 'both';
				$output['options']->pointSize = '5';				

			break;
			
			case 'report_3':
				$query
				->select('SUM('.$db->qn('t.price').') AS daysum')
				->select('DATE('.$db->qn('t.date').') AS date')
				->select($db->qn('t.currency'))
				->select($db->qn('t.status'))
				->from($db->qn('#__rsmembership_transactions', 't'));

				$query->group(' DATE('.$db->qn('t.date').'), '.$db->qn('t.status'));
				$db->setQuery($query);
				$sales = $db->loadObjectList();
				$query->clear();
				
				// getting the currency
				$currency = (isset($sales[0]->currency) ? $sales[0]->currency : '');
				
				$statuses = array(JText::_('COM_RSMEMBERSHIP_REPORTS_PERIOD'));
				$dates = array();
				$amounts = array();
				
				// get the statuses amount from the selected period and unit
				foreach ($sales as $sale) {
					$statuses[] = $sale->status;
					if ( $unit == 'quarter' ) $format = $this->getQuarter( JFactory::getDate($sale->date)->toUnix() );
					$currentDate =  RSMembershipHelper::showDate( $sale->date, $format );
					if (!in_array($currentDate, $dates)) {
						$dates[]	= RSMembershipHelper::showDate( $sale->date, $format );
					}
					if ($unit == 'month' || $unit == 'year' || $unit == 'quarter') {
						if(isset($amounts[$currentDate][$sale->status])) {
							$amounts[$currentDate][$sale->status] += $sale->daysum;
						} else {
							$amounts[$currentDate][$sale->status] = $sale->daysum;
						}
					}
					else $amounts[$currentDate][$sale->status] = $sale->daysum;
				}
				
				$statuses = array_unique($statuses);
				
				// get the statuses names from the language file
				$name_statuses = $statuses;
				
				foreach ($name_statuses as $i => $status) {
					if ($i > 0) $name_statuses[$i] = array('number'=>JText::_('COM_RSMEMBERSHIP_TRANSACTION_STATUS_'.strtoupper($status)));
					else $name_statuses[$i] = array('string'=>$status);
				}
				
				// preparing the data for the output
				
				$totalIncome = array();
				$countIncomes = array();
				foreach ($dates as $date) {
					$entrance = array($date);
					foreach ($statuses as $i=>$status) {
						if($i>0) {
							if(!isset($totalIncome[$status])) $totalIncome[$status] = 0;
							if(!isset($countIncomes[$status])) $countIncomes[$status] = 0;
							$entrance[] = (isset($amounts[$date][$status])? (float) $amounts[$date][$status] : 0);
							$entrance[] = '<div style="padding:5px;"><strong>'.JText::_('COM_RSMEMBERSHIP_'.$unit).':</strong> '.$date.'<br/><strong>'.JText::_('COM_RSMEMBERSHIP_AMOUNT').':</strong> '.(isset($amounts[$date][$status])? (float) $amounts[$date][$status] : 0).' '.$currency.'</div>';
							$totalIncome[$status] += (isset($amounts[$date][$status])? (float) $amounts[$date][$status] : 0);
							$countIncomes[$status]++;
							
						}
						
					}
					$data[] = $entrance;
				}
				$averageIncome = array();
				
				foreach ($totalIncome as $key=>$total) {
					$averageIncome[$key] = ( $countIncomes[$key]>0 ? round($total / $countIncomes[$key], 2) : 0);
				}
				
				// building the output
				$new_columns = array();
				
				foreach ($name_statuses as $i=>$status) {
					if($i > 0) {
						$new_columns[] = $status;
						$new_columns[] = array('type'=>'string', 'role'=>'tooltip');
					} else {
						$new_columns[] = $status;
					}
				}
				$output['columns'] = $new_columns;
				$output['rows'] = array();
				
				if (isset($data) && count($data)>0) {
					foreach ($data as $entrance) {
						$output['rows'][] = $entrance;
					}
				}
				
				$output['options']  = new stdClass();
				$output['options']->title 					= JText::_('COM_RSMEMBERSHIP_REPORT_3');
				$output['options']->hAxis					= new stdClass();
				$output['options']->hAxis->title 			= JText::_('COM_RSMEMBERSHIP_'.$unit); 
				$output['options']->hAxis->titleTextStyle 	= "color: 'red'"; 
				$output['options']->crosshair = new stdClass(); 
				$output['options']->crosshair->trigger = 'both';
				$output['options']->pointSize = '5';
				$output['options']->vAxis = new stdClass();
				$output['options']->vAxis->format = '#,### '.$currency;
				$output['options']->tooltip = new stdClass();
				$output['options']->tooltip->isHtml = true;
				$output['info'] = new stdClass();
				$output['info']->total = $totalIncome; 
				$output['info']->average = $averageIncome; 
				$output['info']->currency = $currency; 
			break;
		}
		
		unset ($return);
		return $output;
	}

	function getMembershipName($id)
	{
		return @$this->_membership_names[$id];
	}
	
	function getQuarter($date)
	{
		$q = (int)floor(date('m', $date) / 3.1) + 1;
		return "Y Q$q";
	}
}