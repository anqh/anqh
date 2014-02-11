<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Event edit form.
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Event_Edit extends View_Article {

	/** @var  string  URL for cancel action */
	public $cancel;

	/** @var  Model_Event */
	public $event;

	/** @var  array */
	public $event_errors;

	/** @var  string */
	public $flyer_error;

	/** @var  Model_Venue */
	public $venue;

	/** @var  array */
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

?>

<div id="preview"></div>

<?= Form::open(null, array('id' => 'form-event', 'class' => 'row')) ?>

<div class="col-md-8">

	<?php if ($this->event_errors || $this->venue_errors): ?>
	<div class="alert alert-danger">
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
		<?= Form::input_wrap(
				'name',
				$this->event->name,
				array('class' => 'input-lg'),
				__('Name'),
				Arr::get($this->event_errors, 'name'),
				__("Please don't use dates in the name, looks nasty everywhere as the date is usually shown automagically.")
		) ?>

		<?= Form::textarea_wrap(
				'info',
				$this->event->info,
				array('class' => 'input-lg', 'rows' => 20),
				true,
				__('Information'),
				Arr::get($this->event_errors, 'info'),
				__('Remember, only the first few rows are visible in the calendar view.'),
				true
		) ?>
	</fieldset>

	<fieldset>
		<?= Form::button('save', __('Save event'), array('type' => 'submit', 'class' => 'btn btn-success btn-lg')) ?>
		<?= Form::button('preview', __('Preview'), array(
				'class'              => 'btn btn-default btn-lg',
				'data-content-class' => '*',
				'data-prepend'       => '#preview',
			)) ?>
		<?= $this->cancel ? HTML::anchor($this->cancel, __('Cancel'), array('class' => 'cancel')) : '' ?>

		<?= Form::csrf() ?>
		<?= Form::hidden('latitude',  $this->venue->latitude,  array('data-geo' => 'lat')) ?>
		<?= Form::hidden('longitude', $this->venue->longitude, array('data-geo' => 'lng')) ?>
		<?= Form::hidden('city',      $this->event->city_name, array('data-geo' => 'locality')) ?>
		<?= Form::hidden('venue_id',  $this->venue->id) ?>
	</fieldset>
</div>

<div class="col-md-4">

	<fieldset id="fields-when" class="row form-inline">
		<div class="col-md-3">
			<?= Form::input_wrap(
				'stamp_begin[date]',
				is_numeric($this->event->stamp_begin) ? Date::format('DMYYYY', $this->event->stamp_begin) : $this->event->stamp_begin,
				array('class' => 'date', 'maxlength' => 10, 'size' => 7, 'placeholder' => 'd.m.yyyy'),
				__('From'),
				Arr::Get($this->event_errors, 'stamp_begin')
			) ?>
		</div>

		<div class="col-md-3">
			<?= Form::select_wrap(
				'stamp_begin[time]',
				array_reverse(Date::hours_minutes(30, true)),
				is_numeric($this->event->stamp_begin) ? Date::format('HHMM', $this->event->stamp_begin) : (empty($this->event->stamp_begin) ? '22:00' : $this->event->stamp_begin),
				array('class' => 'time'),
				'&nbsp;',
				Arr::get($this->event_errors, 'stamp_begin')
			) ?>
		</div>

		<div class="col-md-3">
			<?= Form::select_wrap(
				'stamp_end[time]',
				Date::hours_minutes(30, true),
				is_numeric($this->event->stamp_end) ? Date::format('HHMM', $this->event->stamp_end) : (empty($this->event->stamp_end) ? '04:00' : $this->event->stamp_end),
				array('class' => 'time'),
				__('To'),
				Arr::get($this->event_errors, 'stamp_end')
			) ?>
		</div>

		<div class="col-md-3">
			<?= Form::input_wrap(
				'stamp_end[date]',
				is_numeric($this->event->stamp_end) ? Date::format('DMYYYY', $this->event->stamp_end) : $this->event->stamp_end,
				array('class' => 'date', 'maxlength' => 10, 'size' => 7, 'placeholder' => 'd.m.yyyy'),
				'&nbsp;',
				Arr::Get($this->event_errors, 'stamp_end')
			) ?>
		</div>
	</fieldset>

	<br>

	<fieldset id="fields-venue" class="row">
		<div class="col-md-12">
			<?= Form::input_wrap(
				'city_name',
				$this->event->city_name,
				null,
				__('City'),
				Arr::get($this->event_errors, 'city_name')
			) ?>
		</div>

		<div class="col-md-7">
			<?= Form::input_wrap(
				'venue_name',
				$this->event->venue_name,
				(bool)$this->event->venue_hidden ? array('disabled') : null,
				__('Venue'),
				Arr::get($this->event_errors, 'venue_name')
			) ?>
		</div>

		<div class="col-md-5">
			<br>
			<?= Form::checkbox_wrap(
				'venue_hidden',
				'true',
				(bool)$this->event->venue_hidden,
				array('id' => 'field-ug'),
				__('Underground')
			) ?>
		</div>

		<div class="col-md-12 venue-placeholder hidden">
			<label><?= __('Venue') ?></label>
			<p>
				<span class="venue-name"><?= $this->event->venue_name ?></span>,
				<span class="venue-city"><?= $this->event->city_name ?></span>
				<a href="#venue"><?= __('Change') ?></a>
			</p>
		</div>
	</fieldset>

	<div id="map" class="well"></div>

	<fieldset>
		<?php if (!$this->event->flyer_front_image_id) echo
			Form::input_wrap(
					'flyer',
					$this->event->flyer_front_url,
					array('type' => 'url', 'placeholder' => 'http://'),
					__('Flyer'),
					$this->flyer_error,
					__('If you have the flyer only locally you can upload it after saving the event.')
		) ?>

		<?= Form::input_wrap(
			'homepage',
			$this->event->homepage,
			array('type' => 'url', 'placeholder' => 'http://'),
			__('Homepage'),
			Arr::get($this->event_errors, 'homepage')
		) ?>
	</fieldset>

	<fieldset id="fields-tickets" class="row">
		<div class="col-md-4">
			<?= Form::input_wrap(
				'price',
				$this->event->price,
				$this->event->price === '0.00' ? array('disabled', 'type' => 'number', 'min' => 0, 'step' => 0.5) : array('type' => 'number', 'min' => 0, 'step' => 0.5),
				__('Tickets'),
				Arr::get($this->event_errors, 'tickets'),
				null,
				'&euro;'
			) ?>
		</div>

		<div class="col-md-8">
			<br>
			<?= Form::checkbox_wrap(
				'free',
				'true',
					$this->event->price === '0.00',
				array('id' => 'field-free'),
				__('Free entry')
			) ?>
		</div>

		<div class="col-md-12">
			<?= Form::input_wrap(
				'tickets_url',
				$this->event->tickets_url,
				array('placeholder' => 'http://'),
				__('Buy tickets from'),
				Arr::get($this->event_errors, 'tickets_url')
			) ?>
		</div>

		<div class="col-md-5">
			<?= Form::input_wrap(
				'age',
				$this->event->age,
				array('type' => 'number', 'min' => 0, 'max' => 50, 'maxlength' => 3),
				__('Age limit'),
				Arr::get($this->event_errors, 'age'),
				null,
				__('years')
			) ?>
		</div>
	</fieldset>

	<fieldset id="fields-music">
		<?= Form::checkboxes_wrap(
				'tag',
				$this->tags(),
				$this->event->tags(),
				array('class' => 'block-grid three-up'),
				__('Music'),
				$this->event_errors
		) ?>
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
			'nextText'        => '&#9658;',
			'prevText'        => '&#9668;',
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

	// Tickets
	$('#field-free input').on('click', function() {
		console.log($(this).is(':checked'));
		if ($(this).is(':checked')) {
			$('input[name=price]').attr('disabled', 'disabled');
		} else {
			$('input[name=price]').removeAttr('disabled');
		}
	});

	// Venue
	$('#field-ug input').on('click', function() {
		console.log(this.checked);
		if ($(this).is(':checked')) {
			$('input[name=venue_name]').attr('disabled', 'disabled');
		} else {
			$('input[name=venue_name]').removeAttr('disabled');
		}
	});

	// Datepicker
	var pickerOptions = <?= json_encode($datepicker) ?>
	  , $dateBegin = $('input[name="stamp_begin[date]"]')
	  , $dateEnd   = $('input[name="stamp_end[date]"]');
	$dateEnd.datepicker(pickerOptions);
	$dateBegin.datepicker($.extend(pickerOptions, {
		onClose: function(dateText, inst) {
			var startDate = $dateBegin.datepicker('getDate');
			startDate.setDate(startDate.getDate() + 1);
			$dateEnd.datepicker('setDate', startDate);
		}
	}));

	// City autocomplete
/*	$('input[name=city_name]').placecomplete({
		requestParams: {
			types: [ '(cities)' ]
		}
	});*/
	$('input[name=city_name]').geocomplete({
		map:              '#map',
		details:          '#form-event',
		detailsAttribute: 'data-geo',
		location:         '<?= $this->event->city_name ? $this->event->city_name : 'Helsinki' ?>',
		types:            [ '(cities)' ]
	});
	//$('input[name=city_name]').autocompleteGeo();

	// Venue autocomplete
	function toggleVenue(toggle) {
		$('#fields-venue div').toggleClass('hidden', toggle);
		$('#fields-venue .venue-placeholder').toggleClass('hidden', !toggle);
	}

	var venues = <?= json_encode($venues) ?>;
	$('input[name=venue_name]')
		.on('change', function() {
			$('input[name=venue_id]').val('');
		})
		.on('typeahead:selected', function(event, selection, name) {

			// Update form
			$('input[name=venue_id]').val(selection.id);
			if (selection.latitude && selection.longitude) {
				$('input[name=latitude]').val(selection.latitude);
				$('input[name=longitude]').val(selection.longitude);
				$('#map').googleMap({
					lat:    selection.latitude,
					long:   selection.longitude,
					marker: true
				});
			} else {
				$('#map').googleMap({
					city:   selection.city,
					marker: true
				});
			}
			$('input[name=city_name], input[name=city]').val(selection.city);

			// Update label
			$('#fields-venue .venue-name').text(selection.label);
			$('#fields-venue .venue-city').text(selection.city);
			toggleVenue(true);
		})
		.typeahead({
			name:       'venue',
			displayKey: 'label',
			local:      venues
		});

	$('.venue-placeholder a').on('click', function() {
		toggleVenue(false);

		return false;
	});

	<?php if (!$this->venue_errors && $this->event->venue_name && $this->event->city_name): ?>
	toggleVenue(true);
	<?php endif; ?>
	//$('input[name=venue_name]').autocompleteVenue({ source: venues });

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
