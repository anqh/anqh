<?php defined('SYSPATH') or die('No direct script access.');
/**
 * NewsfeedItem model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_NewsfeedItem extends AutoModeler {

	protected $_table_name = 'newsfeeditems';

	protected $_data = array(
		'id'      => null,
		'user_id' => null,
		'stamp'   => null,
		'class'   => null,
		'type'    => null,
		'data'    => null,
	);

	protected $_rules = array(
		'user_id' => array('not_empty', 'digit'),
		'stamp'   => array('not_empty', 'digit'),
		'class'   => array('max_length' => array(':value', 64)),
		'type'    => array('max_length' => array(':value', 64)),
	);


	/**
	 * Find Newsfeed items.
	 *
	 * @static
	 * @param   integer  $limit
	 * @param   array    $users  User ids
	 * @return  Database_Result
	 */
	public static function find_items($limit = 20, array $users = null) {
		$query = DB::select()->order_by('id', 'DESC');
		if (is_array($users)) {
			$query = $query->where('user_id', 'IN', $users);
		}

		return AutoModeler::factory('newsfeeditem')
			->load($query, $limit);
	}


	/**
	 * Override __get() to handle JSON in data, returned as array.
	 *
	 * @param   string  $key
	 * @return  mixed
	 */
	public function __get($key) {
		if ($key == 'data' && $this->_data['data']) {
			return json_decode($this->_data['data'], true);
		}

		return parent::__get($key);
	}


	/**
	 * Override __set() to handle JSON.
	 *
	 * @param   string  $key
	 * @param   mixed   $value
	 */
	public function __set($key, $value) {
		if ($key == 'data' && is_array($value)) {
			$value = @json_encode($value);
		}

		parent::__set($key, $value);
	}

}
