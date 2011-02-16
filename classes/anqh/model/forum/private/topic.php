<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Forum Private Topic model
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Forum_Private_Topic extends Model_Forum_Topic {

	/** Group topic, many recipients */
	const GROUP = 'group';

	/** Personal 1-on-1 topic */
	const PERSONAL = 'personal';


	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta->fields(array(
			'first_post' => new Jelly_Field_BelongsTo(array(
				'column'  => 'first_post_id',
				'foreign' => 'forum_private_post',
			)),
			'last_post' => new Jelly_Field_BelongsTo(array(
				'column'  => 'last_post_id',
				'foreign' => 'forum_private_post',
			)),
			'posts' => new Jelly_Field_HasMany(array(
				'foreign' => 'forum_private_post.forum_topic_id',
			)),

			'recipient_count' => new Jelly_Field_Integer,
			'recipients' => new Jelly_Field_HasMany(array(
				'foreign' => 'forum_private_recipient.forum_topic_id'
			)),
		));

		parent::initialize($meta);
	}


	/**
	 * Find active topics
	 *
	 * @static
	 * @param   integer  $limit
	 * @return  Jelly_Collection
	 *
	 * @todo  Remove
	 */
	public static function find_active($limit = 10) {
		return null;
	}


	/**
	 * Find latest topics
	 *
	 * @static
	 * @param   integer     $limit
	 * @param   string      $type  GROUP, PERSONAL or null for both
	 * @param   Model_User  $user  Recipient
	 * @return  Jelly_Collection
	 *
	 * @throws  InvalidArgumentException  if user missing
	 */
	public static function find_by_latest_post($limit = 10, $type = null, Model_User $user = null) {
		if (!$user) {
			throw new InvalidArgumentException('User required.');
		}

		$topics = Jelly::query('forum_private_topic')
			->join('forum_private_recipients', 'INNER')
			->on('forum_private_topic.:primary_key', '=', 'forum_private_recipients.topic:foreign_key')
			->where('user_id', '=', $user->id)
			->order_by('last_post_id', 'DESC')
			->limit($limit);

		return $topics->select();
	}


	/**
	 * Find new topics
	 *
	 * @static
	 * @param   integer  $limit
	 * @return  Jelly_Collection
	 *
	 * @todo  Remove
	 */
	public static function find_new($limit = 10) {
		return null;
	}


	/**
	 * Get message recipient usernames
	 *
	 * @return  array
	 */
	public function find_recipient_names() {
		static $recipients;

		if (!is_array($recipients)) {
			$recipients = array();
		}

		if ($this->loaded()) {
			if (!isset($recipients[$this->id])) {
				$recipients[$this->id] = array();
				foreach ($this->find_recipients() as $recipient) {
					$recipients[$this->id][$recipient] = Arr::get(Model_User::find_user_light($recipient), 'username');
				}
				natcasesort($recipients[$this->id]);
			}

			return $recipients[$this->id];
		}

		return array();
	}


	/**
	 * Get message recipient ids
	 *
	 * @return  array
	 */
	public function find_recipients() {
		static $recipients;

		if (!is_array($recipients)) {
			$recipients = array();
		}

		if ($this->loaded()) {
			if (!isset($recipients[$this->id])) {
				$recipients[$this->id] = array();
				$users = DB::select('user_id')->from('forum_private_recipients')->where('forum_topic_id', '=', $this->id)->execute();
				foreach ($users as $user) {
					$recipients[$this->id][(int)$user['user_id']] = (int)$user['user_id'];
				}
			}

			return $recipients[$this->id];
		}

		return array();
	}


	/**
	 * Get private message count
	 *
	 * @static
	 * @param   Model_User  $user  Recipient
	 * @param   string      $type  GROUP, PERSONAL or null for both
	 * @return  integer
	 */
	public static function get_count(Model_User $user, $type = null) {
		$topics = Jelly::query('forum_private_recipient')
			->where('user_id', '=', $user->id);

		return $topics->count();
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

			case self::PERMISSION_DELETE:
				return $user && $user->has_role('admin');

			case self::PERMISSION_POST:
		    return ($this->status != self::STATUS_LOCKED || $user->has_role('admin')) && $this->has_permission(self::PERMISSION_READ, $user);

			case self::PERMISSION_READ:
				return $user && $this->get('recipients')->where('user_id', '=', $user->id)->count();

			case self::PERMISSION_UPDATE:
				return $this->has_permission(self::PERMISSION_READ, $user) && parent::has_permission($permission, $user);

		}

	  return false;
	}


	/**
	 * Mark private message as read
	 *
	 * @param   Model_User  $user
	 * @return  boolean
	 */
	public function mark_as_read(Model_User $user) {
		return (int)DB::update('forum_private_recipients')
			->set(array('unread' => 0))
			->where('forum_topic_id', '=', $this->id)
			->and_where('user_id', '=', $user->id)
			->and_where('unread', '>', 0)
			->execute();
	}


	/**
	 * Notify message recipients about new post
	 *
	 * @param   Model_User  $user  New post author
	 * @return  integer  Updated count
	 */
	public function notify_recipients(Model_User $user) {
		return DB::update('forum_private_recipients')
			->set(array('unread' => DB::expr('unread + 1')))
			->where('forum_topic_id', '=', $this->id)
			->and_where('unread', '>=', 0)
			->and_where('user_id', '<>', $user->id)
			->execute();
	}


	/**
	 * Refresh topic foreign values
	 *
	 * @param   boolean  $save
	 */
	public function refresh($save = true) {
		if (!$this->loaded()) {
			return false;
		}

		// First post
		$first_post = Jelly::query('forum_private_post')
			->where('forum_topic_id', '=', $this->id)->order_by('id', 'ASC')
			->limit(1)
			->select();
		$this->first_post = $first_post;

		// Last post
		$last_post = Jelly::query('forum_private_post')
			->where('forum_topic_id', '=', $this->id)
			->order_by('id', 'DESC')
			->limit(1)
			->select();
		$this->last_post = $last_post;
		$this->last_posted = $last_post->created;
		$this->last_poster = $last_post->author_name;

		$this->post_count = count($this->posts);

		if ($save) {
			$this->save();
		}

		return true;
	}


	/**
	 * Set message recipients
	 *
	 * @param   array  $recipients
	 * @return  count  New recipient count
	 */
	public function set_recipients(array $recipients) {

		// Removed recipients
		$old_recipients = array_diff_key($this->find_recipient_names(), $recipients);
		if ($old_recipients) {
			DB::delete('forum_private_recipients')
				->where('forum_topic_id', '=', $this->id)
				->and_where('user_id', 'IN', array_keys($old_recipients))
				->execute();
		}

		// New recipients
		$new_recipients = array_diff_key($recipients, $this->find_recipient_names());
		if ($new_recipients) {
			foreach ($new_recipients as $recipient_id => $recipient_name) {
				Model_Forum_Private_Recipient::factory()->set(array(
					'topic'  => $this,
					'area'   => $this->area,
					'user'   => $recipient_id,
					'unread' => $this->post_count ? $this->post_count : 1
				))->save();
			}
		}

		$this->recipient_count = count($recipients);
		$this->save();
	}


	/**
	 * Empty slug to hide topic in URLs
	 *
	 * @return  string
	 */
	public function slug() {
		return '';
	}

}
