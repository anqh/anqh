<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Flyers_Latest
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Flyers_Latest extends View_Section {

	public $class = 'full';

	/**
	 * @var  Model_Flyer[]
	 */
	public $flyers;


	/**
	 * Create new view.
	 *
	 * @param  Model_Flyer[]  $flyers
	 */
	public function __construct($flyers) {
		parent::__construct();

		$this->flyers = $flyers;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

?>

<ul class="thumbnails">

	<?php foreach ($this->flyers as $flyer): $name = $flyer->event ? $flyer->event->name : $flyer->name ?>

	<li class="span2">
		<?= HTML::anchor(
			Route::get('flyer')->uri(array('id' => $flyer->id)),
			HTML::image($flyer->image->get_url('thumbnail'))
				. '<h4>' . HTML::chars($name) . '</h4>',
			array('class' => 'thumbnail', 'title' => HTML::chars($name))
		) ?>


	</li>

	<?php endforeach ?>

</ul>


<?php

		return ob_get_clean();
	}

}
