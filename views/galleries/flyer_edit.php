<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Edit flyer
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

echo Form::open(null, array('id' => 'form-flyer-edit'));
?>

<div class="grid8 first">
	<fieldset id="fields-info">
		<ul>
			<?php echo $flyer->input('name', 'form/anqh', array('errors' => $errors)) ?>
		</ul>
	</fieldset>
</div>

<div class="grid3">
	<fieldset id="fields-date">
		<ul>
			<?php echo $flyer->input('stamp_begin', 'form/anqh', array('default_time' => '22:00', 'errors' => $errors)) ?>
		</ul>
	</fieldset>
</div>

<div class="grid1">
	<fieldset>
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

// Date
echo HTML::script_source('
head.ready("jquery-ui", function() {
	$("#field-stamp-begin-date").datepicker(' . json_encode($options) . ');
});
');
