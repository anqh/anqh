<?php defined('SYSPATH') or die('No direct script access.');
/**
 * API Request model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_API_Request extends AutoModeler {

	protected $_table_name = 'api_requests';

	protected $_data = array(
		'id'      => null,
		'ip'      => null,
		'created' => null,
		'request' => null,
	);


	/**
	 * Get request count for the last n seconds
	 *
	 * @static
	 * @param   integer  $since  Timestamp
	 * @param   string   $ip
	 * @return  integer
	 */
	public static function request_count($since, $ip = null) {
		$count = DB::select(array(DB::expr('COUNT(*)'), 'request_count'))
			->from('api_requests')
			->where('created', '>', $since);

		return $ip
			? $count->and_where('ip', '=', $ip)->execute()->get('request_count')
			: $count->execute()->get('request_count');
	}

}
