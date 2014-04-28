<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Filters view.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011-2014 Antti Qvickström
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
	 * @var  string  Section class
	 */
	public $class = 'filters';

	/**
	 * @var  boolean  Clear query string
	 */
	public $clear_query = false;

	/**
	 * @var  array  Filters
	 */
	public $filters = array();

	/**
	 * @var  string  Section id
	 */
	public $id = 'filters';

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

?>

<button type="button" class="btn btn-<?= $this->selected ? 'info' : 'default' ?> btn-sm filter" data-toggle="collapse" data-target="#<?= $this->id ?> .collapse">
	<i class="fa fa-filter"></i> <?= __('Show') ?>: <span><?= $this->selected ? $this->selected : __('All') ?></span>
</button>

<?php

		switch ($this->type):

			// Filter with javascript
			case self::TYPE_JAVASCRIPT:
				foreach ($this->filters as $type => $filter):

?>

<div class="btn-toolbar filters collapse">
	<button type="button" data-filter="all" data-toggle="button" class="btn btn-xs btn-default"><?= __('All') ?></button>

	<?php foreach ($filter['filters'] as $key => $name): ?>
	<button type="button" data-filter="<?= $type . '-' . $key ?>" data-toggle="button" class="btn btn-xs btn-default"><?= HTML::chars($name) ?></button>
	<?php endforeach ?>

</div>

<?php

				endforeach;

				echo $this->javascript();
				break;


			// Filter with query
			case self::TYPE_QUERY:
				foreach ($this->filters as $filter):

?>

<div class="btn-toolbar filters collapse">
	<button type="button" href="<?= self::url(false) ?>" data-toggle="button" class="btn btn-xs btn-default <?= $this->selected ? 'active' : '' ?>"><?= __('All') ?></button>

	<?php foreach ($filter['filters'] as $name): ?>
	<button type="button" href="<?= self::url($name) ?>" data-toggle="button" class="btn btn-xs btn-default <?= $this->selected == $name ? 'active' : '' ?>"><?= HTML::chars($name) ?></button>
	<?php endforeach; ?>

</div>

<?php

				endforeach;
				break;


			// Navigate to url
			case self::TYPE_URL:
				foreach ($this->filters as $filter):

?>

<div class="btn-toolbar filters collapse">
	<a href="<?= self::url(false) ?>" class="btn btn-xs btn-default <?= !$this->selected ? 'active' : '' ?>"><?= __('All') ?></a>

	<?php foreach ($filter['filters'] as $name): ?>
	<a href="<?= self::url($name) ?>" class="btn btn-xs btn-default <?= $this->selected == $name ? 'active' : '' ?>"><?= HTML::chars($name) ?></a>
	<?php endforeach; ?>

</div>

<?php

				endforeach;
				break;

		endswitch;

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
	head.ready('jquery', function _hookFilters() {
		var $filters = $('#<?= $this->id ?> .filters .btn')
		  , $filter  = $('#<?= $this->id ?> .filter span');

		$filters.on('click', function _filterClick() {
			var activated = !$(this).hasClass('active') // Class is toggled after this
			  , filter    = $(this).data('filter');

			if (filter === 'all' && activated) {

				// Show all
				$filters.filter('[data-filter!=all]').removeClass('active');

			} else if (filter !== 'all') {

				// Individual filters, uncheck 'All'
				$filters.filter('[data-filter=all]').removeClass('active');

			}

			// Show/hide filtered items
			$filters.each(function _filterToggle() {
				var filtering = $(this).data('filter')
				  , active    = $(this).hasClass('active');

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

			// Update button after class toggled
			setTimeout(function _runNext() {
				var filters = [];
				$filters.filter('.active').each(function _filter() {
					filters.push($(this).text());
				});
				$filter.text(filters.join(', '));
			}, 0);

		});
	});

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
