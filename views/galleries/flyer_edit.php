<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Edit flyer
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

echo Form::open(null, array('id' => 'form-flyer-edit-event'));
?>

<div class="grid8 first">
	<fieldset id="fields-event">
		<ul>
			<?php echo Form::input_wrap('event', null, null, __('Event')) ?>
		</ul>
	</fieldset>
</div>

<div class="grid1">
	<fieldset class="grid1">
		<?php echo Form::hidden('event_id', $flyer->event_id) ?>
		<?php echo Form::csrf() ?>
		<?php echo Form::submit_wrap('save', __('Save')) ?>
	</fieldset>
</div>

<?php echo Form::close(); echo Form::open(null, array('id' => 'form-flyer-edit')); ?>

<div class="grid8 first">
	<fieldset id="fields-info">
		<ul>
			<?php echo Form::input_wrap('name', $flyer, null, __('Event'), $errors) ?>
		</ul>
	</fieldset>
</div>

<div class="grid3">
	<fieldset id="fields-date">
		<ul>
			<?php echo Form::input_wrap(
				'stamp_begin[date]',
				is_numeric($flyer->stamp_begin) ? Date::format('DMYYYY', $flyer->stamp_begin) : $flyer->stamp_begin,
				array('class' => 'date', 'maxlength' => 10),
				__('Date'),
				Arr::get($errors, 'stamp_begin')
			) ?>
			<?php echo Form::select_wrap(
				'stamp_begin[time]',
				Date::hours_minutes(30, true),
				is_numeric($flyer->stamp_begin) ? Date::format('HHMM', $flyer->stamp_begin) : (empty($flyer->stamp_begin) ? '22:00' : $flyer->stamp_begin),
				array('class' => 'time'),
				__('At'),
				Arr::get($errors, 'stamp_begin')
			) ?>
		</ul>
	</fieldset>
</div>

<div class="grid1">
	<fieldset class="grid1">
		<?php echo Form::csrf() ?>
		<?php echo Form::submit_wrap('save', __('Save')) ?>
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

// Event search date range
if ($flyer->stamp_begin) {
	$year = Arr::get(getdate($flyer->stamp_begin), 'year');
	$event_options = array('filter' => 'date:' . mktime(0, 0, 0, 1, 1, $year) . '-' . mktime(0, 0, 0, 1, 1, $year + 1));
} else {
	$event_options = null;;
}

echo HTML::script_source('

// Date
head.ready("jquery-ui", function() {
	$("#field-stamp-begin-date").datepicker(' . json_encode($options) . ');
});

// Event
head.ready("anqh", function() {
	$("input[name=event]").autocompleteEvent(' . json_encode($event_options) . ');
});
');
