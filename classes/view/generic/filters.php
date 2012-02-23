<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Filters view.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Generic_Filters extends View_Section {

	/**
	 * @var  array  Filters
	 */
	public $filters = array();


	/**
	 * Create new view.
	 */
	public function __construct() {
		parent::__construct();

		$this->id    = 'filters';
		$this->class = 'filters';
	}


	/**
	 * Render content.
	 *
	 * @return  string
	 */
	public function content() {
		if (!$this->filters) {
			return '';
		}

		ob_start();

		foreach ($this->filters as $type => $filter) {
?>

<div class="btn-toolbar filters">
	<div class="btn-group" data-toggle="buttons-checkbox">
		<a data-filter="all" class="btn btn-small active"><?php echo __('All') ?></a>
	</div>

	<div class="btn-group" data-toggle="buttons-checkbox">

		<?php foreach ($filter['filters'] as $key => $name) { ?>
		<a data-filter="<?php echo $type . '-' . $key ?>" class="btn btn-small"><?php echo HTML::chars($name) ?></a>
		<?php } ?>

	</div>
</div>

<?php } ?>

<script>
(function() {

	// Hook clicks
	head.ready('jquery-ui', function hookFilters() {
		$('.btn-toolbar.filters a').on('click', function filterClick() {
			var activated = !$(this).hasClass('active'); // Class is toggled after this
			var filter = $(this).data('filter');

			if (filter === 'all' && activated) {

				// Show all
				$('.btn-toolbar.filters a[data-filter!=all]').removeClass('active');

			} else if (filter !== 'all') {

				// Individual filters, uncheck 'All'
				$('.btn-toolbar.filters a[data-filter=all]').removeClass('active');

			}

			// Show/hide filtered items
			$('.btn-toolbar.filters a').each(function filterToggle() {
				var filtering = $(this).data('filter');
				var active    = $(this).hasClass('active');

				if (
					(filter === 'all' && activated) ||     // All selected
					(filtering === filter && activated) || // Current filter selected
					(filtering !== filter && active)       // Other filter already selected
				) {
					$('.' + filtering + ':hidden').slideDown('normal');
				} else {
					$('.' + filtering + ':visible').slideUp('normal');
				}
			});

		});
	});

	/** @todo  Disabled, using filters loses current position
	// Hook sticky filters
	head.ready('jquery-fixedscroll', function stickyFilters() {
		$('#filters').scrollToFixed({
			marginTop: $('#header').outerHeight(), // Show below header
			limit:     $(this).parent().outerHeight()
		});
	});
	*/

})();
</script>

<?php

		return ob_get_clean();
	}

}
