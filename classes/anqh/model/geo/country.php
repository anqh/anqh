<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Geo Country model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Geo_Country extends Jelly_Model {

	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta->fields(array(
			'id' => new Jelly_Field_Primary,
			'name' => new Jelly_Field_String(array(
				'label'  => __('Country'),
				'unique' => true,
				'rules'  => array(
					'max_length' => array(200),
					'not_empty'  => array(true),
				),
			)),
			'code' => new Jelly_Field_String(array(
				'label'  => __('Country code'),
				'unique' => true,
				'rules'  => array(
					'not_empty'    => array(true),
					'exact_length' => array(2),
				),
			)),
			'currency' => new Jelly_Field_String(array(
				'label' => __('Currency'),
				'rules' => array(
					'exact_length' => array(3),
				),
			)),
			'population' => new Jelly_Field_Integer,
			'created' => new Jelly_Field_Timestamp(array(
				'auto_now_create' => true,
			)),
			'modified'   => new Jelly_Field_Timestamp(array(
				'auto_now_update' => true,
			)),
			'i18n' => new Jelly_Field_JSON,
			'cities' => new Jelly_Field_HasMany(array(
				'foreign' => 'geo_city',
			)),
		));
	}

}
