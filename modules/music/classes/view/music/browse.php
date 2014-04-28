<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Music Browse view.
 *
 * @package    Music
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Music_Browse extends View_Section {

	/**
	 * @var  string  Section class
	 */
	public $class = 'browse';

	/**
	 * @var  Model_Music_Track[]
	 */
	public $tracks;


	/**
	 * Create new article.
	 *
	 * @param  Model_Music_Track[]  $tracks
	 */
	public function __construct($tracks) {
		parent::__construct();

		$this->tracks = $tracks;
	}


	/**
	 * Section content.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		foreach ($this->tracks as $track):
			echo new View_Music_Track($track);
		endforeach;

		return ob_get_clean();
	}

}
