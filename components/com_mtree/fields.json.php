<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2012 Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

require_once( JPATH_COMPONENT_ADMINISTRATOR.'/mfields.class.php' );

switch($task)
{
	case "fields.list":
		fieldsList($cat_id, $link_id);
		break;
}

function fieldsList($cat_id, $link_id)
{
	$db	= JFactory::getDBO();
	$my	= JFactory::getUser();
	$link	= new mtLinks( $db );

	$is_admin= JFactory::getApplication()->input->getInt('is_admin', 0);

	# Do not allow Guest to edit listing
	if ( $link_id > 0 && $my->id <= 0 ) {
		$link->load( 0 );
	} else {
		$link->load( $link_id );
	}
	
	$cf_ids = array(1);
	$cf_ids = array_merge($cf_ids,getAssignedFieldsID($cat_id));
	
	# Load all published CORE & custom fields
	$sql = "SELECT cf.*, " . ($link_id ? $link_id : 0) . " AS link_id, cfv.value AS value, cfv.attachment, cfv.counter FROM #__mt_customfields AS cf "
		.	"\nLEFT JOIN #__mt_cfvalues AS cfv ON cf.cf_id=cfv.cf_id AND cfv.link_id = " . $link_id
		.	"\nWHERE cf.hidden ='0' AND cf.published='1'"
		.	((!empty($cf_ids))?"\nAND cf.cf_id IN (" . implode(',',$cf_ids). ") ":'')
		.	"\nORDER BY ordering ASC";
	$db->setQuery($sql);
	
	$fieldsOutput = array();
	$fields = new mFields();
	$fields->setCoresValue( $link->link_name, $link->link_desc, $link->address, $link->city, $link->state, $link->country, $link->postcode, $link->contactperson, $link->mobile, $link->date, $link->year, $link->telephone, $link->fax, $link->email, $link->website, $link->price, $link->link_hits, $link->link_votes, $link->link_rating, $link->link_featured, $link->link_created, $link->link_modified, $link->link_visited, $link->publish_up, $link->publish_down, $link->metakey, $link->metadesc, $link->user_id, '' );
	$fields->loadFields($db->loadObjectList());

    // Check if category has associated category
    $top_level_cat_id = getTopLevelCatID($cat_id);
    $db->setQuery( 'SELECT * FROM #__mt_cats WHERE cat_id = ' . $top_level_cat_id . ' LIMIT 1');
    $top_level_cat = $db->loadObject();

    # Check to see if listing categories has association
    if( $top_level_cat->cat_association > 0 ) {

        // Get the name/caption of the associated category.
        $db->setQuery( 'SELECT cat_id, cat_name FROM #__mt_cats where cat_id = '.$top_level_cat->cat_association.' LIMIT 1');
        $assoc_cat = $db->loadObject();

        // Now get the associated listings name.
        $db->setQuery(
            'SELECT DISTINCT link_id2, l.link_id, l.link_name FROM #__mt_links_associations AS links_assoc '
            .	"\n LEFT JOIN #__mt_links AS l ON links_assoc.link_id1 = l.link_id "
            .	"\n WHERE links_assoc.link_id2 = " . $link->link_id
        );
        $links_assoc = $db->loadObjectList('link_id2');

        $fields->setAssocLink(
            array(
                'cat_name'	=> $assoc_cat->cat_name,
                'cat_id'	=> $assoc_cat->cat_id,
                'link_id'	=> (isset($links_assoc[$link->link_id]->link_id))?$links_assoc[$link->link_id]->link_id:null,
                'link_name'	=> (isset($links_assoc[$link->link_id]->link_name))?$links_assoc[$link->link_id]->link_name:null
            )
        );
    }

    $fields->resetPointer();
	while( $fields->hasNext() ) {
		unset($fieldObj);
		$field = $fields->getField();
		if( $field->hasInputField() )
		{
			if( $is_admin == 1 && in_array($field->getName(),array('metakey','metadesc')) ) {
				$fields->next();
				continue;
			}
            $fieldObj = new stdClass();
			$fieldObj->id = $field->getId();
			$fieldObj->name = $field->getName();
			$fieldObj->caption = $field->getCaption();
			$fieldObj->modPrefixText = $field->getModPrefixText();
			$fieldObj->inputHTML = $field->getInputHTML();
			$fieldObj->modSuffixText = $field->getModSuffixText();
			$fieldObj->jsValidation = $field->getJsValidation();
			$fieldObj->isRequired = $field->isRequired();
			$fieldObj->fieldTypeClassName = $field->getFieldTypeClassName();
			array_push($fieldsOutput,$fieldObj);
		}
		$fields->next();
	}
	
	echo json_encode($fieldsOutput);
}
?>