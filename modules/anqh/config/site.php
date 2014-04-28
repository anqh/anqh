<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Site config
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
return array(

	/** Site name */
	'site_name' => 'Anqh',

	/** Domain name for static images, for CDN like */
	'image_server' => 'images.domain.tld',

	/** Set the site as invite only */
	'inviteonly' => false,

	/** Email address for contact */
	'email_contact' => 'noreply@domain.tld',

	/** Email address of the invitation sending */
	'email_invitation' => 'noreply@domain.tld',

	/** Google configs */
	'google_api_key'          => false,
	'google_analytics'        => false, // UA-123456-7
	'google_analytics_domain' => false, // domain.tld

	/** Facebook configs */
	'facebook' => false, // Facebook App id

	/** Default open graph image */
	'og' => array(
		'image' => null,
	),

	/** Foursquare configs */
	'foursquare_client_id'     => null,
	'foursquare_client_secret' => null,

	/** Twitter configs */
	'twitter_username' => null,

	/** 3rd party share id */
	'share' => false,

	/** Main menu */
	'menu' => array(
		'home'       => array('url' => URL::site(),              'text' => __('Home'),       'icon' => 'fa fa-home', 'footer' => true),
		'events'     => array('url' => Route::url('events'),     'text' => __('Events'),     'icon' => 'fa fa-calendar'),
		'forum'      => array('url' => Route::url('forum'),      'text' => __('Forum'),      'icon' => 'fa fa-comments-o'),
		'galleries'  => array('url' => Route::url('galleries'),  'text' => __('Galleries'),  'icon' => 'fa fa-camera-retro'),
		'music'      => array('url' => Route::url('charts'),     'text' => __('Music'),      'icon' => 'fa fa-music'),
		'blogs'      => array('url' => Route::url('blogs'),      'text' => __('Blogs'),      'icon' => 'fa fa-book'),
		'venues'     => array('url' => Route::url('venues'),     'text' => __('Venues'),     'icon' => 'fa fa-map-marker'),
		'members'    => array('url' => Route::url('users'),      'text' => __('Members'),    'icon' => 'fa fa-group'),
		'developers' => array('url' => Route::url('developers'), 'text' => __('Developers'), 'icon' => 'fa fa-gift',       'footer' => true),
		'contact'    => array('url' => Route::url('contact'),    'text' => __('Contact'),    'icon' => 'fa fa-envelope-o', 'footer' => true),
	),

	'menu_visitor' => array(
		'profile'   => array('url' => URL::user(true),                              'text' => __('Profile'),          'icon' => 'fa fa-fw fa-user'),
		'messages'  => array('url' => Forum::private_messages_url(),                'text' => __('Private messages'), 'icon' => 'fa fa-fw fa-envelope'),
		'favorites' => array('url' => URL::user(true, 'favorites'),                 'text' => __('Favorites'),        'icon' => 'fa fa-fw fa-heart'),
		'friends'   => array('url' => URL::user(true, 'friends'),                   'text' => __('Friends'),          'icon' => 'fa fa-fw fa-group'),
		'ignores'   => array('url' => URL::user(true, 'ignores'),                   'text' => __('Ignores'),          'icon' => 'fa fa-fw fa-ban'),
		'settings'  => array('url' => URL::user(true, 'settings'),                  'text' => __('Settings'),         'icon' => 'fa fa-fw fa-cog'),
		'signout'   => array('url' => Route::url('sign', array('action' => 'out')), 'text' => __('Logout'),           'icon' => 'fa fa-fw fa-sign-out'),
	),

	'menu_admin' => array(
		'roles'    => array('url' => Route::url('roles'), 'text' => __('Roles'),    'icon' => 'fa fa-fw fa-asterisk'),
		'tags'     => array('url' => Route::url('tags'),  'text' => __('Tags'),     'icon' => 'fa fa-fw fa-tags'),
		'profiler' => array('url' => '#debug',            'text' => __('Profiler'), 'icon' => 'fa fa-fw fa-signal', 'attributes' => array('onclick' => "$('.kohana').toggle();")),
	),

	/** News area */
	'news' => array(
		'forum_area_id' => 1,
		'author_id'     => 1,
	),

	/** Available skins */
	'themes' => array(
		'light' => array(
			'name' => __('Light'),
			'icon' => 'fa fa-fw fa-circle-o'
		),
		'mixed' => array(
			'name' => 'Mixed',
			'icon' => 'fa fa-fw fa-adjust'
		),
		'dark' =>array(
			'name' => __('Dark'),
			'icon' => 'fa fa-fw fa-circle'
		),
	),

	/** Default skin */
	'theme' => 'dark',

	/** Ad zones */
	'ads' => array(
		'enabled' => false,
		'slots' => array(
			'header' => 'head',
			'side'   => 'side_ads',
		),
	),

	/** Smileys */
	'smiley' => array(
		'dir' => 'smiley',
		'smileys' => array(
//			'smileyname'     => array('src' => 'smileyname.gif'),
		),
	),

);
