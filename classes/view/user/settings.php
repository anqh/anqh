<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * User_Settings
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
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

		echo Form::open(null, array('class' => 'row'));

?>

<div class="span4">
	<fieldset id="fields-basic">
		<legend><?php echo __('Basic information') ?></legend>

		<?php echo Form::control_group(
			Form::input('name', $this->user->name, array('class' => 'input-large')),
			array('name' => __('Name')),
			Arr::get($this->errors, 'name')) ?>

		<?= Form::control_group(
			Form::input('email', $this->user->email, array('class' => 'input-large')),
			array('email' => __('Email')),
			Arr::get($this->errors, 'email')) ?>

		<?= Form::control_group(
			Form::input('homepage', $this->user->homepage, array('class' => 'input-large')),
			array('homepage' => __('Homepage')),
			Arr::get($this->errors, 'homepage')) ?>

		<?php echo Form::radios_wrap('gender', array('f' => __('Female'), 'm' => __('Male')), $this->user, null, __('Gender'), $this->errors) ?>

		<?php echo Form::control_group(
			Form::input('dob', Date::format('DMYYYY', $this->user->dob), array('class' => 'date input-small', 'maxlengt' => 10, 'placeholder' => __('d.m.yyyy'))),
			array('dob' => __('Date of Birth')),
			Arr::get($this->errors, 'dob')) ?>

		<?php echo Form::control_group(
			Form::input('title', $this->user->title, array('class' => 'input-large')),
			array('title' => __('Title')),
			Arr::get($this->errors, 'title')) ?>

		<?php echo Form::control_group(
			Form::textarea('description', $this->user->description, array('class' => 'input-large', 'rows' => 3), true),
			array('description' => __('Description')),
			Arr::get($this->errors, 'description')) ?>

	</fieldset>
</div>

<div class="span4">
	<fieldset id="fields-contact">
		<legend><?php echo __('Contact information') ?></legend>

		<?= Form::control_group(
			Form::input('address_street', $this->user->address_street),
			array('address_street' => __('Street address')),
			Arr::get($this->errors, 'address_street')) ?>

		<?= Form::control_group(
			Form::input('address_zip', $this->user->address_zip),
			array('address_zip' => __('Zip code')),
			Arr::get($this->errors, 'address_zip')) ?>

		<?= Form::control_group(
			Form::input('address_city', $this->user->address_city),
			array('address_city' => __('City')),
			Arr::get($this->errors, 'address_city')) ?>

	</fieldset>

	<fieldset id="fields-forum">
		<legend><?php echo __('Forum settings') ?></legend>

		<?php echo Form::control_group(
			Form::textarea('signature', $this->user->signature, array('class' => 'input-large', 'rows' => 5), true),
			array('signature' => __('Signature')),
			Arr::get($this->errors, 'signature')) ?>

	</fieldset>
</div>

<fieldset class="span8 form-actions">
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

		return HTML::script_source('

		// Date picker
		head.ready("jquery-ui", function() {
			$("input[name=dob]").datepicker(' . json_encode($options) . ');
		});

		// Maps
		head.ready("jquery", function() {
			$("#fields-contact").append("<div id=\"map\">' . __('Loading map..') . '</div>");
		});

		head.ready("anqh", function() {
			$("#map").googleMap(' . ($this->user->latitude ? json_encode(array('marker' => true, 'lat' => $this->user->latitude, 'long' => $this->user->longitude)) : '') . ');

			$("input[name=address_city]").autocompleteGeo();

			$("input[name=address_street], input[name=address_city]").blur(function(event) {
				var address = $("input[name=address_street]").val()
				  , city    = $("input[name=address_city]").val();

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

	}
}
