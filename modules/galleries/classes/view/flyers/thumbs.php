<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Flyer thumbnails.
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
	 * @var  boolean  Wide view
	 */
	public $wide = true;


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

<div class="row">

	<?php foreach ($this->flyers as $flyer): $name = $flyer->event ? $flyer->event->name : $flyer->name ?>

	<article class="<?= $this->wide ? 'col-xs-6 col-sm-4 col-md-3 col-lg-2' : 'col-xs-6 col-md-4 col-lg-3' ?>">
		<div class="thumbnail">

			<?= HTML::anchor(
				Route::get('flyer')->uri(array('id' => $flyer->id)),
				HTML::image($flyer->image->get_url('thumbnail'))) ?>

			<div class="caption">
				<h4><?= HTML::anchor(Route::url('flyer', array('id' => $flyer->id)), HTML::chars($name), array('title' => HTML::chars($name))) ?></h4>
			</div>

		</div>
	</article>

	<?php endforeach ?>

</div>

<?php

		return ob_get_clean();
	}

}
