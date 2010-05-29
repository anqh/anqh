<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Role model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Role extends Jelly_Model {

	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta
			->name_key('name')
			->fields(array(
				'id' => new Field_Primary,
				'name' => new Field_String(array(
					'unique' => true,
					'rules'  => array(
						'max_length' => array(32),
						'not_empty' => array(true),
					)
				)),
				'description' => new Field_Text,
				'users' => new Field_ManyToMany,
		));
	}

}
