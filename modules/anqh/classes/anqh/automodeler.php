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
	 * Set the items in the $data array.
	 *
	 * 	$blog_entry = new Model_Blog;
	 * 	$blog_entry->title = 'Demo';
	 * 	$blog_entry->content = 'My awesome content';
	 *
	 * @param   string  $key    the field name to set
	 * @param   string  $value  the value to set to
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

		if (array_key_exists($key, $this->_data)) {
			$this->_data[$key] = $value;
			$this->_validated = FALSE;
		}
	}


	/**
	 * Get model author light array
	 *
	 * @return  array
	 */
	public function author() {
		return !empty($this->_data['author_id']) ? Model_User::find_user_light($this->_data['author_id']) : null;
	}


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
	 * @return  Database_Result
	 */
	public function find_all() {
		return AutoModeler::factory(Model::model_name(get_called_class()))->load(null, null);
	}


	/**
	 * Get primary key value.
	 *
	 * @return  integer
	 */
	public function id() {
		return $this->_data[$this->_primary_key];
	}


	/**
	 * Overwrite AutoModeler is_valid() to fix Validation.
	 *
	 * @param   mixed  $validation  a manual validation object to combine the model properties with
	 * @return  boolean
	 *
	 * @throws  Validation_Exception
	 */
	public function is_valid($validation = null) {
		$data = $validation ? $validation->copy($validation->as_array() + $this->_data) : Validation::factory($this->_data);
		$data->bind(':model', $this);

		foreach ($this->_rules as $field => $rule) {
			foreach ($rule as $key => $value) {
				if (is_int($key)) {
					$data->rule($field, $value);
				} else {
					$data->rule($field, $key, $value);
				}
			}
		}

		if ($data->check()) {
			$this->_validation = null;

			return $this->_validated = true;
		}	else {
			$this->_validation = $data;

			throw new Validation_Exception($data, 'Could not validate data.');
		}
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
	 * Override save() to fix primary keys.
	 * Set $validation to false to skip validation.
	 *
	 * @param   mixed  $validation a manual validation object to combine the model properties with
	 * @return  integer
	 */
	public function save($validation = null) {

		// Don't try to insert null primary keys
		if (!$this->loaded() && !$this->id()) {
			unset($this->_data[$this->_primary_key]);
		}

		// Skip validation?
		if ($validation === false) {
			$this->_validated = true;
		}

		return parent::save($validation);
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
			return (bool)DB::select(array(DB::expr('COUNT(*)'), 'total_count'))
				->from($this->_table_name)
				->where($field, '=', $value)
				->where($this->_primary_key, '!=', $this->id())
				->execute($this->_db)
				->get('total_count');
		}

		throw new AutoModeler_Exception('Field ' . $field . ' does not exist in ' . get_class($this) . '!', array(), '');
	}

}
