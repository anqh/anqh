<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * View_Page
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
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

			<div id="<?php echo $column ?>" class="<?php echo $class ?>">

				<?php echo implode("\n<!--<br />\n-->", $this->_content[$column]) ?>

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
			{ 'bootstrap':          '<?php echo $this->base ?>static/js/bootstrap.js' },
			{ 'jquery-markitup':    '<?php echo $this->base ?>js/jquery.markitup.js' },
			{ 'bbcode':             '<?php echo $this->base ?>js/markitup.bbcode.js' },
			{ 'jquery-tools':       '<?php echo $this->base ?>js/jquery.tools.min.js' },
			{ 'jquery-form':        '<?php echo $this->base ?>js/jquery.form.js' },
			{ 'jquery-imgarea':     '<?php echo $this->base ?>js/jquery.imgareaselect.js' },
			{ 'jquery-fixedscroll': '<?php echo $this->base ?>js/jquery-scrolltofixed.js' },
			{ 'anqh':               '<?php echo $this->base ?>js/anqh.js?2' }
		);
	</script>

<?php

		echo $this->foot();

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

		<section role="complementary">

			<nav role="navigation">
				<ul role="menubar" class="nav nav-pills">
					<li role="menuitem"><a href="<?php echo $this->base ?>"><?php echo __('Front page') ?></a></li>
					<?php foreach (Kohana::config('site.menu') as $id => $item) { ?>
					<li role="menuitem" class="menu-<?php echo $id ?>"><a href="<?php echo $item['url'] ?>"><?php echo HTML::chars($item['text']) ?></a></li>
					<?php } ?>
				</ul>
			</nav>

			<div class="row">

				<?php echo $this->footer() ?>

			</div>

		</section>

		<hr />

		<section role="contentinfo">
			<?php echo $this->_statistics() ?><br />
			Copyright &copy; 2000&ndash;<?php echo date('Y')?> <?php echo Kohana::config('site.site_name') ?> -
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
	<base href="<?php echo $this->base ?>" />

	<title><?php echo ($this->title ? HTML::chars($this->title) . ' | ' : '') . Kohana::config('site.site_name') ?></title>
	<link rel="icon" type="image/png" href="<?php echo $this->base ?>ui/favicon.png" />

	<?php echo $this->_styles() ?>

	<?php echo $this->_skins() ?>

	<?php echo HTML::style('ui/site.css') ?>

	<?php echo HTML::script(Kohana::$environment == Kohana::PRODUCTION ? 'js/head.min.js' : 'js/head.js') ?>

	<?php echo $this->head(); ?>

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

	<header id="header" class="navbar navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container">

		<?php echo $this->_notifications() ?>

		<?php echo $this->_mainmenu() ?>

		<?php echo $this->_visitor() ?>

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
		<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</a>
		<a class="brand" href="<?php echo $this->base ?>"><?php echo Kohana::config('site.site_name') ?></a>
		<ul class="nav nav-collapse" role="menubar">
			<?php foreach (Kohana::config('site.menu') as $id => $item) { ?>
			<li role="menuitem" class="menu-<?php echo $id ?>"><a href="<?php echo $item['url'] ?>"><?php echo HTML::chars($item['text']) ?></a></li>
			<?php } ?>
		</ul>
	</nav><!-- #mainmenu -->

<?php

		return ob_get_clean();
	}


	/**
	 * Add new notification.
	 *
	 * @static
	 * @param   string  $message
	 * @param   string  $type
	 */
	public static function notify($message, $type = null) {
		self::$notifications[] = array(
			'message' => $message,
			'type'    => $type,
		);
	}


	/**
	 * Render notifications.
	 *
	 * @return  string
	 */
	protected function _notifications() {
		if (self::$notifications) {
			ob_start();

			foreach (self::$notifications as $notification) {
?>

		<div role="alert"><?php echo $notification['message'] ?></div>

<?php
			}

			return ob_get_clean();
		}

		return '';
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
<html lang="<?php echo $this->language ?>">

<?php echo $this->_head() ?>

<body id="<?php echo $this->id ?>" class="<?php echo $this->class ?>">


	<!-- HEADER -->

	<?php echo $this->_header() ?>

	<!-- /HEADER -->


	<?php echo Widget::get('ad_top') ?>


	<!-- CONTENT -->

	<?php echo $this->_content() ?>

	<!-- /CONTENT -->


	<!-- FOOTER -->

	<?php echo $this->_footer() ?>

	<!-- /FOOTER -->


	<?php echo $this->_foot() ?>

</body>

</html>

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

				<?php if ($this->title) { ?>
				<h1><?php echo HTML::chars($this->title) ?></h1>
				<?php } ?>

				<?php if ($this->subtitle) { ?>
				<p><?php echo $this->subtitle ?></p>
				<?php } ?>

				<?php if ($this->actions) { ?>
				<nav>

				<?php foreach ($this->actions as $action) {
						if (is_array($action)) {

							// Action is a link
							$attributes = $action;
							unset($attributes['link'], $attributes['text']);
							$attributes['class'] = isset($attributes['class']) ? 'btn ' . $attributes['class'] : 'btn';
							echo HTML::anchor($action['link'], $action['text'], $attributes) . ' ';

						} else {

							// Action is HTML
							echo $action;

						}
					} ?>

				</nav>
				<?php } ?>

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

		if (self::$_user_id) {

			// Authenticated user
?>

	<nav id="visitor" class="nav-collapse navbar-text pull-right">
		<ul class="nav" role="menubar">

			<?php foreach (Anqh::notifications(self::$_user) as $class => $link) { ?>
			<li role="menuitem" class="<?php echo $class ?>"><?php echo $link ?></li>
			<?php } ?>

			<li role="menuitem" class="menuitem-profile"><?php echo HTML::avatar(self::$_user->avatar, self::$_user->username, true) ?></li>

			<li class="dropdown menu-me" role="menuitem" aria-haspopup="true">
				<a class="dropdown-toggle" href="#" data-toggle="dropdown"><?php echo HTML::chars(self::$_user->username) ?> <b class="caret"></b></a>
				<ul class="dropdown-menu" role="menu">
					<li role="menuitem"><a href="<?php echo URL::user(self::$_user->username) ?>"><i class="icon-home"></i> <?php echo __('Profile') ?></a><li>
					<li role="menuitem"><a href="<?php echo Forum::private_messages_url() ?>"><i class="icon-envelope"></i> <?php echo __('Private messages') ?></a></li>
					<li role="menuitem"><a href="<?php echo URL::user(self::$_user, 'friends') ?>"><i class="icon-user"></i> <?php echo  __('Friends') ?></a></li>
					<li role="menuitem"><a href="<?php echo URL::user(self::$_user, 'ignores') ?>"><i class="icon-ban-circle"></i> <?php echo __('Ignores') ?></a></li>
					<li role="menuitem"><a href="<?php echo URL::user(self::$_user, 'settings') ?>"><i class="icon-cog"></i> <?php echo __('Settings') ?></a></li>
					<?php if (self::$_user->has_role('admin')) { ?>
					<li role="menuitem" class="admin"><a href="<?php echo Route::url('roles') ?>"><i class="icon-asterisk"></i> <?php echo __('Roles') ?></a></li>
					<li role="menuitem" class="admin"><a href="<?php echo Route::url('tags') ?>"><i class="icon-tags"></i> <?php echo __('Tags') ?></a></li>
					<li role="menuitem" class="admin"><a href="#debug" onclick="$('div.kohana').toggle();"> <i class="icon-signal"></i> <?php echo __('Profiler') ?></a></li>
					<?php } ?>
				</ul>
			</li>

			<li role="menuitem" class="menu-signout"><?php echo HTML::anchor(Route::url('sign', array('action' => 'out')), __('Sign out')) ?></li>
		</ul>
	</nav><!-- #visitor -->


<?php

		} else {

			// Non-authenticated user

?>

	<nav id="visitor" class="nav-collapse navbar-text pull-right">
		<form class="nav form-inline" action="<?php echo Route::url('sign', array('action' => 'in')) ?>" method="post">
			<?php echo HTML::anchor(Route::url('sign', array('action' => 'up')), __('Sign up!')) ?>
			<input class="input-small" name="username" type="text" placeholder="<?php echo __('Username') ?>" />
			<input class="input-small" name="password" type="password" placeholder="<?php echo __('Password') ?>" />
			<button class="btn btn-small btn-primary" type="submit" title="<?php echo __('Automatically sign out if idle') ?>"><?php echo __('Sign in') ?></button>
			<button class="btn btn-small btn-inverse" type="submit" name="remember" value="true" title="<?php echo __('Remember your sign in on this browser') ?>"><?php echo __('Stay in') ?></button>
		</form>
	</nav>

<?php

		}

		return ob_get_clean();
	}

}
