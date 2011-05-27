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
	$_city = Text::capitalize($v->city() ? $v->city()->name : $v->city_name);
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

echo Form::open(null, array('id' => 'form-event'));
?>

<!--
	<?php if ($event_errors) { ?>
	<ul class="errors">
		<?php foreach ($event_errors as $error) { ?>
		<li><?php echo $error ?></li>
		<?php } ?>
	</ul>
	<?php } ?>
	<?php if ($venue_errors) { ?>
	<ul class="errors">
		<?php foreach ($venue_errors as $error) { ?>
		<li><?php echo $error ?></li>
		<?php } ?>
	</ul>
	<?php } ?>
-->

	<div class="grid8 first">
		<fieldset id="fields-primary">
			<ul>
				<?php echo Form::input_wrap('name', $event, null, __('Event'), $event_errors) ?>
				<?php echo Form::textarea_wrap('dj',   $event, null, true, __('Performers'), $event_errors) ?>
				<?php echo Form::textarea_wrap('info', $event, null, true, __('Other information'), $event_errors, null, true) ?>
				<?php echo Form::input_wrap('homepage', $event, null, __('Homepage'), $event_errors) ?>
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
				<?php echo Form::input_wrap(
					'stamp_begin[date]',
					is_numeric($event->stamp_begin) ? Date::format('DMYYYY', $event->stamp_begin) : $event->stamp_begin,
					array('class' => 'date', 'maxlength' => 10),
					__('When?'),
					Arr::get($event_errors, 'stamp_begin')
				) ?>
				<?php echo Form::select_wrap(
					'stamp_begin[time]',
					Date::hours_minutes(30, true),
					is_numeric($event->stamp_begin) ? Date::format('HHMM', $event->stamp_begin) : (empty($event->stamp_begin) ? '22:00' : $event->stamp_begin),
					array('class' => 'time'),
					__('At'),
					Arr::get($event_errors, 'stamp_begin')
				) ?>

				<?php echo Form::select_wrap(
					'stamp_end[time]',
					Date::hours_minutes(30, true),
					is_numeric($event->stamp_end) ? Date::format('HHMM', $event->stamp_end) : (empty($event->stamp_end) ? '04:00' : $event->stamp_end),
					array('class' => 'time'),
					'-',
					Arr::get($event_errors, 'stamp_end')
				) ?>
			</ul>
		</fieldset>

		<fieldset id="fields-venue">
			<legend><?php echo __('Venue') ?></legend>
			<ul>
				<li class="choice"><?php echo Form::checkbox('venue_hidden', 'true', (bool)$event->venue_hidden, array('id' => 'field-ug')), Form::label('field-ug', __('Underground')) ?></li>
<!--				<?php echo Form::select_wrap('venue', $list, $venue ? $venue->id : '', null, __('Venue')) ?>
				<li class="choice"><?php echo HTML::anchor('#new-venue', __('Not in list?'), array('class' => 'venue-add')) ?></li>-->
				<?php echo Form::input_wrap('venue_name', $event, (bool)$event->venue_hidden ? array('disabled' => 'disabled') : null, __('Venue'), $event_errors) ?>
				<?php echo Form::input_wrap('city_name',  $event, null, __('City'), $event_errors) ?>
			</ul>
		</fieldset>

		<fieldset id="fields-tickets">
			<legend><?php echo __('Tickets') ?></legend>
			<ul>
				<li class="choice"><?php echo Form::checkbox('free', 'true', $event->price === '0.00', array('id' => 'field-free')), Form::label('field-free', __('Free entry')) ?></li>
				<?php echo Form::input_wrap('price', $event, $event->price === '0.00' ? array('disabled' => 'disabled') : null, __('Tickets'), $event_errors) ?>
<!--				<?php echo Form::input_wrap('price2', $event, null, __('Preasel tickets'), $event_errors) ?>-->
				<?php echo Form::input_wrap('age', $event, null, __('Age limit'), $event_errors) ?>
			</ul>
		</fieldset>

		<fieldset id="fields-music">
			<ul>
				<?php echo Form::checkboxes_wrap('tag', $tags, $event->tags(), __('Music'), $event_errors, null, 'pills') ?>
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

	//$("#field-city-name").autocompleteCity({ latitude: "city_latitude", longitude: "city_longitude" });
	$("#field-city-name").autocompleteGeo();

	// Tickets
	$("#field-free").change(function() {
		this.checked ? $("#field-price").attr("disabled", "disabled") : $("#field-price").removeAttr("disabled");
//		$("#field-price, label[for=field-price], #field-price2, label[for=field-price2]").toggle(!this.checked);
	});

	// Venue
	$("#field-ug").change(function() {
		this.checked ? $("#field-venue-name").attr("disabled", "disabled") : $("#field-venue-name").removeAttr("disabled");
	});
/*
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
*/
});
');
