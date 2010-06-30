<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Template
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>
<!doctype html>
<html lang="<?php echo $language ?>">

<head>
	<meta charset="utf-8" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo HTML::chars($page_title) ?><?php echo (!empty($page_title) ? ' | ' : '') . Kohana::config('site.site_name') ?></title>
	<link rel="icon" type="image/png" href="/ui/favicon.png" />
	<?php echo
		HTML::style('ui/boot.css'),
		HTML::style('ui/grid.css'),
		HTML::style('ui/typo.css'),
		HTML::style('ui/base.css'),
		Less::style($skin, null, false, $skin_imports),
		HTML::style('ui/jquery-ui.css'),
		HTML::style('ui/dark/jquery-ui.css'),
		HTML::style('ui/site.css'),
		HTML::style('http://fonts.googleapis.com/css?family=Yanone+Kaffeesatz:light');
?>

	<!--[if IE]><?php echo HTML::script('http://html5shiv.googlecode.com/svn/trunk/html5.js'); ?><![endif]-->
	<?php echo
		//HTML::script('http://www.google.com/jsapi?key=' . Kohana::config('site.google_api_key')),
		HTML::script('http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js'),
		HTML::script('http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/jquery-ui.min.js'),
		HTML::script('http://maps.google.com/maps/api/js?sensor=false'),
		HTML::script('js/jquery.tools.min.js'); ?>

<?php echo Widget::get('head') ?>

</head>

<body id="<?php echo $page_id ?>" class="<?php echo $page_class ?>">


	<!-- HEADER -->

	<header id="header">
		<div class="content">

			<section id="logo" class="unit size1of6">
				<h1><?php echo html::anchor('/', Kohana::config('site.site_name')) ?></h1>
			</section>

			<section id="search" class="unit size1of2">

<?php echo Widget::get('search') ?>

			</section>

			<section id="visitor" class="unit size1of3">

<?php echo Widget::get('visitor') ?>

			</section>

		</div>
	</header><!-- #header -->

	<!-- /HEADER -->

<?php echo Widget::get('ad_top') ?>


	<!-- BODY -->

	<section id="body">
		<div class="content">


			<!-- SIDE NARROW -->

			<section id="side-narrow" class="unit size1of6">

<?php echo Widget::get('navigation') ?>

<?php //echo Widget::get('tabs') ?>

<?php echo Widget::get('ad_side') ?>

			</section><!-- #side-narrow -->

			<!-- /SIDE NARROW -->


			<!-- MAIN -->

			<section id="main" class="unit <?php echo ($wide = Widget::get('wide')) ? 'size5of6' : 'size1of2' ?>">
				<header id="title">

<?php //echo Widget::get('breadcrumb') ?>

					<hgroup>
						<h2><?php echo $page_title ?></h2>
						<?php echo !empty($page_subtitle) ? '<h3>' . $page_subtitle . '</h3>' : '' ?>
					</hgroup>

<?php echo Widget::get('actions') ?>

				</header><!-- #title -->

				<?php echo Widget::get('error') ?>
				<?php echo $wide ? $wide : Widget::get('main') ?>

			</section><!-- main -->

			<!-- /MAIN -->

			<?php if (!$wide): ?>

			<!-- SIDE -->

			<aside id="side" class="unit size1of3">

<?php echo Widget::get('side') ?>

			</aside><!-- #side -->

			<!-- /SIDE -->

			<?php endif; ?>

		</div>
	</section><!-- #body -->

	<!-- /BODY -->


	<!-- DOCK -->

	<section id="dock" class="pinned">
		<div class="content">
			<div class="unit size2of5">

<?php echo Widget::get('dock') ?>

			</div>
			<div class="unit size3of5 extra-actions">

<?php echo Widget::get('dock2') ?>

			</div>
		</div>
	</section><!-- #dock -->

	<!-- /DOCK -->


	<!-- FOOTER -->

	<footer id="footer">
		<div class="content">

<?php echo Widget::get('navigation') ?>
<?php echo Widget::get('footer') ?>

		</div>
		<div id="end" class="content">

<?php echo Widget::get('end') ?>

		</div>
	</footer><!-- #footer -->

	<!-- /FOOTER -->


<?php echo
	HTML::script('js/jquery.form.js'),
	HTML::script('js/jquery.text-overflow.js'); ?>

<script>
//<![CDATA[
var map;
var geocoder;
$.fn.googleMap = function(options) {
	var defaults = {
		lat: 60.1695,
		long: 24.9355,
		zoom: 14,
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		marker: false,
		infowindow: false
	};

	options = $.extend(defaults, options || {});
	var center = new google.maps.LatLng(options.lat, options.long);

	map = new google.maps.Map(this.get(0), $.extend(options, { center: center }));
	if (options.marker) {
		var marker = new google.maps.Marker({
		  position: center,
		  map: map,
			title: options.marker == true ? '' : options.marker
		});
		if (options.infowindow) {
			var infowindow = new google.maps.InfoWindow({
				content: options.infowindow
			});
			google.maps.event.addListener(marker, 'click', function() {
				infowindow.open(map, marker);
			})
		}
	}
}

$(function() {

	// Google Maps
	geocoder = new google.maps.Geocoder();

	// Form input hints
	$('input:text, textarea, input:password').hint('hint');


	// Ellipsis ...
	$('.cut li').ellipsis();


	// Tooltips
	$('a[title]').tooltip({
		effect: 'slide',
		position: 'top center'
	});


	// Delete confirmations
	function confirm_delete(title, action) {
		if (title === undefined) title = '<?php echo __('Are you sure you want to do this?') ?>';
		if (action === undefined) action = function() { return true; }
		if ($('#dialog-confirm').length == 0) {
			$('body').append('<div id="dialog-confirm" title="' + title + '"><?php echo __('Are you sure?') ?></div>');
			$('#dialog-confirm').dialog({
				dialogClass: 'confirm-delete',
				modal: true,
				close: function(ev, ui) { $(this).remove(); },
				closeText: '✕',
				buttons: {
					'<?php echo '✓ ' . __('Yes, do it!') ?>': function() { $(this).dialog('close'); action(); },
					'<?php echo '✕ ' . __('No, cancel') ?>': function() { $(this).dialog('close'); }
				}
			});
		} else {
			$('#confirm-dialog').dialog('open');
		}
	}

	$('a[class*="-delete"]').live('click', function(e) {
		e.preventDefault();
		var action = $(this);
		if (action.data('action')) {
			confirm_delete(action.text(), function() { action.data('action')(); });
		} else if (action.is('a')) {
			confirm_delete(action.text(), function() { window.location = action.attr('href'); });
		} else {
			confirm_delete(action.text(), function() { action.parent('form').submit(); });
		}
	});

	$('.mod a[class*="-delete"]')
		.live('mouseenter', function () {
			$(this).closest('article').addClass('delete');
		})
		.live('mouseleave', function () {
			$(this).closest('article').removeClass('delete');
		});

	$('.mod a[class*="-edit"]')
		.live('mouseenter', function () {
			$(this).closest('article').addClass('edit');
		})
		.live('mouseleave', function () {
			$(this).closest('article').removeClass('edit');
		});


	// Peepbox
	if ($('#peepbox').length == 0) {
		$('body').append('<div id="peepbox"></div>');
		$('#peepbox').data('cache', []);
	}

	function peepbox(href, $tip) {
		var cache = $tip.data('cache');
		if (!cache[href]) {
			$tip.text('<?php echo __('Loading...') ?>');
			$.get(href + '?peep', function(response) {
				$tip.html(cache[href] = response);
			});
			$tip.data('cache', cache);
			return;
		}
		$tip.html(cache[href]);
	}

	$('a.user, .avatar a').tooltip({
		predelay: 500,
		tip: '#peepbox',
		lazy: false,
		position: 'bottom right',
		onBeforeShow: function() {
			peepbox(this.getTrigger().attr('href'), this.getTip());
		}
	}).dynamic({
		bottom: {
			direction: 'down',
			bounce: true
		}
	});

});
//]]>
</script>

<?php echo Widget::get('foot') ?>

</body>

</html>
