<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Search
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<?php echo
	Form::open(null, array('role' => 'search')),
	Form::input('search', null, array('title' => __('Search'), 'disabled' => 'disabled', 'class' => 'search')),
	Form::close();

