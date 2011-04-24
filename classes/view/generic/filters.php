<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Filters view.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Generic_Filters extends View_Section {

	/**
	 * @var  string  Section class
	 */
	public $class = 'filters';

	/**
	 * @var  array  Filters
	 */
	public $filters = array();

	/**
	 * @var  string  Filter type
	 */
	public $type = 'filter';

}
