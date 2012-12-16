<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Calendar view.
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Calendar extends View_Section {

	/**
	 * @var  integer  Stamp
	 */
	public $date;

	/**
	 * @var  string  Day URL template
	 */
	public $url_day;

	/**
	 * @var  string  Month URL template
	 */
	public $url_month;

	/**
	 * @var  string  Week URL template
	 */
	public $url_week;


	/**
	 * Create new view.
	 */
	public function __construct() {
		parent::__construct();

		$this->class = 'calendar';
		$this->title = __('Calendar');

		$this->date = time();
	}


	/**
	 * Render content.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

?>

<div id="calendar"></div>

<script>
head.ready('jquery-ui', function() {
	var $calendar = $('#calendar');

	$calendar.datepicker(<?= $this->_options() ?>);

	// Make weeks clickable
	$calendar.delegate('td.ui-datepicker-week-col', 'click', function cbClick() {

		// Capture click
		$calendar.datepicker('option', 'onSelect', function cbSelect(date, inst) {
			var date = $(this).datepicker('getDate')
			  , week = (!date ? 0 : $.datepicker.iso8601Week(date))
			  , url  = $calendar.datepicker('option', 'urlWeek')
					.replace(":year", inst.selectedYear)
					.replace(":week", week);

			window.location = url;
		});

		// Click first available day
		$(this).nextAll('td').not('.ui-state-disabled').first().click();

	});

});
</script>

<?php

		return ob_get_clean();
	}


	/**
	 * Var method for options.
	 *
	 * @return  string
	 */
	protected function _options() {
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
			'nextText'        => '&#9658;',
			'prevText'        => '&#9668;',
			'showWeek'        => true,
			'weekHeader'      => __('Wk'),
			'urlWeek'         => $this->url_week,
		);

		// Start date set
		if (isset($this->date)) {
			$options['defaultDate'] = date('j.n.Y', $this->date);
		}
		$options = json_encode($options);

		// Highlight days
		$options = substr_replace($options,	',
beforeShowDay: function(date) {
	return [ true, date.getDate() % 5 == 0 ? "highlight" : "" ];
}}', -1, 1);

		// Month change
		if (isset($this->url_month)) {
			$options = substr_replace($options,	',
onChangeMonthYear: function(year, month, inst) {
	var url = "' . $this->url_month . '"
		.replace(":year", year)
		.replace(":month", month);
}}', -1, 1);
		}

		// Day change
		if (isset($this->url_day)) {
			$options = substr_replace($options,	',
onSelect: function(date, inst) {
	var url = "' . $this->url_day . '"
		.replace(":year", inst.selectedYear)
		.replace(":month", inst.selectedMonth + 1)
		.replace(":day", inst.selectedDay);
	window.location = url;
}}', -1, 1);
		}

		return $options;
	}


}
