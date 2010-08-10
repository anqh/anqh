<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Events API controller
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Events_API extends Controller_API {

	/**
	 * @var  array  Fetchable fields
	 */
	public static $_fields = array(
		'id', 'name', 'homepage', 'stamp_begin', 'stamp_end', 'venue', 'city',
		'country', 'dj', 'info', 'age', 'price', 'price2', 'created',
		'modified', 'flyer_front', 'flyer_back', 'favorite_count'
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
	 * Action: event
	 */
	public function action_event() {
		$event_id = Arr::get($_REQUEST, 'id');

		// Load event
		$event = Jelly::select('event')->load($event_id);
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

			// Validate filter
			$filter = !empty($filter) && ($filter == 'upcoming' || $filter == 'past') ? $filter : null;

			// Build query
			$events = Jelly::select('event')->limit($limit);
			foreach ($orders as $column => $direction) {
				$events->order_by($column, $direction);
			}
			if ($filter == 'upcoming') {
				$events->where('stamp_begin', '>=', time());
			} else if ($filter == 'past') {
				$events->where('stamp_begin', '<', time());
			}
			$events->where_open();
			foreach ($searches as $search) {
				if ($search == 'venue' || $search == 'city') $search .= '_name';
				$events->or_where($search, 'ILIKE', '%' . $term . '%');
			}
			$events->where_close();

			// Build data
			foreach ($events->execute() as $event) {
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
					$data[$field] = $event->venue->id ? $event->venue->name : $event->venue_name;
			    break;
				case 'city':
					$data[$field] = $event->city->id ? $event->city->name : $event->city_name;
			    break;
				case 'country':
					$data[$field] = $event->country->id ? $event->country->name : '';
			    break;
				case 'flyer_front':
				case 'flyer_back':
			    $data[$field] = $event->$field->id ? $event->$field->get_url() : '';
			    break;

			}
		}

		return $data;
	}

}
