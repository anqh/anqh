<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Side image
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

if ($image):
?>

<div id="slideshow-image">
	<?php echo HTML::image(is_string($image) ? $image : $image->get_url(), array('width' => 290)) ?>
</div>

<?php else:
	echo __('No images yet.');
endif;
