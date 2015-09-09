<?php
/**
* @package RSEvents!Pro
* @copyright (C) 2015 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

class rseventsproModelRseventspro extends JModelLegacy
{	
	/**
	 * Constructor.
	 *
	 * @since	1.6
	 */
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * Method to get events.
	 */
	public function getEvents() {		
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);
		
		$query->clear()
			->select('COUNT('.$db->qn('u.id').') as subscribers')->select($db->qn('e.id'))->select($db->qn('e.name'))
			->select($db->qn('e.start'))->select($db->qn('e.end'))->select($db->qn('e.allday'))
			->from($db->qn('#__rseventspro_events','e'))
			->join('LEFT',$db->qn('#__rseventspro_users','u').' ON '.$db->qn('e.id').' = '.$db->qn('u.ide'))
			->where($db->qn('e.published').' = 1')
			->where($db->qn('e.completed').' = 1')
			->where($db->qn('e.start').' > '.$db->q(JFactory::getDate()->toSql()))
			->group($db->qn('e.id'))
			->order($db->qn('e.start').' ASC');
		
		$db->setQuery($query, 0, rseventsproHelper::getConfig('dashboard_upcoming_nr','int',5));
		return $db->loadObjectList();
	}
	
	/**
	 * Method to get subscribers.
	 */
	public function getSubscribers() {
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);
		
		$query->clear()
			->select($db->qn('e.id','eid'))->select($db->qn('e.name','ename'))
			->select($db->qn('u.id'))->select($db->qn('u.name'))->select($db->qn('u.date'))
			->from($db->qn('#__rseventspro_users','u'))
			->join('left',$db->qn('#__rseventspro_events','e').' ON '.$db->qn('e.id').' = '.$db->qn('u.ide'))
			->order($db->qn('u.date').' DESC');
		
		$db->setQuery($query, 0, rseventsproHelper::getConfig('dashboard_subscribers_nr','int',5));
		return $db->loadObjectList();
	}
	
	/**
	 * Method to get comments.
	 */
	public function getComments() {
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$limit = rseventsproHelper::getConfig('dashboard_comments_nr','int',5);
		
		switch(rseventsproHelper::getConfig('event_comment','int')) {
			//no comments or Facebook
			default:
			case 0:
			case 1:
				return array();
			break;
			
			//RSComments!
			case 2:
				$query->clear();
				$query->select($db->qn('e.id').', '.$db->qn('e.name').', '.$db->qn('c.IdComment','cid').', '.$db->qn('c.name','cname').', '.$db->qn('c.comment').', '.$db->qn('c.date').', '.$db->qn('c.published'))
						->from($db->qn('#__rscomments_comments','c'))
						->join('left',$db->qn('#__rseventspro_events','e').' ON '.$db->qn('e.id').' = '.$db->qn('c.id'))
						->where($db->qn('c.option').' = '.$db->q('com_rseventspro'))
						->order($db->qn('c.date').' DESC');
						
				$db->setQuery($query, 0, $limit);
				$comments = $db->loadObjectList();
			break;
			
			//JComments
			case 3:
				$query->clear();
				$query->select($db->qn('e.id').', '.$db->qn('e.name').', '.$db->qn('c.id','cid').', '.$db->qn('c.name','cname').', '.$db->qn('c.comment').', UNIX_TIMESTAMP('.$db->qn('c.date').') as date, '.$db->qn('c.published'))
						->from($db->qn('#__jcomments','c'))
						->join('left',$db->qn('#__rseventspro_events','e').' ON '.$db->qn('e.id').' = '.$db->qn('c.object_id'))
						->where($db->qn('c.object_group').' = '.$db->q('com_rseventspro'))
						->order($db->qn('c.date').' DESC');
				
				$db->setQuery($query, 0, $limit);
				$comments = $db->loadObjectList();
			break;
			
			//Jom Comments
			case 4:
				$query->clear();
				$query->select($db->qn('e.id').', '.$db->qn('e.name').', '.$db->qn('c.id','cid').', '.$db->qn('c.name','cname').', '.$db->qn('c.comment').', UNIX_TIMESTAMP('.$db->qn('c.date').') as date, '.$db->qn('c.published'))
						->from($db->qn('#__jomcomment','c'))
						->join('left',$db->qn('#__rseventspro_events','e').' ON '.$db->qn('e.id').' = '.$db->qn('c.contentid'))
						->where($db->qn('c.option').' = '.$db->q('com_rseventspro'))
						->order($db->qn('c.date').' DESC');
				
				$db->setQuery($query, 0, $limit);
				$comments = $db->loadObjectList();
			break;
		}
		return $comments;
	}
}