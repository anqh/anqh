<?php defined('SYSPATH') or die('No direct script access.');
/**
 * NewsfeedItem model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_NewsfeedItem extends Jelly_Model {

	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta
			->sorting(array('id' => 'DESC'))
			->fields(array(
				'id'    => new Field_Primary,
				'user'  => new Field_BelongsTo,
				'stamp' => new Field_Timestamp(array(
					'auto_now_create' => true
				)),
				'class' => new Field_String,
				'type'  => new Field_String,
				'data'  => new Field_JSON,
			));
	}

}
