<?php defined('SYSPATH') or die('No direct access allowed.');

require_once(Kohana::find_file('vendor', 'nbbc/nbbc'));

/**
 * BBCode lbrary
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @copyright  (c) 2008-9, the Phantom Inker
 * @copyright  (c) 2004-2008 AddedBytes
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_BB extends BBCode {

	/**
	 * The BBCode formatted text
	 *
	 * @var  string
	 */
	protected $text = null;


	/**
	 * Create new BBCode object and initialize our own settings
	 */
	public function __construct($text = null) {
		parent::BBCode();

		$this->text = $text;

		// Automagically print hrefs
		$this->SetDetectURLs(true);

		// We have our own smileys
		$config = Kohana::config('site.smiley');
		if (!empty($config)) {
			$this->ClearSmileys();
			$this->SetSmileyURL(URL::base() . $config['dir']);
			foreach ($config['smileys'] as $name => $smiley) {
				$this->AddSmiley($name, $smiley['src']);
			}
		} else {
			$this->SetEnableSmileys(false);
		}

		// We handle newlines with Kohana
		$this->SetIgnoreNewlines(true);
		$this->SetPreTrim('a');
		$this->SetPostTrim('a');

		// User our own quote
		$this->AddRule('quote', array(
			'mode'     => BBCODE_MODE_CALLBACK,
			'method'   => array($this, 'bbcode_quote'),
			'class'    => 'block',
			'allow_in' => array('listitem', 'block', 'columns'),
			'content'  => BBCODE_REQUIRED,
		));
	}


	/**
	 * Handle forum quotations
	 *
	 * @param   BBCode  $bbcode
	 * @param   string  $action
	 * @param   string  $name
	 * @param   string  $default
	 * @param   array   $params
	 * @param   string  $content
	 * @return  string
	 */
	public function bbcode_quote($bbcode, $action, $name, $default, $params, $content) {

		// Pass all to 2nd phase
		if ($action == BBCODE_CHECK) {
			return true;
		}

		// Parse parameters
		foreach ($params['_params'] as $param) {
			switch ($param['key']) {

				// Parent post id
				case 'post':
					$post = Model_Forum_Post::find((int)$param['value']);
					break;

				// Parent post author
				case 'author':
					$author_name = $param['value'];
					$author = Model_User::find_user_light($author_name);
					break;

			}
		}

		// Add parent post
		if (isset($post) && $post->loaded()) {
			$quote = '<blockquote cite="' . URL::site(Route::model($post->topic)) . '?post=' . $post->id . '#post-' . $post->id . '">';

			// Override author
			$author = Model_User::find_user_light($post->original('author'));
		} else {
			$quote = '<blockquote>';
		}

		$quote .= '<p>' . trim($content) . '</p>';

		// Post author
		if (isset($author) && $author) {
			$quote .= '<cite>' . __('-- :author', array(':author' => HTML::user($author))) . '</cite>';
		} else if (isset($author_name)) {
			$quote .= '<cite>' . __('-- :author', array(':author' => HTML::chars($author_name))) . '</cite>';
		}

		$quote .= '</blockquote>';

		return $quote;
	}


	/**
	 * Creates and returns new BBCode object
	 *
	 * @param   string  $text
	 * @return  BB
	 */
	public static function factory($text = null) {
		return new BB($text);
	}


	/**
	 * Return BBCode parsed to HTML
	 *
	 * @return  string
	 */
	public function render() {
		if (is_null($this->text)) {
			return '';
		}

		// Convert old system tags to BBCode
		$this->text = str_replace(array('[link', '[/link]', '[q]', '[/q]'), array('[url', '[/url]', '[quote]', '[/quote]'), $this->text);

		return text::auto_p($this->Parse($this->text));
	}

}
