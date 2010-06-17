<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Event info
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<?php if ($event->flyer_front_image_id || $event->flyer_back_image_id): ?>
<article class="flyers">
	<?php echo $event->flyer_front_image_id ? HTML::img($event->flyer_front, 'normal', array('title' => __('Flyer, front'), 'width' => '100%')) : '' ?>
	<?php echo $event->flyer_back_image_id  ? HTML::img($event->flyer_back,  'normal', array('title' => __('Flyer, back'),  'width' => '100%')) : '' ?>
</article>
<?php endif; ?>

<?php if ($event->flyer_front_url || $event->flyer_back_url): ?>
<article class="flyers">
	<?php echo $event->flyer_front_url ? HTML::image($event->flyer_front_url, array('alt' => __('Flyer, front'), 'title' => __('Flyer, front'), 'width' => '100%')) : '' ?>
	<?php echo $event->flyer_back_url  ? HTML::image($event->flyer_back_url,  array('alt' => __('Flyer, back'),  'title' => __('Flyer, back'),  'width' => '100%')) : '' ?>
</article>
<?php endif; ?>

<article class="information">
	<header>
		<h3><?php echo __('Event information') ?></h3>
	</header>

	<dl>
		<?php if (!empty($event->homepage)): ?>
		<dt><?php echo __('Homepage') ?></dt><dd><?php echo HTML::anchor($event->homepage) ?></dd>
		<?php endif; ?>

		<dt><?php echo __('Opening hours') ?></dt><dd><?php echo $event->end_time ?
				__('From :from to :to', array(
					':from' => HTML::time(date::format('HHMM', $event->start_time), $event->start_time),
					':to'   => HTML::time(date::format('HHMM', $event->end_time), $event->end_time))
				) :
				__('From :from onwards', array(
					':from' => HTML::time(date::format('HHMM', $event->start_time), $event->start_time))
				) ?></dd>

		<?php if ($event->venue_id): ?>
		<dt><?php echo __('Venue') ?></dt><dd><?php echo HTML::anchor(url::model($event->venue), $event->venue->name) ?>, <?php echo HTML::chars($event->venue->city_name) ?></dd>
		<?php elseif ($event->venue_name): ?>
		<dt><?php echo __('Venue') ?></dt><dd><?php echo ($event->venue_url ?
			HTML::anchor($event->venue_url, $event->venue_name) :
			HTML::chars($event->venue_name)) .
			($event->city_name ? ', ' . HTML::chars($event->city_name) : '') ?></dd>
		<?php elseif ($event->city_name): ?>
		<dt><?php echo __('City') ?></dt><dd><?php echo HTML::chars($event->city_name) ?></dd>
		<?php endif; ?>

		<?php if (!empty($event->age)): ?>
		<dt><?php echo  __('Age limit') ?></dt><dd><?php echo __(':years years', array(':years' => '<var>' . $event->age . '</var>')) ?></dd>
		<?php endif; ?>

		<?php if ($event->price == 0): ?>
		<dt><?php echo __('Tickets') ?></dt><dd><?php echo __('Free entry') ?></dd>
		<?php elseif ($event->price > 0): ?>
		<dt><?php echo __('Tickets') ?></dt>
		<dd><?php echo __(':price by the door', array(':price' => '<var>' . Num::format($event->price, 2, true) . '</var>')) ?></dd>
		<?php echo $event->price2 !== null ? '<dd>' . __('presale :price', array(':price' => '<var>' . Num::format($event->price2, 2, true) . '</var>')) . '</dd>' : '' ?>
		<?php endif; ?>

		<?php if (count($event->tags)): ?>
		<dt><?php echo __('Music') ?></dt><dd><?php foreach ($event->tags as $tag): ?><?php echo $tag->name ?> <?php endforeach; ?></dd>
		<?php endif; ?>
		<?php if (!empty($event->music)): ?>
		<dt><?php echo __('Music') ?></dt><dd><?php echo $event->music ?></dd>
		<?php endif; ?>

	</dl>
</article>

<?php if (count($event->find_favorites())): ?>
<article>
	<header>
		<h3><?php echo __('Favorites') ?></h3>
	</header>

	<?php echo View::factory('generic/users', array('viewer' => $user, 'users' => $event->find_favorites())) ?>

</article>
<?php endif; ?>
