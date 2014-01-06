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
				case self::COLUMN_MAIN: $class = 'eleven wide column'; break;
				case self::COLUMN_SIDE: $class = 'five wide column'; break;
				case self::COLUMN_TOP:  $class = 'sixteen wide column'; break;
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

	<div class="ui grid">

		<?= $this->content(self::COLUMN_TOP) ?>

	</div>

	<div class="stackable ui grid">

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
	head.load(
		{ jquery:   '//cdnjs.cloudflare.com/ajax/libs/jquery/2.0.3/jquery.min.js' },
		{ semantic: '//cdnjs.cloudflare.com/ajax/libs/semantic-ui/0.11.0/javascript/semantic.min.js' },
//		{ 'jquery-ui': '//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js' },
		{ vendor:   '<?= $this->base ?>static/js/c/vendor.min.js?_=<?= filemtime('static/js/c/vendor.min.js') ?>' },
		{ anqh:     '<?= $this->base ?>static/js/c/anqh.min.js?_=<?= filemtime('static/js/c/anqh.min.js') ?>' },
		function () {
/*			Anqh.APIURL = '<?= Kohana::$config->load('api.url') ?>';

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
			}*/

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

<nav role="menubar" class="ui center aligned secondary menu">
	<?php foreach (Kohana::$config->load('site.menu') as $id => $item): ?>
	<?= HTML::anchor($item['url'], HTML::chars($item['text']), array(
			'role' => 'menuitem',
			'class' => ($id == $this->id ? 'active ' : '') . 'item'
		)) ?>
	<?php endforeach ?>
</nav>

<div class="ui three column grid">
	<div class="row">

	<?= $this->footer() ?>

	</div>
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

	<?= HTML::script('//cdnjs.cloudflare.com/ajax/libs/headjs/1.0.3/head.load.js') ?>
	<?= HTML::script('//maps.googleapis.com/maps/api/js?sensor=false&libraries=places') ?>

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

	<nav id="mainmenu" role="menubar" class="ui menu inverted">
		<div class="container">

			<?php foreach (Kohana::$config->load('site.menu') as $id => $item): if ($item['footer']) continue; ?>
				<?= HTML::anchor($item['url'], '<!--<i class="' . $item['icon'] . ' icon"></i> --><span>' . $item['text'] . '</span>', array(
					'role'  => 'menuitem',
					'class' => ($id == $this->id ? 'active ' : '') . 'item'
				)) ?>
			<?php endforeach; ?>

			<div class="right menu">
			<?php if (self::$_user_id):
				echo $this->_search();
				echo $this->_visitor();
			else:
				echo $this->_signin();
			endif; ?>
			</div>
			
		</div>
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

	<div class="main container">

		<!-- ADS -->

		<?= Ads::slot(Ads::TOP) ?>

		<!-- /ADS -->


		<!-- CONTENT -->

		<?= $this->_content() ?>

		<!-- /CONTENT -->

		<?= $this->_foot() ?>

	</div>

	<footer class="container">

		<?= $this->_footer() ?>

	</footer>

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

		echo '<div class="item">';
		echo Form::open(null, array('id' => 'form-search', 'class' => 'ui icon input'));
		echo Form::input('search', null, array('placeholder' => __('Search...')));
		echo '<i class="search link icon"></i>';
		echo Form::close();
		echo '</div>';

		return ob_get_clean();
?>

<div class="item">
	<div class="ui icon input"></div>
</div>
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

<div class="item">
	<?= HTML::anchor(
				Route::url('sign', array('action' => 'up')),
				__('Join'),
				array('class' => 'ui orange button', 'title' => __("Did we mention it's FREE!"))
			) ?>

	<div class="ui teal top right pointing dropdown button">
		<span class="text"><?= __('Login') ?></span>
		<div class="menu">
			<?= Form::open(Route::url('sign', array('action' => 'in')), array('class' => 'ui form segment')) ?>
			<?= Form::hidden('remember', 'true') ?>

			<div class="field">
				<div class="ui left labeled icon input">
					<i class="user icon"></i>
					<?= Form::input('username', null, array('placeholder' => __('Username or email'))) ?>
				</div>
			</div>

			<div class="field">
				<div class="ui left labeled icon input">
					<i class="lock icon"></i>
					<?= Form::password('password', null, array('placeholder' => __('Password'), 'title' => __('Forgot it? Just leave me empty'))) ?>
				</div>
			</div>

			<?= Form::button(null, __('Login') . ' <i class="sign in icon"></i>', array('class' => 'ui teal icon labeled submit button', 'title' => __('Remember to sign out if on a public computer!'))) ?>

			<?= HTML::anchor(Route::url('password'), __('Forgot those?')) ?>

			<div class="ui horizontal divider">
				<?= __('or') ?>
			</div>

			<?= HTML::anchor(
						Route::url('oauth', array('action' => 'login', 'provider' => 'facebook')),
						__('Sign in with') . ' <i class="facebook icon"></i>',
						array('class' => 'ui facebook button', 'title' => __('Sign in with your Facebook account'))
					) ?>

			<?= Form::close() ?>

		</div>
	</div>
</div>

<?php

		return ob_get_clean();

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
	<div class="ui right floated compact menu">
		<?php foreach ($this->actions as $action):
				$attributes = $action;
				unset($attributes['link'], $attributes['text']);
				$attributes['class'] = $attributes['class'] ? 'item ' . $attributes['class'] : 'item';

				echo HTML::anchor($action['link'], $action['text'], $attributes) . ' ';
			endforeach; ?>
	</div>
	<?php endif; ?>

	<?php if ($this->title_html || $this->title): ?>
	<h1 class="ui header">
		<?= $this->title_html ? $this->title_html : HTML::chars($this->title) ?>

		<?php if ($this->subtitle): ?>
		<p class="sub header"><?= $this->subtitle ?></p>
		<?php endif; ?>
	</h1>
	<?php endif; ?>


	<?php if ($this->tabs): ?>
	<div class="ui secondary pointing menu">

	<?php foreach ($this->tabs as $tab_id => $tab):
			if (is_array($tab)):

				// Tab is a link
				$attributes = $tab;
				unset($attributes['link'], $attributes['text'], $attributes['dropdown'], $attributes['active']);

				$attributes['class'] .= ($tab_id === $this->tab ? ' active' : '') . ' item';

				if ($tab['dropdown']):
	?>

		<?= HTML::anchor($tab['link'], $tab['text'], $attributes) ?>
		<div class="ui dropdown item<?= ($tab_id === $this->tab ? ' active' : '') ?>">
			<i class="dropdown icon"></i>
			<div class="menu">
				<?php foreach ($tab['dropdown'] as $dropdown): ?>
					<?php if ($dropdown['divider']): ?>
				<div class="ui divider"></div>
					<?php else: ?>
				<?= HTML::anchor($dropdown['link'], $dropdown['text'], array('class' => 'item')) ?>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>

	<?php

				else:
					echo HTML::anchor($tab['link'], $tab['text'], $attributes);
				endif;

			else:

				// Action is HTML
				echo '<li' . ($tab_id === $this->tab ? ' class="active"' : '') . '>' . $tab . '</li>';

			endif;
		endforeach;

	?>

	</div>
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

		if ($notifications = Anqh::notifications(self::$_user)):

?>

<div class="ui circular teal labels item notifications">
	<div class="ui label"><?= implode('</div> <div class="ui label">', $notifications) ?></div>
</div>

<?php endif; ?>

<div class="ui dropdown item">

	<?= HTML::avatar(self::$_user->avatar, self::$_user->username, 'ssmall') ?>
	<span><?= HTML::chars(self::$_user->username) ?></span> <i class="dropdown icon"></i>

	<div class="menu" role="menu">
		<?= HTML::anchor(URL::user(self::$_user->username), '<i class="user icon"></i> ' . __('Profile'), array('role' => 'menuitem', 'class' => 'item')) ?>
		<?= HTML::anchor(Forum::private_messages_url(), '<i class="mail outline icon"></i> ' . __('Private messages'), array('role' => 'menuitem', 'class' => 'item')) ?>
		<?= HTML::anchor(URL::user(self::$_user, 'favorites'), '<i class="calendar icon"></i> ' . __('Favorites'), array('role' => 'menuitem', 'class' => 'item')) ?>
		<?= HTML::anchor(URL::user(self::$_user, 'friends'), '<i class="heart icon"></i> ' . __('Friends'), array('role' => 'menuitem', 'class' => 'item')) ?>
		<?= HTML::anchor(URL::user(self::$_user, 'ignores'), '<i class="mute icon"></i> ' . __('Ignores'), array('role' => 'menuitem', 'class' => 'item')) ?>
		<?= HTML::anchor(URL::user(self::$_user, 'settings'), '<i class="settings icon"></i> ' . __('Settings'), array('role' => 'menuitem', 'class' => 'item')) ?>
		<?= HTML::anchor(Route::url('sign', array('action' => 'out')), '<i class="sign out icon"></i> ' . __('Sign out'), array('role' => 'menuitem', 'class' => 'item')) ?>
		<?php if (self::$_user->has_role('admin')): ?>
		<div class="header item"><?= __('Admin functions') ?></div>
		<?= HTML::anchor(Route::url('roles'), '<i class="asterisk icon"></i> ' . __('Roles'), array('role' => 'menuitem', 'class' => 'item')) ?>
		<?= HTML::anchor(Route::url('tags'), '<i class="tags icon"></i> ' . __('Tags'), array('role' => 'menuitem', 'class' => 'item')) ?>
		<?= HTML::anchor('#debug', '<i class="signal icon"></i> ' . __('Profiler'), array('onclick' => "$('.kohana').toggle();", 'role' => 'menuitem', 'class' => 'item')) ?>
		<?php endif; ?>
	</div>
</div>

<?php

		return ob_get_clean();
	}

}
