<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Event list
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<ul class="events">

	<?php foreach ($events as $event): ?>
	<li class="event">
		<?php echo Date::format('DDMM', $event->stamp_begin) ?>
		<?php echo HTML::anchor(Route::model($event), $event->name) ?>
	</li>
	<?php endforeach; ?>

</ul>
