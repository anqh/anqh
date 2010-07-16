<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Image slideshow
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<?php if (count($images)): ?>
<a class="scrollable-action action prev">&laquo;</a>
<a class="scrollable-action action next">&raquo;</a>
<div class="scrollable slideshow icons">
	<div class="items">
		<?php foreach ($images as $image):
			echo HTML::anchor(
				$image->get_url(),
				HTML::image($image->get_url('icon')),
				isset($default_id) && $default_id == $image->id
					? array('data-image-id' => $image->id, 'class' => 'default active')
					: array('data-image-id' => $image->id)
			);
		endforeach; ?>
	</div>
</div>
<?php endif; ?>
