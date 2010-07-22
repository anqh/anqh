<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Image model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Image extends Jelly_Model implements Permission_Interface {

	const DELETED      = 'd';
	const HIDDEN       = 'h';
	const NOT_ACCEPTED = 'n';
	const VISIBLE      = 'v';

	// Deprecated
	const NORMAL   = 'normal';
	const ORIGINAL = '';
	const THUMB    = 'thumb';

	/**
	 * @var  string  Normal size image config
	 */
	public $normal = 'main';

	/**
	 * @var  array  Thumbnail configs
	 */
	public $thumbnails = array('thumbnail', 'icon');


	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta->fields(array(
			'id'       => new Field_Primary,
			'status'   => new Field_Enum(array(
				'default' => self::VISIBLE,
				'choices' => array(
					self::DELETED      => __('Deleted'),
					self::HIDDEN       => __('Hidden'),
					self::NOT_ACCEPTED => __('Not accepted'),
					self::VISIBLE      => __('Visible'),
				)
			)),
			'description' => new Field_String,
			'format'      => new Field_String,
			'created'     => new Field_Timestamp(array(
				'auto_now_create' => true,
			)),
			'view_count' => new Field_Integer(array(
				'column' => 'views',
			)),
			'comment_count' => new Field_Integer(array(
				'column' => 'comments',
			)),
			'rate_count' => new Field_Integer,
			'rate_total' => new Field_Integer,

			'original_size'   => new Field_Integer,
			'original_width'  => new Field_Integer,
			'original_height' => new Field_Integer,
			'width'           => new Field_Integer,
			'height'          => new Field_Integer,
			'thumb_width'     => new Field_Integer,
			'thumb_height'    => new Field_Integer,

			'postfix' => new Field_String,
			'file'    => new Field_File(array(
				'label' => __('Image'),
				'path'  => Kohana::config('image.upload_path'),
			)),
			'legacy_filename' => new Field_String,

			'author' => new Field_BelongsTo(array(
				'column'  => 'author_id',
				'foreign' => 'user',
			)),
			'exif' => new Field_HasOne(array(
				'foreign' => 'image_exif',
			)),
			'comments' => new Field_HasMany(array(
				'foreign' => 'image_comment',
			)),
		));
	}


	/**
	 * Deletes a single image and files
	 *
	 * @param   $key  A key to use for non-loaded records
	 * @return  boolean
	 **/
	public function delete($key = null) {
		if (!$key && $this->loaded()) {

			// Delete default
			if (is_file($this->get_filename())) {
				unlink($this->get_filename());
			}

			// Delete original
			if (is_file($this->get_filename('original'))) {
				unlink($this->get_filename('original'));
			}

			// Delete other sizes
			$sizes = Kohana::config('image.sizes');
			foreach ($sizes as $size => $config) {
				if (isset($config['postfix']) && is_file($this->get_filename($size))) {
					unlink($this->get_filename($size));
				}
			}

			// Delete exif
			$this->exif->delete();

		}

		return parent::delete($key);
	}


	/**
	 * Generate normal size and thumbnails
	 *
	 * @param   string  $original
	 * @return  array   of generated images
	 *
	 * @throws  Kohana_Exception
	 */
	protected function _generate_images($original) {

		// Update image information
		$image = Image::factory($original);
		$this->original_width  = $image->width;
		$this->original_height = $image->height;
		$this->original_size   = filesize($original);

		$path  = rtrim(realpath(pathinfo($original, PATHINFO_DIRNAME)), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		$sizes = Kohana::config('image.sizes');
		$queue = array_merge((array)$this->normal, $this->thumbnails);
		foreach ($queue as $size => $config) {

			// Load config if required
			if (!is_array($config)) {
				$size = $config;
				$config = Arr::get($sizes, $size);
			}
			if (!is_array($config)) {
				throw new Kohana_Exception('Config not found for image size :size', array(':size' => $size));
			}

			// Destination file
			$dest  = $path . $this->id . ($this->postfix ? '_' . $this->postfix : '') . Arr::get($config, 'postfix') . '.jpg';
			!isset($image) and $image = Image::factory($original);

			// Run configured methods in correct order
			foreach ($config as $method => $args) {
				if (in_array($method, array('resize', 'crop')) && $args) {

					// Resize/crop only if needed
					if ($args[0] < $this->width || $args[1] < $this->height) {
						call_user_func_array(array($image, $method), $args);
					}

				}
			}

			$image->save($dest, Arr::get($config, 'quality', Kohana::config('image.quality')));

			// If no prefix, assumed to be the default size
			if (!isset($config['postfix'])) {
				$this->width  = $image->width;
				$this->height = $image->height;
			}

			unset($image);
		}

		parent::save();
	}


	/**
	 * Get full path and filename of specific image size or empty for default
	 *
	 * @param   string   $size
	 * @return  string
	 */
	public function get_filename($size = null) {
		if (!$this->loaded()) {
			return null;
		}

		// Saved image, based on ID
		$path = Kohana::config('image.path') . URL::id($this->id);
		$path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		$postfix = $size == 'original' ? Kohana::config('image.postfix_original') : Arr::path(Kohana::config('image.sizes'), $size . '.postfix');

		return $path . $this->id . ($this->postfix ? '_' . $this->postfix : '') . $postfix . '.jpg';
	}


	/**
	 * Get url of specific image size or empty for default
	 *
	 * @param   string   $size
	 * @return  string
	 */
	public function get_url($size = null, $legacy_dir = null) {
		if (!$this->loaded()) {
			return null;
		}

		// Saved image, based on ID
		$server = Kohana::config('site.image_server');
		if ($this->legacy_filename) {
			if ($size == 'thumbnail') {
				$size = 'thumb_';
			} elseif ($size == 'original') {
				$size = '';
			} else {
				$size = 'normal_';
			}
			$url = ($server ? 'http://' . $server : '') . '/kuvat/' . $legacy_dir . '/' . $size . $this->legacy_filename;
		} else {
			$postfix = $size == 'original' ? Kohana::config('image.postfix_original') : Arr::path(Kohana::config('image.sizes'), $size . '.postfix');
			$url     = ($server ? 'http://' . $server : '') . '/' . Kohana::config('image.url') . URL::id($this->id) . '/';
			$url     = $url . $this->id . ($this->postfix ? '_' . $this->postfix : '') . $postfix . '.jpg';
		}

		return $url;
	}


	/**
	 * Check permission
	 *
	 * @param   string      $permission
	 * @param   Model_User  $user
	 * @return  boolean
	 */
	public function has_permission($permission, $user) {
		switch ($permission) {
			case self::PERMISSION_CREATE:
		    return (bool)$user;

			case self::PERMISSION_DELETE:
			case self::PERMISSION_UPDATE:
		    return $user && ($user->id == $this->author->id || $user->has_role('admin', 'photo moderator'));

			case self::PERMISSION_READ:
		    return true;

		}

		return false;
	}


	/**
	 * Creates or updates the current image
	 *
	 * If $key is passed, the record will be assumed to exist
	 * and an update will be executed, even if the model isn't loaded().
	 *
	 * @param   mixed  $key
	 * @return  $this
	 */
	public function save($key = null) {
		$new = !$this->loaded() && !$key;

		if ($new && (!$this->file || !Upload::not_empty($this->file) || !Upload::type($this->file, array('jpg', 'jpeg', 'gif', 'png')))) {
			throw new Kohana_Exception('Image required');
		}

		parent::save($key);

		// Some magic on created images only
		if ($new) {

			// Make sure we have the new target directory
			$new_path = Kohana::config('image.path') . URL::id($this->id);
			if (!is_dir($new_path)) {
				mkdir($new_path, 0777, true);
				chmod($new_path, 0777);
			}
			if (is_writable($new_path)) {
				$new_path = rtrim($new_path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
			} else {
				throw new Kohana_Exception(get_class($this) . ' can not write to directory');
			}

			// New file name with some random postfix for hard to guess filenames
			!$this->postfix and $this->postfix = Text::random('alnum', 8);
			$new_file = $this->id . '_' . $this->postfix . Kohana::config('image.postfix_original') . '.jpg';

			// Rename and move to correct directory using image id
			$old_path = Kohana::config('image.upload_path');
			$old_file = $this->file;
			if (!rename($old_path . $old_file, $new_path . $new_file)) {
				throw new Kohana_Exception(get_class($this) . ' could not move uploaded image');
			}
			$this->file = $new_file;

			// Start creating images
			$this->_generate_images($new_path . $new_file);

			parent::save();
		}

		return $this;
	}


	/**
	 * Build image URL
	 *
	 * @param   string  $size
	 * @return  string
	 *
	 * @see  NORMAL
	 * @see  THUMB
	 */
	public function url($size = self::NORMAL) {
		$url = '';

		if ($this->loaded()) {
			$path = URL::id($this->id);

			// Postfix filename if necessary
			$postfix = in_array($size, array(self::NORMAL, self::THUMB)) ? '_' . substr($size, 0, 1) : '';
			$filename = $this->id . $postfix . '.' . $this->format;

			$url = 'http://' . Kohana::config('site.image_server') . '/' . $path . '/' . $filename;
		}

		return $url;
	}

}
