<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Events API controller
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Events_API extends Controller_API {

	/**
	 * @var  array  Fetchable fields
	 */
	public static $_fields = array(
		'id', 'name', 'homepage', 'stamp_begin', 'stamp_end', 'venue', 'city',
		'country', 'dj', 'info', 'age', 'price', 'price2', 'created', 'modified',
		'flyer_front', 'flyer_front_thumb', 'flyer_front_icon',
		'flyer_back', 'flyer_back_thumb', 'flyer_back_icon',
		'favorite_count', 'music', 'url'
	);

	/**
	 * @var  array  Orderable fields
	 */
	public static $_orderable = array(
		'id', 'name', 'stamp_begin', 'stamp_end', 'venue', 'city', 'country', 'age',
		'price', 'created', 'modified', 'favorite_count'
	);

	/**
	 * @var  array  Searchable fields
	 */
	public static $_searchable = array(
		'name', 'venue', 'city', 'dj'
	);


	/**
	 * Action: browse
	 */
	public function action_browse() {
		$this->data['events'] = array();

		// Get start stamp
		$from = Arr::get($_REQUEST, 'from', 'today');
		$from = Valid::numeric($from) ? (int)$from : strtotime($from);

		// Get order
		$order = Arr::get($_REQUEST, 'order') == 'desc' ? 'desc' : 'asc';

		// Get limit, time span or 500 events max
		$limit = Arr::get($_REQUEST, 'limit', '1w');
		if (Valid::numeric($limit)) {

			// Limit given as event count
			$to    = false;
			$limit = max(min((int)$limit, 500), 1);

		} else if ($span = Date::parse_span($limit)) {

			// Limit given as timespan
			$to    = strtotime(($order == 'asc' ? '+' : '-') . $span, $from);
			$limit = 500;

		} else {

			// No limit given
			$to    = false;
			$limit = 500;

		}

		// Get fields
		$field  = explode(':', Arr::get($_REQUEST, 'field', 'id:name:city'));
		$fields = (empty($field) || $field[0] = 'all') ? self::$_fields : array_intersect($field, self::$_fields);
		$fields = empty($fields) ? array('id', 'name') : $fields;

		// Build query
		$event  = new Model_Event();
		$events = DB::select_array($event->fields())->order_by('stamp_begin', $order);
		if ($order == 'asc') {

			// Upcoming events
			if ($to) {
				$events->where('stamp_begin', 'BETWEEN', array($from, $to));
			} else {
				$events->where('stamp_begin', '>=', $from);
			}

		} else {

			// Past events
			if ($to) {
				$events->where('stamp_begin', 'BETWEEN', array($to, $from));
			} else {
				$events->where('stamp_begin', '<=', $from);
			}

		}

		// Build data
		foreach ($event->load($events, $limit) as $event) {
			$this->data['events'][] = $this->_prepare_event($event, $fields);
		}

	}


	/**
	 * Action: event
	 */
	public function action_event() {
		$event_id = Arr::get($_REQUEST, 'id');

		// Load event
		$event = new Model_Event($event_id);
		if ($event->loaded()) {
			$this->data['events'] = array($this->_prepare_event($event));
		} else {
			$this->data['events'] = array();
		}

	}


	/**
	 * Action: search
	 */
	public function action_search() {
		$this->data['events'] = array();

		$term   = trim(Arr::get($_REQUEST, 'q', ''));
		$search = explode(':', Arr::get($_REQUEST, 'search', 'name'));
		$limit  = (int)Arr::get($_REQUEST, 'limit', 25);
		$order  = Arr::get($_REQUEST, 'order', 'name.asc');
		$field  = explode(':', Arr::get($_REQUEST, 'field', 'id:name:city'));
		$filter = Arr::get($_REQUEST, 'filter');

		// Term must be at least 3 characters
		if (strlen($term) >= 3) {

			// 500 events max
			$limit = max(min($limit, 500), 1);

			// Get order
			$orders = $this->_prepare_order($order, self::$_orderable);
			$orders = empty($orders) ? array('name' => 'asc') : $orders;

			// Get fields
			$fields = empty($field) || $field[0] = 'all' ? self::$_fields : array_intersect($field, self::$_fields);
			$fields = empty($fields) ? array('id', 'name') : $fields;

			// Get search
			$searches = empty($search) ? self::$_searchable : array_intersect($search, self::$_searchable);
			$searches = empty($searches) ? array('name') : $searches;

			// Validate filter
			$filter = !empty($filter) && ($filter == 'upcoming' || $filter == 'past' || strpos($filter, 'date:') !== false) ? $filter : null;

			// Build query
			$event  = new Model_Event();
			$events = DB::select_array($event->fields());
			foreach ($orders as $column => $direction) {
				$events->order_by($column, $direction);
			}
			if ($filter == 'upcoming') {

				// Upcoming events
				$events->where('stamp_begin', '>=', time());

			} else if ($filter == 'past') {

				// Past events
				$events->where('stamp_begin', '<', time());

			} else {

				$filter = explode(':', $filter);
				if (count($filter) == 2 && $filter[0] == 'date') {

					// Search only between dates
					list($from, $to) = explode('-', $filter[1]);
					if ((int)$from && (int)$to) {
						$events->where('stamp_begin', 'BETWEEN', array($from, $to));
					} else if ((int)$from) {
						$events->where('stamp_begin', '>=', $from);
					} else if ((int)$to) {
						$events->and_where('stamp_begin', '<=', $to);
					}

				}
			}
			$events->where_open();
			foreach ($searches as $search) {
				if ($search == 'venue' || $search == 'city') $search .= '_name';
				$events->or_where($search, 'ILIKE', '%' . $term . '%');
			}
			$events->where_close();

			// Build data
			foreach ($event->load($events, $limit) as $event) {
				$this->data['events'][] = $this->_prepare_event($event, $fields);
			}

		}

	}


	/**
	 * Prepare event for data array
	 *
	 * @param   Model_Event  $event
	 * @param   array        $fields
	 * @return  array
	 */
	protected function _prepare_event(Model_Event $event, array $fields = null) {
		$data = array();
		empty($fields) and $fields = self::$_fields;
		foreach ($fields as $field) {
			switch ($field) {

				// Raw value
				case 'id':
				case 'name':
				case 'homepage':
				case 'stamp_begin':
				case 'stamp_end':
				case 'dj':
				case 'info':
				case 'age':
				case 'price':
				case 'price2':
				case 'created':
				case 'modified':
				case 'favorite_count':
					$data[$field] = $event->$field;
					break;

				// Custom value
				case 'venue':
					$data[$field] = ($venue = $event->venue()) ? $venue->name : $event->venue_name;
			    break;

				case 'city':
					$data[$field] = ($city = $event->city()) ? $city->name : $event->city_name;
			    break;

				case 'country':
					$data[$field] = ($country = $event->country()) ? $country->name : '';
			    break;

				case 'flyer_front':
				case 'flyer_back':
				case 'flyer_front_icon':
				case 'flyer_back_icon':
				case 'flyer_front_thumb':
				case 'flyer_back_thumb':
					if (strpos($field, 'icon')) {
						$column = str_replace('_icon', '', $field) . '_image_id';
						$size   = Model_Image::SIZE_ICON;
					} else if (strpos($field, '_thumb')) {
						$column = str_replace('_thumb', '', $field) . '_image_id';
						$size   = Model_Image::SIZE_THUMBNAIL;
					} else {
						$column = $field . '_image_id';
						$size   = null;
					}
					$image  = new Model_Image($event->$column);
			    $data[$field] = $image->loaded() ? $image->get_url($size) : null;
			    break;

				case 'music':
					if ($tags = $event->tags()) {
						$music = implode(', ', $tags);
					} else if (!empty($event->music)) {
						$music = $event->music;
					} else {
						$music = null;
					}
					$data[$field] = $music;
					break;

				case 'url':
					$data[$field] = URL::site(Route::model($event), true);
					break;

			}
		}

		return $data;
	}

}
