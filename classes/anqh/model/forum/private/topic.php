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

	protected $_table_name = 'forum_private_topics';

	protected $_data = array(
		'id'            => null,
		'forum_area_id' => null,
		'bind_id'       => null,

		'type'          => null,
		'status'        => self::STATUS_NORMAL,
		'sticky'        => false,
		'read_only'     => null,
		'votes'         => null,
		'points'        => null,

		'name'          => null,
		'old_name'      => null,
		'author_id'     => null,
		'author_name'   => null,

		'first_post_id' => null,
		'last_post_id'  => null,
		'last_posted'   => null,
		'last_poster'   => null,
		'read_count'    => null,
		'post_count'    => null,

		'recipient_count' => null,
	);

	protected $_has_many = array(
		'posts', 'recipients'
	);


	/**
	 * Find active topics
	 *
	 * @param   integer  $limit
	 * @return  null
	 *
	 * @todo  Remove
	 */
	public function find_active($limit = 10) {
		return null;
	}


	/**
	 * Find latest topics
	 *
	 * @param   integer     $limit
	 * @param   string      $type  GROUP, PERSONAL or null for both
	 * @param   Model_User  $user  Recipient
	 * @return  Model_Forum_Private_Topic[]
	 *
	 * @throws  InvalidArgumentException  if user missing
	 */
	public function find_by_latest_post($limit = 10, $type = null, Model_User $user = null) {
		if (!$user) {
			throw new InvalidArgumentException('User required.');
		}

		return $this->load(
			DB::select_array($this->fields())
				->join('forum_private_topic', 'INNER')
				->on('forum_private_topic.id', '=', 'forum_private_recipients.forum_topic_id')
				->where('user_id', '=', $user->id)
				->order_by('last_post_id', 'DESC'),
			$limit
		);
	}


	/**
	 * Find new topics
	 *
	 * @param   integer  $limit
	 * @return  null
	 *
	 * @todo  Remove
	 */
	public function find_new($limit = 10) {
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
				foreach ($this->recipients() as $recipient) {
					$recipients[$this->id][$recipient] = Arr::get(Model_User::find_user_light($recipient), 'username');
				}
				natcasesort($recipients[$this->id]);
			}

			return $recipients[$this->id];
		}

		return array();
	}


	/**
	 * Get private message count
	 *
	 * @param   Model_User  $user  Recipient
	 * @param   string      $type  GROUP, PERSONAL or null for both
	 * @return  integer
	 */
	public function get_count(Model_User $user, $type = null) {
		return (int)DB::select(array(DB::expr('COUNT(id)'), 'message_count'))
			->from('forum_private_recipients')
			->where('user_id', '=', $user->id)
			->execute($this->_db)
			->get('message_count');
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
				return $user && in_array($user->id, $this->recipients());

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
	 * Find topic posts by page
	 *
	 * @param   Pagination  $pagination
	 * @return  Model_Forum_Post[]
	 */
	public function posts(Pagination $pagination = null) {
		$post = Model_Forum_Private_Post::factory();

		$query = DB::select_array($post->fields())
			->where('forum_topic_id', '=', $this->id);

		if ($pagination) {
			return $post->load($query->offset($pagination->offset), $pagination->items_per_page);
		} else {
			return $post->load($query, null);
		}
	}


	/**
	 * Get message recipient ids
	 *
	 * @return  array
	 */
	public function recipients() {
		static $recipients;

		if (!is_array($recipients)) {
			$recipients = array();
		}

		if ($this->loaded()) {
			if (!isset($recipients[$this->id])) {
				$recipients[$this->id] = array();
				$users = DB::select('user_id')
					->from('forum_private_recipients')
					->where('forum_topic_id', '=', $this->id)
					->execute();
				foreach ($users as $user) {
					$recipients[$this->id][(int)$user['user_id']] = (int)$user['user_id'];
				}
			}

			return $recipients[$this->id];
		}

		return array();
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
				$recipient = Model_Forum_Private_Recipient::factory();
				$recipient->set_fields(array(
					'forum_topic_id'  => $this->id,
					'forum_area_id'   => $this->forum_area_id,
					'user_id'         => $recipient_id,
					'unread'          => $this->post_count ? $this->post_count : 1
				));
				$recipient->save();
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
