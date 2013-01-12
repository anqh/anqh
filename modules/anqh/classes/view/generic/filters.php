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

	/** Filter with JavaScript */
	const TYPE_JAVASCRIPT = 'javascript';

	/** Filter with request, filter in query param */
	const TYPE_QUERY = 'query';

	/** Filter with request, filter in url */
	const TYPE_URL = 'url';

	/**
	 * @var  string  Base URL
	 */
	public $base_url;

	/**
	 * @var  boolean  Clear query string
	 */
	public $clear_query = false;

	/**
	 * @var  array  Filters
	 */
	public $filters = array();

	/**
	 * @var  boolean  Multiple filters per query
	 */
	public $multiple_query = false;

	/**
	 * @var  string  Query string parameter
	 */
	public $parameter = 'page';

	/**
	 * @var  string  selected item
	 */
	public $selected;

	/**
	 * @var  string  Filter type
	 */
	public $type = self::TYPE_JAVASCRIPT;


	/**
	 * Create new view.
	 */
	public function __construct(array $filters = null, $selected = null) {
		parent::__construct();

		$this->id    = 'filters';
		$this->class = 'filters';

		$this->filters  = $filters;
		$this->selected = $selected;
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

		if ($this->type == self::TYPE_JAVASCRIPT):

			// Filter with javascript
			foreach ($this->filters as $type => $filter):

?>

<div class="btn-toolbar filters">
	<div class="btn-group" data-toggle="buttons-checkbox">
		<a data-filter="all" class="btn btn-mini btn-inverse active"><?= __('All') ?></a>
	</div>

	<div class="btn-group" data-toggle="buttons-checkbox">

		<?php foreach ($filter['filters'] as $key => $name) { ?>
		<a data-filter="<?= $type . '-' . $key ?>" class="btn btn-mini btn-inverse"><?= HTML::chars($name) ?></a>
		<?php } ?>

	</div>
</div>

<?php

			echo $this->javascript();

			endforeach;

		else:

			// Filter with query
			foreach ($this->filters as $type => $filter):

?>

<div class="btn-toolbar filters">
	<div class="btn-group" data-toggle="buttons-checkbox">
		<a href="<?= self::url(false) ?>" class="btn btn-mini btn-inverse <?= $this->selected ? '' : 'active' ?>"><?= __('All') ?></a>
	</div>

	<div class="btn-group" data-toggle="buttons-checkbox">

		<?php foreach ($filter['filters'] as $key => $name): ?>
		<a href="<?= self::url($name) ?>" class="btn btn-mini btn-inverse <?= $this->selected == $name ? 'active' : '' ?>"><?= HTML::chars($name) ?></a>
		<?php endforeach; ?>

	</div>
</div>

<?php

			endforeach;

		endif;

		return ob_get_clean();
	}


	/**
	 * JavaScripts for live filters.
	 *
	 * @return  string
	 */
	public function javascript() {
		ob_start();

?>

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


	/**
	 * Generates the full URL for a certain page.
	 *
	 * @param   string|boolean  $filter
	 * @return  string
	 */
	public function url($filter) {
		if ($this->type === self::TYPE_URL) {
			return URL::site($this->base_url . '/' . $filter);
		}

		$query = $_GET;
		if ($filter === false) {

			// Remove filters
			unset($query[$this->parameter]);

		} elseif ($this->multiple_query) {

			// Add filter
			$filters   = explode(',', Arr::get($query, $this->parameter));
			$filters[] = $filter;
			$query[$this->parameter] = implode(',', $filters);

		} else {

			// Replace filter
			$query[$this->parameter] = $filter;

		}

		// Clear other parameters?
		if ($this->clear_query) {
			$query = Arr::intersect($query, array($this->parameter));
		}

		list($uri) = $this->base_url ? array($this->base_url) : explode('?', Request::current()->current_uri());

		return URL::site($uri) . URL::query($query);
	}

}
