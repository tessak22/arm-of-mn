<?php
/*
$cust_1 = $this->fields->getFieldByCaption('Custom Text'); // getFieldByCaption() allow you to get the field by the Caption. This is not the best way to get a field since changing the caption in the back-end will break the reference.
echo '<br />Field ID: ' . $cust_1->getId();

$cust_2 = $this->fields->getFieldById(29);  // getFieldById() is the ideal way of getting a field. The ID can be found at 'Custom Fields' section in Mosets Tree's back-end.
echo '<br />Name: ' . $cust_2->getName();
echo '<br />Has Caption? ' . (($cust_2->hasCaption()) ? 'Yes' : 'No');
echo '<br />Caption: ' . $cust_1->getCaption();
echo '<br />Value: ' . $cust_2->getValue();
echo '<br />Output: ' . $cust_2->getOutput(1);
echo '<hr />';
$this->fields->resetPointer();
while( $this->fields->hasNext() ) {
	$field = $this->fields->getField();
	echo '<br /><strong>' . $field->getCaption() . '</strong>';
	echo ': ';
	echo $field->getOutput(1); // getOutput() returns the formatted value of the field. ie: For a youtube video, the youtube player will be loaded
	// echo $field->getValue(); // getValue() returns the raw value without additional formatting. ie: When getting value from a Online Video field type, it will return the URL.
	$this->fields->next();
}
*/
	
?>
<!-- Listing Details Default Style -->
<div id="listing" class="row-fluid link-id-<?php echo $this->link_id; ?> cat-id-<?php echo $this->link->cat_id; ?> tlcat-id-<?php echo $this->link->tlcat_id; ?>">
<?php
// Listing title
include $this->loadTemplate( 'sub_listingDetailsTitle.tpl.php' );
?>

<div class="row-fluid">
<?php

if ( !empty($this->mambotBeforeDisplayContent) && $this->mambotBeforeDisplayContent[0] <> '' ) { 
	echo trim( implode( "\n", $this->mambotBeforeDisplayContent ) ); 
}

echo '<div class="span8">';

echo '<div class="listing-desc">';

if(!is_null($this->fields->getFieldById(2))) { 
	$link_desc = $this->fields->getFieldById(2);
	echo '<span itemprop="description">';
	if( $link_desc->hasValue() )
	{
		echo $link_desc->getDisplayPrefixText(); 
		echo $link_desc->getOutput(1);
		echo $link_desc->getDisplaySuffixText(); 
	}
	echo '</span>';
}
echo '</div>';

if ( !empty($this->mambotAfterDisplayContent) ) { echo trim( implode( "\n", $this->mambotAfterDisplayContent ) ); }

// Rating & Favourite Box
include $this->loadTemplate( 'sub_listingDetailsRatingFavourite.tpl.php' );

echo '</div>';

echo '<div class="span4">';

if (!empty($this->images)) include $this->loadTemplate( 'sub_images.tpl.php' );

// Other custom fields
include $this->loadTemplate( 'sub_listingDetailsFields.tpl.php' );

echo '</div>';

echo '</div>'; // End of .row

// Action buttons
include $this->loadTemplate( 'sub_listingDetailsActions.tpl.php' );

include $this->loadTemplate( 'sub_listingShare.tpl.php' );

// Load User Profile
if( $this->config->get('show_user_profile_in_listing_details') )
{
	include $this->loadTemplate( 'sub_userProfile.tpl.php' );
}

// Load Contact Owner Form
if(	$this->config->get('contact_form_location') == 2 ) {
    include $this->loadTemplate( 'sub_contactOwnerForm.tpl.php' );
}

?>
</div>