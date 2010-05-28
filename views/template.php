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
		Less::style($skin, null, $skin_imports); ?>

	<!--[if IE]><?php echo HTML::script('http://html5shiv.googlecode.com/svn/trunk/html5.js'); ?><![endif]-->
	<script src="http://www.google.com/jsapi?key=<?php echo Kohana::config('site.google_api_key') ?>"></script>
	<?php echo
		HTML::script('http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js'),
		HTML::script('http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.1/jquery-ui.min.js'),
		HTML::script('js/jquery.tools.min.js'); ?>

<?php //echo widget::get('head') ?>

</head>

<body id="<?php echo $page_id ?>" class="<?php echo $page_class ?>">


	<!-- HEADER -->

	<header id="header">
		<div class="content">

<h1><?php echo html::anchor('/', Kohana::config('site.site_name')) ?></h1>
<?php //echo widget::get('navigation') ?>

		</div>
	</header><!-- #header -->

	<!-- /HEADER -->


<?php //echo widget::get('ad_top') ?>


	<!-- BODY -->

	<section id="body">
		<div class="content">


			<!-- CONTENT -->

			<section id="content" class="unit size5of6">

				<header id="title">

					<h2><?php echo $page_title ?></h2>
					<?php echo !empty($page_subtitle) ? '<p class="subtitle">' . $page_subtitle . '</p>' : '' ?>

<?php //echo widget::get('actions') ?>
<?php //echo widget::get('tabs') ?>

				</header><!-- #title -->


				<!-- MAIN CONTENT -->

				<section id="wide" class="unit size1of1">

<?php //echo widget::get('wide') ?>

				</section><!-- wide -->

				<section id="main" class="unit size3of5">

<?php //echo widget::get('main') ?>

				</section><!-- #main -->

				<!-- /MAIN CONTENT -->


				<!-- SIDE CONTENT -->

				<aside id="side" class="unit size2of5">

<?php //echo widget::get('side') ?>

				</aside><!-- #side -->

				<!-- /SIDE CONTENT -->


			</section><!-- #content -->

			<!-- /CONTENT -->


			<!-- SIDE ADS -->

			<section id="side-ads" class="unit size1of6">

<?php //echo widget::get('ad_side') ?>

			</section><!-- #side-ads -->

			<!-- /SIDE ADS -->


		</div>
	</section><!-- #body -->

	<!-- /BODY -->


	<!-- DOCK -->

	<section id="dock">
		<div class="content">
			<div class="unit size2of5">

<?php //echo widget::get('dock') ?>

			</div>
			<div class="unit size3of5 extra-actions">

<?php //echo widget::get('dock2') ?>

			</div>
		</div>
	</section><!-- #dock -->

	<!-- /DOCK -->


	<!-- FOOTER -->

	<footer id="footer">
		<div class="content">

<?php //echo widget::get('navigation') ?>
<?php //echo widget::get('footer') ?>

		</div>
		<div id="end" class="content">

<?php //widget::get('end') ?>

		</div>
	</footer><!-- #footer -->

	<!-- /FOOTER -->


<?php echo
//	HTML::script('js/jquery.autocomplete.pack.js');
	HTML::script('js/jquery.form.js'),
	HTML::script('js/jquery.text-overflow.js'); ?>

<script>
//<![CDATA[
$(function() {

	// Form input hints
	$('input:text, textarea, input:password').hint('hint');


	// Ellipsis ...
	$('.cut li').ellipsis();


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
				buttons: {
					'<?php echo __('Yes, do it!') ?>': function() { $(this).dialog('close'); action(); },
					'<?php echo __('No, cancel') ?>': function() { $(this).dialog('close'); }
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

<?php //echo widget::get('foot') ?>

</body>

</html>
