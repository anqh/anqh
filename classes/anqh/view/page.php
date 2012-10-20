<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Page view class.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011-2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_View_Page extends View_Base {

	const COLUMN_MAIN = 'main';
	const COLUMN_SIDE = 'side';
	const COLUMN_TOP  = 'top';

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
	 * @var  array  Basic stylesheets
	 */
	public $styles = array();

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

		if ($title) $this->title = $title;

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
		if ($deprecated = Widget::get($column)) {
			$this->_content[$column] = (array)$this->_content[$column];
			array_unshift($this->_content[$column], $deprecated);
		}

		if (!empty($this->_content[$column])) {
			ob_start();

			switch ($column) {
				case self::COLUMN_MAIN: $class = 'span8'; break;
				case self::COLUMN_SIDE: $class = 'span4'; break;
				case self::COLUMN_TOP:  $class = 'span12'; break;
				default:                $class = '';
			}

?>

			<div id="<?= $column ?>" class="<?= $class ?>">

				<?= implode("\n<hr /><!--<br />\n-->", $this->_content[$column]) ?>

				<?= Ads::slot($column) ?>

			</div><!-- #<?php echo $column ?> -->

<?php

			return ob_get_clean();
		}

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

	<div id="content" class="container">

		<?php echo $this->_title() ?>

		<div class="row">

			<?php echo $this->content(self::COLUMN_TOP) ?>

		</div>

		<div class="row">

			<?php echo $this->content(self::COLUMN_MAIN) ?>

			<?php echo $this->content(self::COLUMN_SIDE) ?>

		</div>

	</div><!-- #content -->

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
			{ 'google-maps':        'http://maps.google.com/maps/api/js?sensor=false&callback=isNaN' }, // Use callback hack to initialize correctly
			{ 'jquery':             'http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js' },
			{ 'jquery-ui':          'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js' },
			{ 'bootstrap':          '<?= $this->base ?>static/js/bootstrap.js' },
			{ 'jquery-markitup':    '<?= $this->base ?>js/jquery.markitup.js' },
			{ 'bbcode':             '<?= $this->base ?>js/markitup.bbcode.js' },
			{ 'jquery-tools':       '<?= $this->base ?>js/jquery.tools.min.js' },
			{ 'jquery-form':        '<?= $this->base ?>js/jquery.form.js' },
			{ 'jquery-imgarea':     '<?= $this->base ?>js/jquery.imgareaselect.js' },
			{ 'jquery-fixedscroll': '<?= $this->base ?>js/jquery-scrolltofixed.js' },
			{ 'anqh':               '<?= $this->base ?>js/anqh.js?2' },
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

	<footer id="footer" class="container">

		<section>

			<nav role="navigation">
				<ul role="menubar" class="nav nav-pills">
					<li role="menuitem"><a href="<?= $this->base ?>"><?= __('Front page') ?></a></li>
					<?php foreach (Kohana::$config->load('site.menu') as $id => $item): ?>
					<li role="menuitem" class="menu-<?= $id ?>"><a href="<?= $item['url'] ?>"><?= HTML::chars($item['text']) ?></a></li>
					<?php endforeach ?>
				</ul>
			</nav>

			<div class="row">

				<?= $this->footer() ?>

			</div>

		</section>

		<hr />

		<section class="copyright">
			<?= $this->_statistics() ?><br />
			Copyright &copy; 2000&ndash;<?php echo date('Y')?> <?php echo Kohana::$config->load('site.site_name') ?> -
			Powered by Anqh v<?php echo Anqh::VERSION ?> and Kohana v<?php echo Kohana::VERSION ?>
		</section>

	</footer><!-- #footer -->

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
	<meta charset="<?php echo Kohana::$charset ?>" />
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo Kohana::$charset ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />

	<title><?php echo ($this->title ? HTML::chars($this->title) . ' | ' : '') . Kohana::$config->load('site.site_name') ?></title>
	<link rel="icon" type="image/png" href="<?php echo $this->base ?>ui/favicon.png" />

	<?= $this->_styles() ?>

	<?= $this->_skins() ?>

	<?= HTML::style('ui/site.css') ?>

	<?= HTML::script(Kohana::$environment == Kohana::PRODUCTION ? 'js/head.min.js' : 'js/head.js') ?>

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

?>

	<header id="header" class="navbar navbar-fixed-top navbar-inverse">
		<div class="container">
			<div class="pull-left">

<?= $this->_mainmenu() ?>

			</div>
			<div class="pull-right">

<?php if (self::$_user_id):
	echo $this->_visitor();
else:
	echo $this->_signin();
endif; ?>

			</div>
		</div>

	</header><!-- #header -->

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
		<?= HTML::anchor($this->base, Kohana::$config->load('site.site_name'), array('class' => 'brand')) ?>

		<ul class="nav" role="menubar">
			<?php foreach (Kohana::$config->load('site.menu') as $id => $item): ?>
			<li role="menuitem" class="menu-<?= $id ?>"><?= HTML::anchor($item['url'], '<i class="' . $item['icon'] . ' icon-white visible-phone"></i><span class="hidden-phone">' . $item['text'] . '</span>') ?></li>
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


	<!-- HEADER -->

	<?= $this->_header() ?>

	<!-- /HEADER -->


	<?= Ads::slot(Ads::TOP) ?>


	<!-- CONTENT -->

	<?= $this->_content() ?>

	<!-- /CONTENT -->


	<!-- FOOTER -->

	<?= $this->_footer() ?>

	<!-- /FOOTER -->


	<?= $this->_foot() ?>

</body>

</html>

<?php

		return ob_get_clean();
	}


	/**
	 * Render search.
	 *
	 * @return  string
	 */
	protected function _search() {
		ob_start();

		echo Form::open(null, array('id' => 'form-search', 'class' => 'navbar-form form-search navbar-search pull-right hidden-phone'));

		echo Form::input('search-events', null, array('class' => 'input-small search-query', 'placeholder' => __('Search events..'), 'title' => __('Enter at least 3 characters')));
		echo Form::input('search-users', null, array('class' => 'input-small search-query', 'placeholder' => __('Search users..'), 'title' => __('Enter at least 2 characters')));

		/*
?>

	<div class="input-append">

		<?= Form::input('search', null, array('class' => 'input-medium search-query', 'placeholder' => __('Search..'))) .
		    Form::button(null, '<i class="icon-search icon-white"></i>', array('class' => 'btn btn-inverse')) ?>

	</div>

<?php
		*/

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

	<nav id="visitor">
		<?= Form::open(Route::url('sign', array('action' => 'in')), array('class' => 'navbar-form form-inline')) ?>
		<?= Form::input('username', null, array('class' => 'input-mini', 'placeholder' => __('Username'), 'title' => __('HOT TIP: You can also use your email'))) ?>
		<?= Form::password('password', null, array('class' => 'input-mini', 'placeholder' => __('Password'), 'title' => __('Forgot it? Just leave me empty'))) ?>
		<?= Form::button(null, __('Sign in'), array('class' => 'btn btn-primary', 'title' => __('Remember to sign out if on a public computer!'))) ?>
		<?= Form::hidden('remember', 'true') ?>
		<?= HTML::anchor(
				Route::url('sign', array('action' => 'up')),
				__('Sign up') . ' <i class="icon-heart icon-white"></i>',
				array('class' => 'btn btn-lovely', 'title' => __("Did we mention it's FREE!"))
			) ?>
		<?= Form::close(); ?>
	</nav>

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
		if ($this->title || $this->actions) {
			ob_start();

?>

			<header id="title">

				<?php if ($this->breadcrumbs): ?>
				<nav>

					<?php foreach ($this->breadcrumbs as $breadcrumb)
						echo HTML::anchor($breadcrumb['url'], $breadcrumb['text']); ?>

				</nav>
				<?php endif; ?>

				<?php if ($this->title_html || $this->title): ?>
				<h1><?= $this->title_html ? $this->title_html : HTML::chars($this->title) ?></h1>
				<?php endif; ?>

				<?php if ($this->subtitle): ?>
				<p><?= $this->subtitle ?></p>
				<?php endif; ?>

				<?php if ($this->actions): ?>
				<nav>

				<?php foreach ($this->actions as $action):
						if (is_array($action)):

							// Action is a link
							$attributes = $action;
							unset($attributes['link'], $attributes['text']);
							$attributes['class'] = isset($attributes['class']) ? 'btn ' . $attributes['class'] : 'btn btn-inverse';
							echo HTML::anchor($action['link'], $action['text'], $attributes) . ' ';

						else:

							// Action is HTML
							echo $action;

						endif;
					endforeach; ?>

				</nav>
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

	<nav id="visitor" class="navbar-text">
		<ul class="nav" role="menubar">
			<li class="menuitem-notifications"><span><?= implode(' ', Anqh::notifications(self::$_user)) ?></span></li>
			<li role="menuitem" class="menuitem-profile"><?= HTML::avatar(self::$_user->avatar, self::$_user->username, true) ?></li>

			<li class="dropdown menu-me" role="menuitem" aria-haspopup="true">
				<a class="dropdown-toggle" href="#" data-toggle="dropdown"><?= HTML::chars(self::$_user->username) ?> <b class="caret"></b></a>
				<ul class="dropdown-menu pull-right" role="menu">
					<li role="menuitem"><?= HTML::anchor(URL::user(self::$_user->username), '<i class="icon-home icon-white"></i> ' . __('Profile')) ?><li>
					<li role="menuitem"><?= HTML::anchor(Forum::private_messages_url(), '<i class="icon-envelope icon-white"></i> ' . __('Private messages')) ?></li>
					<li role="menuitem"><?= HTML::anchor(URL::user(self::$_user, 'friends'), '<i class="icon-heart icon-white"></i> ' . __('Friends')) ?></li>
					<li role="menuitem"><?= HTML::anchor(URL::user(self::$_user, 'ignores'), '<i class="icon-ban-circle icon-white"></i> ' . __('Ignores')) ?></li>
					<li role="menuitem"><?= HTML::anchor(URL::user(self::$_user, 'settings'), '<i class="icon-cog icon-white"></i> ' . __('Settings')) ?></li>
					<?php if (self::$_user->has_role('admin')) { ?>
					<li class="divider"></li>
					<li class="nav-header"><?= __('Admin functions') ?></li>
					<li role="menuitem" class="admin"><?= HTML::anchor(Route::url('roles'), '<i class="icon-asterisk icon-white"></i> ' . __('Roles')) ?></li>
					<li role="menuitem" class="admin"><?= HTML::anchor(Route::url('tags'), '<i class="icon-tags icon-white"></i> ' . __('Tags')) ?></li>
					<li role="menuitem" class="admin"><?= HTML::anchor('#debug', '<i class="icon-signal icon-white"></i> ' . __('Profiler'), array('onclick' => "$('div.kohana').toggle();")) ?></li>
					<?php } ?>
					<li class="divider"></li>
					<li role="menuitem">
						<?= HTML::anchor(Route::url('sign', array('action' => 'out')), '<i class="icon-off icon-white"></i> ' . __('Sign out')) ?>
					</li>
				</ul>
			</li>

			<li class="dropdown menu-search" role="menuitem" aria-haspopup="true">
					<a class="dropdown-toggle" href="#" data-toggle="dropdown"><i class="icon-search icon-white"></i> <b class="caret"></b></a>
					<ul class="dropdown-menu pull-right" role="menu">
						<li role="menuitem">
							<?= Form::open(null, array('id' => 'form-search-events', 'class' => 'hidden-phone')) ?>
							<?= Form::input('search-events', null, array('class' => 'input-small search-query', 'placeholder' => __('Search events..'), 'title' => __('Enter at least 3 characters'))); ?>
							<?= Form::close(); ?>
						</li>
						<li role="menuitem">
							<?= Form::open(null, array('id' => 'form-search-users', 'class' => 'hidden-phone')) ?>
							<?= Form::input('search-users', null, array('class' => 'input-small search-query', 'placeholder' => __('Search users..'), 'title' => __('Enter at least 2 characters'))); ?>
							<?= Form::close(); ?>
						</li>
					</ul>
			</li>


		</ul>
	</nav><!-- #visitor -->


<?php

		return ob_get_clean();
	}

}
