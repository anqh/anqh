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

			<div id="preview"></div>

			<div class="span7">

				<?php if ($this->event_errors || $this->venue_errors): ?>
				<div class="alert alert-error">
					<strong><?= __('Error happens!') ?></strong>
					<ul class="">
						<?php foreach ((array)$this->event_errors as $error): ?>
						<li><?= $error ?></li>
						<?php endforeach; ?>
						<?php foreach ((array)$this->venue_errors as $error): ?>
						<li><?= $error ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
				<?php endif; ?>

				<fieldset id="fields-primary">
					<?= Form::control_group(
						Form::input('name', $this->event->name, array('class' => 'input-block-level')),
						array('name' => __('Event')),
						Arr::get($this->event_errors, 'name')) ?>

					<?= Form::control_group(
						Form::textarea('dj', $this->event->dj, array('class' => 'input-block-level'), true),
						array('dj' => __('Line-up')),
						Arr::get($this->event_errors, 'dj')) ?>

					<?= Form::control_group(
						Form::textarea_editor('info', $this->event->info, array('class' => 'input-block-level'), true),
						array('info' => __('Other information')),
						Arr::get($this->event_errors, 'info')) ?>
				</fieldset>

				<fieldset class="form-actions">
					<?= Form::button('save', __('Save event'), array('type' => 'submit', 'class' => 'btn btn-success btn-large')) ?>
					<?= Form::button('preview', __('Preview'), array(
							'class'              => 'btn btn-inverse btn-large',
							'data-content-class' => '*',
							'data-prepend'       => '#preview',
						)) ?>
					<?= $this->cancel ? HTML::anchor($this->cancel, __('Cancel'), array('class' => 'cancel')) : '' ?>

					<?= Form::csrf() ?>
					<?= Form::hidden('latitude', $this->venue->latitude) ?>
					<?= Form::hidden('longitude', $this->venue->longitude) ?>
					<?= Form::hidden('venue_id', $this->venue->id) ?>
				</fieldset>
			</div>

			<div class="span3">

				<fieldset id="fields-when" class="row">
					<?= Form::control_group(
						Form::input(
							'stamp_begin[date]',
							is_numeric($this->event->stamp_begin) ? Date::format('DMYYYY', $this->event->stamp_begin) : $this->event->stamp_begin,
							array('class' => 'date', 'maxlength' => 10, 'placeholder' => 'd.m.yyyy')),
						array('stamp_begin[date]' => __('Date')),
						Arr::get($this->event_errors, 'stamp_begin'),
						null,
						array('class' => 'span1')) ?>

					<?= Form::control_group(
						Form::select(
							'stamp_begin[time]',
							array_reverse(Date::hours_minutes(30, true)),
							is_numeric($this->event->stamp_begin) ? Date::format('HHMM', $this->event->stamp_begin) : (empty($this->event->stamp_begin) ? '22:00' : $this->event->stamp_begin),
							array('class' => 'time')),
						array('stamp_begin[time]' => __('From')),
						Arr::get($this->event_errors, 'stamp_begin'),
						null,
						array('class' => 'span1')) ?>

					<?= Form::control_group(
						Form::select(
							'stamp_end[time]',
							Date::hours_minutes(30, true),
							is_numeric($this->event->stamp_end) ? Date::format('HHMM', $this->event->stamp_end) : (empty($this->event->stamp_end) ? '04:00' : $this->event->stamp_end),
							array('class' => 'time')),
						array('stamp_end[time]' => __('To')),
						Arr::get($this->event_errors, 'stamp_end'),
						null,
						array('class' => 'span1')) ?>
				</fieldset>

				<?= Form::control_group(
					Form::input('homepage', $this->event->homepage, array('class' => 'input-block-level', 'placeholder' => 'http://')),
					array('homepage' => __('Homepage')),
					Arr::get($this->event_errors, 'homepage')) ?>

				<fieldset id="fields-venue">
					<legend>
						<?= Form::control_group(
							'<label class="checkbox">'
								. Form::checkbox('venue_hidden', 'true', (bool)$this->event->venue_hidden, array('id' => 'field-ug'))
								. ' ' . __('Underground')
								. '</label>',
							null, null, null,
							array('class' => 'span2')) ?>

						<?= __('Venue') ?>
					</legend>
					<?= Form::control_group(
						Form::input('venue_name', $this->event->venue_name, (bool)$this->event->venue_hidden ? array('class' => 'input-block-level', 'disabled' => 'disabled') : array('class' => 'input-block-level')),
						array('venue_name' => __('Venue')),
						Arr::get($this->event_errors, 'venue_name'),
						null,
						array('class' => 'group-venue')) ?>

					<?= Form::control_group(
						Form::input('city_name', $this->event->city_name, array('class' => 'input-block-level')),
						array('city_name' => __('City')),
						Arr::get($this->event_errors, 'city_name')) ?>
				</fieldset>

				<fieldset id="fields-tickets">
					<legend>
						<?= Form::control_group(
							'<label class="checkbox">'
								. Form::checkbox('free', 'true', $this->event->price === '0.00', array('id' => 'field-free'))
								. ' ' . __('Free entry')
								. '</label>',
							null, null, null,
							array('class' => 'span2')) ?>

						<?= __('Tickets') ?>
					</legend>

					<?= Form::control_group(
						'<div class="input-append">'
							. Form::input('price', $this->event->price, $this->event->price === '0.00'
									? array('class' => 'input-mini', 'disabled' => 'disabled')
									: array('class' => 'input-mini')
								)
							. '<span class="add-on">&euro;</span>'
							. '</div>',
						array('price' => __('Tickets')),
						Arr::get($this->event_errors, 'price'),
						null,
						array('class' => 'group-price')) ?>

					<?= Form::control_group(
						Form::input('tickets_url', $this->event->tickets_url, array('class' => 'input-block-level', 'placeholder' => 'http://')),
						array('tickets_url' => __('Buy tickets from')),
						Arr::get($this->event_errors, 'tickets_url'),
						null,
						array('class' => 'group-price')) ?>

					<?= Form::control_group(
						'<div class="input-append">'
							. Form::input('age', $this->event->age, array('class' => 'input-mini'))
							. '<span class="add-on">years</span>'
							. '</div>',
						array('age' => __('Age limit')),
						Arr::get($this->event_errors, 'age')) ?>
				</fieldset>

				<fieldset id="fields-music">
					<legend><?= __('Music') ?></legend>
					<?= Form::checkboxes_wrap('tag', $this->tags(), $this->event->tags(), null, $this->event_errors, null, 'block-grid two-up') ?>
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
		ob_start();

?>
<script>
head.ready('anqh', function() {

	// Datepicker
	$('input.date').datepicker(<?= json_encode($datepicker) ?>);

	// City autocomplete
	$('input[name=city_name]').autocompleteGeo();

	// Venue autocomplete
	var venues = <?= json_encode($venues) ?>;
	$('input[name=venue_name]').autocompleteVenue({ source: venues });

	// Tickets
	$('#field-free').change(function _togglePrice() {
		if (this.checked) {
			$('input[name=price]').attr('disabled', 'disabled');
			$('.group-price').slideUp();
		} else {
			$('input[name=price]').removeAttr('disabled');
			$('.group-price').slideDown();
		}
	});

	// Venue
	$('#field-ug').change(function _toggleVenue() {
		if (this.checked) {
			$('input[name=venue_name]').attr('disabled', 'disabled');
			$('.group-venue').slideUp();
		} else {
			$('input[name=venue_name]').removeAttr('disabled');
			$('.group-venue').slideDown();
		}
	});
});
</script>
<?php

		return ob_get_clean();
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
