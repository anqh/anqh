<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Venues
 *
 * @package    Venues
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<?php if (count($venues)): ?>

	<?php	foreach ($venues as $city => $city_venues): ?>
		<header class="city">
			<h3 id="<?php echo HTML::chars(mb_strtolower($city)) ?>"><?php echo HTML::chars($city) ?></h3>
		</header>

		<?php foreach ($city_venues as $venue): ?>
		<?php echo View::factory('venues/mini', array('venue' => $venue)) ?>
		<?php endforeach; ?>

	<?php	endforeach; ?>

<?php else: ?>

<article class="empty">
	<?php echo __('No venues yet.') ?>
</article>

<?php endif; ?>
