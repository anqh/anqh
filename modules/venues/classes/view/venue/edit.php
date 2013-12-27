<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Venue_Edit
 *
 * @package    Venues
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Venue_Edit extends View_Section {

	/**
	 * @var  string  URL for cancel action
	 */
	public $cancel;

	/**
	 * @var  array
	 */
	public $errors;

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

		$this->venue = $venue;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		echo Form::open(null, array('id' => 'form-venue', 'class' => 'row-fluid'));

?>

	<div class="span8">
		<fieldset>
			<?= Form::control_group(
				Form::input('name', $this->venue->name, array('class' => 'input-block-level')),
				array('name' => __('Venue')),
				Arr::get($this->errors, 'name')) ?>

			<?= Form::control_group(
				Form::input('homepage', $this->venue->homepage, array('class' => 'input-block-level')),
				array('homepage' => __('Homepage')),
				Arr::get($this->errors, 'homepage')) ?>

			<?= Form::control_group(
				Form::input('description', $this->venue->description, array('class' => 'input-block-level')),
				array('description' => __('Short description')),
				Arr::get($this->errors, 'description')) ?>

			<?= Form::control_group(
				Form::textarea('hours', $this->venue->hours, array('class' => 'input-block-level'), true),
				array('hours' => __('Opening hours')),
				Arr::get($this->errors, 'hours')) ?>

			<?= Form::control_group(
				Form::textarea('info', $this->venue->info, array('class' => 'input-block-level'), true),
				array('info' => __('Other information')),
				Arr::get($this->errors, 'info')) ?>
		</fieldset>

		<fieldset class="form-actions">
			<?= Form::hidden('city_id', $this->venue->city_id) ?>
			<?= Form::hidden('latitude', $this->venue->latitude) ?>
			<?= Form::hidden('longitude', $this->venue->longitude) ?>
			<!--<?= Form::hidden('foursquare_id', $this->venue->foursquare_id) ?>-->
			<!--<?= Form::hidden('foursquare_category_id', $this->venue->foursquare_category_id) ?>-->

			<?= Form::csrf() ?>
			<?= Form::button('save', __('Save'), array('type' => 'submit', 'class' => 'btn btn-success btn-large')) ?>
			<?= $this->cancel ? HTML::anchor($this->cancel, __('Cancel'), array('class' => 'cancel')) : '' ?>
		</fieldset>
	</div>

	<div class="span4">
		<fieldset id="fields-location">
			<?= Form::control_group(
				Form::input('city_name', $this->venue->city_name, array('class' => 'input-block-level')),
				array('city_name' => __('City')),
				Arr::get($this->errors, 'city_name')) ?>

			<?= Form::control_group(
				Form::input('address', $this->venue->address, array('class' => 'input-block-level')),
				array('address' => __('Address')),
				Arr::get($this->errors, 'address')) ?>
		</fieldset>
	</div>

	<?= Form::close(); ?>

<script>
head.ready('anqh', function() {

	$('#fields-location').append('<div id="map"><?= __('Loading map..') ?></div>');

	var loader;
	function initMap() {
		if ('maps' in google && 'Geocoder' in google.maps) {
			clearTimeout(loader);
		} else {
			loader = setTimeout(initMap, 500);

			return;
		}

		$('#map').googleMap(<?= ($this->venue->latitude ? json_encode(array('marker' => true, 'lat' => $this->venue->latitude, 'long' => $this->venue->longitude)) : '') ?>);
	}
	initMap();

	$('input[name=city_name]').autocompleteGeo();

	$('input[name=address], input[name=city_name]').on('blur', function geoCode(event) {
		var address = $("input[name=address]").val()
		 ,  city    = $("input[name=city_name]").val();

		if (city != '') {
			var geocode = city;
			if (address != '') {
				geocode = address + ", " + geocode;
			}

			Anqh.geocoder.geocode({ address: geocode }, function geoCoded(results, status) {
				if (status == google.maps.GeocoderStatus.OK && results.length) {
					Anqh.map.setCenter(results[0].geometry.location);
					$('input[name=latitude]').val(results[0].geometry.location.lat());
					$('input[name=longitude]').val(results[0].geometry.location.lng());
					var marker = new google.maps.Marker({
						position: results[0].geometry.location,
						map:      Anqh.map
					});
				}
			});
		}
	});

});
</script>

<?php

		return ob_get_clean();
	}

}
