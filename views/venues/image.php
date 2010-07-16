<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Venue image
 *
 * @package    Venues
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<div id="slideshow-image">
	<?php echo HTML::image($venue->default_image->get_url(), array('width' => 290)) ?>
</div>
