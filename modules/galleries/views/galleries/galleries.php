<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Galleires
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

/** @var  Model_Gallery $gallery */
?>

<ul>

<?php foreach ($galleries as $gallery): $default_image = $gallery->default_image(); ?>
	<li class="grid2<?php echo Text::alternate(' first', '', '', '') ?>">
		<article>
			<div class="thumb">
				<?php echo HTML::anchor(Route::model($gallery, isset($approval) ? 'pending' : null), $default_image ? HTML::image($default_image->get_url('thumbnail', $gallery->dir)) : __('New gallery')) ?>
			</div>
			<h4><?= HTML::anchor(Route::model($gallery, isset($approval) ? 'pending' : null), HTML::chars($gallery->name)) ?></h4>
			<div class="info">
				<!--
				<?php echo HTML::time(
					Date::format('DMYYYY', $gallery->date),
					array(
						'datetime' => $gallery->date,
						'title'    => __('Updated :updated', array(':updated' => Date::format('DMYYYY_HM', $gallery->modified)))
					),
					true) ?><br />
				-->

				<?php if (isset($approval)):
					$copyrights = array();
					$pending_images = $gallery->find_images_pending($approval ? null : $user);
					foreach ($pending_images as $image) $copyrights[$image->author_id] = $image->author();
					foreach ($copyrights as $copyright_id => &$copyright) $copyright = HTML::user($copyright);
				?>

					<?php echo '&copy; ', implode(', ', $copyrights) ?><br />
					<?php echo HTML::icon_value(array(':images' => count($pending_images)), ':images image pending', ':images images pending', 'images') ?>

				<?php else: ?>

					<?php if ($gallery->rate_count > 0)
						echo HTML::rating($gallery->rate_total, $gallery->rate_count, false, false) ?>

					<?php echo HTML::icon_value(array(':images' => $gallery->image_count), ':images image', ':images images', 'images') ?><br />

					<?php if ($gallery->comment_count > 0)
						echo HTML::icon_value(array(':comments' => $gallery->comment_count), ':comments comment', ':comments comments', 'comments'), '<br />'; ?>

					<?php if ($gallery->copyright):
						$copyrights = explode(',', $gallery->copyright);
						foreach ($copyrights as &$copyright) $copyright = HTML::user(trim($copyright));
						echo '&copy; ', implode(', ', $copyrights), '<br />';
					endif; ?>

				<?php endif; ?>

			</div>
		</article>
	</li>
<?php endforeach; ?>

</ul>
