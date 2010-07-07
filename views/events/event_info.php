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

<dl>
	<?php if (!empty($event->homepage)): ?>
	<dt><?php echo __('Homepage') ?></dt><dd><?php echo HTML::anchor($event->homepage) ?></dd>
	<?php endif; ?>

	<dt><?php echo __('Opening hours') ?></dt>
	<dd><?php echo HTML::time(Date('l ', $event->stamp_begin) . Date::format('DDMMYYYY', $event->stamp_begin), $event->stamp_begin, true) ?></dd>
	<dd><?php echo $event->stamp_end ?
			__('From :from to :to', array(
				':from' => HTML::time(Date::format('HHMM', $event->stamp_begin), $event->stamp_begin),
				':to'   => HTML::time(Date::format('HHMM', $event->stamp_end), $event->stamp_end)
			)) :
			__('From :from onwards', array(
				':from' => HTML::time(Date::format('HHMM', $event->stamp_begin), $event->stamp_begin),
			)) ?></dd>

	<?php if ($event->venue->id): ?>
	<dt><?php echo __('Venue') ?></dt>
	<dd><?php echo HTML::anchor(Route::model($event->venue), HTML::chars($event->venue->name)) ?>, <?php echo HTML::chars($event->venue->city_name) ?></dd>
	<?php if ($event->venue->latitude && $event->venue->longitude): ?>
	<dd><?php echo HTML::anchor('#map', __('Toggle map')) ?></dd>
	<dt id="map" style="display: none"><?php echo __('Map loading') ?></dt>
	<?php
		$options = array(
			'marker'     => HTML::chars($event->venue->name),
			'infowindow' => HTML::chars($event->venue->address) . '<br />' . HTML::chars($event->venue->city_name),
			'lat'        => $event->venue->latitude,
			'long'       => $event->venue->longitude
		);
		Widget::add('foot', HTML::script_source('
		$(function() {
			$(".event-info a[href=#map]").click(function() { $("#map").toggle("fast", function() { $("#map").googleMap(' .  json_encode($options) . '); }); return false; });
		});
		'));
	endif; ?>

	<?php elseif ($event->venue_name): ?>
	<dt><?php echo __('Venue') ?></dt><dd><?php echo ($event->venue_url ?
		HTML::anchor($event->venue_url, HTML::chars($event->venue_name)) :
		HTML::chars($event->venue_name)) .
		($event->city_name ? ', ' . HTML::chars($event->city_name) : '') ?></dd>
	<?php elseif ($event->city_name): ?>
	<dt><?php echo __('City') ?></dt><dd><?php echo HTML::chars($event->city_name) ?></dd>
	<?php endif; ?>

	<?php if ($event->age > 0): ?>
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

	<dt><?php echo __('Added') ?></dt><dd><?php echo HTML::time(Date::format('DMYYYY', $event->created), $event->created) ?></dd>
	<?php if ($event->modified): ?>
	<dt><?php echo __('Modified') ?></dt><dd><?php echo HTML::time(Date::format('DMYYYY', $event->modified), $event->modified) ?></dd>
	<?php endif ?>

</dl>
