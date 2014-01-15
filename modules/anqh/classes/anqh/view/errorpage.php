<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Error page.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_View_ErrorPage extends Anqh_View_Page {

	public $styles = array(
		'//cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.css',
		'//cdnjs.cloudflare.com/ajax/libs/semantic-ui/0.11.0/css/semantic.min.css',
		'static/css/anqh.css'
	);


	/**
	 * Render page.
	 *
	 * @return  string
	 */
	public function render() {
		ob_start();

?>

<!doctype html>
<html lang="<?= $this->language ?>">

<?= $this->_head() ?>

<body id="<?= $this->id ?>" class="<?= $this->class ?>">

<?= $this->_mainmenu() ?>

<!-- CONTENT -->

<?= $this->_content() ?>

<!-- /CONTENT -->


<?= $this->_foot() ?>

</body>

</html>

<?php

		return ob_get_clean();
	}


}
