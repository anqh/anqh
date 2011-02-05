<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Edit event
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

// Build venue list
$venues_json = array();
$list        = array(__('Choose venue..'));
$_city       = '';
foreach ($venues as $v):

	// Group by city
	$_city = Text::capitalize($v->city->loaded() ? $v->city->name : $v->city_name);
	!isset($list[$_city]) and $list[$_city] = array();
	$list[$_city][$v->id] = $v->name;

	// JSON list for map
	$venues_json[$v->id] = array(
		'id'        => $v->id,
		'name'      => $v->name,
		'address'   => $v->address,
		'city'      => $_city,
		'latitude'  => $v->latitude,
		'longitude' => $v->longitude,
	);

endforeach;
unset($venues, $v);

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

		<!--
		<fieldset id="fields-flyers">
			<legend><?php echo __('Flyers') ?></legend>
			<ul>

				<?php if ($event->flyer_front_url || $event->flyer_back_url): ?>
				<li>
					<?php if ($event->flyer_front_url) echo HTML::anchor('#flyer-front', HTML::image($event->flyer_front_url, array('width' => 150)), array('title' => __('Change front flyer'), 'onclick' => "\$('input[name=flyer_front_url]').toggle(); return false;")) ?>
					<?php if ($event->flyer_back_url)  echo HTML::anchor('#flyer-back',  HTML::image($event->flyer_back_url,  array('width' => 150)), array('title' => __('Change back flyer'),  'onclick' => "\$('input[name=flyer_back_url]').toggle(); return false;")) ?>
				</li>
				<?php endif; ?>

				<?php echo $event->input('flyer_front_url', 'form/anqh', array(
					'label'      => null,
					'errors'     => $event_errors,
					'attributes' => array(
						'style'       => $event->flyer_front_url ? 'display: none' : '',
						'title'       => __('Front flyer'),
						'placeholder' => __('Front flyer'),
					))) ?>
				<?php echo $event->input('flyer_back_url',  'form/anqh', array(
					'label'  => null,
					'errors' => $event_errors,
					'tip'    => __('You can also upload flyers after saving the event'),
					'attributes' => array(
						'style'       => $event->flyer_back_url ? 'display: none' : '',
						'title'       => __('Back flyer'),
						'placeholder' => __('Back flyer'),
					))) ?>

			</ul>
		</fieldset>
		-->

		<fieldset id="fields-tickets">
			<legend><?php echo __('Tickets') ?></legend>
			<ul>
				<li class="choice"><?php echo Form::checkbox('free', 'true', false, array('id' => 'field-free')), Form::label('field-free', __('Free entry')) ?></li>
				<?php echo $event->input('price',  'form/anqh', array('errors' => $event_errors)) ?>
				<?php echo $event->input('price2', 'form/anqh', array('errors' => $event_errors)) ?>
				<?php echo $event->input('age',    'form/anqh', array('errors' => $event_errors)) ?>
			</ul>
		</fieldset>

		<fieldset id="fields-venue">
			<legend><?php echo __('Venue') ?></legend>
			<ul>
				<li class="choice"><?php echo Form::checkbox('venue_hidden', 'true', $event->venue_hidden, array('id' => 'field-ug')), Form::label('field-ug', __('Underground')) ?></li>
				<?php echo Form::select_wrap('venue', $list, $venue && $venue->loaded() ? $venue->id : '', null,	__('Venue')) ?>
				<li class="choice"><?php echo HTML::anchor('#new-venue', __('Not in list?'), array('class' => 'venue-add')) ?></li>
				<?php echo $event->input('venue_name', 'form/anqh', array('errors' => $event_errors)) ?>
				<?php echo $event->input('city_name',  'form/anqh', array('errors' => $event_errors)) ?>
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
head.ready("anqh", function() {

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

	// $("#fields-venue").append("<div id=\"map\">' . __('Loading map..') . '</div>");
	// $("#map").googleMap(' . ($venue->latitude ? json_encode(array('marker' => true, 'lat' => $venue->latitude, 'long' => $venue->longitude)) : '') . ');
	$("#field-city-name").autocompleteCity({ latitude: "city_latitude", longitude: "city_longitude" });
	// $("#field-venue-name").foursquareVenue({ venueId: "foursquare_id", categoryId: "foursquare_category_id" });

	/*
	$("input[name=address]").blur(function(event) {
		var address = $("input[name=address]").val();
		var city = $("input[name=city_name]").val();
		if (address != "" && city != "") {
			var geocode = address + ", " + city;
			Anqh.geocoder.geocode({ address: geocode }, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK && results.length) {
					$("input[name=latitude]").val(results[0].geometry.location.lat());
					$("input[name=longitude]").val(results[0].geometry.location.lng());
					$("#map").googleMap({ marker: true, lat: results[0].geometry.location.lat(), long: results[0].geometry.location.lng() });
				}
			});
		}
	});
	*/


	// Tickets
	$("#field-free").change(function() {
		$("#field-price, label[for=field-price], #field-price2, label[for=field-price2]").toggle(!this.checked);
	});

	// Venue
	var unlisted = false;
	$("#field-ug").change(function() {
		$("#field-venue, label[for=field-venue], a.venue-add").toggle(!this.checked && !unlisted);
		$("#field-venue-name, label[for=field-venue-name]").toggle(!this.checked && unlisted);
	});
	$("a.venue-add").click(function() {
		unlisted = true;
		$("#field-ug, label[for=field-ug], a.venue-add, #field-venue, label[for=field-venue]").hide();
		$("#field-venue-name, label[for=field-venue-name]").show();
	});
	$("#field-venue").change(function() {
		$("#field-ug").attr("checked", false);
		$("#field-city-name").val($("#field-venue option:selected").parent("optgroup").attr("label"));
	});

});
');
