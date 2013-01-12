<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * AddThis
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
$attributes = array();

// Custom URL
if ($url = Anqh::open_graph('url')) {
	$attributes['addthis:url'] = $url;
}

// Custom title
if ($title = Anqh::open_graph('title')) {
	$attributes['addthis:title'] = $title;
}

?>
<div class="addthis_toolbox addthis_pill_combo"<?php echo HTML::attributes($attributes) ?>>
	<a class="addthis_button_facebook_like"></a>
	<a class="addthis_button_tweet" tw:count="horizontal"></a>
	<a class="addthis_counter addthis_pill_style"></a>
</div>
