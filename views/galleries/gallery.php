<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Gallery
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

/** @var  Model_Gallery $gallery  */

$approve = isset($approval) && !is_null($approval) ? 'approve' : '';
$images = $approve
	? $gallery->find_images_pending($approval ? null : $user)
	: $gallery->find_images();

if ($approve && $approval) echo Form::open();
?>

<ul>

	<?php foreach ($images as $image): ?>

	<li class="unit size1of3">
		<div class="thumb">
			<?php echo HTML::anchor(
				Route::get('gallery_image')->uri(array('gallery_id' => Route::model_id($gallery), 'id' => $image->id, 'action' => $approve)),
				HTML::image($image->get_url('thumbnail', $gallery->dir)),
					$image->description ? array('title' => HTML::chars($image->description)) : null) ?>
		</div>
		<?php if (!isset($approval)): ?>
			<?php echo HTML::icon_value(array(':comments' => $image->comment_count), ':comments comment', ':comments comments', 'posts') ?>
			<?php echo HTML::icon_value(array(':views' => $image->view_count), ':views view', ':views views', 'views') ?><br />
		<?php endif; ?>
	</li>

	<?php endforeach; ?>

</ul>
<?php
if ($approve && $approval):
	echo Form::csrf();
	echo Form::close();
endif;
