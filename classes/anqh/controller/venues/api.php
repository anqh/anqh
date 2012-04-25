<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Venues API controller
 *
 * @package    Venues
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Venues_API extends Controller_API {

	/**
	 * @var  array  Fetchable fields
	 */
	public static $_fields = array(
		'id', 'name', 'homepage', 'description', 'hours', 'default_image',
		'address', 'zip', 'city', 'info', 'latitude', 'longitude', 'created',
		'modified', 'foursquare_id', 'foursquare_category_id', 'url'
	);

	/**
	 * @var  array  Orderable fields
	 */
	public static $_orderable = array(
		'id', 'name', 'city', 'created', 'modified', 'favorite_count'
	);

	/**
	 * @var  array  Searchable fields
	 */
	public static $_searchable = array(
		'name', 'city', 'description', 'info'
	);


	/**
	 * Action: foursquare proxy
	 */
	public function action_foursquare() {
		$foursquare = Arr::get_once($_REQUEST, 'method');
		$url        = 'https://api.foursquare.com/v2';
		$method     = 'GET';
		$required   = $optional = array();

		switch ($foursquare) {

			// Venue info
			case 'venue':
				$url     .= '/venue.json';
				$required = array('vid');
				break;

			// Venue search
			case 'venues':
				$url     .= '/venues/search';
				$required = array('ll', 'query');
				$optional = array('limit', 'intent');
		    break;

			default:
		    return;

		}

		$params = array_filter(Arr::intersect($_REQUEST, $required));
		if (!empty($params)) {
			$params += array_filter(Arr::intersect($_REQUEST, $optional));
			try {
				if ($method == 'GET') {

					// Send GET request
					if (!empty($params)) {
						$url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params, '', '&');
					}
					$options = array();

				} else {

					// Send POST request
					$options = array(
						CURLOPT_POST           => true,
						CURLOPT_FOLLOWLOCATION => true,
					);
					if (!empty($params)) {
						$options[CURLOPT_POSTFIELDS] = http_build_query($params);
					}

				}

				/** @var  Request  $request */
				$request = Request::factory($url);
				$request
					->get_client()
					->options($options);

				$response = $request->execute();
				var_dump($response);
				if ($response->status() == 200) {
					$this->data[$foursquare] = json_decode($response->body());
				}
			} catch (Kohana_Exception $e) {
			}
		}
	}


	/**
	 * Action: search
	 */
	public function action_search() {
		$this->data['venues'] = array();

		$term   = trim(Arr::get($_REQUEST, 'q', ''));
		$search = explode(':', Arr::get($_REQUEST, 'search', 'name'));
		$limit  = (int)Arr::get($_REQUEST, 'limit', 25);
		$order  = Arr::get($_REQUEST, 'order', 'name.asc');
		$field  = explode(':', Arr::get($_REQUEST, 'field', 'id:name:city'));

		// Term must be at least 2 characters
		if (strlen($term) >= 3) {

			// 500 events max
			$limit = min($limit, 500);

			// Get order
			$orders = $this->_prepare_order($order, self::$_orderable);
			$orders = empty($orders) ? array('name' => 'asc') : $orders;

			// Get fields
			$fields = empty($field) ? self::$_fields : array_intersect($field, self::$_fields);
			$fields = empty($fields) ? array('id', 'name') : $fields;

			// Get search
			$searches = empty($search) ? self::$_searchable : array_intersect($search, self::$_searchable);
			$searches = empty($searches) ? array('name') : $searches;

			// Build query
			$venue  = new Model_Venue();
			$venues = DB::select_array($venue->fields());
			foreach ($orders as $column => $direction) {
				$venues->order_by($column, $direction);
			}
			$venues->where_open();
			foreach ($searches as $search) {
				if ($search == 'city') $search .= '_name';
				$venues->or_where($search, 'ILIKE', '%' . $term . '%');
			}
			$venues->where_close();

			// Build data
			foreach ($venue->load($venues, $limit) as $venue) {
				$this->data['venues'][] = $this->_prepare_venue($venue, $fields);
			}

		}

	}


	/**
	 * Prepare venue for data array.
	 *
	 * @param   Model_Venue  $venue
	 * @param   array        $fields
	 * @return  array
	 */
	protected function _prepare_venue(Model_Venue $venue, array $fields = null) {
		$data = array();
		empty($fields) and $fields = self::$_fields;
		foreach ($fields as $field) {
			switch ($field) {

				// Raw value
				case 'id':
				case 'name':
				case 'homepage':
				case 'description':
				case 'hours':
				case 'address':
				case 'zip':
				case 'info':
				case 'latitude':
				case 'longitude':
				case 'created':
				case 'modified':
				case 'foursquare_id':
				case 'foursquare_category_id':
					$data[$field] = $venue->$field;
					break;

				// Custom value
				case 'city':
					$data[$field] = $venue->city_name;
			    break;

				case 'default_image':
					$image = new Model_Image($venue->default_image_id);
			    $data[$field] = $image->loaded() ? $image->get_url() : '';
			    break;

				case 'url':
					$data[$field] = URL::site(Route::model($venue), true);
					break;

			}
		}

		return $data;
	}

}
