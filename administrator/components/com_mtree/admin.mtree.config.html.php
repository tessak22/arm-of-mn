<?php
/**
 * @package      Mosets Tree
 * @copyright    (C) 2015-present Mosets Consulting. All rights reserved.
 * @license      GNU General Public License
 * @author       Lee Cher Yeong <mtree@mosets.com>
 * @url          http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class MTConfigHtml
{

	public static function _($function, $items = array(), $config = null)
	{
		$args = func_get_args();
		array_shift($args);
		$i = 0;

		foreach ($items AS $item)
		{
			if (!isset($item['override']))
			{
				$item['override'] = null;
			}

			if (!isset($items[$i]['override']))
			{
				$items[$i]['override']   = null;
				$args[0][$i]['override'] = null;
			}

			if (!empty($config['namespace']))
			{
				$args[0][$i]['varname'] = $config['namespace'] . '[' . $args[0][$i]['varname'] . ']';
			}
			$i++;
		}

		if (empty($function))
		{
			return call_user_func_array(array('MTConfigHtml', 'self::text'), $args);
		}
		else
		{
			return call_user_func_array(array('MTConfigHtml', 'self::' . $function), $args);
		}
	}

	public static function overrideCheckbox($items = array(), $config = null)
	{
		$checked = ($items[0]['override'] != '' ? true : false);
		$class   = (!empty($config['class']) ? 'class="' . $config['class'] . '" ' : '');

		return '<input type="checkbox" name="override[' . $items[0]['varname'] . ']" value="1" ' . ($checked ? 'checked ' : '') . $class . 'onclick="" />';
	}

	public static function text($items, $config = null)
	{
		return '<input name="' . $items[0]['varname'] . '" value="' . self::getValue($items[0]) . '" size="30" />';
	}

	public static function label($items, $config = null)
	{
		return JText::_('COM_MTREE_CONFIGLABEL_' . strtoupper($items[0]['varname']));
	}

	public static function type_of_listings_in_index($items, $config = null)
	{
		# Listings type in index
		$type_of_listings_in_index = array();
		$arr_tmp                   = array('listcurrent', 'listpopular', 'listmostrated', 'listtoprated', 'listmostreview', 'listnew', 'listupdated', 'listfavourite', 'listfeatured');

		foreach ($arr_tmp AS $tmp)
		{
			$type_of_listings_in_index[] = JHtml::_('select.option', $tmp, JText::_('COM_MTREE_TYPES_OF_LISTINGS_IN_INDEX_OPTION_' . strtoupper($tmp)));
		}

		return JHtml::_('select.genericlist', $type_of_listings_in_index, $items[0]['varname'], 'size="1"', 'value', 'text', self::getValue($items[0]));
	}

	public static function owner_default_page($items, $config = null)
	{
		$default_owner_listing_page   = array();
		$default_owner_listing_page[] = JHtml::_('select.option', "viewuserslisting", JText::_('COM_MTREE_DEFAULT_OWNER_LISTING_PAGE_OPTION_VIEWUSERSLISTING'));
		$default_owner_listing_page[] = JHtml::_('select.option', "viewusersfav", JText::_('COM_MTREE_DEFAULT_OWNER_LISTING_PAGE_OPTION_VIEWUSERSFAV'));
		$default_owner_listing_page[] = JHtml::_('select.option', "viewusersreview", JText::_('COM_MTREE_DEFAULT_OWNER_LISTING_PAGE_OPTION_VIEWUSERSREVIEW'));

		return JHtml::_('select.genericlist', $default_owner_listing_page, $items[0]['varname'], 'size="1"', 'value', 'text', self::getValue($items[0]));
	}

	public static function feature_locations($items, $config = null)
	{
		$feature_locations   = array();
		$feature_locations[] = JHtml::_('select.option', "1", JText::_('COM_MTREE_STANDALONE_PAGE'));
		$feature_locations[] = JHtml::_('select.option', "2", JText::_('COM_MTREE_LISTING_DETAILS_PAGE'));

		return JHtml::_('select.genericlist', $feature_locations, $items[0]['varname'], 'size="1"', 'value', 'text', self::getValue($items[0]));
	}

	public static function sef_link_slug_type($items, $config = null)
	{
		$sef_link_slug_type   = array();
		$sef_link_slug_type[] = JHtml::_('select.option', "1", JText::_('COM_MTREE_ALIAS'));
		$sef_link_slug_type[] = JHtml::_('select.option', "2", JText::_('COM_MTREE_LINK_ID'));
		$sef_link_slug_type[] = JHtml::_('select.option', "3", JText::_('COM_MTREE_LINK_ID_AND_ALIAS_HYBRID'));

		return JHtml::_('select.genericlist', $sef_link_slug_type, $items[0]['varname'], 'size="1"', 'value', 'text', self::getValue($items[0]));
	}

	public static function resize_method($items, $config = null)
	{
		$imageLibs = detect_ImageLibs();

		return $imageLibs['gd2'];
	}

	public static function yesno($items, $config = null)
	{
		$arr = array(
			JHtml::_('select.option', '0', JText::_('JNO')),
			JHtml::_('select.option', '1', JText::_('JYES'))
		);

		$html = '<fieldset class="radio btn-group" id="' . str_replace(array('[', ']'), array('_', ''), $items[0]['varname']) . '_fieldset">';

		$yesno_values = array(1, 0);
		$value        = (int) self::getValue($items[0]);

		foreach ($yesno_values AS $yesno_value)
		{
			$html .= '<input type="radio" ';
			if ($value == $yesno_value)
			{
				$html .= 'checked="checked" ';
			}
			$html .= 'value="' . $yesno_value . '" name="' . $items[0]['varname'] . '" id="' . str_replace(array('[', ']'), array('_', ''), $items[0]['varname']) . $yesno_value . '">';
			$html .= '<label for="' . str_replace(array('[', ']'), array('_', ''), $items[0]['varname']) . $yesno_value . '" ';
			$html .= 'class="';
			$html .= '">';
			$html .= ($yesno_value ? JText::_('JYES') : JText::_('JNO') );
			$html .= '</label>';
		}

		$html .= '</fieldset>';

		return $html;
	}

	public static function cat_order($items, $config = null)
	{
		# Sort Direction
		$sort[] = JHtml::_('select.option', "asc", JText::_('COM_MTREE_ASCENDING'));
		$sort[] = JHtml::_('select.option', "desc", JText::_('COM_MTREE_DESCENDING'));

		# Category Order
		$cat_order   = array();
		$cat_order[] = JHtml::_('select.option', '', JText::_(''));
		$cat_order[] = JHtml::_('select.option', "lft", JText::_('COM_MTREE_CONFIG_CUSTOM_ORDER'));
		$cat_order[] = JHtml::_('select.option', "cat_name", JText::_('COM_MTREE_NAME'));
		$cat_order[] = JHtml::_('select.option', "cat_featured", JText::_('COM_MTREE_FEATURED'));
		$cat_order[] = JHtml::_('select.option', "cat_created", JText::_('COM_MTREE_CREATED'));

		$html = JHtml::_('select.genericlist', $cat_order, $items[0]['varname'], 'size="1"', 'value', 'text', self::getValue($items[0]));
		$html .= JHtml::_('select.genericlist', $sort, $items[1]['varname'], 'size="1"', 'value', 'text', self::getValue($items[1]));

		return $html;
	}


	public static function predefined_reply_title($items, $config = null)
	{
		$html = '<input name="' . $items[0]['varname'] . '" value="' . self::getValue($items[0]) . '" size="60" />';
		$html .= '<br />';
		$html .= '<textarea style="margin-top:5px" name="' . $items[1]['varname'] . '" cols="80" rows="8" />' . self::getValue($items[1]) . '</textarea>';

		return $html;
	}

	public static function note($items)
	{
		return JText::_('COM_MTREE_CONFIGNOTE_' . strtoupper($items[0]['varname']));
	}

	public static function listing_order($items, $config = null)
	{
		# Sort Direction
		$sort[] = JHtml::_('select.option', "asc", JText::_('COM_MTREE_ASCENDING'));
		$sort[] = JHtml::_('select.option', "desc", JText::_('COM_MTREE_DESCENDING'));

		# Listing Order
		$listing_order   = array();
		$listing_order[] = JHtml::_('select.option', "link_name", JText::_('COM_MTREE_NAME'));
		$listing_order[] = JHtml::_('select.option', "link_hits", JText::_('COM_MTREE_HITS'));
		$listing_order[] = JHtml::_('select.option', "link_votes", JText::_('COM_MTREE_VOTES'));
		$listing_order[] = JHtml::_('select.option', "link_rating", JText::_('COM_MTREE_RATING'));
		$listing_order[] = JHtml::_('select.option', "link_visited", JText::_('COM_MTREE_VISIT'));
		$listing_order[] = JHtml::_('select.option', "link_featured", JText::_('COM_MTREE_FEATURED'));
		$listing_order[] = JHtml::_('select.option', "link_created", JText::_('COM_MTREE_CREATED'));
		$listing_order[] = JHtml::_('select.option', "link_modified", JText::_('COM_MTREE_MODIFIED'));
		$listing_order[] = JHtml::_('select.option', "address", JText::_('COM_MTREE_ADDRESS'));
		$listing_order[] = JHtml::_('select.option', "city", JText::_('COM_MTREE_CITY'));
		$listing_order[] = JHtml::_('select.option', "state", JText::_('COM_MTREE_STATE'));
		$listing_order[] = JHtml::_('select.option', "country", JText::_('COM_MTREE_COUNTRY'));
		$listing_order[] = JHtml::_('select.option', "postcode", JText::_('COM_MTREE_POSTCODE'));
		$listing_order[] = JHtml::_('select.option', "contactperson", JText::_('COM_MTREE_CONTACTPERSON'));
		$listing_order[] = JHtml::_('select.option', "mobile", JText::_('COM_MTREE_MOBILE'));
		$listing_order[] = JHtml::_('select.option', "date", JText::_('COM_MTREE_DATE'));
		$listing_order[] = JHtml::_('select.option', "year", JText::_('COM_MTREE_YEAR'));
		$listing_order[] = JHtml::_('select.option', "telephone", JText::_('COM_MTREE_TELEPHONE'));
		$listing_order[] = JHtml::_('select.option', "fax", JText::_('COM_MTREE_FAX'));
		$listing_order[] = JHtml::_('select.option', "email", JText::_('COM_MTREE_EMAIL'));
		$listing_order[] = JHtml::_('select.option', "website", JText::_('COM_MTREE_WEBSITE'));
		$listing_order[] = JHtml::_('select.option', "price", JText::_('COM_MTREE_PRICE'));

		if (in_array('l.ordering', array($items[0]['value'], $items[0]['override'], $items[1]['value'], $items[1]['override'])))
		{
			$listing_order[] = JHtml::_('select.option', "l.ordering", JText::_('COM_MTREE_ORDERING'));
		}

		$html = JHtml::_('select.genericlist', $listing_order, $items[0]['varname'], 'size="1"', 'value', 'text', self::getValue($items[0]));
		$html .= JHtml::_('select.genericlist', $sort, $items[1]['varname'], 'size="1"', 'value', 'text', self::getValue($items[1]));

		return $html;
	}

	public static function review_order($items, $config = null)
	{
		# Sort Direction
		$sort[] = JHtml::_('select.option', "asc", JText::_('COM_MTREE_ASCENDING'));
		$sort[] = JHtml::_('select.option', "desc", JText::_('COM_MTREE_DESCENDING'));

		# Review Order
		$review_order[] = JHtml::_('select.option', '', JText::_(''));
		$review_order[] = JHtml::_('select.option', "rev_date", JText::_('COM_MTREE_REVIEW_DATE'));
		$review_order[] = JHtml::_('select.option', "vote_helpful", JText::_('COM_MTREE_TOTAL_HELPFUL_VOTES'));
		$review_order[] = JHtml::_('select.option', "vote_total", JText::_('COM_MTREE_TOTAL_VOTES'));

		$html = JHtml::_('select.genericlist', $review_order, $items[0]['varname'], 'size="1"', 'value', 'text', self::getValue($items[0]));
		$html .= JHtml::_('select.genericlist', $sort, $items[1]['varname'], 'size="1"', 'value', 'text', self::getValue($items[1]));

		return $html;
	}

	public static function sort($items, $config = null)
	{
		$sort_by_options = array('-link_featured', '-link_created', '-link_modified', '-link_hits', '-link_visited', '-link_rating', '-link_votes', 'link_name', '-price', 'price');

		foreach ($sort_by_options AS $sort_by_option)
		{
			$sort_by[] = JHtml::_('select.option', $sort_by_option, JText::_('COM_MTREE_ALL_LISTINGS_SORT_OPTION_' . strtoupper($sort_by_option)));
		}
		$html = JHtml::_('select.genericlist', $sort_by, $items[0]['varname'], 'size="1"', 'value', 'text', self::getValue($items[0]));

		return $html;
	}

	public static function sort_options($items, $config = null)
	{
		$sort_by_options = array('-link_featured', '-link_created', '-link_modified', '-link_hits', '-link_visited', '-link_rating', '-link_votes', 'link_name', '-price', 'price');

		$sort_by_option_values = self::getValue($items[0]);
		if (!is_array($sort_by_option_values))
		{
			$sort_by_option_values = explode('|', $sort_by_option_values);
		}

		$html = '';
		$html .= '<fieldset>';
		$html .= '<ul>';
		foreach ($sort_by_options AS $sort_by_option)
		{
			$html .= '<li>';
			$html .= '<label style="">';
			$html .= '<input type="checkbox" name="' . $items[0]['varname'] . '[]" value="' . $sort_by_option . '"';
			$html .= ' style="clear:left"';
			if (isset($sort_by_option_values) && in_array($sort_by_option, $sort_by_option_values))
			{
				$html .= ' checked';
			}
			$html .= ' />';
			$html .= JText::_('COM_MTREE_ALL_LISTINGS_SORT_OPTION_' . strtoupper($sort_by_option));
			$html .= '</label>';
			$html .= '</li>';
		}
		$html .= '</ul>';
		$html .= '</fieldset>';

		return $html;
	}

	public static function access_level($items, $config = null)
	{
		$db	= JFactory::getDBO();

		$db->setQuery( 'SELECT id, title FROM #__viewlevels ORDER BY ordering ASC' );
		$access_levels	= $db->loadObjectList();

		foreach ($access_levels AS $access_level)
		{
			$jhtml_access_levels[] = JHtml::_('select.option', $access_level->id, $access_level->title);
		}
		$html = JHtml::_('select.genericlist', $jhtml_access_levels, $items[0]['varname'], 'size="1"', 'value', 'text', self::getValue($items[0]));

		return $html;
	}

	public static function getValue($item)
	{
		if (isset($item['override']) && $item['override'] != '')
		{
			return $item['override'];
		}
		else
		{
			return $item['value'];
		}
	}

}