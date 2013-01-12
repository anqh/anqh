<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Events timeline view.
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Events_Timeline extends View_Section {

	/**
	 * @var  Model_Event[]
	 */
	public $events = null;


	/**
	 * Create new view.
	 *
	 * @param  Model_Event[]  $events
	 */
	public function __construct($events = null) {
		parent::__construct();

		$this->events = $events;
	}


	/**
	 * Draw charts.
	 *
	 * @param   array  $charts
	 * @return  string
	 */
	public function charts($charts) {
		ob_start();

?>

<div id="charts"></div>

<script src="https://www.google.com/jsapi"></script>
<script>
	google.load('visualization', '1', { packages: ['corechart'] });
</script>
<script>

	function drawCharts() {
		var data    = google.visualization.arrayToDataTable(<?= json_encode($charts) ?>)
		  , options = {
					height:     250,
					chartArea:  { width: '100%' },
//					theme:      'maximized',
					legend:     { position: 'none' },
					bar:        { groupWidth: '95%' },
					seriesType: 'bars',

					backgroundColor: 'transparent',
					hAxis: { textStyle: { color: '#999' } },
					vAxis: { gridlines: { color: '#333' }, textPosition: 'in' },
					colors: [
						'white',
						'white',
						'#ffc40d',
						'#ffc40d',
						'#ffc40d',
						'#46a546',
						'#46a546',
						'#46a546',
						'#f89406',
						'#f89406',
						'#f89406',
						'white'
					],
					series: {
						12: {
							type: 'line',
							targetAxisIndex: 1
						}
					}
				}
		  , chart   = new google.visualization.ComboChart(document.getElementById('charts'));

		chart.draw(data, options);
	}

	google.setOnLoadCallback(drawCharts);

</script>

<?php

		return ob_get_clean();
	}


	/**
	 * Render content.
	 *
	 * @return  string
	 */
	public function content() {
		if (!$this->events) {
			return '';
		}


		// Build event list and calculate charts
		ob_start();

		$charts = array(
			array(
				__('Year'),
				__('January'),
				__('February'),
				__('March'),
				__('April'),
				__('May'),
				__('June'),
				__('July'),
				__('August'),
				__('September'),
				__('October'),
				__('November'),
				__('December'),
				__('Per year'),
			)
		);
		$empty = array_fill(0, 14, 0);
		$chart = $empty;

		// Divide by year
		$current_year = null;
		$count        = 0;
		foreach ($this->events as $event):

			// Add separator?
			$year  = date('Y', $event->stamp_begin);
			$month = date('n', $event->stamp_begin);
			if ($year != $current_year):
				if ($current_year):

?>

</ul>

<?= __($count == 1 ? ':count event' : ':count events', array(':count' => $count)) ?>

<?php

					$charts[] = $chart;
				endif;

				// Reset year
				$current_year = $year;
				$count        = 0;
				$chart        = $empty;
				$chart[0]     = $current_year;

?>

<h3><?= $year ?></h3>
<ul class="unstyled">

<?php

			endif;

			// Events per year
			$count++;
			$chart[$month]++;
			$chart[13]++;

?>

	<li>
		<time title="<?php echo Date::format(Date::DATETIME, $event->stamp_begin) . ($event->stamp_end ? ' - ' . Date::format(Date::TIME, $event->stamp_end) : '') ?>"><?php echo Date::format(Date::DM_PADDED, $event->stamp_begin) ?></time>
		<?php echo HTML::anchor(Route::model($event), HTML::chars($event->name), array('class' => 'hoverable')) ?>
	</li>

<?php

		endforeach;

		if ($current_year):

?>

</ul>

<?= __($count == 1 ? ':count event' : ':count events', array(':count' => $count)) ?>

<?php

			$charts[] = $chart;
		endif;

		$list = ob_get_clean();


		// Draw charts
		$header = array_shift($charts);
		$charts = array_reverse($charts);
		array_unshift($charts, $header);
		echo $this->charts($charts);

		// Draw list
		echo $list;


		return ob_get_clean();
	}

}
