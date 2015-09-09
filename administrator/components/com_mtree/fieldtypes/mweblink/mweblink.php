<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2011-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class mFieldType_mweblink extends mFieldType_weblink {
	
	var $acceptedProtocols = array('http','https');
	var $defaultPrefix = 'http://';
	
	function getJSValidationFunction() {
		$acceptedProtocols = $this->getAcceptedProtocols();
		$arrProtocolRegex = array();
		foreach( $acceptedProtocols AS $acceptedProtocol )
		{
            $arrProtocolRegex[] = $acceptedProtocol;
		}
        return 'function(){return (arguments[0].value != "" && /^(?:(?:'.implode('|',$arrProtocolRegex).'):\/\/)?(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:\/\S*)?$/i.test(arguments[0].value))}';
    }

	function parseValue($value)
	{
		$value = trim(strip_tags($value));
		$protocol = substr($value,0,strpos($value,':'));
		$acceptedProtocols = $this->getAcceptedProtocols();
		
		if( $protocol !== false )
		{
			if( in_array($protocol,$acceptedProtocols) ) {
				return $value;
			} else {
				return $this->defaultPrefix . $value;
			}
		}
		elseif(!empty($value))
		{
			return $this->defaultPrefix . $value;
		}
		else
		{
			return '';
		}
	}
	
	function getAcceptedProtocols()
	{
		$acceptFTP = $this->getParam( 'acceptFTP', 0 );
		
		$acceptedProtocols = $this->acceptedProtocols;
		if($acceptFTP) {
			array_push($acceptedProtocols,'ftp');
		}

		return $acceptedProtocols;
	}
	
	function getValue($arg=null) 
	{
		return $this->value;
	}
	
	function getOutput($view=1)
	{
		$my	= JFactory::getUser();
		
		$maxUrlLength 		= $this->getParam('maxUrlLength',60);
		$text 			= $this->getParam('text','');
		$title 			= $this->getParam('title','');
		$image			= $this->getParam('image','');
		$openNewWindow 		= $this->getParam('openNewWindow',1);
		$useNofollow		= $this->getParam('useNofollow',0);
		$useGA 			= $this->getParam('useGA',0);
		$gaPageTrackDirectory	= $this->getParam('gaPageTrackDirectory','/outgoing/');
		$showCounter 		= $this->getParam('showCounter',1);
	
		$html = '';
		$html .= '<a href="' . $this->getOutputURL() . '"';
		
		if( $openNewWindow == 1 )
		{
			$html .= ' target="_blank"';
		}
		
		if( !empty($title) )
		{
			$html .= ' title="' . $title . '"';
		}
		
		if($useNofollow)
		{
			$html .= ' rel="nofollow"';
		}
		
		if($useGA)
		{
			$html .= ' onClick="javascript: pageTracker._trackPageview(\'' . $gaPageTrackDirectory . $this->striphttp($this->getValue()) . '\');"';
		}
		
		$html .= '>';
		
		if(!empty($image))
		{
			global $mtconf;
			$live_site = $mtconf->getjconf('live_site');
			$html .= '<img src="' . trim(str_replace('{live_site}',$live_site,$image)) . '"';
			$html .= ' alt="' . $text . '"';
			$html .= ' />';
		}
		elseif(!empty($text))
		{
			$html .= $text;
		}
		else
		{
			$value = $this->striphttp($this->getValue());
			if( empty($maxUrlLength) || $maxUrlLength == 0 ) {
				$html .= $value;
			} else {
				$html .= substr($value,0,$maxUrlLength);
				if( strlen($value) > $maxUrlLength ) {
					$html .= $this->getParam('clippedSymbol');
				}
			}
		}
		
		$html .= '</a>';
		
		if( $showCounter )
		{
			$html .= '<span class="counter">('.JText::sprintf('FLD_WEBLINK_NUMBER_OF_VISITS', $this->counter).')</span>';
		}
		
		return $html;
	}
	
	function striphttp($url)
	{
		if(strpos($url,'://') !== false) {
			$return = substr($url,(strpos($url,'://')+3));
		} else {
			return $url;
		}
		return $return;
	}
}
?>