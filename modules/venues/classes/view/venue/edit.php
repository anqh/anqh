<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Venue edit form.
 *
 * @package    Venues
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
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

		echo Form::open(null, array('id' => 'form-venue', 'class' => 'row'));

?>

	<div class="col-md-8">
		<fieldset>
			<?= Form::input_wrap(
				'name',
				$this->venue->name,
				array('class' => 'input-lg'),
				__('Venue'),
				Arr::get($this->errors, 'name')
			) ?>

			<?= Form::input_wrap(
				'homepage',
				$this->venue->homepage,
				null,
				__('Homepage'),
				Arr::get($this->errors, 'homepage')
			) ?>

			<?= Form::input_wrap(
				'description',
				$this->venue->description,
				null,
				__('Short description'),
				Arr::get($this->errors, 'description')
			) ?>

			<?= Form::textarea_wrap(
				'hours',
				$this->venue->hours,
				null,
				true,
				__('Opening hours'),
				Arr::get($this->errors, 'hours')
			) ?>

			<?= Form::textarea_wrap(
				'info',
				$this->venue->info,
				null,
				true,
				__('Other information'),
				Arr::get($this->errors, 'info')
			) ?>
		</fieldset>

		<fieldset>
			<?= Form::hidden('latitude',      $this->venue->latitude,  array('data-geo' => 'lat')) ?>
			<?= Form::hidden('longitude',     $this->venue->longitude, array('data-geo' => 'lng')) ?>
			<?= Form::hidden('foursquare_id', $this->venue->foursquare_id) ?>
			<!--<?= Form::hidden('foursquare_category_id', $this->venue->foursquare_category_id) ?>-->

			<?= Form::csrf() ?>
			<?= Form::button('save', __('Save'), array('type' => 'submit', 'class' => 'btn btn-success btn-large')) ?>
			<?= $this->cancel ? HTML::anchor($this->cancel, __('Cancel'), array('class' => 'cancel')) : '' ?>
		</fieldset>
	</div>

	<div class="col-md-4">
		<fieldset id="fields-location">
			<?= Form::input_wrap(
				'city_name',
				$this->venue->city_name,
				array('data-geo' => 'locality'),
				__('City'),
				Arr::get($this->errors, 'city_name')
			) ?>

			<?= Form::input_wrap(
				'foursquare',
				null,
				null,
				'<i class="fa fa-foursquare"></i> '
					. __('Foursquare Venue') 
					. ' '
					. ($this->venue->foursquare_id 
						? ('<span class="text-success" title="' . $this->venue->foursquare_id . '">(' . __('Set') . ')</span>')
						: ('<span class="text-warning">(' . __('Not set') . ')</span>'))
			) ?>

			<?= Form::input_wrap(
				'address',
				$this->venue->address,
				null,
				__('Address'),
				Arr::get($this->errors, 'address')
			) ?>
		</fieldset>
	</div>

	<?= Form::close(); ?>

<script>
head.ready('anqh', function() {

	$('#fields-location').append('<div id="map"><?= __('Loading map..') ?></div>');

	var $city = $('input[name=city_name]');
	$city.geocomplete({
		map:              '#map',
		details:          '#form-venue',
		detailsAttribute: 'data-geo',
		location:         <?= $this->venue->latitude ? ('[ ' . $this->venue->latitude . ', ' . $this->venue->longitude . ' ]') : ("'" . ($this->venue->city_name ? $this->venue->city_name : 'Helsinki') . "'")  ?>,
		types:            [ '(cities)' ]
	});

	var
		$latitude  = $('input[name=latitude]'),
		$longitude = $('input[name=longitude]'),
		$address   = $('input[name=address]')
		
	$('input[name=foursquare]')
		.on('typeahead:selected', function(event, selection, name) {
			var map    = $city.geocomplete('map')
			  , marker = $city.geocomplete('marker');

			// Update form
			if (selection.foursquare_id) {
				$('input[name=foursquare_id]').val(selection.foursquare_id);
			} else {
				$('input[name=foursquare_id]').val('');
			}
			if (selection.latitude && selection.longitude) {
				$latitude.val(selection.latitude);
				$longitude.val(selection.longitude);
				var center = new google.maps.LatLng(selection.latitude, selection.longitude);
				map.setCenter(center);
				marker.setPosition(center);
				$address.val(selection.address);
			} else {
				$city.geocomplete('find', selection.city);
			}
			$('input[name=city_name], input[name=city]').val(selection.city);

			// Update label
//			$('#fields-venue .venue-name').text(selection.value);
//			$('#fields-venue .venue-city').text(selection.city || '');
//			toggleVenue(true);
		})
		.typeahead([
			{
				name:   'foursquare',
				remote: {
					url:      Anqh.APIURL + '/v1/venues/foursquare',
					dataType: 'jsonp',
					replace:  function(url, uriEncodedQuery) {
						return url += '?method=venues&ll=' + $latitude.val() + ',' + $longitude.val() + '&query=' + uriEncodedQuery;
					},
					filter: function(parsedResponse) {
						return parsedResponse.venues || [];
					}
				}
			}
		]);
		
/*
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
*/
});
</script>

<?php

		return ob_get_clean();
	}

}
