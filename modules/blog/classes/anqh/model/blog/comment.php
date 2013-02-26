<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Blog Comment model
 *
 * @package    Blog
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Blog_Comment extends Model_Comment {

	protected $_table_name = 'blog_comments';

	protected $_data = array(
		'id'            => null,
		'comment'       => null,
		'private'       => 0,
		'author_id'     => null,
		'user_id'       => null,
		'created'       => null,
		'blog_entry_id' => null,
	);


	/**
	 * Add new comment.
	 *
	 * @param   integer           $author_id
	 * @param   integer           $user_id
	 * @param   string            $comment
	 * @param   boolean           $private
	 * @param   Model_Blog_Entry  $blog_entry
	 * @return  Model_Blog_Comment
	 */
	public function add($author_id, $user_id = null, $comment, $private = false, $blog_entry = null) {
		$this->blog_entry_id = $blog_entry->id;

		return parent::add($author_id, $blog_entry->author_id, $comment, $private);
	}


	/**
	 * Get comment blog entry
	 *
	 * @return  Model_Blog_Entry
	 */
	public function blog_entry() {
		try {
			return $this->blog_entry_id ? new Model_Blog_Entry($this->blog_entry_id) : null;
		} catch (AutoModeler_Exception $e) {
			return null;
		}
	}

}
