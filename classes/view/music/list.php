<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Music_List
 *
 * @package    Music
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Music_List extends View_Section {

	public $class = 'span4 cut';

	/**
	 * @var  Model_Music_Track[]
	 */
	public $tracks;


	/**
	 * Create new view.
	 *
	 * @param  Model_Music_Track[]  $tracks
	 */
	public function __construct($tracks = null) {
		parent::__construct();

		$this->tracks = $tracks;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

?>

<ul class="unstyled">

	<?php foreach ($this->tracks as $track): ?>
	<li>
		<?= HTML::anchor(Route::model($track), HTML::chars($track->name), array('class' => 'hoverablee', 'title' => HTML::chars($track->name))) ?>
	</li>
	<?php endforeach; ?>

</ul>

<?php

		return ob_get_clean();
	}

}
