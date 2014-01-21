<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Page view class.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_View_Page extends View_Base {

	const COLUMN_TOP    = 'top';
	const COLUMN_LEFT   = 'left';
	const COLUMN_CENTER = 'center';
	const COLUMN_RIGHT  = 'right';
	const COLUMN_BOTTOM = 'bottom';
	const COLUMN_FOOTER = 'footer';

	/** @deprecated */
	const COLUMN_MAIN = 'main';

	/** @deprecated */
	const COLUMN_SIDE = 'side';

	/** @deprecated */
	const SPANS_93    = '9+3';

	/** @deprecated */
	const SPANS_84    = '8+4';

	/** @deprecated */
	const SPANS_82    = '8+2';

	/** @deprecated */
	const SPANS_73    = '7+3';

	/** @deprecated */
	const SPANS_66    = '6+6';

	/** @deprecated */
	const SPANS_64    = '6+4';

	/** @deprecated */
	const SPANS_55    = '5+5';

	/**
	 * @var  array  Page actions
	 */
	public $actions = array();

	/**
	 * @var  string  Base URL
	 */
	public $base = '/';

	/**
	 * @var  array  Breadcrumbs
	 */
	public $breadcrumbs = array();

	/**
	 * @var  array  Column contents
	 */
	protected $_content = array();

	/**
	 * @var  array  Container classes
	 */
	protected $_content_class = array();

	/**
	 * @var  string  Page language
	 */
	public $language = 'en';

	/**
	 * @var  array  Notifications
	 */
	public static $notifications = array();

	/**
	 * @var  array  JavaScripts for footer
	 */
	public $scripts = array();

	/**
	 * @var  string  Content column span sizes
	 */
	public $spans = self::SPANS_84;

	/**
	 * @var  string  Active tab
	 */
	public $tab;

	/**
	 * @var  array  Page tabs
	 */
	public $tabs = array();

	/**
	 * @var  string  Page title
	 */
	public $title;

	/**
	 * @var  string  Page title with HTML
	 */
	public $title_html;

	/**
	 * @var  string  Page subtitle
	 */
	public $subtitle;


	/**
	 * Create new page.
	 *
	 * @param  string  $title  Page title
	 */
	public function __construct($title = null) {
		parent::__construct();

		if ($title) {
			$this->title = $title;
		}

		// Get base URL
		$this->base = URL::base(!Request::current()->is_initial());

	}


	/**
	 * Add content to a column.
	 *
	 * @param  string        $column
	 * @param  string|array  $content
	 *
	 * @see  COLUMN_*
	 */
	public function add($column, $content) {
		if (!isset($this->_content[$column])) {
			$this->_content[$column] = array();
		}

		if (is_array($content)) {
			foreach ($content as $_content) {
				$this->_content[$column][] = $_content;
			}
		} else {
			$this->_content[$column][] = $content;
		}
	}


	/**
	 * Render a column.
	 *
	 * @param   string  $column
	 * @return  string
	 *
	 * @see  COLUMN_*
	 */
	public function content($column) {
		if ($deprecated = Widget::get($column)):
			$this->_content[$column] = (array)$this->_content[$column];
			array_unshift($this->_content[$column], $deprecated);
		endif;

		if (!empty($this->_content[$column])):
			$left   = !(empty($this->_content[self::COLUMN_LEFT]));
			$center = !(empty($this->_content[self::COLUMN_CENTER]));
			$right  = !(empty($this->_content[self::COLUMN_RIGHT]));
			ob_start();

			switch ($column):
				case self::COLUMN_LEFT:   $class = $center && $right ? 'col-sm-3' : ($center ? 'col-sm-4' : 'col-sm-6'); break;
				case self::COLUMN_CENTER: $class = $left && $right ? 'col-sm-6' : ($left || $right ? 'col-sm-8' : 'col-sm-12'); break;
				case self::COLUMN_RIGHT:  $class = $left && $center ? 'col-sm-3' : ($center ? 'col-sm-4' : 'col-sm-6'); break;

				// Deprecated
				case self::COLUMN_MAIN: $class = 'col-sm-8'; break;
				case self::COLUMN_SIDE: $class = 'col-sm-4'; break;

				default:                $class = 'col-sm-12';
			endswitch;

?>

			<div id="content-<?= $column ?>" class="<?= $class ?>">

				<?= implode("\n", $this->_content[$column]) ?>

				<?= /*$column === self::COLUMN_SIDE ? '' :*/ Ads::slot($column) ?>

			</div><!-- #content-<?= $column ?> -->

<?php

			return ob_get_clean();
		endif;

		return '';
	}


	/**
	 * Factory method for new page.
	 *
	 * @param  string  $title  Page title
	 */
	public static function factory($title = null) {
		$view = get_called_class();

		return new $view($title);
	}


	/**
	 * Render foot.
	 *
	 * @return  string
	 */
	protected function _foot() {
		ob_start();

		// todo: Move to page controller
?>

<script>
	head.js(
		{ 'jquery':    '//cdnjs.cloudflare.com/ajax/libs/jquery/2.0.3/jquery.min.js' },
		{ 'jquery-ui': '//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js' },
		{ 'bootstrap': '//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.0.3/js/bootstrap.min.js' },
		{ 'vendor':    '<?= $this->base ?>static/js/c/vendor.min.js?_=<?= filemtime('static/js/c/vendor.min.js') ?>' },
		{ 'anqh':      '<?= $this->base ?>static/js/c/anqh.min.js?_=<?= filemtime('static/js/c/anqh.min.js') ?>' },
		function _loaded() {
			Anqh.APIURL = '<?= Kohana::$config->load('api.url') ?>';

			// Search
			var $search = $('#form-search-events, #form-search-users, #form-search-images');
			if ($search.length) {
				$search.on('submit', function _disable(event) {
					event.preventDefault();
				});
				$search.find('[name=search-events]').autocompleteEvent({
					action:   'redirect'
				});
				$search.find('[name=search-users]').autocompleteUser({
					action:   'redirect'
				});
				$search.find('[name=search-images]').autocompleteUser({
					action:   'redirect',
					position: { my: 'right top', at: 'right bottom', of: '#form-search-images', collision: 'flip' }
				});
			}

		}
	);
</script>

<?php

		echo Widget::get('foot');

		echo Ads::foot();

		return ob_get_clean();
	}


	/**
	 * Render <footer>.
	 *
	 * @return  string
	 */
	protected function _footer() {
		ob_start();

?>

<nav role="navigation">
	<ul role="menubar" class="nav nav-pills">
		<?php foreach (Kohana::$config->load('site.menu') as $id => $item): ?>
		<li role="menuitem" class="menu-<?= $id ?>"><?= HTML::anchor($item['url'], HTML::chars($item['text'])) ?></li>
		<?php endforeach ?>
	</ul>
</nav>

<div class="row">

	<?= $this->content(self::COLUMN_FOOTER) ?>

</div>

<hr />

<small class="muted copyright">
	&copy; 2000&ndash;<?= date('Y')?> <?= Kohana::$config->load('site.site_name') ?><br>
	Powered by <?= HTML::anchor('https://github.com/anqh/anqh', 'Anqh v' . Anqh::VERSION, array('target' => '_blank')) ?>
</small>

<?php

		return ob_get_clean();
	}


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

	<title><?= $this->title ? HTML::chars($this->title) : Kohana::$config->load('site.site_name') ?></title>
	<link rel="icon" type="image/png" href="<?= $this->base ?>ui/favicon.png" />

	<?= HTML::style('static/css/anqh.css?_=' . filemtime('static/css/anqh.css')) ?>
	<?= HTML::style('//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.0.3/css/font-awesome.min.css') ?>

	<?= HTML::script('//cdnjs.cloudflare.com/ajax/libs/headjs/1.0.3/head.load.js') ?>
	<?= HTML::script('//maps.googleapis.com/maps/api/js?sensor=false&libraries=places') ?>

	<?= Widget::get('head'); ?>

	<?= Ads::head() ?>

</head>

<?php

		return ob_get_clean();
	}


	/**
	 * Render <header> - visitor, main menu, search etc.
	 *
	 * @return  string
	 */
	protected function _header() {
		ob_start();

		$menu = Kohana::$config->load('site.menu.' . $this->id);

?>

	<header>
		<div class="pull-left">
			<?= HTML::anchor('', Kohana::$config->load('site.site_name'), array('class' => 'brand')) ?>
			<?= ($this->id != 'home' && $menu) ? HTML::anchor($menu['url'], $menu['text'], array('class' => 'section-title')) : '' ?>
		</div>

		<div class="pull-right">
			<?php if (self::$_user_id):
				echo $this->_search();
				echo $this->_visitor();
			else:
				echo $this->_signin();
			endif; ?>
		</div>
	</header>

<?php

		return ob_get_clean();
	}


	/**
	 * Render main menu.
	 *
	 * @return  string
	 */
	protected function _mainmenu() {
		ob_start();

?>

<nav id="mainmenu" role="navigation" class="navbar navbar-inverse navbar-static-top">
<!--	--><?//= HTML::anchor('', Kohana::$config->load('site.site_name'), array('class' => 'brand')) ?>

	<ul class="nav navbar-nav" role="menubar">
		<?php foreach (Kohana::$config->load('site.menu') as $id => $item): if ($item['footer']) continue; ?>
		<li role="menuitem" class="<?= $id == $this->id ? 'active' : '' ?>"><?= HTML::anchor($item['url'], '<i class="' . $item['icon'] . ' visible-xs"></i><span class="hidden-xs">' . $item['text'] . '</span>') ?></li>
		<?php endforeach; ?>
	</ul>

	<?= self::$_user_id ? $this->_visitor() : $this->_signin() ?>

	<?= $this->_search() ?>

</nav><!-- #mainmenu -->

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

<?= $this->_mainmenu() ?>


<!-- ADS -->

<?= Ads::slot(Ads::TOP) ?>

<!-- /ADS -->


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


<footer id="footer" class="content">
	<div class="container">

<?= $this->_footer() ?>

	</div>
</footer><!-- #footer -->

<?= $this->_foot() ?>

<!-- <?= $this->_statistics() ?> -->

</body>

</html>

<?php

		return ob_get_clean();
	}


	/**
	 * Search form.
	 *
	 * @return  string
	 */
	protected function _search() {
		ob_start();

		echo Form::open(null, array('id' => 'search', 'role' => 'search', 'class' => 'navbar-form navbar-right'));

?>

<div class="form-group">
	<?= Form::input('search', null, array('class' => 'form-control', 'placeholder' => __('Search'))) ?>
</div>

<!--
<div id="search">

	<?= Form::open(null, array('id' => 'form-search-events', 'class' => 'hidden-phone')) ?>
		<label>
			<i class="icon-calendar"></i>
			<?= Form::input('search-events', null, array('class' => 'input-small search-query', 'placeholder' => __('Search events..'), 'title' => __('Enter at least 3 characters'))); ?>
		</label>
	<?= Form::close(); ?>

	<?= Form::open(null, array('id' => 'form-search-images', 'class' => 'hidden-phone')) ?>
		<label>
			<i class="icon-camera-retro"></i>
			<?= Form::input('search-images', null, array(
					'class'         => 'input-small search-query',
					'placeholder'   => __('Search images..'),
					'title'         => __('Enter at least 2 characters'),
					'data-redirect' => Route::url('galleries', array('action' => 'search')) . '?user=:value'
				)); ?>
		</label>
	<?= Form::close(); ?>

	<?= Form::open(null, array('id' => 'form-search-users', 'class' => 'hidden-phone')) ?>
		<label>
			<i class="icon-user"></i>
			<?= Form::input('search-users', null, array('class' => 'input-small search-query', 'placeholder' => __('Search users..'), 'title' => __('Enter at least 2 characters'))); ?>
		</label>
	<?= Form::close(); ?>

</div>
-->

<?php

		echo Form::close();

		return ob_get_clean();
	}


	/**
	 * Login form.
	 *
	 * @return  string
	 */
	protected function _signin() {
		ob_start();

?>

	<?= Form::open(Route::url('sign', array('action' => 'in')), array('id' => 'signin', 'class' => 'form-inline')) ?>
		<?= HTML::anchor(
				Route::url('sign', array('action' => 'up')),
				__('Sign up') . ' <i class="icon-heart"></i>',
				array('class' => 'btn btn-lovely', 'title' => __("Did we mention it's FREE!"))
			) ?>
		<?= HTML::anchor(
				Route::url('oauth', array('action' => 'login', 'provider' => 'facebook')),
				__('Sign in with') . ' <i class="icon-facebook"></i>',
				array('class' => 'btn btn-primary', 'title' => __('Sign in with your Facebook account'))
			) ?>
	|
		<?= Form::input('username', null, array('class' => 'input-mini', 'placeholder' => __('Username'), 'title' => __('HOT TIP: You can also use your email'))) ?>
		<?= Form::password('password', null, array('class' => 'input-mini', 'placeholder' => __('Password'), 'title' => __('Forgot it? Just leave me empty'))) ?>
		<?= Form::button(null, __('Sign in') . ' <i class="icon-signin"></i>', array('class' => 'btn btn-primary', 'title' => __('Remember to sign out if on a public computer!'))) ?>
		<?= Form::hidden('remember', 'true') ?>
	<?= Form::close(); ?>

<?php

		return ob_get_clean();
	}


	/**
	 * Render page statistics.
	 *
	 * @return  string
	 */
	private function _statistics() {

		// Count DB queries
		$queries = 0;
		if (Kohana::$profiling) {
			foreach (Profiler::groups() as $group => $benchmarks) {
				if (strpos($group, 'database') === 0) {
					$queries += count($benchmarks);
				}
			}
		}

		return __('Page rendered in :execution_time seconds, using :memory_usage of memory, :database_queries database queries and :included_files files', array(
			':memory_usage'     => number_format((memory_get_peak_usage() - KOHANA_START_MEMORY) / 1024, 2) . 'KB',
			':execution_time'   => number_format(microtime(true) - KOHANA_START_TIME, 5),
			':database_queries' => $queries,
			':included_files'   => count(get_included_files()),
		));
	}


	/**
	 * Render page title.
	 *
	 * @return  string
	 */
	protected function _title() {
		if ($this->title || $this->title_html || $this->actions || $this->breadcrumbs || $this->tabs) {
			ob_start();

?>

<header id="title" class="content">
	<div class="container">

		<?php if ($this->breadcrumbs): ?>
		<nav class="breadcrumbs">
			<?= implode(' &rsaquo; ', $this->breadcrumbs); ?>
		</nav>
		<?php endif; ?>

		<?php if ($this->actions): ?>
		<div class="actions">
			<?php foreach ($this->actions as $action):
					$attributes = $action;
					unset($attributes['link'], $attributes['text']);
					$attributes['class'] = $attributes['class'] ? 'btn ' . $attributes['class'] : 'btn btn-inverse';

					echo HTML::anchor($action['link'], $action['text'], $attributes) . ' ';
				endforeach; ?>
		</div>
		<?php endif; ?>

		<?php if ($this->title_html || $this->title): ?>
		<h1><?= $this->title_html ? $this->title_html : HTML::chars($this->title) ?></h1>
		<?php endif; ?>

		<?php if ($this->subtitle): ?>
		<p><?= $this->subtitle ?></p>
		<?php endif; ?>

		<?php if ($this->tabs): ?>
		<ul class="nav nav-tabs">

		<?php foreach ($this->tabs as $tab_id => $tab):
				if (is_array($tab)):

					// Tab is a link
					$attributes = $tab;
					unset($attributes['link'], $attributes['text'], $attributes['dropdown'], $attributes['active']);

					if ($tab['dropdown']):
						$attributes['class']      .= ' dropdown-toggle';
						$attributes['data-toggle'] = 'dropdown';
						$attributes['data-target'] = '#';
		?>

			<li class="dropdown<?= $tab_id === $this->tab ? ' active' : '' ?>">
				<?= HTML::anchor($tab['link'], $tab['text'] . ' <span class="caret"></span>', $attributes) ?>
				<ul class="dropdown-menu">
					<?php foreach ($tab['dropdown'] as $dropdown): ?>
						<?php if ($dropdown['divider']): ?>
					<li class="divider"></li>
						<?php else: ?>
					<li><?= HTML::anchor(Arr::get_once($dropdown, 'link'), Arr::get_once($dropdown, 'text'), $dropdown) ?></li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			</li>

		<?php

					else:
						echo '<li' . ($tab_id === $this->tab ? ' class="active"' : '') . '>' . HTML::anchor($tab['link'], $tab['text'], $attributes) . '</li>';
					endif;

				else:

					// Action is HTML
					echo '<li' . ($tab_id === $this->tab ? ' class="active"' : '') . '>' . $tab . '</li>';

				endif;
			endforeach;

		?>

		</ul>
		<?php endif; ?>

	</div>
</header><!-- #title -->

<?php

			return ob_get_clean();
		}

		return '';
	}


	/**
	 * Render visitor.
	 *
	 * @return  string
	 */
	protected function _visitor() {
		ob_start();

?>

<ul id="visitor" class="nav navbar-nav navbar-right">
	<li><?= implode(' ', Anqh::notifications(self::$_user)) ?></li>

	<li class="dropdown">
		<?= HTML::avatar(self::$_user->avatar, self::$_user->username, 'small') ?>
		<a class="user dropdown-toggle" href="#menu-profile" data-toggle="dropdown"><?= HTML::chars(self::$_user->username) ?> <i class="fa fa-caret-down"></i></a>
		<ul class="dropdown-menu pull-right" role="menu">
			<?php foreach (Kohana::$config->load('site.menu_visitor') as $item): ?>
			<li role="menuitem"><?= HTML::anchor($item['url'], '<i class="' . $item['icon'] . '"></i> ' . $item['text']) ?></li>
			<?php endforeach; ?>
			<?php if (self::$_user->has_role('admin')): ?>
			<li role="presentation" class="dropdown-header"><?= __('Admin functions') ?></li>
				<?php foreach (Kohana::$config->load('site.menu_admin') as $item): ?>
			<li role="menuitem"><?= HTML::anchor($item['url'], '<i class="' . $item['icon'] . '"></i> ' . $item['text'], Arr::get($item, 'attributes')) ?></li>
				<?php endforeach; ?>
			<?php endif; ?>
		</ul>
	</li>
</ul>

<?php

		return ob_get_clean();
	}

}
