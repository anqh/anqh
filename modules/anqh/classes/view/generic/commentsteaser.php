<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * CommentsTeaser
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Generic_CommentsTeaser extends View_Section {

	/**
	 * @var  string  Section class
	 */
	public $class = 'comments';

	/**
	 * @var  integer
	 */
	public $comment_count = 0;


	/**
	 * Create new view.
	 *
	 * @param  integer  $comment_count
	 */
	public function __construct($comment_count = 0) {
		parent::__construct();

		$this->comment_count = (int)$comment_count;
		$this->title         = __('Comments');
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		if ($this->comment_count > 1):
			$text = __('There are :comments comments. Please login to read them and write your own.', array(':comments' => $this->comment_count));
		elseif ($this->comment_count > 0):
			$text = __('There is :comments comment. Please login to read it and write your own.', array(':comments' => $this->comment_count));
		else:
			$text = __('Please login to read and write comments.');
		endif;

		return (string)new View_Alert($text, null, View_Alert::INFO);
	}

}
