<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Facebook Like
 *
 * @package    Facebook
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

$attributes = array(
	'show_faces' => false,
	'font'       => 'lucida grande',
	'height'     => 35,
	'width'      => isset($width) ? $width : 300,
);
isset($colorscheme) and $attributes['colorscheme'] = $colorscheme;
isset($href) and $attributes['href'] = $href;
isset($ref) and $attributes['ref'] = $ref;
?>

<fb:like<?php echo HTML::attributes($attributes) ?>></fb:like>
