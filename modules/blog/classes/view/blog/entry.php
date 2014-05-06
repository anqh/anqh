<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Blog entry
 *
 * @package    Blog
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Blog_Entry extends View_Section {

	/**
	 * @var  Model_Blog_Entry
	 */
	public $blog_entry;

	/**
	 * @var  string  Section class
	 */
	public $class = 'blog-entry';


	/**
	 * Create new view.
	 *
	 * @param  Model_Blog_Entry  $blog_entry
	 * @param  boolean           $show_title
	 */
	public function __construct(Model_Blog_Entry $blog_entry, $show_title = false) {
		parent::__construct();

		$this->blog_entry = $blog_entry;

		if ($show_title) {
			$author = $blog_entry->author();
//			$this->avatar   = HTML::avatar($author['avatar'], $author['username']);
			$this->title    = HTML::anchor(Route::model($blog_entry), HTML::chars($blog_entry->name));
			$this->subtitle = __('By :user, :date', array(
				':user' => HTML::user($author),
				':date' => date('l ', $blog_entry->created) . Date::format(Date::DMY_SHORT, $blog_entry->created)
			));

			if (Permission::has($blog_entry, Model_Blog_Entry::PERMISSION_COMMENTS, Visitor::$user)) {
				$this->subtitle .= ' | ' . HTML::anchor(Route::model($blog_entry), __('Comments') . ' (' . (int)$blog_entry->comment_count . ')');
			}
		}

	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		return BB::factory($this->blog_entry->content)->render();
	}

}
