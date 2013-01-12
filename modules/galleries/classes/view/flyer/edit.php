<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Flyer_Edit
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Flyer_Edit extends View_Section {

	/**
	 * @var  array  Form errors
	 */
	public $errors;

	/**
	 * @var  Model_Flyer
	 */
	public $flyer;


	/**
	 * Create new view.
	 *
	 * @param  Model_Flyer  $flyer
	 */
	public function __construct(Model_Flyer $flyer) {
		parent::__construct();

		$this->flyer = $flyer;
		$this->title = __('Edit flyer');
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		echo $this->event();

		echo $this->date();

		return ob_get_clean();
	}


	/**
	 * Event select form.
	 *
	 * @return  string
	 */
	public function event() {
		ob_start();

		// Event search date range
		if ($this->flyer->stamp_begin) {
			$year = Arr::get(getdate($this->flyer->stamp_begin), 'year');
			$event_options = array('filter' => 'date:' . mktime(0, 0, 0, 1, 1, $year) . '-' . mktime(0, 0, 0, 1, 1, $year + 1));
		} else {
			$event_options = null;
		}

		// Form
		echo Form::open(null, array('id' => 'form-flyer-edit-event', 'class' => 'form-inline'));

		echo Form::label('field-known-event', __('Event')), ' ';
		echo Form::input('event', null, array(
			'id'          => 'field-known-event',
			'class'       => 'input-xxlarge',
			'title'       => __('Existing event'),
			'placeholder' => __('Add to an existing event')
		)), ' ';

		echo Form::submit('save', __('Save'), array('class' => 'btn'));
		echo Form::hidden('event_id', $this->flyer->event_id);
		echo Form::csrf();

		echo Form::close();

?>

<script>
// Event autocomplete
head.ready('anqh', function() {
	$('#field-known-event').autocompleteEvent(<?= json_encode($event_options) ?>);
});
</script>

<?php

		return ob_get_clean();
	}


	/**
	 * Date select form.
	 *
	 * @return  string
	 */
	public function date() {
		ob_start();

		// Date picker options
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

		// Form
		echo Form::open(null, array('id' => 'form-flyer-edit', 'class' => 'form-inline'));

		echo Form::label('field-unknown-event', __('Event')), ' ';
		echo Form::input('name', $this->flyer->name, array(
			'id'          => 'field-unknown-event',
			'class'       => 'input-xxlarge',
			'title'       => __('Clean event name'),
			'placeholder' => __('Clean event name')
		)), ' ';

		echo Form::label('field-date', __('Date')), ' ';
		echo Form::input(
			'stamp_begin[date]',
			is_numeric($this->flyer->stamp_begin) ? Date::format('DMYYYY', $this->flyer->stamp_begin) : $this->flyer->stamp_begin,
			array('id' => 'field-date', 'class' => 'input-small date', 'maxlength' => 10)
		), ' ';

		echo Form::label('field-time', __('At')), ' ';
		echo Form::select(
			'stamp_begin[time]',
			Date::hours_minutes(30, true),
			is_numeric($this->flyer->stamp_begin) ? Date::format('HHMM', $this->flyer->stamp_begin) : (empty($this->flyer->stamp_begin) ? '22:00' : $this->flyer->stamp_begin),
			array('id' => 'field-time', 'class' => 'input-small time')
		), ' ';

		echo Form::submit('save', __('Save'), array('class' => 'btn'));
		echo Form::csrf();

		echo Form::close();

?>

<script>
// Date picker
head.ready('jquery-ui', function() {
	$('#field-date').datepicker(<?= json_encode($options) ?>);
});
</script>

<?php

		return ob_get_clean();
	}

}
