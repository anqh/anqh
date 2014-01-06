<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Flyers_Thumbs
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Flyers_Thumbs extends View_Section {

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

<div class="ui four items">

	<?php foreach ($this->flyers as $flyer): ?>

	<a class="item" href="<?= Route::get('flyer')->uri(array('id' => $flyer->id)) ?>">
		<div class="image">
			<?= HTML::image($flyer->image->get_url('thumbnail')) ?>
		</div>

		<div class="content">
			<p class="name"><?= HTML::chars($flyer->event ? $flyer->event->name : $flyer->name) ?></p>
		</div>
	</a>

	<?php endforeach ?>

</div>

<?php

		return ob_get_clean();
	}

}
