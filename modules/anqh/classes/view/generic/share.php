<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Share view.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Generic_Share extends View_Base {

	/**
	 * @var  string  Section id
	 */
	public $id = 'share';

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
	public function render() {
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

<div class="addthis_toolbox addthis_floating_style addthis_32x32_style"<?= HTML::attributes($attributes) ?>>
	<a class="addthis_button_facebook"></a>
	<a class="addthis_button_twitter"></a>
	<a class="addthis_button_google_plusone_share"></a>
	<a class="addthis_button_email"></a>
</div>

<script>
var addthis_config = { data_track_clickback: true, pubid: '<?= Kohana::$config->load('site.share') ?>' }
  , addthis_share  = { templates: { twitter: '{{title}}: {{url}} (via @<?= Kohana::$config->load('site.share') ?>)' } };
</script>
<script src="//s7.addthis.com/js/300/addthis_widget.js#pubid=<?= Kohana::$config->load('site.share') ?>"></script>

<?php

		return ob_get_clean();
	}

}
