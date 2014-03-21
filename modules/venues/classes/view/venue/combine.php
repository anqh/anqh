<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Venue Combine form.
 *
 * @package    Venues
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Venue_Combine extends View_Section {

	/**
	 * @var  Model_Venue
	 */
	public $venue;

	/**
	 * @var  Model_Venue
	 */
	public $venue_duplicate;


	/**
	 * Create new view.
	 *
	 * @param  Model_Venue  $venue
	 * @param  Model_Venue  $venue_duplicate
	 */
	public function __construct(Model_Venue $venue, Model_Venue $venue_duplicate = null) {
		parent::__construct();

		$this->venue           = $venue;
		$this->venue_duplicate = $venue_duplicate;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		echo Form::open();

		if ($this->venue_duplicate):

			// Confirm
			$venue     = new View_Venue_Info($this->venue);
			$duplicate = new View_Venue_Info($this->venue_duplicate);

?>

<div class="col-sm-3">

	<h3><?= HTML::anchor(Route::model($this->venue_duplicate), HTML::chars($this->venue_duplicate->name)) ?></h3>
	#<?= $this->venue_duplicate->id ?>

	<?= $duplicate->content() ?>

</div>

<div class="col-sm-1">

	<h3><?= __('to') ?></h3>

</div>

<div class="col-sm-3">

	<h3><?= HTML::anchor(Route::model($this->venue), HTML::chars($this->venue->name)) ?></h3>
	#<?= $this->venue->id ?>

	<?= $venue->content() ?>

</div>

<div class="col-sm-1">

	<?= HTML::anchor(
			Route::model($this->venue, 'combine') . '/' . $this->venue_duplicate->id . '?' . Security::csrf_query(),
			__('Merge'),
			array('class' => 'btn btn-primary')
		) ?>

</div>

<?php

		else:

			// Select duplicate
			echo Form::input_wrap('venue', null, null, __('Combine to'));

?>

<script>
	head.ready('anqh', function() {

		var venues = <?= json_encode(Model_Venue::factory()->find_all_autocomplete($this->venue->id)) ?>;
		$('input[name=venue]').autocompleteVenue({
			source: venues,
			action: function(event, ui) {
				window.location = window.location + '/' + ui.item.id;
			}
		});

	});
</script>

<?php

		endif;

		echo Form::close();

		return ob_get_clean();
	}

}
