<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Event model
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Event extends AutoModeler_ORM implements Permission_Interface {

	/** Permission to add favorite */
	const PERMISSION_FAVORITE = 'favorite';

	protected $_table_name = 'events';

	protected $_data = array(
		'id'                   => null,
		'name'                 => null,
		'title'                => null,
		'homepage'             => null,
		'stamp_begin'          => null,
		'stamp_end'            => null,

		'venue_hidden'         => null,
		'venue_id'             => null,
		'venue_name'           => null,
		'venue_url'            => null,
		'city_name'            => null,
		'geo_city_id'          => null,
		'geo_country_id'       => null,

		'dj'                   => null,
		'info'                 => null,
		'age'                  => null,
		'price'                => null,
		'price2'               => null,
		'music'                => null,
		'flyer_front_url'      => null,
		'flyer_back_url'       => null,
		'flyer_front_image_id' => null,
		'flyer_back_image_id'  => null,
		'favorite_count'       => null,

		'modifies'             => null,
		'views'                => null,
		'created'              => null,
		'modified'             => null,
		'author_id'            => null,
	);

	protected $_rules = array(
		'name'                 => array('not_empty', 'length' => array(':value', 3, 64)),
		'homepage'             => array('url'),
		'stamp_begin'          => array('not_empty', 'digit'),
		'stamp_end'            => array('not_empty', 'digit'),

		'venue_hidden'         => array('in_array' => array(':value', array(0, 1))),
		'venue_id'             => array('digit'),
		'venue_url'            => array('url'),
		//'city_name'            => array('not_empty'),
		'geo_city_id'          => array('digit'),
		'geo_country_id'       => array('digit'),

		'age'                  => array('range' => array(':value', 0, 99)),
		'flyer_front_url'      => array('url'),
		'flyer_back_url'       => array('url'),
		'flyer_front_image_id' => array('digit'),
		'flyer_back_image_id'  => array('digit'),
		'favorite_count'       => array('digit'),
	);

	protected $_has_many = array(
		'tags', 'images', 'favorites'
	);


	/**
	 * @var  array  User editable fields
	 */
	public static $editable_fields = array(
		'name', 'homepage', 'stamp_begin', 'stamp_end', 'venue_id', 'venue_name',
		'geo_city_id', 'city_name', 'age', 'price', 'price2', 'dj', 'info',
	);

	/**
	 * @var  array  Favorites static cache
	 */
	public static $_favorites = array();

	/**
	 * Override __set() to handle datetime.
	 *
	 * @param   string  $key
	 * @param   mixed   $value
	 */
	public function __set($key, $value) {
		if (($key == 'stamp_begin' || $key == 'stamp_end') && !is_numeric($value)) {
			$value = strtotime(is_array($value) ? $value['date'] . ' ' . $value['time'] : $value);
		}

		parent::__set($key, $value);
	}


	/**
	 * Create favorite
	 *
	 * @param  Model_User  $user
	 */
	public function add_favorite(Model_User $user) {
		if ($this->loaded()	&& !$this->is_favorite($user)) {

			// Create favorite
			$favorite = Model_Favorite::factory();
			$favorite->user_id  = $user->id;
			$favorite->event_id = $this->id;
			$favorite->created  = time();

			if ($favorite->save()) {
				$this->favorite_count++;
				$this->save();

				self::$_favorites[$this->id][(int)$user->id] = (int)$user->id;

				return true;
			}
		}

		return false;
	}


	/**
	 * Add flyer to event
	 *
	 * @param   Model_Image  $image
	 * @return  boolean
	 */
	public function add_flyer(Model_Image $image) {
		if ($this->loaded()) {
			$flyer = new Model_Flyer();
			$flyer->image_id    = $image->id;
			$flyer->event_id    = $this->id;
			$flyer->name        = $this->name;
			$flyer->stamp_begin = $this->stamp_begin;

			return $flyer->save();
		}

		return false;
	}


	/**
	 * Get event city.
	 *
	 * @return  Model_Geo_City
	 */
	public function city() {
		return $this->geo_city_id ? new Model_Geo_City($this->geo_city_id) : null;
	}


	/**
	 * Get event country.
	 *
	 * @return  Model_Geo_Country
	 */
	public function country() {
		return $this->geo_country_id ? new Model_Geo_Country($this->geo_country_id) : null;
	}


	/**
	 * Delete favorite
	 *
	 * @param  Model_User  $user
	 */
	public function delete_favorite(Model_User $user) {
		if ($this->loaded() && $this->is_favorite($user)) {

			// Delete favorite
			if ((bool)DB::delete('favorites')
				->where('user_id', '=', $user->id)
				->and_where('event_id', '=', $this->id)
				->execute()) {

				$this->favorite_count--;
				$this->save();

				unset(self::$_favorites[$this->id][(int)$user->id]);

				return true;
			}
		}

		return false;
	}


	/**
	 * Get users who have added event as their favorite
	 *
	 * @return  array
	 */
	public function find_favorites() {
		if (!is_array(self::$_favorites[$this->id])) {
			self::$_favorites[$this->id] = array();
			if ($this->loaded()) {
				$users = DB::select('user_id')
					->from('favorites')
					->where('event_id', '=', $this->id)
					->execute();
				foreach ($users as $user) {
					self::$_favorites[$this->id][(int)$user['user_id']] = (int)$user['user_id'];
				}
			}
		}

		return self::$_favorites[$this->id];
	}


	/**
	 * Get user's past favorites.
	 *
	 * @param   Model_User  $user
	 * @param   integer     $limit
	 * @return  Model_Event[]
	 */
	public function find_favorites_past(Model_User $user, $limit = 5) {
		return $this->load(
			DB::select_array($this->fields())
				->join('favorites', 'INNER')
				->on('favorites.event_id', '=', 'events.id')
				->where('favorites.user_id', '=', $user->id)
				->and_where('events.stamp_begin', '<', strtotime('today'))
				->order_by('stamp_begin', 'DESC'),
			$limit
		);
	}


	/**
	 * Get user's upcoming favorites.
	 *
	 * @param   Model_User  $user
	 * @param   integer     $limit
	 * @param   string      $order
	 * @return  Model_Event[]
	 */
	public function find_favorites_upcoming(Model_User $user, $limit = 5, $order = 'ASC') {
		return $this->load(
			DB::select_array($this->fields())
				->join('favorites', 'INNER')
				->on('favorites.event_id', '=', 'events.id')
				->where('favorites.user_id', '=', $user->id)
				->and_where('events.stamp_begin', '>', strtotime('today'))
				->order_by('stamp_begin', $order),
			$limit
		);
	}


	/**
	 * Find events between given time period, return grouped by date
	 *
	 * @param   integer  $stamp_begin
	 * @param   integer  $stamp_end
	 * @param   string   $order
	 * @return  array
	 */
	public function find_grouped_between($stamp_begin, $stamp_end, $order = 'DESC') {
		$stamp_begin = (int)$stamp_begin;
		$stamp_end   = (int)$stamp_end;

		if (!$stamp_begin || !$stamp_end) {
			throw new Kohana_Exception('Start and end time must be given');
		}

		if ($stamp_begin > $stamp_end) {
			$stamp_temp  = $stamp_begin;
			$stamp_begin = $stamp_end;
			$stamp_end   = $stamp_temp;
		}

		$events = $this->load(
			DB::select_array($this->fields())
				->where('stamp_begin', 'BETWEEN', array($stamp_begin, $stamp_end))
				->order_by('stamp_begin', $order == 'ASC' ? 'ASC' : 'DESC')
				->order_by('city_name', 'ASC'),
			null
		);

		return $this->_group_by_city($events);
	}


	/**
	 * Find past events, return grouped by date.
	 *
	 * @param   integer  $limit
	 * @return  array
	 */
	public function find_grouped_past($limit = 10) {
		$events = $this->load(
			DB::select_array($this->fields())
				->where('stamp_begin', '<', strtotime('today'))
				->order_by('stamp_begin', 'DESC')
				->order_by('city_name', 'ASC'),
			$limit
		);

		return $this->_group_by_city($events);
	}


	/**
	 * Find upcoming events, return grouped by date.
	 *
	 * @param   integer  $limit
	 * @return  array
	 */
	public function find_grouped_upcoming($limit = 10) {
		$events = $this->load(
			DB::select_array($this->fields())
				->where('stamp_begin', '>=', strtotime('today'))
				->order_by('stamp_begin', 'ASC')
				->order_by('city_name', 'ASC'),
			$limit
		);

		return $this->_group_by_city($events);
	}


	/**
	 * Find hot (=favorites) events.
	 *
	 * @param   integer  $limit
	 * @return  Database_Result
	 */
	public function find_hot($limit = 20) {
		return $this->load(
			DB::select_array($this->fields())
				->where('stamp_begin', '>', strtotime('today'))
				->and_where('favorite_count', '>', 0)
				->order_by('favorite_count', 'DESC'),
			$limit
		);
	}


	/**
	 * Find last modified events.
	 *
	 * @param   integer  $limit
	 * @return  Database_Result
	 */
	public function find_modified($limit = 20) {
		return $this->load(
			DB::select_array($this->fields())
				->where('modified', 'IS NOT', null)
				->order_by('modified', 'DESC'),
			$limit
		);
	}


	/**
	 * Find new events
	 *
	 * @param   integer  $limit
	 * @return  Database_Result
	 */
	public function find_new($limit = 20) {
		return $this->load(
			DB::select_array($this->fields())
				->order_by('id', 'DESC'),
			$limit
		);
	}


	/**
	 * Find events happening now.
	 *
	 * @return  Model_Event[]
	 */
	public function find_now() {
		return $this->load(
			DB::select_array($this->fields())
				->where(DB::expr(time()), 'BETWEEN', DB::expr('stamp_begin AND stamp_end'))
				->order_by('city_name', 'ASC'),
			null
		);
	}


	/**
	 * Get front flyer image.
	 *
	 * @return  Model_Image
	 */
	public function flyer_back() {
		try {
			return $this->flyer_back_image_id ? Model_Image::factory($this->flyer_back_image_id) : null;
		} catch (AutoModeler_Exception $e) {
			return null;
		}
	}


	/**
	 * Get front flyer image.
	 *
	 * @return  Model_Image
	 */
	public function flyer_front() {
		try {
			return $this->flyer_front_image_id ? Model_Image::factory($this->flyer_front_image_id) : null;
		} catch (AutoModeler_Exception $e) {
			return null;
		}
	}


	/**
	 * Get event flyers.
	 *
	 * @return  Model_Flyer[]
	 */
	public function flyers() {
		return Model_Flyer::factory()->find_by_event($this->id);
	}


	/**
	 * Get forum topic like string
	 * [name] [date] @ [city]
	 *
	 * @return  string
	 */
	public function get_forum_topic() {
		$topic = $this->name . ' ' . Date::format(Date::DMY_SHORT, $this->stamp_begin);
	  if ($this->city_name) {
		  $topic .= ' @ ' . $this->city_name;
	  }

	  return $topic;
	}


	/**
	 * Group events by city
	 *
	 * @param   array|Database_Result  $events
	 * @return  array
	 */
	protected function _group_by_city($events) {
		$grouped = array();
		if (count($events)) {

			// Build grouped array
			foreach ($events as $event) {

				// Date
				$date = date('Y-m-d', $event->stamp_begin);
				if (!isset($grouped[$date])) {
					$grouped[$date] = array();
				}

				// City
				$city = UTF8::ucfirst(mb_strtolower($event->city_id ? $event->city()->name : $event->city_name));
				if (!isset($grouped[$date][$city])) {
					$grouped[$date][$city] = array();
				}

				$grouped[$date][$city][] = $event;
			}

			// Sort by city
			$dates = array_keys($grouped);
			foreach ($dates as $date) {
				ksort($grouped[$date]);

				// Drop empty cities to last
				if (isset($grouped[$date][''])) {
					$grouped[$date][__('Elsewhere')] = $grouped[$date][''];
					unset($grouped[$date]['']);
				}

			}

		}

		return $grouped;
	}


	/**
	 * Check permission
	 *
	 * @param   string      $permission
	 * @param   Model_User  $user
	 * @return  boolean
	 */
	public function has_permission($permission, $user) {
		switch ($permission) {
			case self::PERMISSION_CREATE:
			case self::PERMISSION_FAVORITE:
		    return (bool)$user;
		    break;

			case self::PERMISSION_DELETE:
			case self::PERMISSION_UPDATE:
		    return $user && ($this->author_id == $user->id || $user->has_role('admin', 'event moderator'));

			case self::PERMISSION_READ:
				return true;
		}

		return false;
	}


	/**
	 * Remove duplicate venues
	 *
	 * @static
	 * @param   integer  $venue_id
	 * @param   integer  $duplicate_id
	 * @return  integer  Update count
	 */
	public static function merge_venues($venue_id, $duplicate_id) {
		return DB::update('events')
			->set(array('venue_id' => $venue_id))
			->where('venue_id', '=', $duplicate_id)
			->execute();
	}


	/**
	 * Check for favorite.
	 *
	 * @param  mixed  $user
	 */
	public function is_favorite($user) {
		if (empty($user)) {
			return false;
		}

		if ($user instanceof Model_User) {
			$user = $user->id;
		} else if (is_array($user)) {
			$user = $user['id'];
		}

		$favorites = $this->find_favorites();

		return isset($favorites[(int)$user]);
	}


	/**
	 * Get event ticket price in event day's currency or Free!
	 *
	 * @return  string
	 */
	public function price() {
		if ($this->price === 0) {
			return __('Free!');
		} elseif ($this->price > 0) {
			return Num::currency($this->price, $this->stamp_begin);
		} else {
			return null;
		}
	}


	/**
	 * Set event tags.
	 *
	 * @param   array  $tags
	 * @return  Model_Event
	 */
	public function set_tags(array $tags = null) {
		$old_tags = $this->tags();
		$new_tags = (array)$tags;

		// Delete removed tags
		foreach (array_diff(array_keys($old_tags), $new_tags) as $tag_id) {
			$this->remove('tag', (int)$tag_id);
		}

		// Add new tags
		$add = array();
		foreach (array_diff($new_tags, array_keys($old_tags)) as $tag_id) {
			$tag = Model_Tag::factory((int)$tag_id);
			if ($tag && $tag->loaded()) {
				$add[] = (int)$tag->id;
			}
		}
		if ($add) {
			$this->relate('tags', $add);
		}

		// Normalized tags for old version, to be deprecated
		if ($this->music != ($music = implode(', ', $this->tags()))) {
			$this->music = $music;
			$this->save();
		}

		return $this;
	}


	/**
	 * Get event tags.
	 *
	 * @return  array
	 */
	public function tags() {
		$tags = array();
		foreach ($this->find_related('tags') as $tag) {
			$tags[$tag->id] = $tag->name;
		}

		return $tags;
	}


	/**
	 * Get event venue
	 *
	 * @return  Model_Venue
	 */
	public function venue() {
		try {
			return $this->venue_id ? Model_Venue::factory($this->venue_id) : null;
		} catch (AutoModeler_Exception $e) {
			return null;
		}
	}

}
