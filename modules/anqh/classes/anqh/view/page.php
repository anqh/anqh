<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Page view class.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_View_Page extends View_Base {

	const COLUMN_MAIN = 'main';
	const COLUMN_SIDE = 'side';
	const COLUMN_TOP  = 'top';
	const SPANS_93    = '9+3';
	const SPANS_84    = '8+4';
	const SPANS_82    = '8+2';
	const SPANS_73    = '7+3';
	const SPANS_66    = '6+6';
	const SPANS_64    = '6+4';
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
	 * @var  array  Skinned stylesheets
	 */
	public $skins = array();

	/**
	 * @var  string  Content column span sizes
	 */
	public $spans = self::SPANS_84;

	/**
	 * @var  array  Basic stylesheets
	 */
	public $styles = array();

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
			ob_start();

			$spans = explode('+', $this->spans);
			switch ($column):
				case self::COLUMN_MAIN: $class = 'span' . $spans[0]; break;
				case self::COLUMN_SIDE: $class = 'span' . $spans[1]; break;
				case self::COLUMN_TOP:  $class = 'span12'; break;
				default:                $class = '';
			endswitch;

?>

			<div id="<?= $column ?>" class="<?= $class ?>">

				<?= implode("\n", $this->_content[$column]) ?>

				<?= /*$column === self::COLUMN_SIDE ? '' :*/ Ads::slot($column) ?>

			</div><!-- #<?= $column ?> -->

<?php

			return ob_get_clean();
		endif;

		return '';
	}


	/**
	 * Render #content.
	 *
	 * @return  string
	 */
	protected function _content() {
		ob_start();

?>

	<?= $this->_title() ?>

	<div class="row-fluid">

		<?= $this->content(self::COLUMN_TOP) ?>

	</div>

	<div class="row-fluid">

		<?= $this->content(self::COLUMN_MAIN) ?>

		<?= $this->content(self::COLUMN_SIDE) ?>

	</div>

<?php

		return ob_get_clean();
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
	 * Render additional foot.
	 *
	 * @return  string
	 */
	public function foot() {
		return Widget::get('foot');
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
//			{ 'google-maps':        'http://maps.google.com/maps/api/js?sensor=false&libraries=places&callback=isNaN' }, // Use callback hack to initialize correctly
			{ 'jquery':             'http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js' },
			{ 'jquery-ui':          'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js' },
			{ 'bootstrap':          '<?= $this->base ?>static/js/bootstrap.js' },
			{ 'jquery-markitup':    '<?= $this->base ?>js/jquery.markitup.js' },
			{ 'bbcode':             '<?= $this->base ?>js/markitup.bbcode.js' },
			{ 'jquery-tools':       '<?= $this->base ?>js/jquery.tools.min.js' },
			{ 'jquery-form':        '<?= $this->base ?>js/jquery.form.js' },
			{ 'jquery-imgarea':     '<?= $this->base ?>js/jquery.imgareaselect.js' },
			{ 'jquery-fixedscroll': '<?= $this->base ?>js/jquery-scrolltofixed.js' },
			{ 'jquery-lazyload':    '<?= $this->base ?>static/js/jquery.lazyload.min.js' },
			{ 'anqh':               '<?= $this->base ?>js/anqh.js?_=<?= filemtime('js/anqh.js') ?>' },
			function _loaded() {
				Anqh.APIURL = '<?= Kohana::$config->load('api.url') ?>';
			}
		);
	</script>

<?php

		echo $this->foot();

		echo Ads::foot();

		return ob_get_clean();
	}


	/**
	 * Render additional <footer>.
	 *
	 * @return  string
	 */
	public function footer() {
		return Widget::get('footer');
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

<div class="row-fluid">

	<?= $this->footer() ?>

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
	 * Render additional <head>.
	 *
	 * @return  string
	 */
	public function head() {
		return Widget::get('head');
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

	<?= $this->_styles() ?>

	<?= $this->_skins() ?>

	<?= HTML::style('ui/site.css') ?>

	<?= HTML::script(Kohana::$environment == Kohana::PRODUCTION ? 'js/head.min.js' : 'js/head.js') ?>
	<?= HTML::script('https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places') ?>

	<?= $this->head(); ?>

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

	<nav id="mainmenu" role="navigation">
		<?= HTML::anchor('', Kohana::$config->load('site.site_name'), array('class' => 'brand')) ?>

		<ul class="nav" role="menubar">
			<?php foreach (Kohana::$config->load('site.menu') as $id => $item): if ($item['footer']) continue; ?>
			<li role="menuitem" class="menu-<?= $id ?> <?= $id == $this->id ? 'active' : '' ?>"><?= HTML::anchor($item['url'], '<i class="' . $item['icon'] . ' icon-white visible-phone"></i><span class="hidden-phone">' . $item['text'] . '</span>') ?></li>
			<?php endforeach; ?>
		</ul>
	</nav><!-- #mainmenu -->

<?php

		return ob_get_clean();
	}


	/**
	 * Render main menu.
	 *
	 * @return  string
	 */
	protected function _menu() {
		ob_start();

?>

	<nav id="mainmenu" role="navigation">
		<ul class="nav nav-list mainmenu" role="menu">
			<?php foreach (Kohana::$config->load('site.menu') as $id => $item): if ($item['footer']) continue; ?>
			<li role="menuitem" class="menu-<?= $id ?><?= $id == $this->id ? ' active' : '' ?>"><?= HTML::anchor($item['url'], '<i class="' . $item['icon'] . '"></i>', array('title' => HTML::chars($item['text']))) ?></li>
			<?php endforeach; ?>
		</ul>
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
					<div class="background-container">
						<div class="container-fluid">

							<!-- ADS -->

							<?= Ads::slot(Ads::TOP) ?>

							<!-- /ADS -->


							<!-- CONTENT -->

							<?= $this->_content() ?>

							<!-- /CONTENT -->

						</div>
					</div>

					<footer id="footer">
						<div class="background-container">
							<div class="container-fluid">

							<?= $this->_footer() ?>

							</div>
						</div>
					</footer><!-- #footer -->

					<?= $this->_foot() ?>

				</td><!-- /#maincontent -->

			</tr>
		</tbody>
	</table>

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

?>

<div id="search">

	<?= Form::open(null, array('id' => 'form-search-events', 'class' => 'hidden-phone')) ?>
		<label>
			<i class="icon-calendar"></i>
			<?= Form::input('search-events', null, array('class' => 'input-small search-query', 'placeholder' => __('Search events..'), 'title' => __('Enter at least 3 characters'))); ?>
		</label>
	<?= Form::close(); ?>

	<?= Form::open(null, array('id' => 'form-search-images', 'class' => 'hidden-phone')) ?>
		<label>
			<i class="icon-camera"></i>
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

<?php

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
				Route::url('oauth', array('action' => 'login', 'provider' => 'facebook')),
				__('Sign in with') . ' <i class="icon-facebook"></i>',
				array('class' => 'btn btn-primary', 'title' => __('Sign in with your Facebook account'))
			) ?>

		<?= Form::input('username', null, array('class' => 'input-mini', 'placeholder' => __('Username'), 'title' => __('HOT TIP: You can also use your email'))) ?>
		<?= Form::password('password', null, array('class' => 'input-mini', 'placeholder' => __('Password'), 'title' => __('Forgot it? Just leave me empty'))) ?>
		<?= Form::button(null, __('Sign in') . ' <i class="icon-signin"></i>', array('class' => 'btn btn-primary', 'title' => __('Remember to sign out if on a public computer!'))) ?>
		<?= Form::hidden('remember', 'true') ?>
		<?= HTML::anchor(
				Route::url('sign', array('action' => 'up')),
				__('Sign up') . ' <i class="icon-heart"></i>',
				array('class' => 'btn btn-lovely', 'title' => __("Did we mention it's FREE!"))
			) ?>
	<?= Form::close(); ?>

<?php

		return ob_get_clean();
	}


	/**
	 * Render skinned stylesheets.
	 *
	 * @return  string
	 */
	protected function _skins() {
		return implode("\n  ", $this->skins);
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
	 * Render stylesheets.
	 *
	 * @return  string
	 */
	protected function _styles() {
		$styles = array();
		foreach ($this->styles as $style) {
			$styles[] = HTML::style($style);
		}

		return implode("\n  ", $styles);
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

			<header id="title">

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
							<li><?= HTML::anchor($dropdown['link'], $dropdown['text']) ?></li>
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

			</header>

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

		/*
		// Sunrise
		if (self::$_user && self::$_user->latitude && self::$_user->longitude) {
			$latitude  = self::$_user->latitude;
			$longitude = self::$_user->longitude;
		} else {
			$latitude  = 60.1829;
			$longitude = 24.9549;
		}
		$sun = date_sun_info(time(), $latitude, $longitude);
		$sunrise = __(':day, week :week | Sunrise: :sunrise | Sunset: :sunset', array(
			':day'     => strftime('%A'),
			':week'    => strftime('%V'),
			':sunrise' => Date::format(Date::TIME, $sun['sunrise']),
			':sunset'  => Date::format(Date::TIME, $sun['sunset'])
		));
		*/

?>

<div id="visitor">
	<span class="menuitem-notifications"><?= implode(' ', Anqh::notifications(self::$_user)) ?></span>

	<span class="profile dropdown">

		<?= HTML::avatar(self::$_user->avatar, self::$_user->username, 'small') ?>

		<a class="user dropdown-toggle" href="#menu-profile" data-toggle="dropdown"><?= HTML::chars(self::$_user->username) ?> <i class="icon-caret-down"></i></a>
		<ul class="dropdown-menu pull-right" role="menu">
			<li role="menuitem"><?= HTML::anchor(URL::user(self::$_user->username), '<i class="icon-user"></i> ' . __('Profile')) ?><li>
			<li role="menuitem"><?= HTML::anchor(Forum::private_messages_url(), '<i class="icon-envelope"></i> ' . __('Private messages')) ?></li>
			<li role="menuitem"><?= HTML::anchor(URL::user(self::$_user, 'favorites'), '<i class="icon-calendar"></i> ' . __('Favorites')) ?></li>
			<li role="menuitem"><?= HTML::anchor(URL::user(self::$_user, 'friends'), '<i class="icon-heart"></i> ' . __('Friends')) ?></li>
			<li role="menuitem"><?= HTML::anchor(URL::user(self::$_user, 'ignores'), '<i class="icon-ban-circle"></i> ' . __('Ignores')) ?></li>
			<li role="menuitem"><?= HTML::anchor(URL::user(self::$_user, 'settings'), '<i class="icon-cog"></i> ' . __('Settings')) ?></li>
			<li role="menuitem"><?= HTML::anchor(Route::url('sign', array('action' => 'out')), '<i class="icon-signout"></i> ' . __('Sign out')) ?></li>
			<?php if (self::$_user->has_role('admin')): ?>
			<li class="nav-header"><?= __('Admin functions') ?></li>
			<li role="menuitem" class="admin"><?= HTML::anchor(Route::url('roles'), '<i class="icon-asterisk"></i> ' . __('Roles')) ?></li>
			<li role="menuitem" class="admin"><?= HTML::anchor(Route::url('tags'), '<i class="icon-tags"></i> ' . __('Tags')) ?></li>
			<li role="menuitem" class="admin"><?= HTML::anchor('#debug', '<i class="icon-signal"></i> ' . __('Profiler'), array('onclick' => "$('div.kohana').toggle();")) ?></li>
			<?php endif; ?>
		</ul>
	</span>
</div>

<?php

		return ob_get_clean();
	}

}
