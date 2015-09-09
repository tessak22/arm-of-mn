<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

class RSMembershipValidation
{
	public static function website($url) {
		return !preg_match('!^(http|https)://([\w-]+\.)+[\w-]+(/[\w- ./?%&=]*)?$!', $url);
	}

	public static function email($email) {
		jimport('joomla.mail.helper');

		return JMailHelper::isEmailAddress($email);
	}

	public static function numeric($number) {
		if (is_array($number)) $number = implode('', $number);
		$number = str_replace(array("\n", "\r", "\t"), '', $number);

		return preg_match("/^([0-9]{1,3}(?:,?[0-9]{3})*(?:\.[0-9]+?)?)$/", $number) || preg_match("/^([0-9]{1,3}(?:\.?[0-9]{3})*(?:,[0-9]+?)?)$/", $number);
	}
	
	public static function alphanumeric($string) {
		if (is_array($string)) $string = implode('', $string);
		$string = str_replace(array("\n", "\r", "\t"), '', $string);

		return !preg_match('#([^a-zA-Z0-9 ])#', $string);
	}
	
	public static function alpha($string) {
		if (is_array($string)) $string = implode('', $string);
		$string = str_replace(array("\n", "\r", "\t"), '', $string);

		return !preg_match('#([^a-zA-Z ])#', $string);
	}
}