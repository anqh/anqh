<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Flyers
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<ul>
	<?php foreach ($flyers as $flyer): $name = $flyer->event ? $flyer->event->name : $flyer->name ?>

	<li class="grid2">
		<article>
			<header>
				<div class="thumb">
					<?php echo HTML::anchor(Route::get('flyer')->uri(array('id' => $flyer->id)), HTML::image($flyer->image->get_url('thumbnail'))) ?>
				</div>
				<h4><?= HTML::anchor(Route::get('flyer')->uri(array('id' => $flyer->id)), HTML::chars($name), array('title' => HTML::chars($name))) ?></h4>
			</header>
		</article>
	</li>

	<?php endforeach ?>

</ul>
