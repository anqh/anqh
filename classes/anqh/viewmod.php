<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * ViewMod
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_ViewMod extends View {

	protected $_viewfile;

	/**
	 * Creates a new View Mod using the given parameters.
	 *
	 * @param   string  $name  view file name
	 * @param   array   $data  pre-load data
	 * @return  ViewMod
	 */
	public static function factory($name = null, array $data = null) {
		return new self($name, $data);
	}


	/**
	 * Wrap requested view inside module and render
	 *
	 * @return  string
	 */
	public function render($file = null) {
		return (string)View::factory('generic/mod', array(
			'class'      => 'mod ' . Arr::get_once($this->_data, 'mod_class', strtr(basename($this->_file, '.php'), '_', '-')),
			'id'         => Arr::get_once($this->_data, 'mod_id'),
			'title'      => Arr::get_once($this->_data, 'mod_title'),
			'pagination' => Arr::get_once($this->_data, 'pagination'),
			'content'    => parent::render($file),
		));
	}

}
