<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * User settings
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

echo Form::open();
?>

	<fieldset id="fields-basic">
		<legend><?php echo __('Basic information') ?></legend>
		<ul>
			<?php echo Form::input_wrap('name', $user, null, __('Name'), $errors) ?>
			<?php echo Form::radios_wrap('gender', array('f' => __('Female'), 'm' => __('Male')), $user, null, __('Gender'), $errors) ?>
			<?php echo Form::input_wrap('dob', Date::format('DMYYYY', $user->dob), array('class' => 'date', 'maxlength' => 10), __('Date of Birth'), $errors) ?>
			<?php echo Form::input_wrap('title', $user, null, __('Title'), $errors) ?>
			<?php echo Form::textarea_wrap('description', $user, null, true, __('Description'), $errors) ?>
		</ul>
	</fieldset>

	<fieldset id="fields-contact">
		<legend><?php echo __('Contact information') ?></legend>
		<ul>
			<?php echo Form::input_wrap('email', $user, null, __('Email'), $errors) ?>
			<?php echo Form::input_wrap('homepage', $user, null, __('Homepage'), $errors) ?>
			<?php echo Form::input_wrap('address_street', $user, null, __('Street address'), $errors) ?>
			<?php echo Form::input_wrap('address_zip', $user, null, __('Zip code'), $errors) ?>
			<?php echo Form::input_wrap('address_city', $user, null, __('City'), $errors) ?>
		</ul>
	</fieldset>

	<fieldset id="fields-forum">
		<legend><?php echo __('Forum settings') ?></legend>
		<ul>
			<?php echo Form::textarea_wrap('signature', $user, array('rows' => 5), true, __('Signature'), $errors) ?>
		</ul>
	</fieldset>

	<fieldset>
		<?php echo Form::hidden('city_id', (int)$user->geo_city_id) ?>
		<?php echo Form::hidden('latitude', $user->latitude) ?>
		<?php echo Form::hidden('longitude', $user->longitude) ?>

		<?php echo Form::csrf() ?>
		<?php echo Form::submit_wrap('save', __('Save'), null, URL::user($user)) ?>
	</fieldset>

<?php
echo Form::close();

// Date picker
$options = array(
	'changeMonth'     => true,
	'changeYear'      => true,
	'dateFormat'      => 'd.m.yy',
	'defaultDate'     => date('j.n.Y', $user->dob),
	'dayNames'        => array(
		__('Sunday'), __('Monday'), __('Tuesday'), __('Wednesday'), __('Thursday'), __('Friday'), __('Saturday')
	),
	'dayNamesMin'    => array(
		__('Su'), __('Mo'), __('Tu'), __('We'), __('Th'), __('Fr'), __('Sa')
	),
	'firstDay'        => 1,
	'monthNames'      => array(
		__('January'), __('February'), __('March'), __('April'),
		__('May'), __('June'), __('July'), __('August'),
		__('September'), __('October'), __('November'), __('December')
	),
	'monthNamesShort' => array(
		__('Jan'), __('Feb'), __('Mar'), __('Apr'),
		__('May'), __('Jun'), __('Jul'), __('Aug'),
		__('Sep'), __('Oct'), __('Nov'), __('Dec')
	),
	'nextText'        => __('&raquo;'),
	'prevText'        => __('&laquo;'),
	'showWeek'        => true,
	'showOtherMonths' => true,
	'weekHeader'      => __('Wk'),
	'yearRange'       => '1900:+0',
);

echo HTML::script_source('

// Date picker
head.ready("jquery-ui", function() {
	$("#field-dob").datepicker(' . json_encode($options) . ');
});

// Maps
head.ready("jquery", function() {
	$("#fields-contact ul").append("<li><div id=\"map\">' . __('Loading map..') . '</div></li>");
});

head.ready("anqh", function() {
	$("#map").googleMap(' . ($user->latitude ? json_encode(array('marker' => true, 'lat' => $user->latitude, 'long' => $user->longitude)) : '') . ');

	$("input[name=address_city]").autocompleteCity();

	$("input[name=address_street], input[name=address_city]").blur(function(event) {
		var address = $("input[name=address_street]").val();
		var city = $("input[name=address_city]").val();
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
