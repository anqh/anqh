<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Forum config
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
return array(

	// Number of posts per page
	'posts_per_page' => 20,

	// Number of topics in long topic lists
	'topics_per_page' => 20,

	/**
	 * Special settings for bound areas
	 */
	'binds' => array(
		'events_upcoming' => array(
			'name'  =>__('Upcoming events'),
			'link'  => __('Show event'),
			'model' => 'event',
			'view'  => array(
				'events/flyers',
				'events/event_info',
			)
		),
		'events_past' => array(
			'name'  => __('Past events'),
			'link'  => __('Show event'),
			'model' => 'event',
			'view'  => array(
				'events/flyers',
				'events/event_info',
			)
		),
	)

);
