<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Login model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
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
			'id' => new Jelly_Field_Primary,
			'stamp' => new Jelly_Field_Timestamp(array(
				'auto_now_create' => true,
			)),
			'user' => new Jelly_Field_BelongsTo(array(
				'empty' => false,
				'column' => 'uid',
			)),
			'username' => new Jelly_Field_String,
			'ip' => new Jelly_Field_String,
			'hostname' => new Jelly_Field_String,
			'success' => new Jelly_Field_Boolean,
			'password' => new Jelly_Field_Boolean,
		));
	}

}
