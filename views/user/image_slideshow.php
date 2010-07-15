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

<?php if (count($user->images)): $images = array(); foreach ($user->images as $image) $images[] = $image; ?>
<a class="scrollable-action action prev">&laquo;</a>
<a class="scrollable-action action next">&raquo;</a>
<div class="scrollable slideshow icons">
	<div class="items">
		<?php foreach (array_reverse($images) as $image):
			echo HTML::anchor(
				$image->get_url(),
				HTML::image($image->get_url('icon')),
				$user->default_image->id == $image->id
					? array('data-image-id' => $image->id, 'class' => 'default active')
					: array('data-image-id' => $image->id)
			);
		endforeach; ?>
	</div>
</div>
<?php endif; ?>
