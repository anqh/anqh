<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Share view.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Generic_Share extends View_Section {

	/**
	 * @var  string  Section class
	 */
	public $class = 'share full';

	/**
	 * @var  string  Shared title
	 */
	public $title;

	/**
	 * @var  string  Shared url
	 */
	public $url;


	/**
	 * Create new view.
	 *
	 * @param  string  $url
	 * @param  string  $title
	 */
	public function __construct($url = null, $title = null) {
		$this->url   = $url;
		$this->title = $title;
	}


	/**
	 * Render content.
	 *
	 * @return  string
	 */
	public function content() {
		static $script = true;

		$attributes = array();

		// Custom URL
		$url = $this->url ? $this->url : Anqh::page_meta('url');
		if ($url):
			$attributes['addthis:url'] = $url;
		endif;

		// Custom title
		$title = $this->title ? $this->title : Anqh::page_meta('title');
		if ($title):
			$attributes['addthis:title'] = $title;
		endif;

		ob_start();

?>

<div class="addthis_toolbox addthis_default_style addthis_32x32_style"<?= HTML::attributes($attributes) ?>>
	<a class="addthis_button_facebook"></a>
	<a class="addthis_button_twitter"></a>
	<a class="addthis_button_google"><?= HTML::image('static/img/google-plus.png', array('alt' => 'Google +1', 'width' => 32, 'height' => 32)) ?></a>
	<a class="addthis_button_email"></a>
	<a class="addthis_button_compact"></a>
	<a class="addthis_counter addthis_bubble_style"></a>
</div>

<?php if ($script): ?>
	<?php if (Kohana::$config->load('site.google_analytics')): ?>

<script>
	var addthis_config = {
		data_track_clickback: true,
		pubid: '<?= Kohana::$config->load('site.share') ?>'
	};
	var addthis_share = {
		templates: {
			twitter: '{{title}}: {{url}} (via @<?= Kohana::$config->load('site.share') ?>)'
		}
	};

	var at = document.createElement('script'); at.type = 'text/javascript'; at.async = true;
	at.src = 'http://s7.addthis.com/js/250/addthis_widget.js';
	(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(at);
</script>

	<?php else: ?>

<script src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=<?= Kohana::$config->load('site.share') ?>"></script>

<?php

			endif;
		endif;

		// Add JavaScript only once
		$script = false;

		return ob_get_clean();
	}


	/**
	 * Render <header>.
	 *
	 * @return  string
	 */
	public function header() {
		return '';
	}

}
