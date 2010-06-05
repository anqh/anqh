<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Route
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Route extends Kohana_Route {

	/**
	 * Return model specific route
	 *
	 * @param   Jelly_Model  $model
	 * @param   string       $params
	 * @return  string
	 */
	public static function model(Jelly_Model $model, $params = null) {
		$route  = Jelly::model_name($model);
		$id     = URL::title($model->id() . ' ' . $model->name());
		try {
			return Route::get($route)->uri(array('id' => $id, 'params' => $params));
		} catch (Kohana_Exception $e) {
			return $route . '/' . $id . ($params ? '/' . $params : '');
		}
	}

}
