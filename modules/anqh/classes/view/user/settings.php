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
	 * @var  OAuth2_Consumer
	 */
	public $consumer;

	/**
	 * @var  array
	 */
	public $errors;

	/**
	 * @var  Model_User_External
	 */
	public $external;

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

		$this->user     = $user;
		$this->errors   = $errors;
		$this->external = Model_User_External::factory()->find_by_user_id($this->user->id, 'facebook');
		if ($this->external && $this->external->loaded()) {
			$this->consumer = new OAuth2_Consumer('facebook', $this->external->access_token());
		}
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		$facebook = $this->consumer ? $this->load_facebook() : false;

		echo Form::open();

?>

<div class="row-fluid">

	<div class="span6">
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
					. Form::input('dob', $this->user->dob ? Date::format('DMYYYY', $this->user->dob) : null, array('class' => 'date input-small', 'maxlength' => 10, 'placeholder' => __('d.m.yyyy')))
					. '</div>',
				array('dob' => __('Date of Birth')),
				Arr::get($this->errors, 'dob')) ?>

		</fieldset>

		<fieldset id="fields-forum">
			<legend><?= __('Forum settings') ?></legend>

			<?= Form::control_group(
				Form::input('title', $this->user->title, array('class' => 'input-block-level')),
				array('title' => __('Title')),
				Arr::get($this->errors, 'title')) ?>

			<?= Form::control_group(
				Form::textarea('signature', $this->user->signature, array('class' => 'input-block-level', 'rows' => 5), true),
				array('signature' => __('Signature')),
				Arr::get($this->errors, 'signature')) ?>

		</fieldset>
	</div>

	<div class="span6">
		<fieldset id="fields-contact">
			<legend><?= __('Location') ?></legend>

			<?= Form::control_group(
				Form::input('location', $this->user->location, array('class' => 'input-block-level')),
				array('location' => __('Where are you')),
				Arr::get($this->errors, 'location'),
				__('e.g. <em>"Helsinki"</em> or <em>"Asema-aukio, Helsinki"</em>')) ?>

			<?= Form::control_group(
				Form::input('city_name', $this->user->city_name, array('class' => 'input-block-level')),
				array('city_name' => __('City')),
				Arr::get($this->errors, 'city_name')) ?>

			<div id="map"></div>
		</fieldset>

		<fieldset id="fields-connections">
			<legend>Facebook</legend>

		<?php if (!$this->external || !$this->external->loaded()): ?>

			<?= HTML::anchor(
					Route::url('oauth', array('action' => 'login', 'provider' => 'facebook')),
					'<i class="icon-facebook"></i> ' . __('Connect to Facebook'),
					array('class' => 'btn btn-primary', 'title' => __('Connect with your Facebook account'))
				) ?>

		<?php elseif (is_array($facebook)): $avatar = 'https://graph.facebook.com/' . $facebook['id'] . '/picture'; ?>

			<div class="media">
				<?= HTML::avatar($avatar, null, 'pull-left facebook') ?>
				<div class="media-body">
					<?= HTML::anchor($facebook['link'], HTML::chars($facebook['name']), array('target' => '_blank')) ?>
					<?= Form::checkbox_wrap('avatar', $avatar, $this->user->avatar == $avatar, null, __('Set as your avatar')) ?>
					<?= Form::checkbox_wrap('picture', $avatar . '?type=large', $this->user->picture == $avatar . '?type=large', null, __('Set as your profile image')) ?>
					<?= HTML::anchor(
								Route::url('oauth', array('action' => 'disconnect', 'provider' => 'facebook')),
								'<i class="icon-facebook"></i> ' . __('Disconnect your Facebook account'),
								array('class' => 'btn btn-danger facebook-delete', 'title' => __('Disconnect your Facebook account'))
							) ?>
				</div>
			</div>

		<?php elseif ($facebook): ?>

			<?= $facebook ?>

			<?= HTML::anchor(
						Route::url('oauth', array('action' => 'disconnect', 'provider' => 'facebook')),
						'<i class="icon-facebook"></i> ' . __('Disconnect your Facebook account'),
						array('class' => 'btn btn-danger facebook-delete', 'title' => __('Disconnect your Facebook account'))
					) ?>

		<?php endif; ?>

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

			// Show marker
			marker.setPosition(place.geometry.location);
			marker.setVisible(true);

			// Coordinates
			var center = map.getCenter();
			$('input[name=latitude]').val(center.lat());
			$('input[name=longitude]').val(center.lng());

			// City
			if (place.address_components) {
				for (var i in place.address_components) {
					var component = place.address_components[i];
					if (component.types[0] == 'locality' || component.types[1] == 'locality') {
						$('input[name=city_name]').val(component.long_name);
					}
				}
			}

		});

	});

</script>

<?php

		return ob_get_clean();
	}


	/**
	 * Load Facebook data.
	 *
	 * @return  View_Alert|array
	 */
	public function load_facebook() {
		if (!$this->consumer) {
			return '';
		}

		try {
			if ($response = $this->consumer->api_call('/' . $this->external->external_user_id . '?fields=id,name,link')) {

				// Received a response from 3rd party
				if ($error = Arr::get($response, 'error')) {

					Kohana::$log->add(Log::NOTICE, 'OAuth2: Failed to load Facebook profile: :error', array(':error' => $error->message));

					// .. but it was an error
					return new View_Alert(
						__('They said ":error"', array(':error' => HTML::chars($error->message))),
						__('Failed to load your profile :('),
						View_Alert::ERROR);


				} else {

					return $response;

				}

			} else {

				// No data received, this should be handled by exceptions
				return new View_Alert(
					__('No data received'),
					__('Failed to load your profile :('),
					View_Alert::ERROR);

			}

		} catch (Kohana_Exception $e) {

			Kohana::$log->add(Log::NOTICE, 'OAuth2: Exception: :error', array(':error' => $e->getMessage()));

			return new View_Alert(
				HTML::chars($e->getMessage()),
				__('Failed to load your profile :('),
				View_Alert::ERROR);

		}
	}

}
