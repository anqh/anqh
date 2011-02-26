<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Geo City model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
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
			'id'         => new Jelly_Field_Primary,
			'name'       => new Jelly_Field_String(array(
				'rules' => array(
					'not_empty'  => array(true),
					'max_length' => array(200),
				),
			)),
			'latitude'   => new Jelly_Field_Float,
			'longitude'  => new Jelly_Field_Float,
			'population' => new Jelly_Field_Integer,
			'created'    => new Jelly_Field_Timestamp(array(
				'auto_now_create' => true,
			)),
			'modified' => new Jelly_Field_Timestamp(array(
				'auto_now_update' => true,
			)),
			'i18n'       => new Jelly_Field_JSON,
			'country'    => new Jelly_Field_BelongsTo(array(
				'column'  => 'geo_country_id',
				'foreign' => 'geo_country',
				'rules'   => array(
					'not_empty' => array(true),
				),
			)),
			'timezone'   => new Jelly_Field_BelongsTo(array(
				'column'  => 'geo_timezone_id',
				'foreign' => 'geo_timezone',
			)),
		));
	}

}
