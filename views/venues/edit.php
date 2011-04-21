<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Edit venue
 *
 * @package    Venues
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

echo Form::open();
?>

	<div class="grid8 first">
		<fieldset>
			<ul>
				<?php echo Form::input_wrap('name', $venue, null, __('Venue'), $errors) ?>
				<?php echo Form::input_wrap('homepage', $venue, null, __('Homepage'), $errors) ?>
				<?php echo Form::input_wrap('description', $venue, null, __('Short description'), $errors) ?>
				<?php echo Form::textarea_wrap('hours', $venue, null, true, __('Opening hours'), $errors) ?>
				<?php echo Form::textarea_wrap('info', $venue, null, true, __('Other information'), $errors) ?>
			</ul>
		</fieldset>

		<fieldset>
			<?php echo Form::hidden('city_id', $venue->city_id) ?>
			<?php echo Form::hidden('latitude', $venue->latitude) ?>
			<?php echo Form::hidden('longitude', $venue->longitude) ?>
			<?php echo Form::hidden('foursquare_id', $venue->foursquare_id) ?>
			<?php echo Form::hidden('foursquare_category_id', $venue->foursquare_category_id) ?>

			<?php echo Form::csrf() ?>
			<?php echo Form::submit_wrap('save', __('Save'), null, $cancel) ?>
		</fieldset>
	</div>

	<div class="grid4">
		<fieldset id="fields-location">
			<ul>
				<?php echo Form::input_wrap('address', $venue, null, __('Street address'), $errors) ?>
				<?php echo Form::input_wrap('city_name', $venue, null, __('City'), $errors) ?>
			</ul>
		</fieldset>
	</div>

<?php
echo Form::close();

echo HTML::script_source('
head.ready("anqh", function() {
	$("#fields-location ul").append("<li><div id=\"map\">' . __('Loading map..') . '</div></li>");

	$("#map").googleMap(' . ($venue->latitude ? json_encode(array('marker' => true, 'lat' => $venue->latitude, 'long' => $venue->longitude)) : '') . ');

	$("input[name=city_name]").autocompleteCity();

	$("input[name=address], input[name=city_name]").blur(function(event) {
		var address = $("input[name=address]").val();
		var city = $("input[name=city_name]").val();
		if (address != "" && city != "") {
			var geocode = address + ", " + city;
			Anqh.geocoder.geocode({ address: geocode }, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK && results.length) {
					Anqh.map.setCenter(results[0].geometry.location);
					$("input[name=latitude]").val(results[0].geometry.location.lat());
					$("input[name=longitude]").val(results[0].geometry.location.lng());
					var marker = new google.maps.Marker({
						position: results[0].geometry.location,
						map: Anqh.map
					});
				}
			});
		}
	});

});
');
