<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Shout model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Shout extends Jelly_Model {

	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta
			->sorting(array('id' => 'DESC'))
			->fields(array(
				'id' => new Field_Primary,
				'created' => new Field_Timestamp(array(
					'auto_now_create' => true,
				)),
				'author' => new Field_BelongsTo(array(
					'empty'   => false,
					'column'  => 'author_id',
					'foreign' => 'users.id',
				)),
				'shout'  => new Field_String(array(
					'empty'      => false,
					'min_length' => 1,
					'max_length' => 250,
				)),
			));
	}

}
