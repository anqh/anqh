<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Friend model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Friend extends Jelly_Model {

	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta->fields(array(
			'id'     => new Jelly_Field_Primary,
			'user'   => new Jelly_Field_BelongsTo,
			'friend' => new Jelly_Field_BelongsTo(array(
				'column'  => 'friend_id',
				'foreign' => 'user'
			)),
			'created' => new Jelly_Field_Timestamp(array(
				'auto_now_create' => true,
			)),
		));
	}

}
