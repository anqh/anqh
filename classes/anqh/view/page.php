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
	 * @var  string  Page <title>
	 */
	public $title;


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
	 * @param  string  $column
	 * @param  string  $content
	 *
	 * @see  COLUMN_*
	 */
	public function add($column, $content) {
		if (!isset($this->_content[$column])) {
			$this->_content[$column] = array($content);
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
		if (!empty($this->_content[$column])) {
			ob_start();

?>

			<div id="<?php echo $column ?>">

				<?php echo implode("\n\n", $this->_content[$column]) ?>

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

	<div id="content">

		<?php echo $this->_title() ?>

		<?php echo $this->content(self::COLUMN_TOP) ?>

		<?php echo $this->content(self::COLUMN_MAIN) ?>

		<?php echo $this->content(self::COLUMN_SIDE) ?>

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
			{ 'google-maps':     'http://maps.google.com/maps/api/js?sensor=false&callback=isNaN' }, // Use callback hack to initialize correctly
			{ 'jquery':          'http://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js' },
			{ 'jquery-ui':       'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js' },
			{ 'jquery-markitup': '<?php echo $this->base ?>js/jquery.markitup.js' },
			{ 'bbcode':          '<?php echo $this->base ?>js/markitup.bbcode.js' },
			{ 'jquery-tools':    '<?php echo $this->base ?>js/jquery.tools.min.js' },
			{ 'jquery-form':     '<?php echo $this->base ?>js/jquery.form.js' },
			{ 'jquery-imgarea':  '<?php echo $this->base ?>js/jquery.imgareaselect.js' },
			{ 'anqh':            '<?php echo $this->base ?>js/anqh.js?2' },
			function() {
				<!-- GeoNames {{#geo}}-->
				Anqh.geoNamesURL  = '{{url}}';
				Anqh.geoNamesUser = '{{user}}';
				<!-- /GeoNames {{/geo}}-->
			}
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

	<footer id="footer">

		<section role="complementary">

			<?php echo $this->footer() ?>

		</section>

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

	<header id="header">

		<?php echo $this->_notifications() ?>

		<?php echo $this->_mainmenu() ?>

		<?php echo $this->_visitor() ?>

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
		<ul role="menubar">
			<li role="menuitem"><h1><a href="<?php echo $this->base ?>"><?php echo Kohana::config('site.site_name') ?></a></h1></li>
			<?php foreach (Kohana::config('site.menu') as $id => $item) { ?>
			<li role="menuitem" class="menu-<?php echo $id ?> {{selected}}"><a href="<?php echo $item['url'] ?>"><?php echo HTML::chars($item['text']) ?></a></li>
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
		if ($this->title) {
			ob_start();

?>

			<header id="title"><h2><?php echo HTML::chars($this->title) ?></h2></header>

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

		if (self::$_user_id) {

			// Authenticated user
?>

	<nav id="visitor">
		<ul role="menubar">

<!--			{{#notifications}}
			<li role="menuitem" class="menu-notification {{class}}">{{{link}}}</li>
			{{/notifications}}-->

			<li role="menuitem" aria-haspopup="true" class="menu-profile">
				<?php echo HTML::avatar(self::$_user->avatar, self::$_user->username, true) ?>
				<?php echo HTML::user(self::$_user) ?>
				<var>[#<?php echo self::$_user_id ?>]</var>
				<a href="#" class="toggler" onclick="$('#visitor ul[role=menu]').toggleClass('toggled'); return false;">&#9660;</a>
				<ul role="menu">
					<li role="menuitem" class="menu-messages"><?php echo HTML::anchor(Forum::private_messages_url(), __('Private messages'), array('class' => 'icon private-message')) ?></li>
					<li role="menuitem" class="menu-friends"><?php echo HTML::anchor(URL::user(self::$_user, 'friends'), __('Friends'), array('class' => 'icon friends')) ?></li>
					<li role="menuitem" class="menu-ignores"><?php echo HTML::anchor(URL::user(self::$_user, 'ignores'), __('Ignores'), array('class' => 'icon ignores')) ?></li>
					<li role="menuitem" class="menu-settings"><?php echo HTML::anchor(URL::user(self::$_user, 'settings'), __('Settings'), array('class' => 'icon settings')) ?></li>
					<?php if (self::$_user->has_role('admin')) { ?>
					<li role="menuitem" class="menu-roles admin"><?php echo HTML::anchor(Route::url('roles'), __('Roles'), array('class' => 'icon role')) ?></li>
					<li role="menuitem" class="menu-roles admin"><?php echo HTML::anchor(Route::url('tags'), __('Tags'), array('class' => 'icon tag')) ?></li>
					<li role="menuitem" class="menu-roles admin"><?php echo HTML::anchor('#debug', __('Profiler'), array('class' => 'icon profiler', 'onclick' => "\$('div.kohana').toggle();")) ?></li>
					<?php } ?>
				</ul>
			</li>

			<li role="menuitem" class="menu-signout"><?php echo HTML::anchor(Route::url('sign', array('action' => 'out')), __('Sign out')) ?></li>

<!--			<li class="menu-theme">
				Theme: {{#skins}} {{{.}}}{{/skins}}
			</li>-->

			<li class="menu-clock">
				<time class="clock">
					<span class="time"><?php echo Date::format(Date::TIME) ?></span>
					<span class="icon date" title="<?php echo HTML::chars($sunrise) ?>"><?php echo Date::format(Date::DMY_MEDIUM) ?></span>
				</time>
			</li>
		</ul>
	</nav><!-- #visitor -->


<?php

		} else {

			// Non-authenticated user

?>

	<nav id="visitor">
		<form action="<?php echo Route::url('sign', array('action' => 'in')) ?>" method="post">
			<ul>
				<li class="input"><?php echo Form::input('username', null, array('placeholder' => __('Username'))) ?></li>
				<li class="input"><?php echo Form::password('password', null, array('placeholder' => __('Password'))) ?></li>
				<li class="input"><?php echo Form::submit('signin', __('Sign in')) ?></li>
				<li><?php echo HTML::anchor(Route::url('sign', array('action' => 'up')), __('Sign up!')) ?></li>

<!--				<li class="menu-theme">
					Theme: {{#skins}} {{{.}}}{{/skins}}
				</li>-->

				<li class="menu-clock">
					<time class="clock">
						<span class="time"><?php echo Date::format(Date::TIME) ?></span>
						<span class="icon date" title="<?php echo HTML::chars($sunrise) ?>"><?php echo Date::format(Date::DMY_MEDIUM) ?></span>
					</time>
				</li>
			</ul>
		</form>
	</nav>

<?php

		}

		return ob_get_clean();
	}

}
