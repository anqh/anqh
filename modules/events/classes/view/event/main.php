<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * View_Event_Main
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Event_Main extends View_Article {

	/**
	 * @var  Model_Event
	 */
	public $event;


	/**
	 * Create new article.
	 *
	 * @param  Model_Event  $event
	 */
	public function __construct(Model_Event $event) {
		parent::__construct();

		$this->event = $event;
	}


	/**
	 * Render content.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		if ($this->event->info):
			echo BB::factory($this->event->info)->render();
		endif;

		return ob_get_clean();
	}

}
