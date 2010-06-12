<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Geo City model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Geo_City extends Jelly_Model {

	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta->fields(array(
			'id'         => new Field_Primary,
			'name'       => new Field_String(array(
				'rules' => array(
					'not_empty'  => array(true),
					'max_length' => array(200),
				),
			)),
			'latitude'   => new Field_Float,
			'longitude'  => new Field_Float,
			'population' => new Field_Integer,
			'created'    => new Field_Timestamp(array(
				'auto_now_create' => true,
			)),
			'modified' => new Field_Timestamp(array(
				'auto_now_update' => true,
			)),
			'i18n'       => new Field_JSON,
			'country'    => new Field_BelongsTo(array(
				'foreign' => 'geo_country',
				'rules'   => array(
					'not_empty' => array(true),
				),
			)),
			'timezone'   => new Field_BelongsTo(array(
				'foreign' => 'geo_timezone',
			)),
		));
	}

}
