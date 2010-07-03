<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Image info
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

$info = array(
	__('Copyright')  => $image->author->id ? HTML::user($image->author) : null,
	__('Added')      => HTML::time(Date::format('DMYYYY_HM', $image->created), $image->created),
	__('Statistics') =>
		HTML::icon_value(array(':comments' => $image->comment_count), ':comments comment', ':comments comments', 'posts') . '<br />' .
		HTML::icon_value(array(':views' => $image->view_count), ':views view', ':views views', 'views')
);

if ($image->exif):
	if ($image->exif->make || $image->exif->model)
		$info[__('Camera')] = ($image->exif->make ? HTML::chars($image->exif->make) . ' ' : '') . ($image->exif->model ? HTML::chars($image->exif->model) . ' ' : '');
	if ($image->exif->exposure)  $info[__('Exposure')] = HTML::chars($image->exif->exposure);
	if ($image->exif->aperture)  $info[__('Aperture')] = 'f/' . HTML::chars($image->exif->aperture);
	if ($image->exif->focal)     $info[__('Focal length')] = HTML::chars($image->exif->focal) . 'mm';
	if ($image->exif->iso)       $info[__('ISO speed')] = HTML::chars($image->exif->iso);
	if ($image->exif->taken)     $info[__('Taken')] = Date::format('DMYYYY_HM', $image->exif->taken);
	if ($image->exif->flash)     $info[__('Flash')] = HTML::chars($image->exif->flash);
	if ($image->exif->program)   $info[__('Program')] = HTML::chars($image->exif->program);
	if ($image->exif->metering)  $info[__('Metering')] = HTML::chars($image->exif->metering);
	if ($image->exif->latitude)  $info[__('Latitude')] = HTML::chars($image->exif->latitude);
	if ($image->exif->longitude) $info[__('Longitude')] = HTML::chars($image->exif->longitude);
	if ($image->exif->altitude)  $info[__('Altitude')] = HTML::chars($image->exif->altitude) . 'm';
	if ($image->exif->lens)      $info[__('Lens')] = HTML::chars($image->exif->lens);
endif;

if (!empty($info)): ?>
<dl>
	<?php foreach ($info as $term => $definition) if (!is_null($definition)): ?>

	<dt><?php echo $term ?></dt><dd><?php echo $definition ?></dd>
	<?php endif; ?>

</dl>
<?php
endif;
