<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Facebook Connect
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<div id="fb-root"></div>
<?php echo HTML::script_source("
window.fbAsyncInit = function() {
	FB.init({
		appId: " . (!is_string($id) ? 'null' : "'" . $id . "'") . ",
		status: true,
		cookie: true,
		xfbml: true
	});
};

head.js({ 'facebook-connect': document.location.protocol + '//connect.facebook.net/en_US/all.js' });
/*
(function() {
	var e = document.createElement('script');
	e.src = document.location.protocol + '//connect.facebook.net/en_US/all.js';
	e.async = true;
	document.getElementById('fb-root').appendChild(e);
}());
*/
");
