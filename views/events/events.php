<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Events
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<ol class="days">

	<?php foreach ($events as $date => $cities): ?>
	<li class="day">

		<header>
			<?php echo HTML::box_day($date) ?>
		</header>

		<?php foreach ($cities as $city => $city_events): ?>

		<div class="city city-<?php echo URL::title($city) ?>">
			<?php if (!empty($city)): ?>

			<header>
				<h3><?php echo HTML::chars($city) ?></h3>
			</header>
			<?php endif; ?>

			<?php	foreach ($city_events as $event): ?>

			<article class="event event-<?php echo $event->id ?>">

				<header>
					<?php echo HTML::anchor(Route::model($event), HTML::chars($event->name)) ?>
				</header>

				<?php if ($event->price !== null && $event->price != -1): ?>
				<span class="details price"><?php echo ($event->price == 0 ? __('Free entry') : '<var>' . Num::format($event->price, 2, true) . '</var>') ?></span>
				<?php endif; ?>

				<?php if ($event->venue_id): ?>
				<span class="details venue">@ <?php echo HTML::anchor(Route::model($event->venue), $event->venue->name) ?>, <?php echo HTML::chars($event->venue->city->name) ?></span>
				<?php elseif ($event->venue_name || $event->city_name): ?>
				<span class="details venue">@ <?php echo HTML::chars($event->venue_name) . ($event->venue_name && $event->city_name ? ', ' : '') . HTML::chars($event->city_name) ?></span>
				<?php endif; ?>

				<?php if ($event->age && $event->age != -1): ?>
				<span class="details age">(<?php echo __('Age limit :limit', array(':limit' => '<var>' . $event->age . '</var>')) ?>)</span>
				<?php endif; ?>

				<?php if ($event->dj): ?>
				<div class="dj"><?php echo HTML::chars($event->dj) ?></div>
				<?php endif; ?>

			</article><!-- /event -->

			<?php endforeach; ?>

		</div><!-- /city -->
		<?php endforeach; ?>

	</li><!-- /day -->
	<?php endforeach; ?>

</ol><!-- /days -->
