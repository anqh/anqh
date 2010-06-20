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
		<div class="thumb unit size2of5">
			<?php echo HTML::anchor(Route::model($gallery), HTML::image('http://' . Kohana::config('site.image_server') . '/kuvat/' . $gallery->dir . '/thumb_' . $gallery->default_image->legacy_filename)) ?>
		</div>
		<header>
			<h4><?= HTML::anchor(Route::model($gallery), HTML::chars($gallery->name)) ?></h4>
			<span class="details">
				<?php echo HTML::time(Date::format('DMYYYY', $gallery->date), $gallery->date, true) ?>,
				<?php echo __2(':images image', ':images images', $gallery->num_images, array(':images' => '<var>' . $gallery->num_images . '</var>')) ?>
			</span>
		</header>
	</li>
<?php endforeach; ?>

</ul>
