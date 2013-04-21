<?php defined('SYSPATH') or die('No direct script access.');
/**
 * NewsfeedItem model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2013 Antti Qvickström
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
	 * @var  boolean  Aggregated pseudo model
	 */
	protected $_aggregate = false;


	/**
	 * Find Newsfeed items.
	 *
	 * @static
	 * @param   integer  $limit
	 * @param   array    $users  User ids
	 * @return  Model_NewsfeedItem[]
	 */
	public static function find_items($limit = 20, array $users = null) {
		$newsfeeditem = new Model_NewsfeedItem();
		$query = DB::select_array($newsfeeditem->fields())->order_by('stamp', 'DESC');
		if (is_array($users)) {
			$query = $query->where('user_id', 'IN', $users);

			// Include friend events
			$friend_ids = array();
			foreach ($users as $user_id) {
				$friend_ids[] = json_encode(array('friend_id' => $user_id));
			}
			$query = $query
				->or_where_open()
				->where('class', '=', 'user')
				->and_where('type', '=', 'friend')
				->and_where('data', 'IN', $friend_ids)
				->or_where_close();

		}

		return $newsfeeditem->load($query, $limit);
	}


	/**
	 * Override __get() to handle JSON in data, returned as array.
	 *
	 * @param   string  $key
	 * @return  mixed
	 */
	public function __get($key) {
		if ($key == 'data' && $this->_data['data'] && !$this->is_aggregate()) {
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
		if ($key == 'data' && is_array($value) && !$this->is_aggregate()) {
			$value = @json_encode($value);
		}

		parent::__set($key, $value);
	}


	/**
	 * Get or set item aggregate pseudo status.
	 *
	 * @param   boolean  $aggregate
	 * @return  boolean
	 */
	public function is_aggregate($aggregate = null) {
		if (is_bool($aggregate)) {
			$this->_aggregate = $aggregate;
		}

		return $this->_aggregate;
	}

}
