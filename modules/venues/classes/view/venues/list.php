<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Venues_List
 *
 * @package    Venues
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Venues_List extends View_Section {

	/**
	 * @var  Model_Venue[]
	 */
	public $venues;


	/**
	 * Create new view.
	 *
	 * @param  Model_Venue[]  $venues
	 */
	public function __construct($venues) {
		parent::__construct();

		$this->venues = $venues;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

?>

<ul class="list-unstyled">

	<?php foreach ($this->venues as $venue): ?>

	<li><?= HTML::anchor(Route::model($venue), HTML::chars($venue->name)) ?></li>

	<?php endforeach; ?>

</ul>

<?php

		return ob_get_clean();
	}

}
