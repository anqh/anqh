<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Field ManyToMany, fixes save on empty relations
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Field_ManyToMany extends Jelly_Field_ManyToMany {

	/**
	 * Implementation for Jelly_Field_Behavior_Saveable.
	 *
	 * @param   Jelly  $model
	 * @param   mixed  $value
	 * @return  void
	 */
	public function save($model, $value, $loaded)
	{
		// Find all current records so that we can calculate what's changed
		$in = ($loaded) ? $this->_in($model, true) : array();

		// Find old relationships that must be deleted
		if ($old = array_diff($in, (array)$value)) {
			Jelly::delete($this->through['model'])
				->where($this->through['columns'][0], '=', $model->id())
				->where($this->through['columns'][1], 'IN', $old)
				->execute(Jelly::meta($model)->db());
		}

		// Find new relationships that must be inserted
		if (!empty($value) && $new = array_diff((array)$value, $in)) {
			foreach ($new as $new_id) {
				if (!is_null($new_id)) {
					Jelly::insert($this->through['model'])
						 ->columns($this->through['columns'])
						 ->values(array($model->id(), $new_id))
						 ->execute(Jelly::meta($model)->db());
				}
			}
		}

	}

}
