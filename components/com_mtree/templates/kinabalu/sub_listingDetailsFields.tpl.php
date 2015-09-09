<h3><?php echo MText::_( 'LISTING_DETAILS', $this->tlcat_id ); ?></h3>
<?php

// Address
$address = '';
if( $this->config->getTemParam('displayAddressInOneRow','1') ) {
	$address_parts = array();
	$address_displayed = false;
	foreach( array( 4,5,6,7,8 ) AS $address_field_id )
	{
		$field = $this->fields->getFieldById($address_field_id);
		if( isset($field) && $output = $field->getOutput(1) )
		{
			$address_parts[] = $output;
		}
	}
	if( !empty($address_parts) ) { $address = implode(', ',$address_parts); }
}

echo '<div class="fields">';
$number_of_columns = $this->config->getTemParam('numOfColumnsInDetailsView','1');
$field_count = 0;
$need_div_closure = false;
$this->fields->resetPointer();
while( $this->fields->hasNext() ) {
	$field = $this->fields->getField();
	$value = $field->getValue();
	$hasValue = $field->hasValue();
	if(
		(
			(
				(!$field->hasInputField() && !$field->isCore() && empty($value))
				||
				(!empty($value) || $value == '0')
			)
			&&
			// This condition ensure that fields listed in array() are skipped
			!in_array($field->getName(),array('link_name','link_desc'))
			&&
			(
				(
					$this->config->getTemParam('displayAddressInOneRow','1') == 1
					&&
					!in_array($field->getId(),array(5,6,7,8))
				)
				||
				$this->config->getTemParam('displayAddressInOneRow','1') == 0
			)
			&&
			$hasValue
			&&
			// User has the view levels to access the custom field
			in_array($field->viewAccessLevel,$this->myAuthorisedViewLevels)
		)
		||
		// Fields in array() are always displayed regardless of its value.
		in_array($field->getName(),array('link_featured'))
	) {
		if( $field_count % $number_of_columns == 0 ) {
			echo '<div class="row0">';
			$need_div_closure = true;
		}

		echo '<div id="field_'.$field->getId().'" class="fieldRow '.$field->getFieldTypeClassName().(($field_count % $number_of_columns == ($number_of_columns -1))?' lastFieldRow':'').'" style="width:'.floor(98/intval($number_of_columns)).'%">';

		if($this->config->getTemParam('displayAddressInOneRow','1') && in_array($field->getId(),array(4,5,6,7,8)) && $address_field = $this->fields->getFieldById(4)) {
			if( $address_displayed == false ) {
				echo '<div class="caption">';
				if($address_field->hasCaption()) {
					echo $address_field->getCaption();
				}
				echo '</div>';
				echo '<div class="output">';
				echo $address_field->getDisplayPrefixText();
				echo $address;
				echo $address_field->getDisplaySuffixText();
				echo '</div>';
				$address_displayed = true;
			}
		} else {
			echo '<div class="caption">';
			if($field->hasCaption()) {
				echo $field->getCaption();
			}
			echo '</div>';
			echo '<div class="output">';
			switch($field->getFieldType())
			{
				case ( $field->getFieldType() == 'coreprice' && $field->getValue() == 0 ):
					echo $field->getOutput(1);
					break;

				default:
					echo $field->getDisplayPrefixText();
					echo $field->getOutput(1);
					echo $field->getDisplaySuffixText();
			}
			echo '</div>';
		}
		echo '</div>';

		if( ($field_count % $number_of_columns) == ($number_of_columns-1) ) {
			echo '</div>';
			$need_div_closure = false;
		}
		$field_count++;
	}
	$this->fields->next();
}
if( $need_div_closure ) {
	echo '</div>';
	$need_div_closure = false;
}
echo '</div>';