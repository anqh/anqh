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
	'site_name'    => 'Anqh',

	/**
	 * Domain name for static images, for CDN like
	 */
	'image_server' => 'images.domain.tld',

	/**
	 * Set the site as invite only
	 */
	'inviteonly'   => false,

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
	 * Main menu
	 */
	'menu' => array(
		'home'    => array('url' => '/', 'text' => __('Home')),
		/*
		'events'  => array('url' => 'events',  'text' => __('Events')),
		'venues'  => array('url' => 'venues',  'text' => __('Venues')),
		'music'   => array('url' => 'music',   'text' => __('Music')),
		'forum'   => array('url' => Route::get('forum')->uri(),   'text' => __('Forum')),
		'blogs'   => array('url' => 'blogs',   'text' => __('Blogs')),
		'members' => array('url' => 'members', 'text' => __('Members')),
		*/
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
			'smileyname'     => array('src' => 'smileyname.gif'),
		),
	),

);
