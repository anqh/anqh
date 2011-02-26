<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Events
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<ol class="days">

	<?php foreach ($events as $date => $cities): ?>
	<li class="day group">

		<header>
			<?php echo HTML::box_day($date) ?>
		</header>

		<?php foreach ($cities as $city => $city_events): ?>

		<section class="city city-<?php echo URL::title($city) ?>">
			<?php if (false && !empty($city)): ?>

			<header>
				<h3><?php echo HTML::chars($city) ?></h3>
			</header>
			<?php endif; ?>

			<?php	foreach ($city_events as $event): $venue = $event->venue(); ?>

			<article>

				<header>
					<?php echo HTML::anchor(Route::model($event), HTML::chars($event->name), array('class' => 'hoverable')) ?>
					<small class="ago"><?php echo HTML::chars($event->city_name) ?></small>
				</header>
				<span class="details">

					<?php if ($event->price !== null && $event->price != -1)
						echo ($event->price == 0 ? __('Free entry') : '<var>' . Num::format($event->price, 2, true) . '&euro;</var>'); ?>

					<?php if ($event->venue_hidden): ?>
					@ <?php echo __('Underground') ?>
					<?php elseif ($venue): ?>
					@ <?php echo HTML::anchor(Route::model($venue), $venue->name) ?>
					<?php elseif ($event->venue_name): ?>
					@ <?php echo HTML::chars($event->venue_name) ?>
					<?php endif; ?>

					<?php if ($event->age && $event->age != -1)
						echo '(', __('Age limit :limit', array(':limit' => '<var>' . $event->age . '</var>')), ')' ?>

				</span>

				<?php if ($event->dj): ?>
				<p class="dj">
					<?php echo HTML::chars($event->dj) ?>
				</p>
				<?php endif; ?>

			</article><!-- /event -->

			<?php endforeach; ?>

		</section><!-- /city -->
		<?php endforeach; ?>

	</li><!-- /day -->
	<?php endforeach; ?>

</ol><!-- /days -->
