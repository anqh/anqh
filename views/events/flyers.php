<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Event flyers
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<?php if ($event->flyer_front->id || $event->flyer_back->id): ?>
	<?php echo $event->flyer_front->id ? HTML::img($event->flyer_front, 'normal', array('title' => __('Flyer, front'), 'width' => '100%')) : '' ?>
	<?php echo $event->flyer_back->id  ? HTML::img($event->flyer_back,  'normal', array('title' => __('Flyer, back'),  'width' => '100%')) : '' ?>
<?php elseif ($event->flyer_front_url || $event->flyer_back_url): ?>
	<?php echo $event->flyer_front_url ? HTML::image($event->flyer_front_url, array('alt' => __('Flyer, front'), 'title' => __('Flyer, front'), 'width' => '100%')) : '' ?>
	<?php echo $event->flyer_back_url  ? HTML::image($event->flyer_back_url,  array('alt' => __('Flyer, back'),  'title' => __('Flyer, back'),  'width' => '100%')) : '' ?>
<?php else: ?>
	<?php echo __('No flyers :(') ?>
<?php endif; ?>
