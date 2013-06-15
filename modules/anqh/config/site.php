<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Site config
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
return array(

	/**
	 * Site name
	 */
	'site_name' => 'Anqh',

	/**
	 * Domain name for static images, for CDN like
	 */
	'image_server' => 'images.domain.tld',

	/**
	 * Set the site as invite only
	 */
	'inviteonly' => false,

	/**
	 * E-mail address of the invitation sending
	 */
	'email_invitation' => 'noreply@domain.tld',

	/**
	 * Google configs
	 */
	'google_api_key'   => false,
	'google_analytics' => false, // UA-123456-7

	/**
	 * Facebook configs
	 */
	'facebook' => false, // Facebook App id

	/**
	 * Foursquare configs
	 */
	'foursquare_client_id'     => null,
	'foursquare_client_secret' => null,

	/**
	 * Twitter configs
	 */
	'twitter_username' => null,

	/**
	 * 3rd party share id
	 */
	'share' => false,

	/**
	 * Main menu
	 */
	'menu' => array(
//		'home'      => array('url' => URL::site(),                          'text' => __('Home')),
		'events'     => array('url' => URL::site(Route::url('events')),      'text' => __('Events'),     'icon' => 'icon-calendar'),
		'forum'      => array('url' => URL::site(Route::url('forum_group')), 'text' => __('Forum'),      'icon' => 'icon-comment'),
		'galleries'  => array('url' => URL::site(Route::url('galleries')),   'text' => __('Galleries'),  'icon' => 'icon-picture'),
		'venues'     => array('url' => URL::site(Route::url('venues')),      'text' => __('Venues'),     'icon' => 'icon-map-marker'),
		'charts'     => array('url' => URL::site(Route::url('charts')),      'text' => __('Charts'),     'icon' => 'icon-music'),
		'blogs'      => array('url' => URL::site(Route::url('blogs')),       'text' => __('Blogs'),      'icon' => 'icon-book'),
		'members'    => array('url' => URL::site(Route::url('users')),       'text' => __('Members'),    'icon' => 'icon-user'),
		'developers' => array('url' => URL::site(Route::url('developers')),  'text' => __('Developers'), 'icon' => 'icon-gift', 'footer' => true),
	),

	/**
	 * Available skins
	 */
	'skins' => array(
		'light' => array(
			'name' => __('Light'),
		),
		'dark' =>array(
			'name' => __('Dark'),
		),
	),

	/**
	 * Default skin
	 */
	'skin' => 'dark',

	/**
	 * Ad zones
	 */
	'ads' => array(
		'enabled' => false,
		'slots' => array(
			'header' => 'head',
			'side'   => 'side_ads',
		),
	),

	/**
	 * Smileys
	 */
	'smiley' => array(
		'dir' => 'smiley',
		'smileys' => array(
//			'smileyname'     => array('src' => 'smileyname.gif'),
		),
	),

);
