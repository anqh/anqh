<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Template
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>
<!doctype html>
<html lang="<?php echo $language ?>">

<head>
	<meta charset="<?php echo Kohana::$charset ?>" />
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo Kohana::$charset ?>" />
	<title><?php echo $page_title ? HTML::chars($page_title) . ' | ' : '' ?><?php echo Kohana::config('site.site_name') ?></title>
	<link rel="icon" type="image/png" href="/ui/favicon.png" />

	<?php foreach ($styles as $file => $attributes) echo HTML::style($file, $attributes), "\n" ?>
	<?php foreach ($skins as $skin_name => $available_skin)
		echo Less::style(
			$available_skin['path'],
			array(
				'title' => $skin_name,
				'rel'   => $skin_name == $skin ? 'stylesheet' : 'alternate stylesheet',
			),
			false,
			$skin_imports
		); ?>
	<?php echo HTML::style('ui/site.css') ?>

	<?php echo HTML::script(Kohana::$environment == Kohana::PRODUCTION ? 'js/head.min.js' : 'js/head.js') ?>
	<?php /*echo HTML::script_source("
head
	.js(
		{ 'jquery': 'http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js' },
		{ 'jquery-ui': 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.4/jquery-ui.min.js' },
		{ 'jquery-tools': '" . URL::base() . "js/jquery.tools.min.js' }
	)
	.js(
		{ 'google-maps': 'http://maps.google.com/maps/api/js?sensor=false' }
	);
");*/ ?>

<?php echo Widget::get('head') ?>

</head>

<body id="<?php echo $page_id ?>" class="<?php echo $page_class ?>">


	<!-- HEADER -->

	<header id="header">
		<div class="container grid12">

			<section id="logo" class="grid1 first">
				<h1><?php echo HTML::anchor('/', Kohana::config('site.site_name')) ?></h1>
			</section>

			<section id="mainmenu" class="grid7">

				<?php echo Widget::get('navigation') ?>

			</section>
			<nav id="visitor" class="grid4">

				<?php echo Widget::get('visitor') ?>

			</nav>

		</div>
	</header><!-- #header -->


	<!-- /HEADER -->


	<?php echo Widget::get('ad_top') ?>


	<!-- BODY -->


	<!-- CONTENT -->

	<section id="main-top">
		<div class="container grid12">

			<header id="title">

<?php echo Widget::get('subnavigation') ?>
<?php //echo Widget::get('breadcrumb') ?>

				<hgroup>
					<h2><?php echo $page_title ?></h2>
					<?php echo !empty($page_subtitle) ? '<span class="subtitle">' . $page_subtitle . '</span>' : '' ?>
				</hgroup>

<?php echo Widget::get('actions') ?>
<?php echo Widget::get('share') ?>

			</header><!-- #title -->

<?php if ($wide = Widget::get('wide')): ?>

			<section id="wide">

<?php echo Widget::get('error') ?>

<?php echo $wide; ?>

			</section><!-- #wide -->

<?php endif; ?>

		</div>
	</section><!-- #main-top -->

<?php if ($main = Widget::get('main') or !$wide): ?>

	<section id="main-bottom">
		<div class="container grid12">

			<section id="main" class="first grid8">

<?php if (!$wide) echo Widget::get('error'); ?>

<?php if ($main) echo $main; ?>

			</section><!-- #main -->

			<aside id="side" class="grid4" role="complementary">

<?php echo Widget::get('side') ?>

<?php echo Widget::get('ad_side') ?>

			</aside><!-- #side -->

		</div>
	</section><!-- #main-bottom -->

<?php endif; ?>

	<!-- /CONTENT -->


	<!-- DOCK -->

	<section id="dock" class="pinned">
		<div class="container grid12">

<?php echo Widget::get('dock') ?>

		</div>
		<a id="customize" class="icon customize" onclick="$('#dock').toggleClass('open'); return false;">&#9660;</a>
	</section><!-- #dock -->

	<!-- /DOCK -->


	<!-- FOOTER -->

	<footer id="footer">
		<div class="container grid12" role="complementary">

<?php echo Widget::get('navigation') ?>
<?php echo Widget::get('footer') ?>

		</div>
		<div id="end" class="container grid12" role="contentinfo">

<?php echo Widget::get('end') ?>

		</div>
	</footer><!-- #footer -->

	<!-- /FOOTER -->


	<div class="lightbox" id="slideshow">
		<div id="slideshow-images">
			<div class="items">
				<div>
					<div class="info"></div>
				</div>
			</div>
		</div>
		<a class="navi prev" title="<?= __('Previous') ?>">&laquo;</a>
		<a class="navi next" title="<?= __('Next') ?>">&raquo;</a>
		<a class="action close" title="<?= __('Close') ?>">&#10006;</a>
	</div>


<?php echo HTML::script_source("
head.js(
	{ 'jquery': 'http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js' },
	{ 'jquery-ui': 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/jquery-ui.min.js' },
	{ 'jquery-tools': '" . URL::base() . "js/jquery.tools.min.js' },
	{ 'jquery-form': '" . URL::base() . "js/jquery.form.js' },
	{ 'jquery-overflow': '" . URL::base() . "js/jquery.text-overflow.js' },
	{ 'anqh': '" . URL::base() . "js/anqh.js?2' }
);
"); ?>

<?php echo Widget::get('foot') ?>

</body>

</html>
