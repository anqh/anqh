<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Flyers
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<ul>
	<?php foreach ($flyers as $flyer): ?>

	<li class="grid2">
		<article>
			<header>
				<div class="thumb">
					<?php echo HTML::anchor(Route::get('flyer')->uri(array('id' => $flyer->id)), HTML::image($flyer->image->get_url('thumbnail'))) ?>
				</div>
				<h4><?= HTML::anchor(Route::get('flyer')->uri(array('id' => $flyer->id)), HTML::chars($flyer->event->name), array('title' => HTML::chars($flyer->event->name))) ?></h4>
			</header>
		</article>
	</li>

	<?php endforeach ?>

</ul>
