<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * View_Event_Edit
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Event_Edit extends View_Article {

	/**
	 * @var  string  URL for cancel action
	 */
	public $cancel;

	/**
	 * @var  Model_Event
	 */
	public $event;

	/**
	 * @var  array
	 */
	public $event_errors;

	/**
	 * @var  Model_Venue
	 */
	public $venue;

	/**
	 * @var  array
	 */
	public $venue_errors;


	/**
	 * Create new view.
	 *
	 * @param  Model_Event  $event
	 */
	public function __construct(Model_Event $event) {
		parent::__construct();

		$this->event = $event;
	}


	/**
	 * Render content.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		echo Form::open(null, array('id' => 'form-event', 'class' => 'row'));
?>

		<!--
			<?php if ($this->event_errors) { ?>
			<ul class="errors">
				<?php foreach ($this->event_errors as $error) { ?>
				<li><?php echo $error ?></li>
				<?php } ?>
			</ul>
			<?php } ?>
			<?php if ($this->venue_errors) { ?>
			<ul class="errors">
				<?php foreach ($this->venue_errors as $error) { ?>
				<li><?php echo $error ?></li>
				<?php } ?>
			</ul>
			<?php } ?>
		-->

			<div class="span8">
				<fieldset id="fields-primary">
					<?php echo Form::control_group(
						Form::input('name', $this->event->name, array('class' => 'input-xxlarge')),
						array('name' => __('Event')),
						Arr::get($this->event_errors, 'name')) ?>

					<?php echo Form::control_group(
						Form::textarea('dj', $this->event->dj, array('class' => 'input-xxlarge'), true),
						array('dj' => __('Performers')),
						Arr::get($this->event_errors, 'dj')) ?>

					<?php echo Form::control_group(
						Form::textarea_editor('info', $this->event->info, array('class' => 'input-xxlarge'), true),
						array('info' => __('Other information')),
						Arr::get($this->event_errors, 'info')) ?>

					<?php echo Form::control_group(
						Form::input('homepage', $this->event->homepage, array('class' => 'input-xxlarge', 'placeholder' => 'http://')),
						array('homepage' => __('Homepage')),
						Arr::get($this->event_errors, 'homepage')) ?>
				</fieldset>

				<fieldset class="form-actions">
					<?php echo Form::button('save', __('Save event'), array('type' => 'submit', 'class' => 'btn btn-success btn-large')) ?>
					<?php echo $this->cancel ? HTML::anchor($this->cancel, __('Cancel'), array('class' => 'cancel')) : '' ?>

					<?php echo Form::csrf() ?>
					<?php echo Form::hidden('latitude', $this->venue->latitude) ?>
					<?php echo Form::hidden('longitude', $this->venue->longitude) ?>
					<?php echo Form::hidden('venue_id', $this->venue->id) ?>
				</fieldset>
			</div>

			<div class="span4">
				<fieldset id="fields-when" class="row">
					<div class="span4">
						<?php echo Form::control_group(
							Form::input(
								'stamp_begin[date]',
								is_numeric($this->event->stamp_begin) ? Date::format('DMYYYY', $this->event->stamp_begin) : $this->event->stamp_begin,
								array('class' => 'input-small date', 'maxlength' => 10, 'placeholder' => 'd.m.yyyy')),
							array('stamp_begin[date]' => __('Date')),
							Arr::get($this->event_errors, 'stamp_begin')) ?>
					</div>
					<div class="span2">
						<?php echo Form::control_group(
							Form::select(
								'stamp_begin[time]',
								Date::hours_minutes(30, true),
								is_numeric($this->event->stamp_begin) ? Date::format('HHMM', $this->event->stamp_begin) : (empty($this->event->stamp_begin) ? '22:00' : $this->event->stamp_begin),
								array('class' => 'input-small time')),
							array('stamp_begin[time]' => __('From')),
							Arr::get($this->event_errors, 'stamp_begin')) ?>
					</div>
					<div class="span2">
						<?php echo Form::control_group(
							Form::select(
								'stamp_end[time]',
								Date::hours_minutes(30, true),
								is_numeric($this->event->stamp_end) ? Date::format('HHMM', $this->event->stamp_end) : (empty($this->event->stamp_end) ? '04:00' : $this->event->stamp_end),
								array('class' => 'input-small time')),
							array('stamp_end[time]' => __('To')),
							Arr::get($this->event_errors, 'stamp_end')) ?>
					</div>
				</fieldset>

				<fieldset id="fields-venue">
					<legend><?php echo __('Venue') ?></legend>
					<?php echo Form::control_group(
						'<label class="inlince checkbox">'
							. Form::checkbox('venue_hidden', 'true', (bool)$this->event->venue_hidden, array('id' => 'field-ug'))
							. ' ' . __('Underground')
							. '</label>') ?>

					<?php echo Form::control_group(
						Form::input('venue_name', $this->event->venue_name, (bool)$this->event->venue_hidden ? array('disabled' => 'disabled') : null),
						array('venue_name' => __('Venue')),
						Arr::get($this->event_errors, 'venue_name')) ?>

					<?php echo Form::control_group(
						Form::input('city_name', $this->event->city_name),
						array('city_name' => __('City')),
						Arr::get($this->event_errors, 'city_name')) ?>
				</fieldset>

				<fieldset id="fields-tickets">
					<legend><?php echo __('Tickets') ?></legend>
					<?php echo Form::control_group(
						'<label class="inline checkbox">'
							. Form::checkbox('free', 'true', $this->event->price === '0.00', array('id' => 'field-free'))
							. ' ' . __('Free entry')
							. '</label>') ?>

					<?php echo Form::control_group(
						'<div class="input-append">'
							. Form::input('price', $this->event->price, $this->event->price === '0.00' ? array('disabled' => 'disabled', 'class' => 'input-mini') : array('class' => 'input-mini'))
							. '<span class="add-on">&euro;</span>'
							. '</div>',
						array('price' => __('Tickets')),
						Arr::get($this->event_errors, 'price')) ?>

					<?php echo Form::control_group(
						'<div class="input-append">'
							. Form::input('age', $this->event->age, array('class' => 'input-mini'))
							. '<span class="add-on">years</span>'
							. '</div>',
						array('age' => __('Age limit')),
						Arr::get($this->event_errors, 'age')) ?>
				</fieldset>

				<fieldset id="fields-music">
					<legend><?php echo __('Music') ?></legend>
					<?php echo Form::checkboxes_wrap('tag', $this->tags(), $this->event->tags(), null, $this->event_errors, null, 'block-grid two-up') ?>
				</fieldset>
			</div>

		<?php
		echo Form::close();

		echo $this->javascript();

		return ob_get_clean();
	}


	/**
	 * Get JavaScripts.
	 *
	 * @return  string
	 */
	public function javascript() {

		// Date picker options
		$datepicker = array(
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

		$venues = Model_Venue::factory()->find_all_autocomplete();

		// Venues, Maps and tickets
		return HTML::script_source('
head.ready("anqh", function() {

	// Datepicker
	$("input.date").datepicker(' . json_encode($datepicker) . ');

	// City autocomplete
	$("input[name=city_name]").autocompleteGeo();

	// Venue autocomplete
	var venues = ' . json_encode($venues) . ';
	$("input[name=venue_name]").autocompleteVenue({ source: venues });

	// Tickets
	$("#field-free").change(function() {
		this.checked ? $("input[name=price]").attr("disabled", "disabled") : $("input[name=price]").removeAttr("disabled");
	});

	// Venue
	$("#field-ug").change(function() {
		this.checked ? $("input[name=venue_name]").attr("disabled", "disabled") : $("input[name=venue_name]").removeAttr("disabled");
	});
});
');
	}


	/**
	 * Get available tags.
	 *
	 * @return  array
	 */
	public function tags() {
		$tags = array();
		$tag_group = new Model_Tag_Group('Music');
		if ($tag_group->loaded() && count($tag_group->tags())) {
			foreach ($tag_group->tags() as $tag) {
				$tags[$tag->id()] = $tag->name();
			}
		}

		return $tags;
	}

}
