<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Login model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Login extends Jelly_Model {

	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta->fields(array(
			'id' => new Field_Primary(),
			'stamp' => new Field_Timestamp(array(
				'auto_now_create' => true,
			)),
			'user' => new Field_BelongsTo(array(
				'empty' => false,
				'column' => 'uid',
			)),
			'username' => new Field_String,
			'ip' => new Field_String,
			'hostname' => new Field_String,
			'success' => new Field_Boolean,
			'password' => new Field_Boolean,
		));
	}

}
