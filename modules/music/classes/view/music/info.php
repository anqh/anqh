<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Track side info view.
 *
 * @package    Music
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Music_Info extends View_Section {

	/**
	 * @var  Model_Music_Track
	 */
	public $track;


	/**
	 * Create new article.
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

		// Cover
		if (Valid::url($this->track->cover)):
			echo HTML::image($this->track->cover, array('class' => 'cover', 'alt' => __('Cover')));
		endif;

		// Time
		if ($this->track->size_time):
			echo '<i class="icon-time icon-white"></i> ', $this->track->size_time, '<br />';
		endif;

		// Listen count
		if ($this->track->listen_count > 1):
			echo '<i class="icon-headphones icon-white"></i> ', __(':count plays', array(':count' => $this->track->listen_count)), '<br />';
		endif;

		// Tags
		if ($tags = $this->track->tags()):
			echo '<i class="icon-music icon-white"></i> ', implode(', ', $tags), '<br />';
		elseif (!empty($this->track->music)):
			echo '<i class="icon-music icon-white"></i> ', $this->track->music, '<br />';
		endif;


		// Meta
		echo '<footer class="meta">';
		echo __('Added'),  ' ', HTML::time(Date::format(Date::DMY_SHORT, $this->track->created), $this->track->created);
		echo '</footer>';


		return ob_get_clean();
	}

}
