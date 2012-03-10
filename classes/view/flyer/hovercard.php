<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Flyer_HoverCard
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Flyer_HoverCard extends View_Section {

	/**
	 * @var  Model_Event
	 */
	public $event;

	/**
	 * @var  Model_Flyer
	 */
	public $flyer;

	/**
	 * @var  Model_Image
	 */
	public $image;


	/**
	 * Create new view.
	 *
	 * @param  Model_Flyer  $flyer
	 */
	public function __construct(Model_Flyer $flyer) {
		parent::__construct();

		$this->flyer = $flyer;
		$this->image = $flyer->image();
		$this->event = $flyer->event();
		$this->title = HTML::chars($this->event ? $this->event->name : $flyer->name);
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		echo '<figure>', HTML::image($this->image->get_url(Model_Image::SIZE_THUMBNAIL)), '</figure>';

		// Comments
		if ($this->image->comment_count) {
			echo '<span class="stats"><i class="icon-comment"></i> ' . $this->image->comment_count . '</span>';
		}

		return ob_get_clean();
	}

}
