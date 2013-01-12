<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Music track model.
 *
 * @package    Music
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Music_Track extends AutoModeler_ORM implements Permission_Interface {

	/** Music type mix */
	const TYPE_MIX   = 1;

	/** Music type track */
	const TYPE_TRACK = 2;

	protected $_table_name = 'music_tracks';

	protected $_data = array(
		'id'             => null,
		'author_id'      => null,
		'name'           => null,
		'description'    => null,
		'tracklist'      => null,
		'created'        => null,
		'music'          => null,
		'forum_topic_id' => null,
		'size_time'      => null,
		'url'            => null,
		'type'           => null,
		'listen_count'   => null,
		'cover'          => null,
	);

	protected $_rules = array(
		'name'           => array('not_empty', 'max_length' => array(':value', 250)),
		'description'    => array('max_length' => array(':value', 4096)),
		'tracklist'      => array('max_length' => array(':value', 1024)),
		'url'            => array('not_empty', 'url'),
		'cover'          => array('url'),
		'forum_topic_id' => array('digit'),
		'type'           => array('not_empty', 'in_array' => array(':value', array(self::TYPE_MIX, self::TYPE_TRACK))),
	);

	protected $_has_many = array(
		'tags'
	);

	/**
	 * @var  array  User editable fields
	 */
	public static $editable_fields = array(
		'name', 'description', 'tracklist', 'music', 'size_time', 'url', 'cover'
	);


	public function __get($key) {
		if ($key === 'size_time') {
			$seconds = parent::__get($key);

			return $seconds ? Num::minutes($seconds) : null;
		}

		return parent::__get($key);
	}


	/**
	 * Override __set() to handle time.
	 *
	 * @param   string  $key
	 * @param   mixed   $value
	 */
	public function __set($key, $value) {
		if ($key === 'size_time' && !is_numeric($value)) {
			$value = Num::seconds($value);
		}

		parent::__set($key, $value);
	}


	/**
	 * Add new forum topic for music.
	 *
	 * @return  boolean
	 */
	public function add_forum_topic() {
		$forum_areas = Kohana::$config->load('music.forum_areas');
		if ($forum_areas && $forum_areas[$this->type]) {
			$forum_area = Model_Forum_Area::factory($forum_areas[$this->type]);
			if ($forum_area->loaded()) {

				// Generate post
				$content = '';

				// Cover
				if (Valid::url($this->cover)) {
					$content .= '[img]' . $this->cover . "[/img]\n\n";
				}

				// Description
				$content .= $this->description . "\n\n";

				if ($this->type == Model_Music_Track::TYPE_MIX && $this->tracklist) {
					$content .= '[b]' . __('Tracklist') . "[/b]\n";
					$content .= $this->tracklist . "\n\n";
				}

				// Tags
				if ($tags = $this->tags()) {
					$content .= '[i]' . implode(', ', $tags) . "[/i]\n\n";
				} else if (!empty($this->music)) {
					$content .= '[i]' . $this->music . "[/i]\n\n";
				}

				// Links
				$content .= '[url=' . URL::site(Route::model($this), true) . ']' . __('Show details') . '[/url] - ';
				$content .= '[url=' . URL::site(Route::model($this, 'listen'), true) . ']' . __('Listen') . '[/url]';

				// Create topic
				$author = $this->author();
				$forum_topic = new Model_Forum_Topic();
				$forum_topic->author_id     = $author['id'];
				$forum_topic->author_name   = $author['username'];
				$forum_topic->name          = $this->name;
				$forum_topic->forum_area_id = $forum_area->id;
				$forum_topic->created       = time();

				// Create post
				$forum_post = new Model_Forum_Post();
				$forum_post->post          = $content;
				$forum_post->forum_area_id = $forum_area->id;
				$forum_post->author_id     = $author['id'];
				$forum_post->author_name   = $author['username'];
				$forum_post->author_ip     = Request::$client_ip;
				$forum_post->author_host   = Request::host_name();
				$forum_post->created       = time();

				// Save
				try {
					$forum_post->is_valid();
					$forum_topic->is_valid();

					$forum_topic->save();

					$forum_post->forum_topic_id = $forum_topic->id;
					$forum_post->save();

					$forum_topic->first_post_id = $forum_topic->last_post_id = $forum_post->id;
					$forum_topic->last_poster   = $author['username'];
					$forum_topic->last_posted   = time();
					$forum_topic->post_count    = 1;
					$forum_topic->save();

					$forum_area->last_topic_id = $forum_topic->id;
					$forum_area->post_count++;
					$forum_area->topic_count++;
					$forum_area->save();

					$this->forum_topic_id = $forum_topic->id;
					$this->save();
				} catch (Validation_Exception $e) {
					return false;
				}

				return true;
			}
		}

		return false;
	}


	/**
	 * Get music count by type.
	 *
	 * @param   integer  $type
	 * @param   string   $tag
	 * @return  integer
	 */
	public function count_by_type($type, $tag = null) {

		// Validate type
		if ($type !== self::TYPE_TRACK && $type !== self::TYPE_MIX) {
			$type = self::TYPE_MIX;
		}

		$query = DB::select(array(DB::expr('COUNT(id)'), 'tracks'))
			->from($this->_table_name)
			->where('type', '=', $type);

		if ($tag) {
			return (int)$query
				->and_where('music', 'LIKE', '%' . $tag . '%')
				->execute($this->_db)
				->get('tracks');
		} else {
			return (int)$query
				->execute($this->_db)
				->get('tracks');
		}
	}


	/**
	 * Browse music.
	 *
	 * @param   integer  $type
	 * @param   string   $tag
	 * @param   integer  $limit
	 * @param   integer  $offset
	 * @param   string   $order_by
	 * @param   string   $order
	 * @return  Model_Music_Track[]
	 */
	public function find_by_type($type, $tag = null, $limit = 10, $offset = 0, $order_by = 'id', $order = 'DESC') {

		// Validate type
		if ($type !== self::TYPE_TRACK && $type !== self::TYPE_MIX) {
			$type = self::TYPE_MIX;
		}

		// Validate order
		if (!in_array($order_by, array('id', 'name', 'size_time', 'listen_count'))) {
			$order_by = 'id';
		}

		// Build query
		$query = DB::select_array($this->fields())
			->where('type', '=', $type)
			->order_by($order_by, $order);

		if ($tag) {
			$query = $query->and_where('music', 'LIKE', '%' . $tag . '%');
		}

		if ($limit || $offset) {
			return $this->load($query->offset($offset), $limit);
		} else {
			return $this->load($query, 0);
		}
	}


	/**
	 * Find latest musics.
	 *
	 * @param   integer  $type
	 * @param   integer  $limit
	 * @return  Model_Music_Track[]
	 */
	public function find_new($type, $limit = 10) {
		return $this->load(
			DB::select_array($this->fields())
				->where('type', '=', $type)
				->order_by('id', 'DESC'),
			$limit
		);
	}


	/**
	 * Find weekly top musics.
	 *
	 * @param   integer  $type
	 * @param   integer  $limit
	 * @return  array    this, last
	 */
	public function find_top_weekly($type, $limit = 10) {
		$this_week = (array)DB::select('music_track_id')
			->from('music_weeks')
			->where('type', '=', $type)
			->and_where('created', '>', strtotime('-1 week', strtotime('today')))
			->order_by(DB::expr('SUM(listen_count)'), 'DESC')
			->limit($limit)
			->group_by('music_track_id')
			->execute()
			->as_array();

		if ($this_week) {
			$last_week = (array)DB::select('music_track_id')
				->from('music_weeks')
				->where('type', '=', $type)
				->and_where('created', 'BETWEEN', array(strtotime('-2 weeks', strtotime('today')), strtotime('-1 week', strtotime('today'))))
				->order_by(DB::expr('SUM(listen_count)'), 'DESC')
				->group_by('music_track_id')
				->execute()
				->as_array();

			return array('this' => $this_week, 'last' => $last_week);
		} else {
			return array('this' => array(), 'last' => array());
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

			case self::PERMISSION_READ:
		    return true;

			case self::PERMISSION_CREATE:
		    return (bool)$user;

			case self::PERMISSION_DELETE:
			case self::PERMISSION_UPDATE:
				return $user && ($this->author_id == $user->id || $user->has_role('admin'));

		}

		return false;
	}


	/**
	 * Get listen URL and update count.
	 *
	 * @param  Model_User  $user
	 * @param  string      $ip
	 */
	public function listen($user, $ip) {

		// Get today's listen counts
		$today   = strtotime('today');
		$listens = (array)DB::select('ip')
			->from('music_weeks')
			->where('music_track_id', '=', $this->id)
			->and_where('created', '=', $today)
			->limit(1)
			->execute()
			->as_array(null, 'ip');

		// Count weekly charts only authenticated users
		if ($user) {
			$ips = $listens ? explode(',', $listens[0]) : array();

			// Count only once per day per ip
			if ($listens && !in_array($ip, $ips)) {
				$ips[] = $ip;

				DB::update('music_weeks')
					->set(array(
						'listen_count' => DB::expr('listen_count + 1'),
						'ip'           => implode(',', $ips)
					))
					->where('music_track_id', '=', $this->id)
					->and_where('created', '=', $today)
					->execute();
			} else if (!$listens) {
				DB::insert('music_weeks')
					->columns(array('music_track_id', 'created', 'listen_count', 'ip', 'type'))
					->values(array($this->id, $today, 1, $ip, $this->type))
					->execute();
			}
		}

		$this->listen_count++;
		$this->save();
	}


	/**
	 * Set track tags.
	 *
	 * @param   array  $tags
	 * @return  Model_Music_Track
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

}
