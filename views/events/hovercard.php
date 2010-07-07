<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Hover card
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<?php echo HTML::time(Date('l ', $event->stamp_begin) . Date::format('DDMMYYYY', $event->stamp_begin), $event->stamp_begin, true) ?> @

<?php if ($event->venue->id): ?>
<?php echo HTML::anchor(Route::model($event->venue), HTML::chars($event->venue->name)) ?>, <?php echo HTML::chars($event->venue->city_name) ?>
<?php elseif ($event->venue_name): ?>
<?php echo ($event->venue_url
	?	HTML::anchor($event->venue_url, $event->venue_name)
	:	HTML::chars($event->venue_name))
	. ($event->city_name ? ', ' . HTML::chars($event->city_name) : '') ?>
<?php elseif ($event->city_name): ?>
<?php echo HTML::chars($event->city_name) ?>
<?php endif; ?>

<?php if (Validate::url($event->flyer_front_url)) echo HTML::image($event->flyer_front_url, array('width' => 160)) . '<br />'; ?>
