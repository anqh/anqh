<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh User controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_User extends Controller_Template {

	/**
	 * Action: comment
	 */
	public function action_comment() {
		$comment_id = (int)$this->request->param('id');
		$action     = $this->request->param('commentaction');

		// Load blog_comment
		$comment = Jelly::select('user_comment')->load($comment_id);
		if (($action == 'delete' || $action == 'private') && Security::csrf_valid() && $comment->loaded()) {
			$user = $comment->user;
			switch ($action) {

				// Delete comment
				case 'delete':
			    if (Permission::has($comment, Model_User_Comment::PERMISSION_DELETE, self::$user)) {
				    $comment->delete();
				    $user->comment_count--;
				    $user->save();
			    }
			    break;

				// Set comment as private
			  case 'private':
				  if (Permission::has($comment, Model_User_Comment::PERMISSION_UPDATE, self::$user)) {
					  $comment->private = true;
					  $comment->save();
				  }
			    break;

			}
			if (!$this->ajax) {
				$this->request->redirect(Route::get('user')->uri(array('username' => urlencode($user->username))));
			}
		}

		if (!$this->ajax) {
			Request::back(Route::get('users')->uri());
		}
	}


	/**
	 * Action: hover card
	 */
	public function action_hover() {

		// Hover card works only with ajax
		if (!$this->ajax) {
			return $this->action_index();
		}

		$user = Model_User::find_user(urldecode((string)$this->request->param('username')));
		if ($user)	{
			echo View_Module::factory('user/hovercard', array(
				'mod_title' => HTML::chars($user->username),
				'user'      => $user
			));
		} else {
			echo __('Member not found o_O');
		}
	}


	/**
	 * Action: image
	 */
	public function action_image() {
		$this->history = false;

		$user = $this->_get_user();
		Permission::required($user, Model_User::PERMISSION_UPDATE, self::$user);

		if (!$this->ajax) {
			$this->_set_page($user);
		}

		// Change existing
		if (isset($_REQUEST['default'])) {
			/** @var  Model_Image  $image */
			$image = Jelly::select('image')->load((int)$_REQUEST['default']);
			if (Security::csrf_valid() && $image->loaded() && $user->has('images', $image)) {
				$user->default_image = $image;
				$user->picture = $image->get_url();
				$user->save();
			}
			$cancel = true;
		}

		// Delete existing
		if (isset($_REQUEST['delete'])) {
			/** @var  Model_Image  $image */
			$image = Jelly::select('image')->load((int)$_REQUEST['delete']);
			if (Security::csrf_valid() && $image->loaded() && $image->id != $user->default_image->id && $user->has('images', $image)) {
				$user->remove('images', $image);
				$user->picture = null;
				$user->save();
				$image->delete();
			}
			$cancel = true;
		}

		// Cancel change
		if (isset($cancel) || isset($_REQUEST['cancel'])) {
			if ($this->ajax) {
				echo $this->_get_mod_image($user);
				return;
			}

			$this->request->redirect(URL::user($user));
		}

		$image = Jelly::factory('image')->set(array(
			'author' => $user,
		));

		// Handle post
		$errors = array();
		if ($_POST && $_FILES && Security::csrf_valid()) {
			$image->file = Arr::get($_FILES, 'file');
			try {
				$image->save();

				// Add exif, silently continue if failed - not critical
				try {
					Jelly::factory('image_exif')
						->set(array('image' => $image))
						->save();
				} catch (Kohana_Exception $e) { }

				// Set the image as user image
				$user->add('images', $image);
				$user->default_image = $image;
				$user->picture = $image->get_url(); // @TODO: Legacy, will be removed after migration
				$user->save();

				// Newsfeed
				NewsfeedItem_User::default_image($user, $image);

				if ($this->ajax) {
					echo $this->_get_mod_image($user);
					return;
				}

				$this->request->redirect(URL::user($user));

			} catch (Validate_Exception $e) {
				$errors = $e->array->errors('validation');
			} catch (Kohana_Exception $e) {
				$errors = array('file' => __('Failed with image'));
			}
		}

		// Build form
		$form = array(
			'ajaxify'    => $this->ajax,
			'values'     => $image,
			'errors'     => $errors,
			'attributes' => array('enctype' => 'multipart/form-data'),
			'cancel'     => $this->ajax ? URL::user($user, 'image') . '?cancel' : URL::user($user),
			'groups'     => array(
				array(
					'fields' => array(
						'file' => array(),
					),
				),
			)
		);

		$view = View_Module::factory('form/anqh', array(
			'mod_title' => __('Add image'),
			'form'      => $form
		));

		if ($this->ajax) {
			echo $view;
			return;
		}

		Widget::add('main', $view);
	}


	/**
	 * Controller default action
	 */
	public function action_index() {
		$user = $this->_get_user();

		// Set generic page parameters
		$this->_set_page($user);

		// Helper variables
		$owner = (self::$user && self::$user->id == $user->id);

		// Comments section
		if (Permission::has($user, Model_User::PERMISSION_COMMENTS, self::$user)) {
			$errors = array();
			$values = array();

			// Handle comment
			if (Permission::has($user, Model_User::PERMISSION_COMMENT, self::$user) && $_POST) {
				$comment = Jelly::factory('user_comment');
				$comment->user       = $user;
				$comment->author     = self::$user;
				$comment->set(Arr::intersect($_POST, Model_User_Comment::$editable_fields));
				try {
					$comment->save();

					// Receiver
					$user->comment_count++;
					if (!$owner) {
						$user->new_comment_count++;
					}
					$user->save();

					// Sender
					self::$user->left_comment_count++;
					self::$user->save();

					if (!$this->ajax) {
						$this->request->redirect(Route::get('user')->uri(array('username' => urlencode($user->username))));
					}
				} catch (Validate_Exception $e) {
					$errors = $e->array->errors('validation');
					$values = $comment;
				}

			}

			// Pagination
			$per_page = 25;
			$pagination = Pagination::factory(array(
				'items_per_page' => $per_page,
				'total_items'    => max(1, $user->get('comments')->viewer(self::$user)->count()),
			));

			$view = View_Module::factory('generic/comments', array(
				'mod_title'  => __('Comments'),
				'delete'     => Route::get('user_comment')->uri(array('id' => '%d', 'commentaction' => 'delete')) . '?token=' . Security::csrf(),
				'private'    => Route::get('user_comment')->uri(array('id' => '%d', 'commentaction' => 'private')) . '?token=' . Security::csrf(),
				'comments'   => $user->get('comments')->viewer(self::$user)->pagination($pagination)->execute(),
				'errors'     => $errors,
				'values'     => $values,
				'pagination' => $pagination,
				'user'       => self::$user,
			));

			if ($this->ajax) {
				echo $view;
				return;
			}
			Widget::add('main', $view, Widget::BOTTOM);
		}

		// Display news feed
		$newsfeed = new NewsFeed($user, Newsfeed::PERSONAL);
		$newsfeed->max_items = 5;
		Widget::add('main', View_Module::factory('generic/newsfeed', array(
			'newsfeed' => $newsfeed->as_array(),
			'mini'     => true
		)), Widget::TOP);

		// Slideshow
		if (count($user->images) > 1) {
			$images = array();
			foreach ($user->images as $image) $images[] = $image;
			Widget::add('side', View_Module::factory('generic/image_slideshow', array(
				'images'     => array_reverse($images),
				'classes'    => array($user->default_image->id => 'default active'),
			)));
		}

		// Portrait
		Widget::add('side', $this->_get_mod_image($user));

		// Info
		Widget::add('side', View_Module::factory('user/info', array(
			'user' => $user,
		)));

	}


	/**
	 * Action: settings
	 */
	public function action_settings() {
		$this->history = false;

		$user = $this->_get_user();
		Permission::required($user, Model_User::PERMISSION_UPDATE, self::$user);

		// Set generic page parameters
		$this->_set_page($user);

		// Handle post
		$errors = array();
		if ($_POST && Security::csrf_valid()) {
			$user->set(Arr::intersect($_POST, Model_User::$editable_fields));

			// GeoNames
			if ($_POST['city_id'] && $city = Geo::find_city((int)$_POST['city_id'])) {
				$user->city = $city;
			}

			$user->modified = time();

			try {
				$user->save();
				$this->request->redirect(URL::user($user));
			} catch (Validate_Exception $e) {
				$errors = $e->array->errors('validation');
			}
		}

		// Build form
		$form = array(
			'values' => $user,
			'errors' => $errors,
			'cancel' => URL::user($user),
			'hidden' => array(
				'city_id'   => $user->city ? $user->city->id : 0,
				'latitude'  => $user->latitude,
				'longitude' => $user->longitude,
			),
			'groups' => array(
				'basic' => array(
					'header' => __('Basic information'),
					'fields' => array(
						'name'   => array(),
						'gender' => array(
							'input' => 'radio',
						),
						'dob' => array(
							'pretty_format' => 'j.n.Y',
						),
						'title'       => array(),
						'description' => array(
							'attributes' => array(
								'rows' => 5
							)
						),
					),
				),
				'contact' => array(
					'header' => __('Contact information'),
					'fields' => array(
						'email'    => array(),
						'homepage' => array(),
						'address_street' => array(),
						'address_zip'    => array(),
						'address_city'   => array(),
					),
				),
				'forum' => array(
					'header' => __('Forum settings'),
					'fields' => array(
						'signature' => array(
							'attributes' => array(
								'rows' => 5
							)
						),
					)
				)
			)
		);

		Widget::add('main', View_Module::factory('form/anqh', array('form' => $form)));

		// Autocomplete
		$this->autocomplete_city('address_city', 'city_id');

		// Date picker
		$options = array(
			'changeMonth'     => true,
			'changeYear'      => true,
			'dateFormat'      => 'd.m.yy',
			'defaultDate'     => date('j.n.Y', $user->dob),
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
		Widget::add('foot', HTML::script_source('$("#field-dob").datepicker(' . json_encode($options) . ');'));

		// Maps
		Widget::add('foot', HTML::script_source('
$(function() {
	$("#fields-contact ul").append("<li><div id=\"map\">' . __('Loading map..') . '</div></li>");

	$("#map").googleMap(' . ($user->latitude ? json_encode(array('marker' => true, 'lat' => $user->latitude, 'long' => $user->longitude)) : '') . ');

	$("input[name=address_street], input[name=address_city]").blur(function(event) {
		var address = $("input[name=address_street]").val();
		var city = $("input[name=address_city]").val();
		if (address != "" && city != "") {
			var geocode = address + ", " + city;
			geocoder.geocode({ address: geocode }, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK && results.length) {
				  map.setCenter(results[0].geometry.location);
				  $("input[name=latitude]").val(results[0].geometry.location.lat());
				  $("input[name=longitude]").val(results[0].geometry.location.lng());
				  var marker = new google.maps.Marker({
				    position: results[0].geometry.location,
				    map: map
				  });
				}
			});
		}
	});

});
'));
	}


	/**
	 * Get image mod
	 *
	 * @param   Model_User  $user
	 * @return  View_Module
	 */
	protected function _get_mod_image(Model_User $user) {
		if ($user->default_image->id) {
			$image = $user->default_image;
		} else if (Validate::url($user->picture)) {
			$image = $user->picture;
		} else {
			$image = null;
		}

		return View_Module::factory('generic/side_image', array(
			'mod_actions2' => Permission::has($user, Model_User::PERMISSION_UPDATE, self::$user)
				? array(
						array('link' => URL::user($user, 'image') . '?token=' . Security::csrf() . '&delete', 'text' => __('Delete'), 'class' => 'image-delete disabled'),
						array('link' => URL::user($user, 'image') . '?token=' . Security::csrf() . '&default', 'text' => __('Set as default'), 'class' => 'image-change disabled', 'data-change' => 'default'),
						array('link' => URL::user($user, 'image'), 'text' => __('Add image'), 'class' => 'image-add ajaxify')
					)
				: null,
			'image' => $image,
		));
	}


	/**
	 * Get user or redirect to user list
	 *
	 * @param   boolean  $redirect
	 * @return  Model_User
	 */
	protected function _get_user($redirect = true) {

		// Get our user, default to logged in user if no username given
		$username = urldecode((string)$this->request->param('username'));
		$user = ($username == '') ? self::$user : Model_User::find_user($username);
		if (!$user && $redirect)	{
			$this->request->redirect(Route::get('users')->uri());
		}

		return $user;
	}


	/**
	 * Set generic page parameters
	 *
	 * @param   Model_User  $user
	 */
	protected function _set_page(Model_User $user) {

		// Set page title
		$this->page_title = HTML::chars($user->username);
		if ($user->title) {
			$this->page_subtitle = HTML::chars($user->title);
		}

		// Set actions
		if (Permission::has($user, Model_User::PERMISSION_UPDATE, self::$user)) {
			$this->page_actions[] = array('link' => URL::user($user, 'settings'), 'text' => __('Settings'), 'class' => 'settings');
		}

	}

}
