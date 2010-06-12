<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Venue mini info
 *
 * @package    Venues
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<article class="venue venue-<?php echo $venue->id ?>">
	<header>
		<h4><?php echo HTML::anchor(Route::model($venue), $venue->name) ?></h4>
	</header>

	<footer>
		<?php if ($venue->default_image_id): ?>
		<?php HTML::anchor(Route::model($venue), HTML::img($venue->default_image, 'thumb'), array('style' => 'display:block; height:31px;')) ?>
		<?php endif; ?>
	</footer>
</article>
