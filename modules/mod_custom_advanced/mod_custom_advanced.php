<?php
/**
 * @copyright   Copyright (C) 2011 JTricks.com.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

$document = JFactory::getDocument();

$moduleStyle = $params->get('moduleStyle');
if (strlen($moduleStyle) > 0)
    $attribs['style'] = $moduleStyle;

$cssOverride = $params->get('cssOverride');
if (strlen($cssOverride) > 0)
    $document->addStyleDeclaration($cssOverride);

$styleSheet = $params->get('styleSheet');
if (strlen($styleSheet) > 0)
    $document->addStyleSheet($styleSheet);

$javascriptFile = $params->get('javascriptFile');
if (strlen($javascriptFile) > 0)
	$document->addScript($javascriptFile); 

?>
<!-- BEGIN: Custom advanced (www.pluginaria.com) -->
<?php
$customHtml = $params->get('customHtml');
if (strlen($customHtml) > 0) {

    if ($params->def('prepare_content', 0))
    {
    	JPluginHelper::importPlugin('content');
    	$customHtml = JHtml::_('content.prepare', $customHtml);
    }

    echo $customHtml;
}

$evalPhp = $params->get('evalPhp');

if (substr($evalPhp, 0, 5) === '<?php') {
    $evalPhp = substr($evalPhp, 5);
}

if (substr($evalPhp, -2) === '?>') {
    $evalPhp = substr($evalPhp, 0, -2);
}

if (strlen($evalPhp))
    eval($evalPhp);
?>
<!-- END: Custom advanced (www.pluginaria.com) -->
