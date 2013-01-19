<?php defined('SYSPATH') or die('No direct access allowed.');

require_once(Kohana::find_file('vendor', 'nbbc/nbbc'));

/**
 * BBCode library
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_BB extends BBCode {

	/**
	 * @var  string  The BBCode formatted text
	 */
	protected $text = null;

	/**
	 * @var  array  Embeddable link templates
	 */
	protected $templates = array(

		// Audio/music templates for [audio]
		'audio' => array(
			'soundcloud' => array(
				'pattern'  => '/^https?:\/\/soundcloud\.com\/([^\/]+)\/([^\/]+)\/?$/i',
				'template' => '<iframe width="100%" height="166" src="https://w.soundcloud.com/player/?url=http%3A%2F%2Fapi.soundcloud.com%2F$1%2F$2&amp;auto_play=false&amp;show_artwork=true" frameborder="0"></iframe>'
			),
			'soundcloud_user' => array(
				'pattern'  => '/^https?:\/\/soundcloud\.com\/([^\/]+)\/?$/i',
				'template' => '<iframe width="100%" height="292" src="https://w.soundcloud.com/player/?url=http%3A%2F%2Fapi.soundcloud.com%2F$1&amp;auto_play=false&amp;show_artwork=true" frameborder="0"></iframe>'
			),
			'spotify' => array(
				'pattern'  => '/^http:\/\/open\.spotify\.com\/(track|album)\/(\w+)$/i',
				'template' => '<iframe width="100%" height="80" src="https://embed.spotify.com/?uri=spotify:$1:$2" frameborder="0" allowtransparency></iframe>',
			),
			'spotify_playlist' => array(
				'pattern'  => '/^http:\/\/open\.spotify\.com\/user([^\/]+)\/playlist\/(\w+)$/i',
				'template' => '<iframe width="100%" height="80" src="https://embed.spotify.com/?uri=spotify:user:$1:playlist:$2" frameborder="0" allowtransparency></iframe>',
			),
			'spotify2' => array(
				'pattern'  => '/^(spotify:(track|album):(\w+))$/i',
				'template' => '<iframe width="100%" height="80" src="https://embed.spotify.com/?uri=$1" frameborder="0" allowtransparency></iframe>',
			),
			'spotify_playlist2' => array(
				'pattern'  => '/^(spotify:user:([^:]+):playlist:(\w+))$/i',
				'template' => '<iframe width="100%" height="80" src="https://embed.spotify.com/?uri=$1" frameborder="0" allowtransparency></iframe>',
			),
		),

		// Image templates for [img]
		'img' => array(
			'all' => array(
				'pattern'  => '/^https?:\/\/([a-z0-9-\.]*)\/(.*\.[jpg|gif|png]\??.*)$/i',
				'template' => '<img src="http://$1/$2" alt="" />',
       ),
		),

		// Video templates for [video]
		'video' => array(
			'ted' => array(
				'pattern'  => '/^https?:\/\/www\.ted\.com\/talks\/(.*)$/i',
				'template' => '<iframe width="100%" height="292" src="http://embed.ted.com/talks/$1" frameborder="0" allowfullScreen></iframe>'
			),
			'vimeo' => array(
				'pattern'  => '/^https?:\/\/(?:www\.)?vimeo\.com\/([0-9]+).*$/i',
				'template' => '<iframe width="100%" height="292" src="http://player.vimeo.com/video/$1" frameborder="0" allowfullScreen></iframe>',
			),
			'youtube' => array(
				'pattern'  => '/^https?:\/\/([a-z0-9]*\.youtube\.com)\/watch\?v=([^"&]+).*$/i',
				'template' => '<iframe width="100%" height="292" src="https://www.youtube.com/embed/$2" frameborder="0" allowfullscreen></iframe>',
			),
			'youtube_short' => array(
				'pattern'  => '/^https?:\/\/youtu\.be\/([^"&\?]+).*$/i',
				'template' => '<iframe width="100%" height="292" src="https://www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe>',
			),
		)

	);


	/**
	 * Create new BBCode object and initialize our own settings
	 *
	 * @param  string  $text
	 */
	public function __construct($text = null) {
		parent::BBCode();

		$this->text = $text;

		// Automagically print hrefs
		$this->SetDetectURLs(true);

		// We have our own smileys
		$config = Kohana::$config->load('site.smiley');
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
		//$this->SetIgnoreNewlines(true);
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

		// Media tags
		$this->AddRule('audio', array(
			'mode'     => BBCODE_MODE_CALLBACK,
			'method'   => array($this, 'bbcode_media'),
			'class'    => 'block',
			'allow_in' => array('listitem', 'block', 'columns', 'inline'),
			'allow'    => array(
				'align' => '/^left|center|right$/',
			),
			'default'  => array(
				'align' => 'left',
			),
			'content'  => BBCODE_REQUIRED,
			'plain_content' => array(''),
		));
		$this->AddRule('video', array(
			'mode'     => BBCODE_MODE_CALLBACK,
			'method'   => array($this, 'bbcode_media'),
			'class'    => 'block',
			'allow_in' => array('listitem', 'block', 'columns', 'inline'),
			'allow'    => array(
				'align' => '/^left|center|right$/',
			),
			'default'  => array(
				'align' => 'left',
			),
			'content'  => BBCODE_REQUIRED,
			'plain_content' => array(''),
		));

	}


	/**
	 * Handle media, [audio] and [video]
	 *
	 * @param   object  $bbcode
	 * @param   string  $action
	 * @param   string  $name
	 * @param   string  $default
	 * @param   array   $params
	 * @param   string  $content
	 * @return  string
	 */
	public function bbcode_media($bbcode, $action, $name, $default, $params, $content) {

    // First stage, check if the tag has valid content
    if ($action == BBCODE_CHECK) {

	    // Validate align params
	    if (isset($params['align']) && !in_array($params['align'], array('left', 'center', 'right'))) {
				return false;
	    }

      // All is ok
      return true;
    }

    // Second stage
    $align = isset($params['align']) ? ' align' . $params['align'] : '';

    // Strip autogenerated anchor tag -.-
    $url = trim(strip_tags($content));

    // Find our media provider
		$content = '';
    foreach ($this->templates[$name] as $regex) {
      if (preg_match($regex['pattern'], $url)) {
				return '<div class="' . $name . $align . '">' . preg_replace($regex['pattern'], $regex['template'], $url) . '</div>';
			}
    }

    return $content;
	}


	/**
	 * Handle forum quotations.
	 *
	 * @param   object  $bbcode
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
					$post = Model_Forum_Post::factory((int)$param['value']);
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
			$quote = '<blockquote cite="' . URL::site(Route::model($post->topic())) . '?post=' . $post->id . '#post-' . $post->id . '">';

			// Override author
			$author = Model_User::find_user_light($post->author_id);
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
	 * Embed orphan media links to use [audio] and [video] tags.
	 *
	 * @param   string  $text
	 * @return  string
	 */
	protected function embed($text) {

		// Find links that aren't inside tags
		if (preg_match_all('/[^\]="](https?:\/\/[\w_=\?\-\/\.]+)/i', ' ' . $text, $links)) {

			// Build replacement list
			$replace = $duplicates = array();
			foreach ($links[1] as $link) {
				foreach ($this->templates as $type => $templates) {
					foreach ($templates as $media) {
						if (preg_match($media['pattern'], $link)) {
							$replace[$link] = '[' . $type . ']' . $link . '[/' . $type . ']';
							break 2;
						}
					}
				}
			}

			// Remove possible duplicate tags and replace
			if ($replace) {
				$duplicates = array();
				foreach (array_keys($this->templates) as $type) {
					$duplicates['[' . $type . '][' . $type . ']']   = '[' . $type . ']';
					$duplicates['[/' . $type . '][/' . $type . ']'] = '[/' . $type . ']';
				}

				$text = strtr(strtr($text, $replace), $duplicates);
			}
		}

		return $text;
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
	 * @param   string  $text
	 * @return  string
	 */
	public function render($text = null) {
		if ($text) {
			$this->text = $text;
		}

		if (is_null($this->text)) {
			return '';
		}

		// Convert old system tags to BBCode
		$this->text = str_replace(array('[link', '[/link]', '[q]', '[/q]'), array('[url', '[/url]', '[quote]', '[/quote]'), $this->text);

		// Convert orphan media links to tags
		$this->text = $this->embed($this->text);

		// Parse BBCode
		$parsed = $this->Parse($this->text);

		return $parsed; //$this->GetPlainMode() ? $parsed : Text::auto_p($parsed);
	}

}
