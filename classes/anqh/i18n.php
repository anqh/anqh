<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Internationalization helper
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_I18n extends Kohana_I18n{}


/**
 * Plural translation function
 *
 * @param   string   $string
 * @param   string   $string_plural
 * @param   integer  $count
 * @param   array    $args
 * @return  string
 */
function __2($string, $string_plural, $count, array $args = null) {
	return (int)$count == 1 ? __($string, $args) : __($string_plural, $args);
}
