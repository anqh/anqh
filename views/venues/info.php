<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Venue info
 *
 * @package    Venues
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<?php if ($venue->default_image_id): ?>
<article class="logo">
	<?php echo HTML::img($venue->default_image, 'normal', array('title' => __('Logo'), 'width' => '100%')) ?>
</article>
<?php endif; ?>

<?php	if ($venue->homepage || $venue->description || $venue->info || $venue->hours || $venue->tags): ?>
<article class="information">
	<header>
		<h3><?php echo __('Basic information') ?></h3>
	</header>

	<dl>
		<?php echo empty($venue->homepage)    ? '' : '<dt>' . __('Homepage')      . '</dt><dd>' . HTML::anchor($venue->homepage) . '</dd>' ?>
		<?php echo empty($venue->description) ? '' : '<dt>' . __('Description')   . '</dt><dd>' . HTML::chars($venue->description) . '</dd>' ?>
		<?php echo empty($venue->info)        ? '' : '<dt>' . __('Extra info')    . '</dt><dd>' . HTML::chars($venue->info) . '</dd>' ?>
		<?php echo empty($venue->hours)       ? '' : '<dt>' . __('Opening hours') . '</dt><dd>' . HTML::chars($venue->hours) . '</dd>' ?>
		<?php echo !count($venue->tags)       ? '' : '<dt>' . __('Tags')          . '</dt><dd>' . implode(', ', $venue->tags->as_array('id', 'name')) . '</dd>' ?>
	</dl>
</article>
<?php	endif; ?>

<article class="contact">
	<header>
		<h3><?php echo __('Contact information') ?></h3>
	</header>

	<?php if ($venue->address || $venue->city_name): ?>
	<dl class="address">
		<dt><?php echo __('Address') ?></dt>
		<dd>
			<address>
				<?php echo HTML::chars($venue->address) ?><br />
				<?php echo HTML::chars($venue->city_name) ?> <?php echo HTML::chars($venue->zip) ?>
			</address>
		</dd>

		<?php if ($venue->latitude && $venue->longitude): ?>
		<dd>
			<?php echo HTML::anchor('#map', __('Toggle map')) ?>
		</dd>
		<?php endif; ?>

	</dl>

		<?php if ($venue->latitude && $venue->longitude): ?>
	<div id="map" style="display: none"><?php echo __('Map loading') ?></div>
		<?php
$options = array(
	'marker'     => HTML::chars($venue->name),
	'infowindow' => HTML::chars($venue->address) . '<br />' . HTML::chars($venue->city_name),
	'lat'        => $venue->latitude,
	'long'       => $venue->longitude
);
Widget::add('foot', HTML::script_source('
$(function() {
	//$("#map").googleMap(' .  json_encode($options) . ');
	$(".contact a[href=#map]").click(function() { $("#map").toggle("fast", function() { $("#map").googleMap(' .  json_encode($options) . '); }); return false; });
});
'));
?>
		<?php endif; ?>
	<?php endif; ?>

</article>

<?php if (count($venue->images)): ?>
<article class="pictures lightboxed">
	<header>
		<h3><?php echo __('Pictures') ?></h3>
	</header>

	<?php foreach ($venue->images as $image): if ($image->id != $venue->default_image->id): ?>
		<?php echo HTML::anchor($image->url('normal'), HTML::img($image, 'thumb',__('Picture')), array('title' => HTML::chars($venue->name))) ?>
	<?php endif; endforeach; ?>

</article>
<?php endif; ?>

<div class="lightbox" id="slideshow">
	<a class="prev" title="<?php echo __('Previous') ?>">&laquo;</a>
	<a class="next" title="<?php echo __('Next') ?>">&raquo;</a>
	<a class="action close" title="<?php echo __('Close') ?>">&#10006;</a>
	<div class="info"></div>
</div>
<?php
return;

echo HTML::script_source('
$(function() {
	$(".lightboxed a").overlay({
		effect: "apple",
		target: "#slideshow",
		expose: {
			color: "#222",
			loadSpeed: 200,
			opacity: 0.75
		}
	}).gallery({
		template: "<strong>${title}</strong> <span class=\"details\">' . __('Image ${index} of ${total}') . '</span>"
	});
});
');
