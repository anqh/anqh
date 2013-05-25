<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * User_Settings
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_User_Settings extends View_Section {

	/**
	 * @var  array
	 */
	public $errors;

	/**
	 * @var  Model_User
	 */
	public $user;


	/**
	 * Create new view.
	 *
	 * @param  Model_User  $user
	 * @param  array       $errors
	 */
	public function __construct(Model_User $user, array $errors = null) {
		parent::__construct();

		$this->user   = $user;
		$this->errors = $errors;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		echo Form::open();

?>

<div class="row">

	<div class="span5">
		<fieldset id="fields-basic">
			<legend><?= __('Basic information') ?></legend>

			<?= Form::control_group(
				Form::input('name', $this->user->name, array('class' => 'input-block-level')),
				array('name' => __('Name')),
				Arr::get($this->errors, 'name')) ?>

			<?= Form::control_group(
				Form::input('email', $this->user->email, array('class' => 'input-block-level')),
				array('email' => __('Email')),
				Arr::get($this->errors, 'email')) ?>

			<?= Form::control_group(
				Form::input('homepage', $this->user->homepage, array('class' => 'input-block-level')),
				array('homepage' => __('Homepage')),
				Arr::get($this->errors, 'homepage')) ?>

			<?= Form::radios_wrap('gender',
				array('f' => __('Female'), 'm' => __('Male')),
				$this->user,
				null,
				__('Gender'),
				$this->errors,
				null,
				'inline') ?>

			<?= Form::control_group(
				'<div class="input-prepend"><span class="add-on"><i class="icon-calendar"></i></span>'
					. Form::input('dob', Date::format('DMYYYY', $this->user->dob), array('class' => 'date input-small', 'maxlength' => 10, 'placeholder' => __('d.m.yyyy')))
					. '</div>',
				array('dob' => __('Date of Birth')),
				Arr::get($this->errors, 'dob')) ?>

			<?= Form::control_group(
				Form::input('title', $this->user->title, array('class' => 'input-block-level')),
				array('title' => __('Title')),
				Arr::get($this->errors, 'title')) ?>

		</fieldset>
	</div>

	<div class="span5">
		<fieldset id="fields-contact">
			<legend><?= __('Location') ?></legend>

			<?= Form::control_group(
				Form::input('location', $this->user->location, array('id' => 'location', 'class' => 'input-block-level')),
				array('location' => __('Where are you')),
				Arr::get($this->errors, 'location'),
				__('e.g. <em>"Helsinki"</em> or <em>"Asema-aukio, Helsinki"</em>')) ?>

			<div id="map"></div>
		</fieldset>

		<fieldset id="fields-forum">
			<legend><?= __('Forum settings') ?></legend>

			<?= Form::control_group(
				Form::textarea('signature', $this->user->signature, array('class' => 'input-block-level', 'rows' => 5), true),
				array('signature' => __('Signature')),
				Arr::get($this->errors, 'signature')) ?>

		</fieldset>
	</div>
</div>

<fieldset class="form-actions">
	<?= Form::hidden('latitude', $this->user->latitude) ?>
	<?= Form::hidden('longitude', $this->user->longitude) ?>

	<?= Form::csrf() ?>
	<?= Form::button('save', __('Save'), array('type' => 'submit', 'class' => 'btn btn-success btn-large')) ?>
	<?= HTML::anchor(URL::user($this->user), __('Cancel'), array('class' => 'cancel')) ?>
</fieldset>

<?php

		echo Form::close();

		echo $this->javascript();

		return ob_get_clean();
	}


	/**
	 * Get Javascripts.
	 *
	 * @return  string
	 */
	protected function javascript() {

		// Date picker
		$options = array(
			'changeMonth'     => true,
			'changeYear'      => true,
			'dateFormat'      => 'd.m.yy',
			'defaultDate'     => date('j.n.Y', $this->user->dob),
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

		ob_start();

?>
<script>

	// Date picker
	head.ready('jquery-ui', function _datePicker() {
		$('input[name=dob]').datepicker(<?= json_encode($options) ?>);
	});

	// Maps
	head.ready('jquery-ui', function() {
		var center       = new google.maps.LatLng(<?= $this->user->latitude ? $this->user->latitude . ', ' . $this->user->longitude : '60.1695, 24.9355' ?>)
		  , mapOptions   = {
					center:                center,
					mapTypeControlOptions: { style: google.maps.MapTypeControlStyle.DROPDOWN_MENU },
					mapTypeId:             google.maps.MapTypeId.ROADMAP,
					zoom:                  14
				}
		  , map          = new google.maps.Map(document.getElementById('map'), mapOptions)
		  , marker       = new google.maps.Marker({
					map:      map,
					position: center
				})
		  , $input       = $('input[name=location]')
		  , $group       = $input.closest('.control-group')
		  , autocomplete = new google.maps.places.Autocomplete($input.get(0));

		// Disable submit on enter
		$input.on('keydown', function _select(e) {
			if (e.which == $.ui.keyCode.ENTER) {
				return false;
			}
		});

		autocomplete.bindTo('bounds', map);

		google.maps.event.addListener(autocomplete, 'place_changed', function _redrawMap() {
			$group.removeClass('warning');
			marker.setVisible(false);

			// Get location
			var place = autocomplete.getPlace();
			if (!place.geometry) {

				// Location not found
				$group.addClass('warning');

				return;
			}

			// Center map
			if (place.geometry.viewport) {
				map.fitBounds(place.geometry.viewport);
			} else {
				map.setCenter(place.geometry.location);
				map.setZoom(14);
			}

			var center = map.getCenter();
			$('input[name=latitude]').val(center.lat());
			$('input[name=longitude]').val(center.lng());

			// Show marker
			marker.setPosition(place.geometry.location);
			marker.setVisible(true);
		});

	});

</script>

<?php

		return ob_get_clean();
	}
}
