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

<?= $this->_title() ?>

<?php if ($top = $this->content(self::COLUMN_TOP)): ?>
<div class="content <?= Arr::get($this->_content_class, self::COLUMN_TOP) ?>">
	<div class="container">
		<div class="row">

<?= $this->content(self::COLUMN_TOP) ?>

		</div>
	</div>
</div>
<?php endif; ?>

<div class="content <?= Arr::get($this->_content_class, self::COLUMN_CENTER) ?>">
	<div class="container">
		<div class="row">

<?= $this->content(self::COLUMN_LEFT) ?>
<?= $this->content(self::COLUMN_CENTER) ?>
<?= $this->content(self::COLUMN_RIGHT) ?>

<?= $this->content(self::COLUMN_MAIN) ?>
<?= $this->content(self::COLUMN_SIDE) ?>

		</div>
	</div>
</div>

<?php if ($bottom = $this->content(self::COLUMN_BOTTOM)): ?>
<div class="content <?= Arr::get($this->_content_class, self::COLUMN_BOTTOM) ?>">
	<div class="container">
		<div class="row">

<?= $this->content(self::COLUMN_BOTTOM) ?>

		</div>
	</div>
</div>
<?php endif; ?>

<!-- /CONTENT -->


<?= $this->_foot() ?>

</body>

</html>

<?php

		return ob_get_clean();
	}

}
