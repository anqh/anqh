<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Venue_Foursquare
 *
 * @package    Venues
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Venue_Foursquare extends View_Section {

	/**
	 * @var  Model_Venue
	 */
	public $venue;


	/**
	 * Create new view.
	 *
	 * @param  Model_Venue  $venue
	 */
	public function __construct($venue) {
		parent::__construct();

		$this->title = __('Foursquare');
		$this->id    = 'foursquare';
		$this->venue = $venue;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		$foursquare = $this->venue->foursquare();

		if (!$foursquare):
			echo new View_Alert(__('This venue has not been linked to Foursquare yet.'), null, View_Alert::INFO);
		else:

			// Homepage
			echo HTML::anchor(
				Arr::path($foursquare, 'short_url'),
				HTML::image(Arr::path($foursquare, 'primarycategory.iconurl'), array(
					'alt'   => HTML::chars(Arr::path($foursquare, 'primarycategory.nodename')),
					'title' => HTML::chars(Arr::path($foursquare, 'primarycategory.nodename'))
				)) . ' ' . HTML::chars(Arr::path($foursquare, 'primarycategory.nodename'))
			), '<br />';

			// Mayor
			if ($mayor = Arr::path($foursquare, 'stats.mayor.user')):
				echo __('Mayor: :mayor, :city', array(
					':mayor' => HTML::anchor(
						'http://foursquare.com/user/' . Arr::get($mayor, 'id'),
						HTML::chars(Arr::get($mayor, 'firstname')) . ' ' . HTML::chars(Arr::get($mayor, 'lastname'))),
					':city'  => HTML::chars($mayor['homecity'])
				)), '<br />';
			endif;

			// Checkins
			echo __('Check-ins: :checkins', array(':checkins' => '<var>' . Arr::path($foursquare, 'stats.checkins') . '</var>')), '<br />';

			// Here now
			echo __('Here now: :herenow', array(':herenow' => '<var>' . Arr::path($foursquare, 'stats.herenow') . '</var>')), '<br />';

			// Tips
			if ($tips = Arr::path($foursquare, 'tips')):
				echo '<h5>', __('Tips (:tips)', array(':tips' => '<var>' . count($tips) . '</var>')), '</h5><dl>';
				foreach (array_slice($tips, 0, 5) as $tip):
					echo '<dt>', HTML::anchor(
						'http://foursquare.com/user/' . Arr::path($tip, 'user.id'),
						HTML::chars(Arr::path($tip, 'user.firstname')) . ' ' . HTML::chars(Arr::path($tip, 'user.lastname'))
					), ', ', HTML::chars(Arr::path($tip, 'user.homecity')), ':</dt>';
					echo '<dd>', Text::auto_p(HTML::chars(Arr::path($tip, 'text'))), '</dd>';
				endforeach;
				echo '</dl>';
			endif;

		endif;


		// Admin controls
		if (Permission::has($this->venue, Model_Venue::PERMISSION_UPDATE, self::$_user)):
			echo HTML::anchor('#map', __('Link to Foursquare'), array('class' => 'action', 'id' => 'link-foursquare'));
			echo $this->form();
		endif;

		return ob_get_clean();
	}


	/**
	 * Get edit form.
	 *
	 * @return  string
	 */
	public function form() {
		ob_start();

		// Map options
		$options = array(
			'marker'     => HTML::chars($this->venue->name),
			'infowindow' => HTML::chars($this->venue->address) . '<br />' . HTML::chars($this->venue->city_name),
			'lat'        => $this->venue->latitude,
			'long'       => $this->venue->longitude
		);

		// Form
		Form::$bootsrap = true;
		echo Form::open(Route::url('venue', array('id' => Route::model_id($this->venue), 'action' => 'foursquare')), array('id' => 'form-foursquare-link', 'style' => 'display: none'));

?>

		<fieldset>
			<?= Form::control_group(
				Form::input('city_name', $this->venue->city_name, array('class' => 'input-large')),
				array('city_name' => __('City'))) ?>
			<?= Form::control_group(
				Form::input('name', $this->venue->name, array('class' => 'input-large', 'placeholder' => __('Fill city first'))),
				array('name' => __('Name'))) ?>
			<?= Form::control_group(
				Form::input('address', $this->venue->address, array('class' => 'input-large', 'placeholder' => __('Fill venue first'))),
				array('address' => __('Address'))) ?>
			<?= Form::control_group(
				Form::input('foursquare_id', $this->venue->foursquare_id, array('class' => 'input', 'readonly' => 'readonly')),
				array('foursquare_id' => __('Foursquare ID'))) ?>
			<?= Form::control_group(
				Form::input('foursquare_category_id', $this->venue->foursquare_category_id, array('class' => 'input', 'readonly' => 'readonly')),
				array('foursquare_id' => __('Foursquare ID'))) ?>
		</fieldset>
		<fieldset>
			<?= Form::hidden('city_id', $this->venue->city_id) ?>
			<?= Form::hidden('latitude', Arr::pick($this->venue->latitude, $this->venue->city ? $this->venue->city->latitude : 0)) ?>
			<?= Form::hidden('longitude', Arr::pick($this->venue->longitude, $this->venue->city ? $this->venue->city->longitude : 0)) ?>

			<?= Form::csrf() ?>
			<?= Form::button('save', __('Link'), array('type' => 'submit', 'class' => 'btn btn-success')) ?>
		</fieldset>

		<?= Form::close(); ?>

<script>
head.ready('anqh', function hookFoursquare() {
	$("#link-foursquare").on('click', function linkFoursquare(event) {
		event.preventDefault();

		$(this).hide();
		$('#foursquare .alert').hide();

		$('#form-foursquare-link').show('fast');
		$('#map').show('fast', function toggleMap() {
			$('#map').googleMap(<?= json_encode($options) ?>);
		});
	});

	$('[name=city_name]:input').autocompleteGeo();

	$('[name=name]:input').foursquareVenue({
		venueId:         'foursquare_id',
		categoryId:      'foursquare_category_id',
		latitudeSearch:  'latitude',
		longitudeSearch: 'longitude'
	});
});
</script>

<?php

		return ob_get_clean();
	}

}
