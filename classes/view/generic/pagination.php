<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Pagination view.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Generic_Pagination extends View_Base {

	/**
	 * @var  boolean  Hide pagination if only 1 page
	 */
	public $auto_hide = true;

	/**
	 * @var  string  Base URL
	 */
	public $base_url;

	/**
	 * @var  integer  Current page number
	 */
	public $current_page;

	/**
	 * @var  string  Text for first page
	 */
	public $first_text = '&laquo;';

	/**
	 * @var  string  First page URL
	 */
	public $first_url;

	/**
	 * @var  integer  How many items to show per page
	 */
	public $items_per_page;

	/**
	 * @var  string  Text for last page
	 */
	public $last_text = '&raquo;';

	/**
	 * @var  string  Last page URL
	 */
	public $last_url;

	/**
	 * @var  string  Text for next page
	 */
	public $next_text = '&rsaquo;';

	/**
	 * @var  string  Next page URL
	 */
	public $next_url;

	/**
	 * @var  integer  Pagination offset for SQL
	 */
	public $offset;

	/**
	 * @var  string  Query string parameter
	 */
	public $parameter = 'page';

	/**
	 * @var  string  Text for previous page
	 */
	public $previous_text = '&lsaquo;';

	/**
	 * @var  string  Previous page URL
	 */
	public $previous_url;

	/**
	 * @var  integer  Total item count
	 */
	public $total_items;

	/**
	 * @var  integer  Total page count
	 */
	public $total_pages;


	/**
	 * Create new pagination.
	 *
	 * @param  array  $setup
	 */
	public function __construct(array $setup = null) {
		parent::__construct();

		if ($setup) {
			foreach ($setup as $key => $value) {
				$this->{$key} = $value;
			}
		}

		$this->setup();
	}


	/**
	 * Go to page with item.
	 *
	 * @param   integer  $item
	 * @return  View_Generic_Pagination
	 */
	public function item($item) {

		// Calculate new page
		$this->current_page = ceil((int)$item / $this->items_per_page);
		$this->previous_url = null;
		$this->next_url     = null;
		$this->offset       = null;

		return $this->setup();
	}


	/**
	 * Go to last page.
	 *
	 * @return  View_Generic_Pagination
	 */
	public function last() {
		$this->current_page = $this->total_pages;

		return $this->setup();
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function render() {
		$this->setup();

		$attributes = array(
			'class' => $this->class . ' pager' //$this->current_page ? 'pagination pagination-centered' : 'pager'
		);

		ob_start();

?>

<ul <?= HTML::attributes($attributes) ?>>

	<?php if ($this->first_url): ?>
	<li class="previous"><?= HTML::anchor($this->first_url, $this->first_text) ?></li>
	<?php endif; ?>

	<?php if ($this->previous_url): ?>
	<li class="previous"><?= HTML::anchor($this->previous_url, $this->previous_text) ?></li>
	<?php endif; ?>

	<?php if ($this->current_page): ?>
	<li class="disabled"><a><?= $this->current_page . ($this->total_pages ? ' / ' . $this->total_pages : '') ?></a></li>
	<?php endif; ?>

	<?php if ($this->last_url): ?>
	<li class="next"><?= HTML::anchor($this->last_url, $this->last_text) ?></li>
	<?php endif; ?>

	<?php if ($this->next_url): ?>
	<li class="next"><?= HTML::anchor($this->next_url, $this->next_text) ?></li>
	<?php endif; ?>

</ul>

<?php

		return ob_get_clean();
	}


	/**
	 * Setup pagination.
	 *
	 * @return  View_Generic_Pagination
	 */
	public function setup() {
		if ($this->total_pages === null && $this->total_items && $this->items_per_page) {
			$this->total_pages = (int)ceil($this->total_items / $this->items_per_page);
		}

		if ($this->current_page === null && $this->total_pages) {
			$page = Arr::get($_GET, $this->parameter, 1);
			$this->current_page = $page == 'last' ? $this->total_pages : (int)$page;
		}

		if ($this->previous_url === null && $this->current_page > 1) {
			$this->previous_url = $this->url($this->current_page - 1);
		}

		if ($this->next_url === null && $this->current_page < $this->total_pages) {
			$this->next_url = $this->url($this->current_page + 1);
		}

		if ($this->first_url === null && $this->total_pages && $this->current_page > 1) {
			$this->first_url = $this->url(1);
		}

		if ($this->last_url === null && $this->total_pages && $this->current_page < $this->total_pages) {
			$this->last_url = $this->url(-1);
		}

		if ($this->offset === null && $this->items_per_page) {
			$this->offset = max(0, $this->current_page - 1) * $this->items_per_page;
		}

		return $this;
	}


	/**
	 * Generates the full URL for a certain page.
	 *
	 * @param   integer  $page
	 * @return  string
	 */
	public function url($page = 1) {

		// Last page
		if ($page === -1 && $this->total_pages) {
			$page = $this->total_pages;
		}

		// Clean the page number
		$page = max(1, (int)$page);


		// No page number in URLs to first page
		if ($page === 1) {
			$page = null;
		}

		list($uri) = $this->base_url ? array($this->base_url) : explode('?', Request::current()->current_uri());
		$query     = $_GET;
		$query[$this->parameter] = $page;

		return URL::site($uri) . URL::query($query);
	}

}
