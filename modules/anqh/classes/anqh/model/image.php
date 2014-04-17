<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Image model.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 *
 * @todo  Refactor to use uniqid instead of id_postfix for filename
 */
class Anqh_Model_Image extends AutoModeler_ORM implements Permission_Interface {

	/** Permission to add/edit notes */
	const PERMISSION_NOTE   = 'note';

	/** Permission to report image */
	const PERMISSION_REPORT = 'report';

	const DELETED      = 'd';
	const HIDDEN       = 'h';
	const VISIBLE      = 'v';

	const SIZE_ICON      = 'icon';
	const SIZE_MAIN      = 'main';
	const SIZE_ORIGINAL  = 'original';
	const SIZE_SIDE      = 'side';
	const SIZE_THUMBNAIL = 'thumbnail';
	const SIZE_WIDE      = 'wide';

	const TOP_COMMENTED  = 'commented';
	const TOP_RATED      = 'rated';
	const TOP_VIEWED     = 'viewed';

	protected $_table_name = 'images';

	protected $_data = array(
		'id'                => null,
		'status'            => self::VISIBLE,
		'description'       => null,
		'format'            => null,
		'created'           => null,
		'view_count'        => 0,
		'comment_count'     => 0,
		'rate_count'        => 0,
		'rate_total'        => 0,

		'original_size'     => null,
		'original_width'    => null,
		'original_height'   => null,
		'width'             => null,
		'height'            => null,
		'thumb_width'       => null,
		'thumb_height'      => null,

		'postfix'           => null,
		'file'              => null,
		'remote'            => null,

		'author_id'         => null,

		// Deprecated
		'new_comment_count' => 0,
		'legacy_filename'   => null,

	);

	protected $_rules = array(
		'status'            => array('not_empty', 'in_array' => array(':value', array(self::DELETED, self::HIDDEN, self::VISIBLE))),
		'remote'            => array('url'),
	);

	protected $_belongs_to = array(
		'galleries'
	);

	/**
	 * @var  string  Normal size image config
	 */
	public $normal = self::SIZE_MAIN;

	/**
	 * @var  array  Thumbnail configs
	 */
	public $thumbnails = array(self::SIZE_THUMBNAIL, self::SIZE_ICON);

	/**
	 * Add new note.
	 *
	 * @param   integer  $author_id
	 * @param   array    $position    x, y, width, height
	 * @param   mixed    $user        Model_User or username
	 * @return  Model_Image_Note
	 */
	public function add_note($author_id, array $position = null, $user = null) {
		Model_Image_Note::factory()->add($author_id, $this->id, $position, $user);

		$this->update_description()->save();
	}


	/**
	 * Get image comments
	 *
	 * @param   Model_User  $viewer
	 * @return  Database_Result
	 */
	public function comments(Model_User $viewer = null) {
		$query = Model_Comment::query_viewer(
			DB::select_array(Model_Image_Comment::factory()->fields())
				->order_by('id', 'DESC'),
			$viewer);

		return $this->find_related('image_comments', $query);
	}


	/**
	 * Convert images from local path to new path and sizes.
	 *
	 * @param   string  $old_path  Legacy path of old image
	 * @throws  Kohana_Exception
	 */
	public function convert($old_path) {

		// Make sure we have the new target directory
		$new_path = Kohana::$config->load('image.path') . URL::id($this->id);
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
		$new_file = $this->id . '_' . $this->postfix . Kohana::$config->load('image.postfix_original') . '.jpg';

		// Rename and copy to correct directory using image id
		if (!copy($old_path . $this->legacy_filename, $new_path . $new_file)) {
			throw new Kohana_Exception(get_class($this) . ' could not copy old image');
		}
		$this->file = $new_file;

		// Start creating images
		$this->_generate_images($new_path . $new_file);
	}


	/**
	 * Deletes a single image and files
	 *
	 * @return  boolean
	 **/
	public function delete() {
		if ($this->loaded()) {

			// Delete default
			if (is_file($this->get_filename())) {
				unlink($this->get_filename());
			}

			// Delete original
			if (is_file($this->get_filename(self::SIZE_ORIGINAL))) {
				unlink($this->get_filename(self::SIZE_ORIGINAL));
			}

			// Delete other sizes
			$sizes = Kohana::$config->load('image.sizes');
			foreach ($sizes as $size => $config) {
				if (isset($config['postfix']) && is_file($this->get_filename($size))) {
					unlink($this->get_filename($size));
				}
			}

			// Delete exif
			if ($exif = $this->exif()) {
				$exif->delete();
			}

		}

		return parent::delete();
	}


	/**
	 * Get EXIF data for image.
	 *
	 * @return  Model_Exif
	 */
	public function exif() {
		if ($exif = $this->find_related('image_exifs')) {
			return $exif->current();
		}

		return null;
	}


	/**
	 * Find by user id.
	 *
	 * @param   integer  $user_id
	 * @return  Model_Image[]
	 */
	public function find_by_user($user_id) {
		$images = array();

		if ($notes = Model_Image_Note::factory()->find_by_user($user_id)) {
			$stamps = array();
			foreach ($notes as $note) {
				$images[] = $image = $note->image();
				$stamps[] = $image->created;
			}

			// Sort by date
			array_multisort($stamps, SORT_DESC, $images);

		}

		return $images;
	}


	/**
	 * Get images with new comments.
	 *
	 * @param   Model_User  $user
	 * @return  Database_Result
	 */
	public function find_new_comments(Model_User $user) {
		return $this->load(
			DB::select_array($this->fields())
				->where('author_id', '=', $user->id)
				->and_where('new_comment_count', '>', 0),
			null
		);
	}


	/**
	 * Get top images.
	 *
	 * @param   string   $type     see TOP_*
	 * @param   integer  $top
	 * @param   integer  $year     false for all
	 * @param   integer  $user_id
	 * @return  Model_Image[]
	 */
	public function find_top($type, $top = 10, $year = null, $user_id = null) {
		$query         = DB::select_array($this->fields());
		$query_gallery = DB::select('image_id')
			->from('galleries_images')
			->join('galleries', 'INNER')
			->on('galleries.id', '=', 'gallery_id');

		// Load only for a specific year?
		if ((int)$year) {
			$start = mktime(0, 0, 0, 1, 1, (int)$year);
			$end   = mktime(23, 59, 59, 31, 12, (int)$year);
			$query_gallery->where('date', 'BETWEEN', array($start, $end));
		}

		// Load only for specific user?
		if ((int)$user_id) {
			$query->where('author_id', '=', (int)$user_id);
		}

		$query->where('id', 'IN', $query_gallery);


		switch ($type) {

			case self::TOP_COMMENTED:
				$query->order_by('comment_count', 'DESC');
				break;

			case self::TOP_RATED:
				$query
					->where('rate_count', '>', 4)
					->order_by(DB::expr('100 * images.rate_total / images.rate_count'), 'DESC');
				break;

			case self::TOP_VIEWED:
				$query->order_by('view_count', 'DESC');
				break;

			default:
				return null;

		}

		return $this->load($query, $top);
	}


	/**
	 * Get image gallery
	 *
	 * @return  Model_Gallery
	 */
	public function gallery() {
		if ($gallery = $this->find_parent('galleries')) {
			$gallery = $gallery->current();
			$gallery->state(AutoModeler::STATE_LOADED);

			return $gallery;
		}

		return null;
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
		$sizes = Kohana::$config->load('image.sizes');
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
				if (in_array($method, array('resize', 'crop')) && is_array($args)) {

					// Resize/crop only if needed
					if ($args[0] < $image->width || $args[1] < $image->height) {
						call_user_func_array(array($image, $method), $args);
					}

				}
			}

			$image->save($dest, Arr::get($config, 'quality', Kohana::$config->load('image.quality')));

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
		$path = Kohana::$config->load('image.path') . URL::id($this->id);
		$path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		$postfix = ($size == self::SIZE_ORIGINAL) ? Kohana::$config->load('image.postfix_original') : Arr::path(Kohana::$config->load('image.sizes'), $size . '.postfix');

		return $path . $this->id . ($this->postfix ? '_' . $this->postfix : '') . $postfix . '.jpg';
	}


	/**
	 * Get url of specific image size or empty for default
	 *
	 * @param   string   $size
	 * @param   string   $legacy_dir  To be deprecated
	 * @return  string
	 */
	public function get_url($size = null, $legacy_dir = null) {
		if (!$this->id) {
			return null;
		}

		// Saved image, based on ID
		$server = Kohana::$config->load('site.image_server');
		if ($this->legacy_filename && !$this->postfix) {
			if ($size == self::SIZE_THUMBNAIL) {
				$size = 'thumb_';
			} elseif ($size == self::SIZE_ORIGINAL) {
				$size = '';
			} else {
				$size = 'pieni_';
			}
			$url = ($server ? '//' . $server : '') . '/kuvat/' . $legacy_dir . '/' . $size . $this->legacy_filename;
		} else {
			$postfix = ($size == self::SIZE_ORIGINAL) ? Kohana::$config->load('image.postfix_original') : Arr::path(Kohana::$config->load('image.sizes'), $size . '.postfix');
			$url     = ($server ? '//' . $server : '') . '/' . Kohana::$config->load('image.url') . URL::id($this->id) . '/';
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
			case self::PERMISSION_NOTE:
			case self::PERMISSION_REPORT:
		    return (bool)$user;

			case self::PERMISSION_DELETE:
			case self::PERMISSION_UPDATE:
		    return $user && ($user->id == $this->author_id || $user->has_role('admin', 'photo moderator'));

			case self::PERMISSION_READ:
		    return true;

		}

		return false;
	}


	/**
	 * Get image notes
	 *
	 * @return  Database_Result
	 */
	public function notes() {
		return $this->find_related(
			'image_notes',
			DB::select_array(Model_Image_Note::factory()->fields())
				->order_by('x', 'ASC')
				->order_by('id', 'ASC')
		);
	}


	/**
	 * Create new resized images from original overwriting old.
	 *
	 * @param   string  $normal  New normal size
	 * @param   array   $thumbs  New thumb sizes
	 * @throws  Kohana_Exception
	 */
	public function resize($normal = null, array $thumbs = null) {
		$this->normal     = (array)$normal;
		$this->thumbnails = (array)$thumbs;

		$this->_generate_images($this->get_filename(self::SIZE_ORIGINAL));
	}


	/**
	 * Creates or updates the current image.
	 *
	 * @param   mixed  $validation a manual validation object to combine the model properties with
	 * @return  integer
	 *
	 * @throws  Kohana_Exception
	 */
	public function save($validation = null) {
		$new = !(bool)$this->id;

		// Validate new image
		if ($new) {
			$path = Kohana::$config->load('image.upload_path');

			// Download remote files
			if ($this->remote && !$this->file) {
				$this->file = Request::factory($this->remote)->download(null, $path);
			}

			if (!$this->file || (!$this->remote && !Upload::not_empty($this->file))) {
				throw new Kohana_Exception(__('No image'));
			} else if (!Upload::size($this->file, Kohana::$config->load('image.filesize'))) {
				throw new Kohana_Exception(__('Image too big (limit :size)', array(':size' => Kohana::$config->load('image.filesize'))));
			} else if (
				!Upload::type($this->file, Kohana::$config->load('image.filetypes'))
				&& !in_array($this->file['type'], Kohana::$config->load('image.mimetypes'))
			) {
				throw new Kohana_Exception(__('Invalid image type (use :types)', array(':types' => implode(', ', Kohana::$config->load('image.filetypes')))));
			}

			$upload = $this->file;

			if ($this->remote && !is_uploaded_file($upload['tmp_name'])) {

				// As a remote file is no actual file field, manually set the filename
				$this->file = basename($upload['tmp_name']);

			} else if (is_uploaded_file($upload['tmp_name'])) {

				// Sanitize the filename
				$upload['name'] = preg_replace('/[^a-z0-9-\.]/', '-', mb_strtolower($upload['name']));

				// Strip multiple dashes
				$upload['name'] = preg_replace('/-{2,}/', '-', $upload['name']);

				// Try to save upload
				if (false !== ($this->file = Upload::save($upload, null, $path))) {

					// Get new filename
					$this->file = basename($this->file);

				}

			}

		}

		try {
			parent::save();
		} catch (Validation_Exception $e) {
			if ($new && $this->file) {
				unlink($path . $this->file);
			}
			throw $e;
		}

		// Some magic on created images only
		if ($new) {

			// Make sure we have the new target directory
			$new_path = Kohana::$config->load('image.path') . URL::id($this->id);
			if (!is_dir($new_path)) {
				mkdir($new_path, 0775, true);
				chmod($new_path, 0775);
			}
			if (is_writable($new_path)) {
				$new_path = rtrim($new_path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
			} else {
				throw new Kohana_Exception(get_class($this) . ' can not write to directory');
			}

			// New file name with some random postfix for hard to guess filenames
			!$this->postfix and $this->postfix = Text::random('alnum', 8);
			$new_file = $this->id . '_' . $this->postfix . Kohana::$config->load('image.postfix_original') . '.jpg';

			// Rename and move to correct directory using image id
			$old_file = $this->file;
			if (!rename($path . $old_file, $new_path . $new_file)) {
				unlink($path . $old_file);
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
	 * Update deprecated description field
	 *
	 * @return  Model_Image
	 */
	public function update_description() {
		$description = array();

		/** @var  Model_Image_Note  $note */
		foreach ($this->notes() as $note) {
			if ($user = $note->user()) {
				$description[] = $user['username'];
			} else {
				$description[] = $note->name;
			}
		}
		$this->description = implode(', ', $description);

		return $this;
	}

}
