<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Galleires
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<ul>

<?php foreach ($galleries as $gallery): ?>
	<li class="unit size1of2">
		<article>
			<header>
				<?= HTML::anchor(Route::model($gallery), HTML::chars($gallery->name)) ?>
			</header>
			<div class="thumb unit size1of3">
				<?php echo HTML::anchor(Route::model($gallery), HTML::image('http://' . Kohana::config('site.image_server') . '/kuvat/' . $gallery->dir . '/thumb_' . $gallery->default_image->legacy_filename)) ?>
			</div>
			<div class="">
				<?php echo HTML::time(
					Date::format('DMYYYY', $gallery->date),
					array(
						'datetime' => $gallery->date,
						'title'    => __('Updated :updated', array(':updated' => Date::format('DMYYYY_HM', $gallery->modified)))
					),
					true) ?><br />
				<?php	if ($gallery->copyright):
					$copyrights = explode(',', $gallery->copyright);
					foreach ($copyrights as &$copyright) $copyright = HTML::user(trim($copyright));
					echo __('Copyright'), ': ', implode(', ', $copyrights), '<br />';
				endif; ?>
				<?php echo HTML::icon_value(array(':images' => $gallery->image_count), ':images images', ':images images', 'images') ?><br />
				<?php if ($gallery->comment_count > 0)
					echo HTML::icon_value(array(':comments' => $gallery->comment_count), ':comments comment', ':comments comments', 'comments'), '<br />'; ?>
				<?php if ($gallery->rate_count > 0)
					echo HTML::rating($gallery->rate_total, $gallery->rate_count), '<br />' ?>
			</div>
		</article>
	</li>
<?php endforeach; ?>

</ul>
