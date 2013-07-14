<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Error page.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_View_ErrorPage extends Anqh_View_Page {

	public $styles = array(
		'static/css/bootstrap.css',
		'static/css/bootstrap-responsive.css',
		'//netdna.bootstrapcdn.com/font-awesome/3.1.1/css/font-awesome.css',
	);


	/**
	 * Render <head>.
	 *
	 * @return  string
	 */
	protected function __head() {
		ob_start();

?>

<head>
	<meta charset="<?= Kohana::$charset ?>" />
	<meta http-equiv="Content-Type" content="text/html; charset=<?= Kohana::$charset ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />

	<title><?= $this->title ? HTML::chars($this->title) : Kohana::$config->load('site.site_name') ?></title>
	<link rel="icon" type="image/png" href="<?= $this->base ?>ui/favicon.png" />

	<?= $this->_styles() ?>

	<?= HTML::style('ui/site.css') ?>

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
<html lang="<?= $this->language ?>">

<?= $this->_head() ?>

<body id="<?= $this->id ?>" class="<?= $this->class ?>">

	<table id="body">
		<tbody>
			<tr>

				<th id="sidebar" rowspan="2">

					<!-- MENU -->

					<?= $this->_menu() ?>

					<!-- /MENU -->

				</th><!-- /#sidebar -->

				<th id="topbar">

					<?= $this->_header() ?>

				</th><!-- /#topbar -->

			</tr>
			<tr>

				<td id="maincontent">
					<div class="container-fluid">

						<!-- CONTENT -->

						<?= $this->_content() ?>

						<!-- /CONTENT -->

					</div>

					<?= $this->_foot() ?>

				</td><!-- /#maincontent -->

			</tr>
		</tbody>
	</table>

</body>

</html>

<?php

		return ob_get_clean();
	}


}
