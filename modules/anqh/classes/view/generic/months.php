<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Generic months brorwser.
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Generic_Months extends View_Section {

	/**
	 * @var  string  Browse action
	 */
	public $action;

	/**
	 * @var  string  View class
	 */
	public $class = 'months';

	/**
	 * @var  integer  Selected month
	 */
	public $month;

	/**
	 * @var  array
	 */
	public $months;

	/**
	 * @var  string  Browse route
	 */
	public $route;

	/**
	 * @var  integer  Selected year
	 */
	public $year;


	/**
	 * Create new view.
	 *
	 * @param  array   $months
	 * @param  string  $route
	 * @param  string  $action
	 */
	public function __construct(array $months, $route, $action) {
		parent::__construct();

		$this->months = $months;
		$this->route  = $route;
		$this->action = $action;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

?>

<nav>
	<ol class="block-grid four-up">

	<?php foreach ($this->months as $years => $y): ?>
		<li<?= $this->year == $years ? ' class="selected"' : '' ?>>
			<h4><?php echo HTML::anchor(
				Route::get($this->route)->uri(array(
					'action' => $this->action,
					'year'   => $years,
				)),
				$years == 1970 ? __('Unknown') : $years
			) ?></h4>
			<ol class="unstyled">

			<?php foreach ($y as $m => $count): ?>
				<li<?= $this->year == $years && $this->month == $m ? ' class="selected"' : '' ?>><?php echo HTML::anchor(
					Route::get($this->route)->uri(array(
						'action' => $this->action,
						'year'   => $years,
						'month'  => $m
					)),
					$m > 0 ? strftime('%b', strtotime("$years-$m-1")) : '???'
				) ?> (<?= $count ?>)</li>
			<?php endforeach ?>

			</ol><br />
		</li>
	<?php endforeach ?>

	</ol>
</nav>

<?php

		return ob_get_clean();
	}

}
