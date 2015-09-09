<?php
/**
 * @version	$Id: corecity.php 1109 2011-05-26 10:54:42Z cy $
 * @package	Mosets Tree
 * @copyright	(C) 2011-2012 Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');
require_once(JPATH_SITE.'/components/com_mtree/mtree.tools.php');

class mFieldType_category extends mFieldType {
	var $numOfInputFields = 0;
	var $primaryCatId = null;
	var $secondaryCatIds = array();

	var $showPrimaryCategory = 1;
	var $showSecondaryCategories = 1;
	var $showBreadcrumbs = 1;

	public function __construct( $data=array() )
	{
		parent::__construct($data);

		$this->showPrimaryCategory = $this->getParam( 'showPrimaryCategory', 1 );
		$this->showSecondaryCategories = $this->getParam( 'showSecondaryCategories', 1 );
		$this->showBreadcrumbs = $this->getParam( 'showBreadcrumbs', 1 );

		if ($this->showPrimaryCategory)
		{
			$this->primaryCatId = $this->getCatId();
		}

		if ($this->showSecondaryCategories)
		{
			$db = JFactory::getDBO();
			$db->setQuery( 'SELECT cat.cat_id, cat.cat_name FROM #__mt_cl AS cl '
				. ' LEFT JOIN #__mt_cats AS cat ON cat.cat_id = cl.cat_id '
				. ' WHERE cl.link_id = ' . $db->Quote($this->getLinkId())
				. ' AND cat.cat_published = 1 '
				. ' AND main = 0'
			);
			$this->secondaryCatIds = $db->loadObjectList();
		}
	}

	/**
	* Return the formatted output
	* @param int Type of output to return. Especially useful when you need to display expanded 
	*		 information in detailed view and use can use this display a summarized version
	*		 for summary view. $view = 1 for Normal/Details View. $view = 2 for Summary View.
	* @return str The formatted value of the field
	*/
	function getOutput($view=1) {
		$html = '';
		$arrCategoryHtml = array();

		if ($this->showPrimaryCategory)
		{
			if($this->showBreadcrumbs) {
				$arrCategoryHtml[] = $this->_categoryOutput($this->primaryCatId);
			} else {
				$arrCategoryHtml[] = '<a href="' . JRoute::_('index.php?option=com_mtree&task=listcats&cat_id='.$this->primaryCatId) . '">' . $this->getCatName(). '</a>';
			}
		}

		if ($this->showSecondaryCategories)
		{
			foreach($this->secondaryCatIds AS $secondaryCategory)
			{
				if($this->showBreadcrumbs) {
					$arrCategoryHtml[] = $this->_categoryOutput($secondaryCategory->cat_id);
				} else {
					$arrCategoryHtml[] = '<a href="' . JRoute::_('index.php?option=com_mtree&task=listcats&cat_id='.$secondaryCategory->cat_id) . '">' . $secondaryCategory->cat_name. '</a>';
				}
			}
		}

		if (!empty($arrCategoryHtml))
		{
			$html .= '<ul><li>';
			$html .= implode('</li><li>',$arrCategoryHtml);
			$html .= '</li><ul>';
		}

		return $html;
	}

	private function _categoryOutput($cat_id) {
		$pathway = new mtPathWay();

		$output = '';

		if( $cat_id == 0 )
		{
			$output .= '<a href="">' . JText::_( 'COM_MTREE_ROOT' ). '</a>';
		} else {
			$output .= $pathway->getBreadcrumbs($cat_id, 1);
		}

		return $output;
	}

	function getSearchHTML( $showSearchValue=false, $showPlaceholder=false, $idprefix='search_' )
	{
		$cat_id = $this->getParam( 'cat_id', 0 );

		getCatsSelectlist( $cat_id, $cat_tree, 1 );

		if( !empty($cat_tree) ) {
			$cat_options[] = JHtml::_('select.option', $this->getCatId(), '&nbsp;');
			foreach( $cat_tree AS $ct ) {
				$cat_options[] = JHtml::_('select.option', $ct["cat_id"], str_repeat("-",($ct["level"]*3)) .(($ct["level"]>0) ? "":''). $ct["cat_name"]);
			}
			
			if( $showSearchValue ) {
				$value = $this->getSearchValue();
			}
			$catlist = JHtml::_(
				'select.genericlist', 
				$cat_options, 
				'cfcat_id', 
				'', 
				'value', 
				'text', 
				$this->getSearchValue()
			);
		} else {
			return null;
		}
		
		$return = $catlist;

		// This element identify itself as a category field
		$return .= '<input type="hidden" name="cfcat" value="'.$this->getName().'" />';

		return $return;
	}
	
	function getWhereCondition() {
		return null;
	}

	function getValue($arg=null) {

		$arrOutput = array();

		if ($this->showPrimaryCategory)
		{
			$arrOutput[] = $this->primaryCatId;
		}

		if ($this->showSecondaryCategories)
		{
			foreach($this->secondaryCatIds AS $secondaryCategory)
			{
				$arrOutput[] = $secondaryCategory->cat_id;
			}
		}

		return implode(',', $arrOutput);
	}

	function hasValue() {
		$value = $this->getValue();

		return !empty($value);
	}
	
	function parseValue( $value ) { return $this->getCatId(); }

}

?>