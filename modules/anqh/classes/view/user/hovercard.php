<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * User hovercard.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_User_HoverCard extends View_Section {

	/**
	 * @var  array
	 */
	public $user;


	/**
	 * Create new view.
	 *
	 * @param  array  $user
	 */
	public function __construct(array $user) {
		parent::__construct();

		$this->user  = $user;
		$this->title = HTML::chars(Arr::get($this->user, 'display_name', $this->user['username']));
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		// Title
		if ($this->user['title']):
			echo HTML::chars(trim($this->user['title'])) . '<br>';
		endif;

		// Image
		if ($this->user['thumb']):
			echo '<figure>' . HTML::image($this->user['thumb'], array('width' => 160)) . '</figure>';
		endif;

		return ob_get_clean();
	}

}
