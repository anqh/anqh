<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Gallery
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<ul>

	<?php foreach ($gallery->find_images() as $image): ?>

	<li class="unit size1of4">
		<div class="thumb">
			<?php echo HTML::anchor(
				Route::get('gallery_image')->uri(array('gallery_id' => Route::model_id($gallery), 'id' => $image->id, 'action' => '')),
				HTML::image('http://' . Kohana::config('site.image_server') . '/kuvat/' . $gallery->dir . '/thumb_' . $image->legacy_filename),
					$image->description ? array('title' => HTML::chars($image->description)) : null) ?>
		</div>
		<?php echo HTML::icon_value(array(':comments' => $image->comment_count), ':comments comment', ':comments comments', 'posts') ?>
		<?php echo HTML::icon_value(array(':views' => $image->view_count), ':views view', ':views views', 'views') ?><br />
	</li>

	<?php endforeach; ?>

</ul>
