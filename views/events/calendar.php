<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Month calendar
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<div id="calendar"></div>

<?php
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
		__('January'), __('February'), __('March'), __('April'),
		__('May'), __('June'), __('July'), __('August'),
		__('September'), __('October'), __('November'), __('December')
	),
	'monthNamesShort' => array(
		__('Jan'), __('Feb'), __('Mar'), __('Apr'),
		__('May'), __('Jun'), __('Jul'), __('Aug'),
		__('Sep'), __('Oct'), __('Nov'), __('Dec')
	),
	'nextText'        => __('Next'),
	'prevText'        => __('Prev'),
	'showWeek'        => true,
	'weekHeader'      => __('Wk'),
);

// Start date set
if (isset($date)) {
	$options['defaultDate'] = date('j.n.Y', $date);
}
$options = json_encode($options);

// Highlight days
$options = substr_replace($options,	',
	beforeShowDay: function(date) {
		// Use JSON data
		return [ true, date.getDate() % 5 == 0 ? "highlight" : "" ];
	}}', -1, 1);

// Month change
if (isset($url_month)) {
	$options = substr_replace($options,	',
	onChangeMonthYear: function(year, month, inst) {
		var url = "' . $url_month . '"
			.replace(":year", year)
			.replace(":month", month);
		// Load JSON data
	}}', -1, 1);
}

// Day change
if (isset($url_day)) {
	$options = substr_replace($options,	',
	onSelect: function(date, inst) {
		var url = "' . $url_day . '"
			.replace(":year", inst.selectedYear)
			.replace(":month", inst.selectedMonth)
			.replace(":day", inst.selectedDay);
		window.location = url;
	}}', -1, 1);
}

echo HTML::script_source('
$("#calendar").datepicker(' . $options . ')');
