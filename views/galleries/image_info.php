<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Image info
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

$info = array(
	__('Copyright')  => $image->author_id ? HTML::user($image->author_id) : null,
	__('Added')      => HTML::time(Date::format('DMYYYY_HM', $image->created), $image->created),
	__('Statistics') =>
		HTML::icon_value(array(':comments' => (int)$image->comment_count), ':comments comment', ':comments comments', 'posts') . '<br />' .
		HTML::icon_value(array(':views' => (int)$image->view_count), ':views view', ':views views', 'views')
);

if ($exif = $image->exif()):
	if ($exif->make || $exif->model):
		$info[__('Camera')] =
			($exif->make ? HTML::chars($exif->make) : '') .
			($exif->model ? ($exif->make ? '<br />' : '') . HTML::chars($exif->model) : '');
	endif;
	if ($exif->exposure)  $info[__('Exposure')]     = HTML::chars($exif->exposure);
	if ($exif->aperture)  $info[__('Aperture')]     = HTML::chars($exif->aperture);
	if ($exif->focal)     $info[__('Focal length')] = HTML::chars($exif->focal);
	if ($exif->iso)       $info[__('ISO speed')]    = HTML::chars($exif->iso);
	if ($exif->taken)     $info[__('Taken')]        = Date::format('DMYYYY_HM', $exif->taken);
	if ($exif->flash)     $info[__('Flash')]        = HTML::chars($exif->flash);
	if ($exif->program)   $info[__('Program')]      = HTML::chars($exif->program);
	if ($exif->metering)  $info[__('Metering')]     = HTML::chars($exif->metering);
	if ($exif->latitude)  $info[__('Latitude')]     = HTML::chars($exif->latitude);
	if ($exif->longitude) $info[__('Longitude')]    = HTML::chars($exif->longitude);
	if ($exif->altitude)  $info[__('Altitude')]     = HTML::chars($exif->altitude) . 'm';
	if ($exif->lens)      $info[__('Lens')]         = HTML::chars($exif->lens);
endif;

if (!empty($info)): ?>
<dl>
	<?php foreach ($info as $term => $definition) if (!is_null($definition)): ?>

	<dt><?php echo $term ?></dt><dd><?php echo $definition ?></dd>
	<?php endif; ?>

</dl>
<?php
endif;
