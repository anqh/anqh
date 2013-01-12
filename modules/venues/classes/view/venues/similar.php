<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Venues_Similar
 *
 * @package    Venues
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Venues_Similar extends View_Section {

	/**
	 * @var  Model_Venue
	 */
	public $venue;

	/**
	 * @var  array
	 */
	public $venues;


	/**
	 * Create new view.
	 *
	 * @param  Model_Venue  $venue
	 * @param  array        $venues
	 */
	public function __construct(Model_Venue $venue, array $venues = null) {
		parent::__construct();

		$this->title = __('Similar venues');

		$this->venue  = $venue;
		$this->venues = $venues;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		$uri = Route::model($this->venue, 'combine');
?>

<ul class="unstyled">

	<?php foreach ($this->venues as $venue): ?>

	<li><?= floor($venue['similarity']) ?>% <?= HTML::anchor($uri . '/' . $venue['venue']->id, HTML::chars($venue['venue']->name) . ', ' . HTML::chars($venue['venue']->city_name)) ?></li>

	<?php endforeach; ?>

</ul>

<?php

		return ob_get_clean();
	}

}
