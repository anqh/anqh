<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * View_Music_Main
 *
 * @package    Music
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Music_Main extends View_Article {

	/**
	 * @var  Model_Music_Track
	 */
	public $track;


	/**
	 * Create new article.
	 *
	 * @param  Model_Music_Track  $track
	 */
	public function __construct(Model_Music_Track $track) {
		parent::__construct();

		$this->track = $track;
	}


	/**
	 * Render content.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		if ($this->track->description):
			echo BB::factory($this->track->description)->render();
		endif;

		if ($this->track->tracklist):
			echo '<h3>Tracklist</h3>';

			echo Text::auto_p(HTML::chars($this->track->tracklist));
		endif;

		return ob_get_clean();
	}

}
