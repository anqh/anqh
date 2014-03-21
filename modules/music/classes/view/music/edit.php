<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Music Edit Form.
 *
 * @package    Music
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Music_Edit extends View_Article {

	/**
	 * @var  string  URL for cancel action
	 */
	public $cancel;

	/**
	 * @var  array
	 */
	public $errors;

	/**
	 * @var  Model_Music_Track
	 */
	public $track;


	/**
	 * Create new view.
	 *
	 * @param  Model_Music_Track  $track
	 */
	public function __construct(Model_Music_Track $track) {
		parent::__construct();

		$this->track = $track;
	}


	/**
	 * Render content.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		echo Form::open(null, array('id' => 'form-music', 'class' => 'row'));

?>

			<div class="col-md-8">
				<fieldset>
					<?= Form::input_wrap(
						'name',
						$this->track->name,
						array('class' => 'input-lg'),
						__('Name'),
						Arr::get($this->errors, 'name')
					) ?>

					<?= Form::input_wrap(
						'url',
						$this->track->url,
						array('placeholder' => 'http://'),
						__('URL'),
						Arr::get($this->errors, 'url')
					) ?>

					<?= Form::textarea_wrap(
						'description',
						$this->track->description,
						null,
						true,
						__('Description'),
						Arr::get($this->errors, 'description')
					) ?>

					<?php if ($this->track->type == Model_Music_Track::TYPE_MIX) echo Form::textarea_wrap(
						'tracklist',
						$this->track->tracklist,
						null,
						true,
						__('Tracklist'),
						Arr::get($this->errors, 'tracklist')
					) ?>
				</fieldset>

				<fieldset class="form-actions">
					<?= Form::button('save', __('Save'), array('type' => 'submit', 'class' => 'btn btn-success btn-large')) ?>
					<?= $this->cancel ? HTML::anchor($this->cancel, __('Cancel'), array('class' => 'cancel')) : '' ?>

					<?= Form::csrf() ?>
				</fieldset>
			</div>

			<div class="col-md-4">
				<fieldset>
					<?= Form::input_wrap(
						'cover',
						$this->track->cover,
						array('placeholder' => 'http://'),
						__('Cover'),
						Arr::get($this->errors, 'cover')
					) ?>

					<?= Form::input_wrap(
						'size_time',
						$this->track->size_time,
						array(
							'maxlength'   => $this->track->type == Model_Music_Track::TYPE_MIX ? 8 : 6,
							'placeholder' => $this->track->type == Model_Music_Track::TYPE_MIX ? __('hh:mm:ss') : __('mm:ss')
						),
						__('Length'),
						Arr::get($this->event_errors, 'size_time'),
						null,
						'min'
					) ?>
				</fieldset>

				<fieldset id="fields-music">
					<?= Form::checkboxes_wrap(
						'tag',
						$this->tags(),
						$this->track->tags(),
						array('class' => 'block-grid three-up'),
						__('Music'),
						$this->errors
					) ?>
				</fieldset>
			</div>

<?php

		echo Form::close();

		return ob_get_clean();
	}


	/**
	 * Get available tags.
	 *
	 * @return  array
	 */
	public function tags() {
		$tags = array();
		$tag_group = new Model_Tag_Group('Music');
		if ($tag_group->loaded() && count($tag_group->tags())) {
			foreach ($tag_group->tags() as $tag) {
				$tags[$tag->id()] = $tag->name();
			}
		}

		return $tags;
	}

}
