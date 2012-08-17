<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Error page.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_View_ErrorPage extends Anqh_View_Page {

	/**
	 * @var  array  Basic stylesheets
	 */
	public $styles = array(
		'http://fonts.googleapis.com/css?family=Terminal+Dosis',
    'static/css/bootstrap.css',
	  'static/css/bootstrap-responsive.css',
	  'static/site.css',
	  'ui/site.css',
	);


	/**
	 * Render <head>.
	 *
	 * @return  string
	 */
	protected function _head() {
		ob_start();

?>

<head>
	<meta charset="<?= Kohana::$charset ?>" />
	<meta http-equiv="Content-Type" content="text/html; charset=<?= Kohana::$charset ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
	<base href="<?php echo $this->base ?>" />

	<title><?= ($this->title ? HTML::chars($this->title) . ' | ' : '') . Kohana::$config->load('site.site_name') ?></title>
	<link rel="icon" type="image/png" href="<?= $this->base ?>ui/favicon.png" />

	<?= $this->_styles() ?>
</head>

<?php

		return ob_get_clean();
	}


	/**
	 * Render page.
	 *
	 * @return  string
	 */
	public function render() {
		ob_start();

?>

<!doctype html>
<html lang="<?php echo $this->language ?>">

<?php echo $this->_head() ?>

<body id="<?php echo $this->id ?>" class="<?php echo $this->class ?>">


	<!-- HEADER -->

	<?php echo $this->_header() ?>

	<!-- /HEADER -->


	<!-- CONTENT -->

	<?php echo $this->_content() ?>

	<!-- /CONTENT -->

</body>

</html>

<?php

		return ob_get_clean();
	}


}
