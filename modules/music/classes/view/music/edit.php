<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * View_Music_Edit
 *
 * @package    Music
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
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

			<div class="span8">
				<fieldset>
					<?= Form::control_group(
						Form::input('name', $this->track->name, array('class' => 'span8')),
						array('name' => __('Name')),
						Arr::get($this->errors, 'name')) ?>

					<?= Form::control_group(
						Form::input('url', $this->track->url, array('class' => 'span8', 'placeholder' => 'http://')),
						array('url' => __('URL')),
						Arr::get($this->errors, 'url')) ?>

					<?= Form::control_group(
						Form::textarea_editor('description', $this->track->description, array('class' => 'span8'), true),
						array('description' => __('Description')),
						Arr::get($this->errors, 'description')) ?>

					<?php if ($this->track->type == Model_Music_Track::TYPE_MIX) echo Form::control_group(
						Form::textarea('tracklist', $this->track->tracklist, array('class' => 'span8'), true),
						array('tracklist' => __('Tracklist')),
						Arr::get($this->errors, 'tracklist')) ?>

				</fieldset>

				<fieldset class="form-actions">
					<?= Form::button('save', __('Save'), array('type' => 'submit', 'class' => 'btn btn-success btn-large')) ?>
					<?= $this->cancel ? HTML::anchor($this->cancel, __('Cancel'), array('class' => 'cancel')) : '' ?>

					<?= Form::csrf() ?>
				</fieldset>
			</div>

			<div class="span4">
				<fieldset>

					<?= Form::control_group(
						Form::input('cover', $this->track->cover, array('class' => 'span4', 'placeholder' => 'http://')),
						array('cover' => __('Cover')),
						Arr::get($this->errors, 'cover')) ?>

					<?= Form::control_group(
						'<div class="input-append">'
							. Form::input('size_time', $this->track->size_time, array(
									'class'       => 'input-mini',
									'maxlength'   => $this->track->type == Model_Music_Track::TYPE_MIX ? 8 : 6,
									'placeholder' => $this->track->type == Model_Music_Track::TYPE_MIX ? __('hh:mm:ss') : __('mm:ss')
								))
							. '<span class="add-on">min</span>'
							. '</div>',
						array('size_time' => __('Length')),
						Arr::get($this->errors, 'size_time')) ?>

				</fieldset>

				<fieldset id="fields-music">
					<legend><?= __('Music') ?></legend>
					<?= Form::checkboxes_wrap('tag', $this->tags(), $this->track->tags(), null, $this->errors, null, 'block-grid two-up') ?>
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
