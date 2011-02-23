<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Anqh AutoModeler
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_AutoModeler extends AutoModeler_Core {

	/** @var  string  Default name key */
	protected $_name_key = 'name';

	/** @var  boolean  Empty values as null */
	protected $_null = true;

	/** @var  string  Default primary key */
	protected $_primary_key = 'id';

	/** @var  boolean  Trim string values automatically */
	protected $_trim = true;

	/** @var  string  Default unique key */
	protected $_unique_key = 'id';


	/**
	 * The factory method returns a model instance of the model name provided.
	 * You can also specify an id to create a specific object.
	 *
	 *  AutoModeler::factory('user');
	 *  AutoModeler::factory('user', 1);
	 *  Model_User::factory();
	 *  Model_User::factory(1);
	 *
	 * @param   string|integer  $model  the model name or id if called from child class
	 * @param   integer         $id     an id to pass to the constructor
	 * @return  AutoModeler
	 */
	public static function factory($model = null, $id = null) {
		$class = get_called_class();
		if (strpos($class, 'AutoModeler') === false) {

			// Used child class to call factory, Model_User::factory(1)
			return new $class($model);

		} else {

			// Used parent class to call factory, AutoModeler::factory('user', 1)
			return parent::factory($model, $id);

		}
	}


	/**
	 * Find all objectcs
	 *
	 * @static
	 * @return  array
	 */
	public static function find_all() {
		return AutoModeler::factory(Model::model_name(get_called_class()))->load(null, null);
	}


	/**
	 * Get primary key value.
	 *
	 * @return  string|integer
	 */
	public function id() {
		return $this->_data[$this->_primary_key];
	}


	/**
	 * Check if the model is loaded.
	 *
	 * @return  boolean
	 */
	public function loaded() {
		return $this->state() === self::STATE_LOADED;
	}


	/**
	 * Load data from external source, e.g. a Database_Result
	 *
	 * @param   array  $data
	 * @return  AutoModeler
	 */
	public function load_from_data(array $data) {
		$this->process_load($data);
		$this->process_load_state();

		return $this;
	}


	/**
	 * Get model name
	 *
	 * @return  string
	 */
	public function name() {
		return $this->_data[$this->_name_key];
	}


	/**
	 * Set the items in the $data array.
	 *
	 * 	$blog_entry = new Model_Blog;
	 * 	$blog_entry->title = 'Demo';
	 * 	$blog_entry->content = 'My awesome content';
	 *
	 * @param   string  $key    the field name to set
	 * @param   string  $value  the value to set to
	 * @throws  AutoModeler_Exception
	 */
	public function __set($key, $value) {

		// Trim strings?
		if ($this->_trim && is_string($value)) {
			$value = trim($value);
		}

		// Convert to null?
		if ($this->_null && $value === '') {
			$value = null;
		}

		parent::__set($key, $value);
	}


	/**
	 * Get model URL slug
	 *
	 * @return  string
	 */
	public function slug() {
		return $this->name();
	}


	/**
	 * Check if field value is unique
	 *
	 * @param   AutoModeler  $model
	 * @param   mixed        $value
	 * @param   string       $field
	 * @return  boolean
	 *
	 * @throws  AutoModeler_Exception  if field not found
	 */
	public static function unique(AutoModeler $model, $value, $field = null) {
		return !$model->unique_key_exists($value, $field);
	}


	/**
	 * Check if the unique key already exists
	 *
	 * @param   mixed   $value
	 * @param   string  $field
	 * @return  boolean
	 *
	 * @throws  AutoModeler_Exception  if field not found
	 */
	public function unique_key_exists($value, $field = null) {
		if ($field === null) {
			$field = $this->_unique_key;
		}

		if (array_key_exists($field, $this->_data)) {
			return (bool)DB::select(array('COUNT(*)', 'total_count'))
				->from($this->_table_name)
				->where($field, '=', $value)
				->where($this->_primary_key, '!=', $this->id())
				->execute($this->_db)
				->get('total_count');
		}

		throw new AutoModeler_Exception('Field ' . $field . ' does not exist in ' . get_class($this) . '!', array(), '');
	}

}
