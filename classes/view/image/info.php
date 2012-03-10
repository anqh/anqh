<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Image_Info
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Image_Info extends View_Section {

	/**
	 * @var  Model_Image
	 */
	public $image;


	/**
	 * Create new view.
	 *
	 * @param  Model_Image  $image
	 */
	public function __construct(Model_Image $image) {
		parent::__construct();

		$this->image = $image;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {

		// Image basic info
		$info = array(
			__('Copyright')  => $this->image->author_id ? HTML::user($this->image->author_id) : null,
			__('Added')      => HTML::time(Date::format('DMYYYY_HM', $this->image->created), $this->image->created),
			__('Statistics') =>
				'<i class="icon-comment"></i> ' . (int)$this->image->comment_count . ', ' .
				'<i class="icon-eye-open"></i> ' . (int)$this->image->view_count
		);

		// Image EXIF
		if ($exif = $this->image->exif()) {
			if ($exif->make || $exif->model) {
				$info[__('Camera')] =
					($exif->make ? HTML::chars($exif->make) : '') .
					($exif->model ? ($exif->make ? '<br />' : '') . HTML::chars($exif->model) : '');
			};
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
		}


		ob_start();

		if (!empty($info)) {

?>

	<dl class="dl-horizontal">
		<?php foreach ($info as $term => $definition) if (!is_null($definition)) { ?>

		<dt><?= $term ?></dt><dd><?= $definition ?></dd>
		<?php } ?>

	</dl>

<?php

		}

		return ob_get_clean();
	}

}
