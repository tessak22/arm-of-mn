<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class JFormFieldPatches extends JFormField 
{
	protected $type = 'Patches';

	public function getInput() 
	{
		$types 				= array('module', 'menu');

		$return  = '<table class="table table-hover">';
		foreach ( $types as $type ) 
		{
			$element_helper 	= RSMembershipPatchesHelper::getPatchFile($type);
			$element_writable	= is_writable($element_helper);
			$element_patched	= RSMembershipPatchesHelper::checkPatches($type);

			$return .= '<tr class="row '.( $element_patched ? 'success' : 'error' ).'">
							<td>'.JText::_('COM_RSMEMBERSHIP_'.strtoupper($type).'_PATCH').'</td>
							<td>'.$element_helper.'</td>
							<td>'.( $element_writable ? '<span class="success">'.JText::_('COM_RSMEMBERSHIP_WRITABLE').'</span>' : '<span class="error">'.JText::_('COM_RSMEMBERSHIP_UNWRITABLE').'</span>' ).'</td>
							<td><strong>'. ( $element_patched ? JText::_('COM_RSMEMBERSHIP_PATCH_APPLIED') : JText::_('COM_RSMEMBERSHIP_PATCH_NOT_APPLIED') ) .'</strong></td>
							<td>
								<button type="button" class="btn btn-small btn-'. ( $element_patched ? 'danger' : 'success' ) .'" onclick="submitbutton(\'configuration.'.( $element_patched ? 'unpatch'.$type : 'patch'.$type ).'\');" '.( !$element_writable ? 'disabled="disabled"' : '').'>'. ( $element_patched ? JText::_('COM_RSMEMBERSHIP_REMOVE_PATCH') : JText::_('COM_RSMEMBERSHIP_APPLY_PATCH') ) .'</button>
							</td>
						</tr>';
		}

		$return .= '</table>';

		return $return;
	}

	public function getLabel() 
	{
		return '';
	}
}