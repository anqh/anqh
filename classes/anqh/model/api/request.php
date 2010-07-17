<?php defined('SYSPATH') or die('No direct script access.');
/**
 * API_Request model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_API_Request extends Jelly_Model {

	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta->fields(array(
			'id' => new Field_Primary,
			'ip' => new Field_String,
			'created' => new Field_Timestamp(array(
				'auto_now_create' => true,
			)),
			'request' => new Field_Text,
		));
	}


	/**
	 * Get request count for the last n seconds
	 *
	 * @static
	 * @param   integer  $since  Timestamp
	 * @param   string   $ip
	 * @return  integer
	 */
	public static function request_count($since, $ip = null) {
		return $ip
			? Jelly::select('api_request')->where('ip', '=', $ip)->and_where('created', '>', $since)->count()
			: Jelly::select('api_request')->where('created', '>', $since)->count();
	}
	
}
