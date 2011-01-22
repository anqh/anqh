<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * AddThis JavaScripts
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

if ($google_analytics = Kohana::config('site.google_analytics')) {

	// Google Analytics integration
	echo HTML::script_source("
var addthis_config;
head.ready('google-analytics',	function() {
	addthis_config = {
		data_ga_tracker: tracker,
		data_track_clickback: true,
		username: '" . $id . "'
	};

	var at = document.createElement('script'); at.type = 'text/javascript'; at.async = true;
	at.src = 'http://s7.addthis.com/js/250/addthis_widget.js';
	(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(at);
});
");

} else {

	// Default
	echo HTML::script('http://s7.addthis.com/js/250/addthis_widget.js#username=' . $id);

}
