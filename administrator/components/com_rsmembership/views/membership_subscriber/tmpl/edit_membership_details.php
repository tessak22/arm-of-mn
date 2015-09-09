<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');
?>
<?php
$this->field->startFieldset(JText::_($this->fieldsets['main']->label), 'adminform form');

foreach ($this->form->getFieldset('main') as $field) {
	if (strtolower($field->type) == 'editor') {
		echo '<div class="clr"></div>';
	}

	$this->field->showField( $field->hidden ? '' : $field->label, $field->input);
}

$this->field->endFieldset();

?>

