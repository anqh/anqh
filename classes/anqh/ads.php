<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Ads.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
abstract class Anqh_Ads {

	/** Ad slots */
	const
		MAINMENU = 'mainmenu',
		SIDE     = 'side',
		TOP      = 'top';

	/**
	 * @var  array
	 */
	public static $config;


	/**
	 * Before </body>
	 *
	 * @return  string
	 */
	public static function foot() {
		return null;
	}


	/**
	 * Check if there is an ad in a slot.
	 *
	 * @param   string  $slot
	 * @return  boolean
	 */
	public static function has_slot($slot) {
		return self::$config['network_code'] && !empty(self::$config[$slot]);
	}


	/**
	 * Before </head>
	 *
	 * @return  string
	 */
	public static function head() {
		if (!self::$config['network_code']) {
			return null;
		}

		ob_start();

?>

<script type='text/javascript'>
	var googletag = googletag || {};
	googletag.cmd = googletag.cmd || [];

	(function() {
		var gads = document.createElement('script');
		gads.async = true;
		gads.type = 'text/javascript';

		var useSSL = 'https:' == document.location.protocol;
		gads.src = (useSSL ? 'https:' : 'http:') + '//www.googletagservices.com/tag/js/gpt.js';

		var node = document.getElementsByTagName('script')[0];
		node.parentNode.insertBefore(gads, node);
	})();
</script>

<script type='text/javascript'>
	googletag.cmd.push(function _ads() {
		<?php foreach (self::$config['slots'] as $ads) foreach ($ads as $ad_unit => $ad_size): ?>
		googletag.defineSlot('/<?= self::$config['network_code'] ?>/<?= $ad_unit ?>', [<?= implode(', ', $ad_size) ?>], 'ad-<?= URL::title($ad_unit) ?>').addService(googletag.pubads());
		<?php endforeach; ?>
		googletag.pubads().enableSingleRequest();
		googletag.enableServices();
	});
</script>

<?php

		return ob_get_clean();
	}


	/**
	 * Initialize Ads.
	 */
	public static function init() {
		self::$config = (array)Kohana::$config->load('ads');
	}


	/**
	 * Print a slot.
	 *
	 * @param  string  $slot
	 */
	public static function print_slot($slot) {
		if (empty(self::$config['slots'][$slot])) {
			return null;
		}

		ob_start();

		foreach (self::$config['slots'][$slot] as $ad_unit => $ad_size):
			$id = 'ad-' . URL::title($ad_unit);

?>

<div id="<?= $id ?>" style="width: <?= $ad_size[0] ?>px; height: <?= $ad_size[1] ?>px;">
	<script>
		googletag.cmd.push(function _ad() {
			googletag.display('<?= $id ?>');
		})
	</script>
</div>

<?php

		endforeach;

		return ob_get_clean();
	}


	/**
	 * Print slot.
	 *
	 * @param   string  $slot
	 * @return  string
	 */
	public static function slot($slot) {
		if (empty(self::$config['slots'][$slot])) {
			return null;
		}

		ob_start();

		foreach (self::$config['slots'][$slot] as $ad_unit => $ad_size):
			$id = 'ad-' . URL::title($ad_unit);

?>

<div class="ad ad<?= implode('x', $ad_size) ?>" id="<?= $id ?>">
	<script>
		googletag.cmd.push(function _ad() {
			googletag.display('<?= $id ?>');
		});
	</script>
</div>

<?php

		endforeach;

		return ob_get_clean();
	}

}
