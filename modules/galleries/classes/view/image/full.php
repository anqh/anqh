<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Image_Full
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Image_Full extends View_Section {

	/**
	 * @var  boolean  Permission to add notes
	 */
	public $can_note = false;

	/**
	 * @var  Model_Gallery
	 */
	public $gallery;

	/**
	 * @var  Model_Image
	 */
	public $image;

	/**
	 * @var  boolean  Viewing pending images
	 */
	public $show_pending = false;

	/**
	 * @var  string  Click URL for image
	 */
	public $url;


	/**
	 * Create new view.
	 *
	 * @param  Model_Image    $image
	 * @param  Model_Gallery  $gallery
	 */
	public function __construct(Model_Image $image, Model_Gallery $gallery) {
		parent::__construct();

		$this->image    = $image;
		$this->gallery  = $gallery;
		$this->can_note = Permission::has($image, Model_Image::PERMISSION_NOTE, self::$_user);
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

?>

<div class="image">
	<figure>

		<?php if ($this->url):
			echo HTML::anchor(
				$this->url,
				HTML::image($this->image->get_url(null, $this->gallery->dir)),
				array('title' => __('Next image'), 'class' => 'image'));
		else:
			echo HTML::anchor(
				Route::model($this->gallery, $this->show_pending ? 'approve' : ''),
				HTML::image($this->image->get_url(null, $this->gallery->dir)),
				array('title' => __('Back to gallery'), 'class' => 'image'));
		endif; ?>

		<?php if ($exif = $this->exif()): ?>

			<div class="exif">
				<i class="iconic-camera"></i>
				<dl class="dl-horizontal">
					<?php foreach ($exif as $term => $definition) if (!is_null($definition)): ?>
					<dt><?= $term ?></dt><dd><?= $definition ?></dd>
					<?php endif; ?>
				</dl>
			</div>

		<?php endif; ?>

		<?php if ($this->can_note): ?>

			<?= Form::open(
				Route::url('gallery_image', array('gallery_id' => Route::model_id($this->gallery), 'id' => $this->image->id, 'action' => 'note')),
				array('id' => 'form-note')) ?>

			<fieldset>
				<?= Form::control_group(Form::input('name')) ?>

				<?= Form::submit('save', __('Save'), array('class' => 'btn btn-success')) ?>
				<a class="cancel" href="#cancel"><?= __('Cancel') ?></a>

				<?= Form::hidden('x') ?>
				<?= Form::hidden('y') ?>
				<?= Form::hidden('width') ?>
				<?= Form::hidden('height') ?>
				<?= Form::hidden('user_id') ?>
			</fieldset>

			<?= Form::close() ?>

		<?php endif; ?>

	</figure>

	<?= $this->notes() ?>

</div>
<?php

		return ob_get_clean();
	}


	/**
	 * Render EXIF info.
	 *
	 * @return  array
	 */
	public function exif() {

		// Basic info
		$info = array(
			'<span>&copy;</span> ' . __('Copyright')  => $this->image->author_id ? HTML::user($this->image->author_id) : null,
			'<i class="iconic-upload"></i> '  . __('Added')      => HTML::time(Date::format('DMYYYY_HM', $this->image->created), $this->image->created),
			'<i class="iconic-comment"></i> ' . __('Comments') => (int)$this->image->comment_count,
			'<i class="iconic-eye"></i> '     . __('Views') => (int)$this->image->view_count,
		);

		// EXIF info
		if ($exif = $this->image->exif()) {
			if ($exif->make || $exif->model) {
				$info['<i class="iconic-camera"></i> ' . __('Camera')] =
					($exif->make ? HTML::chars($exif->make) : '') .
					($exif->model ? ($exif->make ? '<br />' : '') . HTML::chars($exif->model) : '');
			};
			if ($exif->exposure)  $info['<i class="iconic-sun-stroke"></i> '      . __('Shutter Speed')] = HTML::chars($exif->exposure);
			if ($exif->aperture)  $info['<i class="iconic-aperture-alt"></i> '    . __('Aperture')]      = HTML::chars($exif->aperture);
			if ($exif->focal)     $info['<i class="iconic-layers-alt"></i> '      . __('Focal Length')]  = HTML::chars($exif->focal);
			if ($exif->iso)       $info['<span class="iso">iso</span> '           . __('ISO Speed')]     = HTML::chars($exif->iso);
			if ($exif->lens)      $info['<i class="iconic-cd"></i> '              . __('Lens')]          = HTML::chars($exif->lens);
			if ($exif->flash)     $info['<i class="iconic-bolt"></i> '            . __('Flash')]         = HTML::chars($exif->flash);
			if ($exif->program)   $info['<i class="iconic-cloud"></i> '           . __('Program')]       = HTML::chars($exif->program);
			if ($exif->metering)  $info['<i class="iconic-target"></i> '          . __('Metering')]      = HTML::chars($exif->metering);
			if ($exif->latitude)  $info['<i class="iconic-move-horizontal"></i> ' . __('Latitude')]      = HTML::chars($exif->latitude);
			if ($exif->longitude) $info['<i class="iconic-move-vertical"></i> '   . __('Longitude')]     = HTML::chars($exif->longitude);
			if ($exif->altitude)  $info['<i class="iconic-arrow-up"></i> '        . __('Altitude')]      = HTML::chars($exif->altitude) . 'm';
			if ($exif->taken)     $info['<i class="iconic-clock"></i> '           . __('Taken')]         = Date::format('DMYYYY_HM', $exif->taken);
		}

		return $info;
	}


	/**
	 * Render notes.
	 *
	 * @return  string
	 */
	public function notes() {
		$note_array = array();
		$notes      = $this->image->notes();
		if (count($notes)) {
			/** @var  Model_Image_Note  $note */
			foreach ($notes as $note) {
				$note_user    = $note->user();
				$note_array[] = array(
					'id'     => (int)$note->id,
					'x'      => (int)$note->x,
					'y'      => (int)$note->y,
					'width'  => (int)$note->width,
					'height' => (int)$note->height,
					'name'   => $note_user ? $note_user['username'] : $note->name,
					'url'    => $note_user ? URL::base() . URL::user($note_user) : null
				);
			}
		}

		// Add note section
		if ($this->can_note || $note_array):
			ob_start();

?>

	<ul class="unstyled notes">

<?php

			// Add notes
			if ($notes):
				$i = 0;
				foreach ($notes as $note):
					$i++;
					$note_user = $note->user();
					$note_name = $note_user ? $note_user['username'] : $note->name;

					echo '<li>';

					// Add single note
					if ($note_user):
						echo HTML::user($note_user, null, array('data-note-id' => $note->id));
					else:
						echo '<span data-note-id="', $note->id, '">', HTML::chars($note_name), '</span>';
					endif;

					// Deletable?
					if (Permission::has($note, Model_Image_Note::PERMISSION_DELETE, self::$_user)):
						echo ' ', HTML::anchor(
							Route::url('image_note', array('id' => $note->id, 'action' => 'unnote')),
							'&#215;',
							array('class' => 'note-delete', 'data-confirm' => __('Delete note'), 'title' => __('Delete note'))
						);
					endif;

					if ($i < count($notes)):
						echo ', ';
					endif;

					echo '</li>';

				endforeach;
			endif;

			// Add note action
			if ($this->can_note):
				echo '<li><a href="#" class="btn btn-inverse btn-mini note-add"><i class="icon-tag icon-white"></i> ', __('Tag people'), '</a></li>';
			endif;

?>

	</ul>

	<?php if ($this->can_note || $note_array): ?>

<script>
head.ready('anqh', function() {

	// Add notes
	$('a.image').notes(<?= json_encode($note_array) ?>);

	// Autocomplete
	$('input[name=name]').autocompleteUser();

	// Hook new note
	$('a.note-add').on('click', function onNoteAdd() {

		function updateForm(img, area) {
			$('#form-note')
				.css({
					left: area.x1 + "px",
					top: area.y1 + area.height + 5 + "px"
				})
				.show();

			$('#form-note input[name=x]').val(area.x1);
			$('#form-note input[name=y]').val(area.y1);
			$('#form-note input[name=width]').val(area.width);
			$('#form-note input[name=height]').val(area.height);
		}

		$('a.image img').imgAreaSelect({
			onInit:         updateForm,
			onSelectChange: updateForm,
			handles:        true,
			persistent:     true,
			minWidth:       50,
			minHeight:      50,
			maxWidth:       150,
			maxHeight:      150,
			x1:             parseInt($('a.image').width() / 2) - 50,
			y1:             parseInt($('a.image').height() / 2) - 50,
			x2:             parseInt($('a.image').width() / 2) + 50,
			y2:             parseInt($('a.image').height() / 2) + 50
		});

		return false;
	});

	$('#form-note a.cancel').on('click', function onNoteCancel() {
		$('a.image img').imgAreaSelect({ hide: true });
		$('#form-note').hide();

		return false;
	});

});
</script>

<?php
			endif;

		endif;

		return ob_get_clean();
	}
}
