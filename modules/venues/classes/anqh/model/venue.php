<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Venue model
 *
 * @package    Venues
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Venue extends AutoModeler_ORM implements Permission_Interface {

	/**
	 * Permission to combine duplicate venues
	 */
	const PERMISSION_COMBINE = 'combine';

	protected $_table_name = 'venues';

	protected $_data = array(
		'id'                     => null,
		'name'                   => null,
		'description'            => null,
		'homepage'               => null,
		'hours'                  => null,
		'info'                   => null,
		'default_image_id'       => null,
		'event_host'             => null,

		'address'                => null,
		'zip'                    => null,
		'city_name'              => null,
		'geo_city_id'            => null,
		'geo_country_id'         => null,
		'latitude'               => null,
		'longitude'              => null,

		'foursquare_id'          => null,
		'foursquare_category_id' => null,

		'author_id'              => null,
		'created'                => null,
		'modified'               => null,
	);

	protected $_rules = array(
		'name'                   => array('not_empty', 'max_length' => array(':value', 100)),
		'description'            => array('max_length' => array(':value', 250)),
		'homepage'               => array('url'),
		'hours'                  => array('max_length' => array(':value', 250)),
		'info'                   => array('max_length' => array(':value', 512)),
		'default_image_id'       => array('digit'),
		'event_host'             => array('in_array' => array(':value', array(0, 1))),

		'address'                => array('max_length' => array(':value', 50)),
		'zip'                    => array('digit', 'length' => array(':value', 4, 5)),
		'city_name'              => array('not_empty'),
		'geo_city_id'            => array('digit'),
		'geo_country_id'         => array('digit'),
		'latitude'               => array('numeric'),
		'longitude'              => array('numeric'),

		'foursquare_id'          => array('alpha_numeric'),
		'foursquare_category_id' => array('alpha_numeric'),

		'author_id'              => array('digit'),
	);

	protected $_has_many = array(
		'images'
	);

	/**
	 * @var  array  User editable fields
	 */
	public static $editable_fields = array(
		'name', 'description', 'homepage', 'hours', 'info', 'event_host',
		'address', 'zip', 'city_name', 'geo_city_id', 'geo_country_id', 'latitude', 'longitude',
		'foursquare_id', 'foursquare_category_id',
	);


	/**
	 * Get venue city.
	 *
	 * @return  Model_Geo_City
	 */
	public function city() {
		try {
			return $this->geo_city_id ? new Model_Geo_City($this->geo_city_id) : null;
		} catch (AutoModeler_Exception $e) {
			return null;
		}
	}


	/**
	 * Find all venues sorted by city.
	 *
	 * @return  Database_Result
	 */
	public function find_all() {
		return $this->load(
			DB::select_array($this->fields())
				->order_by('city_name', 'ASC')
				->order_by('name', 'ASC'),
			null
		);
	}


	/**
	 * Find all venues in autocomplete friend array.
	 *
	 * @param   boolean  $skip
	 * @return  array
	 */
	public function find_all_autocomplete($skip = null) {
		$venues = array();

		foreach ($this->find_all() as $venue) {
			if ($skip && $skip == $venue->id) {
				continue;
			}

			$venues[] = array(
				'id'        => $venue->id,
				'name'      => $venue->name,
				'value'     => $venue->name,
				'city'      => $venue->city_name,
				'latitude'  => $venue->latitude,
				'longitude' => $venue->longitude,
				'url'       => URL::site(Route::model($venue), true),
			);
		}

		return $venues;
	}


	/**
	 * Find past events at venue.
	 *
	 * @param   integer  $limit
	 * @return  Database_Result
	 */
	public function find_events_past($limit = 25) {
		return $this->find_related(
			'event',
			DB::select_array(Model_Event::factory()->fields())
				->where('stamp_begin', '<=', strtotime('today'))
				->order_by('stamp_begin', 'DESC')
				->limit($limit)
		);
	}


	/**
	 * Find upcoming events at venue.
	 *
	 * @param   integer  $limit
	 * @return  Database_Result
	 */
	public function find_events_upcoming($limit = 25) {
		return $this->find_related(
			'event',
			DB::select_array(Model_Event::factory()->fields())
				->where('stamp_begin', '>=', strtotime('today'))
				->order_by('stamp_begin', 'DESC')
				->limit($limit)
		);
	}


	/**
	 * Find single venue by Foursquare id.
	 *
	 * @param   integer  $foursquare_id
	 * @return  Model_Venue
	 */
	public function find_by_foursquare($foursquare_id) {
		return $this->load(
			DB::select_array($this->fields())
				->where('foursquare_id', '=', (int)$foursquare_id)
		);
	}


	/**
	 * Find multiple venues by name.
	 *
	 * @param   string  $name
	 * @return  Database_Result
	 */
	public function find_by_name($name) {
		return $this->load(
			DB::select_array($this->fields())
				->where(DB::expr('LOWER(name)'), '=', strtolower(trim($name))),
			null
		);
	}


	/**
	 * Find new venues.
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
	 * Find similar sounding venues.
	 *
	 * @param   integer  $similarity  in percents
	 * @return  array
	 */
	public function find_similar($similarity = 69) {
		$venues = array();
		$sort   = array();
		$name   = mb_strtolower($this->name);

		foreach ($this->find_all() as $venue) {
			if ($this->id == $venue->id) {
				continue;
			}

			similar_text($name, mb_strtolower($venue->name), $percent);

			if ($percent > $similarity) {
				$sort[]   = $percent;
				$venues[] = array(
					'venue'      => $venue,
					'similarity' => $percent
				);
			}
		}

		array_multisort($sort, SORT_DESC, $venues);

		return $venues;

	}


	/**
	 * Find updated venues.
	 *
	 * @param   integer  $limit
	 * @return  Database_Result
	 */
	public function find_updated($limit = 20) {
		return $this->load(
			DB::select_array($this->fields())
				->where('modified', 'IS NOT', null)
				->order_by('modified', 'DESC'),
			$limit
		);
	}


	/**
	 * Load Foursquare data
	 *
	 * @return  array
	 */
	public function foursquare() {
		if ($this->foursquare_id) {

			// Use cache to avoid flooding Foursquare
			$foursquare = Anqh::cache_get('foursquare_venue_' . $this->foursquare_id);
			if (!$foursquare) {

				// Store the original request
				$request = $_REQUEST;
				$_REQUEST = array(
					'method' => 'venue',
					'vid'    => $this->foursquare_id
				);
				$response = Request::factory(Route::url('api_venues', array('action' => 'foursquare', 'format' => 'json')))
					->execute()
					->body();

				// Restore the original request
				$_REQUEST = $request;

				$foursquare = Arr::path(json_decode($response, true), 'venue.venue');

				// Cache results for 15 minutes
				Anqh::cache_set('foursquare_venue_' . $this->foursquare_id, $foursquare, 60 * 15);

			}

			return $foursquare;
		}
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
		    return (bool)$user;
		    break;

			case self::PERMISSION_COMBINE:
			case self::PERMISSION_DELETE:
			case self::PERMISSION_UPDATE:
		    return $user && $user->has_role('admin', 'venue moderator');
		    break;

			case self::PERMISSION_READ:
		    return true;
		}

		return false;
	}

}
