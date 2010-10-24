<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Edit event
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

// Build venue list
//$list = array(__('Choose venue..'));
//foreach ($venues as $v) $list[$v['value']] = $v['label'] . ', ' . $v['city'];

echo Form::open(null, array('id' => 'form-event-edit'));
?>

	<div class="grid8 first">
		<fieldset id="fields-primary">
			<ul>
				<?php echo $event->input('name', 'form/anqh', array('errors' => $event_errors)) ?>
				<?php echo $event->input('dj',   'form/anqh', array('errors' => $event_errors)) ?>
				<?php echo $event->input('info', 'form/anqh', array('errors' => $event_errors)) ?>
				<?php echo $event->input('tags', 'form/anqh', array('errors' => $event_errors, 'class' => 'pills', 'values' => $tags)) ?>
			</ul>
		</fieldset>

		<fieldset>
			<?php echo Form::hidden('city_id', $city ? $city->id : 0) ?>
			<?php echo Form::hidden('city_latitude', $city ? $city->latitude : 0) ?>
			<?php echo Form::hidden('city_longitude', $city ? $city->longitude : 0) ?>
			<?php echo Form::hidden('latitude', $venue->latitude) ?>
			<?php echo Form::hidden('longitude', $venue->longitude) ?>
			<?php echo Form::hidden('foursquare_id', $venue->foursquare_id) ?>
			<?php echo Form::hidden('foursquare_category_id', $venue->foursquare_category_id) ?>

			<?php echo Form::csrf() ?>
			<?php echo Form::submit_wrap('save', __('Save'), null, $cancel) ?>

		</fieldset>
	</div>

	<div class="grid4">
		<fieldset id="fields-when">
			<ul>
				<?php echo $event->input('stamp_begin', 'form/anqh', array('label_date' => __('When?'), 'label_time' => __('At'), 'default_time' => '22:00', 'errors' => $event_errors)) ?>
				<?php echo $event->input('stamp_end',   'form/anqh', array('default_time' => '04:00', 'errors' => $event_errors)) ?>
			</ul>
		</fieldset>

		<fieldset id="fields-tickets">
			<legend><?php echo __('Tickets') ?></legend>
			<ul>
				<li class="choice"><?php echo Form::checkbox('free', 'true', false, array('id' => 'field-free')), Form::label('field-free', __('Free entry')) ?></li>
				<?php echo $event->input('price',  'form/anqh', array('errors' => $event_errors)) ?>
				<?php echo $event->input('price2', 'form/anqh', array('errors' => $event_errors)) ?>
				<?php echo $event->input('age',    'form/anqh', array('errors' => $event_errors)) ?>
			</ul>
		</fieldset>

		<fieldset id="fields-where">
			<legend><?php echo __('Where?') ?></legend>
			<ul>
				<?php //echo Form::select_wrap('venue', $list, $venue && $venue->loaded() ? $venue->id : '', null,	__('Venue')) ?>
				<?php echo $event->input('city_name',  'form/anqh', array('errors' => $event_errors)) ?>
				<?php echo $event->input('venue_name', 'form/anqh', array('errors' => $event_errors, 'attributes' => array('placeholder' => __('Fill city first')))) ?>
				<?php //echo Form::input_wrap('venue_name', $venue ? $venue->name : $event->venue_name, array('placeholder' => __('Fill city first')), __('Venue'), Arr::get($venue_errors, 'venue_name')) ?>
				<?php echo $venue->input('address', 'form/anqh', array('errors' => $venue_errors, 'attributes' => array('placeholder' => __('Fill venue first')))) ?>
			</ul>
		</fieldset>
	</div>

<?php
echo Form::close();

$options = array(
	'changeMonth'     => true,
	'changeYear'      => true,
	'dateFormat'      => 'd.m.yy',
	'dayNames'        => array(
		__('Sunday'), __('Monday'), __('Tuesday'), __('Wednesday'), __('Thursday'), __('Friday'), __('Saturday')
	),
	'dayNamesMin'    => array(
		__('Su'), __('Mo'), __('Tu'), __('We'), __('Th'), __('Fr'), __('Sa')
	),
	'firstDay'        => 1,
	'monthNames'      => array(
		__('January'), __('February'), __('March'), __('April'), __('May'), __('June'),
		__('July'), __('August'), __('September'), __('October'), __('November'), __('December')
	),
	'monthNamesShort' => array(
		__('Jan'), __('Feb'), __('Mar'), __('Apr'),	__('May'), __('Jun'),
		__('Jul'), __('Aug'), __('Sep'), __('Oct'), __('Nov'), __('Dec')
	),
	'nextText'        => __('&raquo;'),
	'prevText'        => __('&laquo;'),
	'showWeek'        => true,
	'showOtherMonths' => true,
	'weekHeader'      => __('Wk'),
);

// Venues, Maps and tickets
echo HTML::script_source('
$(function() {

	// Datepicker
	$("#field-stamp-begin-date").datepicker(' . json_encode($options) . ');


	/*
	// Venues
	var venues = ' . json_encode(empty($venues) ? '' : $venues) . ';

	$("#field-venue").change(function() {
		$("#field-venue-name").val("");
		$("#fields-venue").hide();
		var id = this.value;
		$.each(venues, function(index, venue) {
			if (venue.value == id && venue.city) {
				if (venue.latitude && venue.longitude) {
					$("#map").googleMap({ marker: true, lat: venue.latitude, long: venue.longitude });
				}
			}
		});
	});
	*/

	$("#fields-where").append("<div id=\"map\">' . __('Loading map..') . '</div>");
	$("#map").googleMap(' . ($venue->latitude ? json_encode(array('marker' => true, 'lat' => $venue->latitude, 'long' => $venue->longitude)) : '') . ');
	$("#field-city-name").geonamesCity({ latitude: "city_latitude", longitude: "city_longitude" });
	$("#field-venue-name").foursquareVenue({ venueId: "foursquare_id", categoryId: "foursquare_category_id" });

	$("input[name=address]").blur(function(event) {
		var address = $("input[name=address]").val();
		var city = $("input[name=city_name]").val();
		if (address != "" && city != "") {
			var geocode = address + ", " + city;
			geocoder.geocode({ address: geocode }, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK && results.length) {
					$("input[name=latitude]").val(results[0].geometry.location.lat());
					$("input[name=longitude]").val(results[0].geometry.location.lng());
					$("#map").googleMap({ marker: true, lat: results[0].geometry.location.lat(), long: results[0].geometry.location.lng() });
				}
			});
		}
	});


	// Tickets
	$("#field-free").change(function() {
		$("#field-price, label[for=field-price], #field-price2, label[for=field-price2]").toggle(!this.checked);
	});

});
');
