<!-- Listing Details Style 2 -->
<div id="listing" class="row-fluid link-id-<?php echo $this->link_id; ?> cat-id-<?php echo $this->link->cat_id; ?> tlcat-id-<?php echo $this->link->tlcat_id; ?>">
<?php
// Listing title
include $this->loadTemplate( 'sub_listingDetailsTitle.tpl.php' );

if ( !empty($this->mambotBeforeDisplayContent) && $this->mambotBeforeDisplayContent[0] <> '' ) {
	echo trim( implode( "\n", $this->mambotBeforeDisplayContent ) ); 
}

echo '<div class="row-fluid">';

echo '<div class="span7">';

if (!empty($this->images)) include $this->loadTemplate( 'sub_images.tpl.php' );

echo '<div class="listing-desc">';

if(!is_null($this->fields->getFieldById(2))) { 
	$link_desc = $this->fields->getFieldById(2);
	echo $link_desc->getOutput(1);
}
echo '</div>';
if ( !empty($this->mambotAfterDisplayContent) ) { echo trim( implode( "\n", $this->mambotAfterDisplayContent ) ); }

// Rating & Favourite Box
include $this->loadTemplate( 'sub_listingDetailsRatingFavourite.tpl.php' );

echo '</div>';

echo '<div class="span5">';

// Other custom fields
include $this->loadTemplate( 'sub_listingDetailsFields.tpl.php' );

echo '</div>';

echo '</div>'; // End of .row-fluid

// Action buttons
include $this->loadTemplate( 'sub_listingDetailsActions.tpl.php' );

include $this->loadTemplate( 'sub_listingShare.tpl.php' );

// Load User Profile
if( $this->config->get('show_user_profile_in_listing_details') )
{
	include $this->loadTemplate( 'sub_userProfile.tpl.php' );
}

// Load Contact Owner Form
if( $this->config->get('contact_form_location') == 2 )
{
	include $this->loadTemplate( 'sub_contactOwnerForm.tpl.php' );
}

?>
</div>