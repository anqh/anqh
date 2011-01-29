<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh User API controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_User_API extends Controller_API {

	/**
	 * @var  array  Fetchable fields
	 */
	public static $_fields = array(
		'id', 'username', 'homepage', 'city', 'gender', 'description',
		'logins', 'posts', 'adds', 'signature', 'avatar', 'picture', 'title',
		'dob', 'latitude', 'longitude', 'created', 'modified', 'last_login', 'url'
	);

	/**
	 * @var  array  Orderable fields
	 */
	public static $_orderable = array(
		'id', 'username', 'city', 'gender', 'dob', 'created', 'modified', 'last_login',
	);

	/**
	 * @var  array  Searchable fields
	 */
	public static $_searchable = array(
		'username', 'city', 'gender'
	);


	/**
	 * Action: search
	 */
	public function action_search() {
		$this->data['users'] = array();

		// Search term
		$term   = trim(Arr::get($_REQUEST, 'q', ''));

		// Search fields
		$search = explode(':', Arr::get($_REQUEST, 'search', 'username'));

		// Result limit
		$limit  = (int)Arr::get($_REQUEST, 'limit', 25);

		// Result order
		$order  = Arr::get($_REQUEST, 'order', 'username.asc');

		// Result fields
		$field  = explode(':', Arr::get($_REQUEST, 'field', 'id:username'));

		// Term must be at least 3 characters
		if (strlen($term) >= 3) {

			// 500 events max
			$limit = min($limit, 500);

			// Get order
			$orders = $this->_prepare_order($order, self::$_orderable);
			$orders = empty($orders) ? array('username' => 'asc') : $orders;

			// Get fields
			$fields = empty($field) ? self::$_fields : array_intersect($field, self::$_fields);
			$fields = empty($fields) ? array('id', 'username') : $fields;

			// Get search
			$searches = empty($search) ? self::$_searchable : array_intersect($search, self::$_searchable);
			$searches = empty($searches) ? array('username') : $searches;

			// Build query
			$users = Jelly::select('user')->limit($limit);
			foreach ($orders as $column => $direction) {
				$users->order_by($column, $direction);
			}
			$users->where_open();
			foreach ($searches as $search) {
				$search_term = $term;
				switch ($search) {
					case 'city':     $search = 'city_name'; break;
					case 'username': $search = 'username_clean'; $search_term = Text::clean($term); break;
				}
				$users->or_where($search, 'ILIKE', '%' . $search_term . '%');
			}
			$users->where_close();

			// Build data
			foreach ($users->execute() as $user) {
				$this->data['users'][] = $this->_prepare_user($user, $fields);
			}

		}

	}


	/**
	 * Prepare user for data array
	 *
	 * @param   Model_User  $user
	 * @param   array       $fields
	 * @return  array
	 */
	protected function _prepare_user(Model_User $user, array $fields = null) {
		$data = array();
		empty($fields) and $fields = self::$_fields;
		foreach ($fields as $field) {
			switch ($field) {

				// Raw value
				case 'id':
				case 'username':
				case 'homepage':
				case 'gender':
				case 'description':
				case 'logins':
				case 'posts':
				case 'adds':
				case 'signature':
				case 'title':
				case 'dob':
				case 'latitude':
				case 'longitude':
				case 'created':
				case 'modified':
				case 'last_login':
					$data[$field] = $user->$field;
					break;

				// Custom value
				case 'city':
					$data[$field] = $user->city->id ? $user->city->name : $user->city_name;
					break;

				case 'avatar':
					$data[$field] = $user->avatar ? URL::site($user->avatar, true) : URL::site('avatar/unknown.png');
					break;

				case 'picture':
					if ($user->default_image->id) {
						$data[$field] = $user->default_image->get_url();
					} else if (Validate::url($user->picture)) {
						$data[$field] = URL::site($user->picture, true);
					} else {
						$data[$field] = null;
					}
					break;

				case 'url':
					$data[$field] = URL::site(URL::user($user), true);
					break;

			}
		}

		return $data;
	}

}
