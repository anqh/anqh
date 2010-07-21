<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Galleires
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

/** @var  Model_Gallery $gallery */
$gallery = null;
?>

<ul>

<?php foreach ($galleries as $gallery): ?>
	<li class="unit size1of2">
		<article>
			<header>
				<?= HTML::anchor(Route::model($gallery, isset($approval) ? 'pending' : null), HTML::chars($gallery->name)) ?>
			</header>
			<div class="thumb unit size1of2">
				<?php echo HTML::anchor(Route::model($gallery, isset($approval) ? 'pending' : null), HTML::image($gallery->default_image->get_url('thumbnail', $gallery->dir))) ?>
			</div>
			<div class="">
				<?php echo HTML::time(
					Date::format('DMYYYY', $gallery->date),
					array(
						'datetime' => $gallery->date,
						'title'    => __('Updated :updated', array(':updated' => Date::format('DMYYYY_HM', $gallery->modified)))
					),
					true) ?><br />

				<?php if (isset($approval)):
					$pending_images = $gallery->find_images_pending($approval ? null : $user);
					$copyrights = array();
					foreach ($pending_images as $image) $copyrights[$image->author->id] = $image->author;
					foreach ($copyrights as $copyright_id => &$copyright) $copyright = HTML::user($copyright);
					echo __('Copyright'), ': ', implode(', ', $copyrights), '<br />';
				elseif ($gallery->copyright):
					$copyrights = explode(',', $gallery->copyright);
					foreach ($copyrights as &$copyright) $copyright = HTML::user(trim($copyright));
					echo __('Copyright'), ': ', implode(', ', $copyrights), '<br />';
				endif; ?>

				<?php if (isset($approval)): ?>

					<?php echo HTML::icon_value(array(':images' => count($pending_images)), ':images image pending', ':images images pending', 'images') ?><br />

				<?php else: ?>

					<?php echo HTML::icon_value(array(':images' => $gallery->image_count), ':images image', ':images images', 'images') ?><br />
					<?php if ($gallery->comment_count > 0)
						echo HTML::icon_value(array(':comments' => $gallery->comment_count), ':comments comment', ':comments comments', 'comments'), '<br />'; ?>
					<?php if ($gallery->rate_count > 0)
						echo HTML::rating($gallery->rate_total, $gallery->rate_count), '<br />' ?>

				<?php endif; ?>
			</div>
		</article>
	</li>
<?php endforeach; ?>

</ul>
