<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Visitor view class
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Header_Visitor extends Kostache {

	/**
	 * Var method for user avatar.
	 *
	 * @return  string
	 */
	public function avatar() {
		return HTML::avatar(self::$user->avatar, self::$user->username, true);
	}


	/**
	 * Var method for date.
	 *
	 * @return  string
	 */
	public function date() {
		return Date::format(Date::DMY_MEDIUM);
	}


	/**
	 * Var method for user id.
	 *
	 * @return  string
	 */
	public function id() {
		return self::$user->id;
	}


	/**
	 * Initialize view module.
	 */
	public function _initialize() {
		$this->_routes['signin']  = Route::get('sign')->uri(array('action' => 'in'));
		$this->_routes['signout'] = Route::get('sign')->uri(array('action' => 'out'));
		$this->_routes['signup']  = Route::get('sign')->uri(array('action' => 'up'));
	}


	/**
	 * Var method for menu.
	 *
	 * @return  array
	 */
	public function menu() {
		$menu = array();

		$menu[] = array('type' => 'messages', 'link' => HTML::anchor(Forum::private_messages_url(), __('Private messages'), array('class' => 'icon private-message')));
		$menu[] = array('type' => 'friends',  'link' => HTML::anchor(URL::user(self::$user, 'friends'), __('Friends'), array('class' => 'icon friends')));
		$menu[] = array('type' => 'ignores',  'link' => HTML::anchor(URL::user(self::$user, 'ignores'), __('Ignores'), array('class' => 'icon ignores')));
		$menu[] = array('type' => 'settings', 'link' => HTML::anchor(URL::user(self::$user, 'settings'), __('Settings'), array('class' => 'icon settings')));
		if (self::$user->has_role('admin')) {
			$menu[] = array('type' => 'roles admin',    'link' => HTML::anchor(Route::get('roles')->uri(), __('Roles'), array('class' => 'icon role')));
			$menu[] = array('type' => 'tags admin',     'link' => HTML::anchor(Route::get('tags')->uri(), __('Tags'), array('class' => 'icon tag')));
			$menu[] = array('type' => 'profiler admin', 'link' => HTML::anchor('#debug', __('Profiler'), array('class' => 'icon profiler', 'onclick' => '$("div.kohana").toggle();')));
		}

		return $menu;
	}


	/**
	 * Var method for new message notifications.
	 *
	 * @return  array
	 */
	public function notifications() {
		$notifications = array();
		foreach (Anqh::notifications(self::$user) as $class => $link) {
			$notifications[] = array('class' => $class, 'link' => $link);
		}

		return $notifications;
	}


	/**
	 * Var method for skins.
	 *
	 * @return  string
	 */
	public function skins() {
		$skins = array();
		foreach (array('dawn', 'day', 'dusk', 'night') as $skin) {
			$skins[] = HTML::anchor(
				Route::get('setting')->uri(array('action' => 'skin', 'value' => $skin)),
				$skin,
				array('rel' => $skin)
			);
		}

		return $skins;
	}

	/**
	 * Var method for sunrise and sunset.
	 *
	 * @return  string
	 */
	public function sunrise() {
		if (self::$user && self::$user->latitude && self::$user->longitude) {
			$latitude  = self::$user->latitude;
			$longitude = self::$user->longitude;
		} else {
			$latitude  = 60.1829;
			$longitude = 24.9549;
		}
		$sun = date_sun_info(time(), $latitude, $longitude);
		$sunrise = __(':day, week :week | Sunrise: :sunrise | Sunset: :sunset', array(
			':day'     => strftime('%A'),
			':week'    => strftime('%V'),
			':sunrise' => Date::format(Date::TIME, $sun['sunrise']),
			':sunset'  => Date::format(Date::TIME, $sun['sunset'])
		));

		return HTML::chars($sunrise);
	}


	/**
	 * Var method for time.
	 *
	 * @return  string
	 */
	public function time() {
		return Date::format(Date::TIME);
	}


	/**
	 * Var method for user user.
	 *
	 * @return  string
	 */
	public function user() {
		return HTML::user(self::$user);
	}

}
