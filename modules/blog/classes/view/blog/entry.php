<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Blog_Entry
 *
 * @package    Blog
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Blog_Entry extends View_Section {

	/**
	 * @var  Model_Blog_Entry
	 */
	public $blog_entry;


	/**
	 * Create new view.
	 *
	 * @param  Model_Blog_Entry  $blog_entry
	 */
	public function __construct(Model_Blog_Entry $blog_entry) {
		parent::__construct();

		$this->blog_entry = $blog_entry;
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
