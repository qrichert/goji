<?php

	namespace Goji\Toolkit;

	use Goji\Core\ConfigurationLoader;
	use Exception;

	/**
	 * Class SimpleTemplate
	 *
	 * Easily handles page templates.
	 *
	 * Ex:
	 *
	 * ```php
	 * // Controller
	 *
	 * use Goji\Toolkit\SimpleTemplate;
	 *
	 * // Template is an object containing all the data the template
	 * // file may need to be generated, like page title and content.
	 * $template = new SimpleTemplate();
	 * 		$template->setPageTitle('Title'); // Set page title
	 * 		$template->setPageDescription('Description'); // Set page description
	 * 		$template->setRobotsBehaviour(SimpleTemplate::ROBOTS_NOINDEX_NOFOLLOW); // Disallow robot indexing
	 * 		$template->setSpecials([
	 * 			'tracking_event' => 'View page',
	 * 			'og_image' => 'img/og.jpg'
	 * 		]);
	 *
	 * // Instead of buffering the view file, you could also just
	 * // do $template->setPageContent($string), but it's usually easier
	 * // to just buffer an entire HTML file into a string than generating
	 * // a string from scratch.
	 * $template->startBuffer(); // Start buffering HTML
	 *
	 * 		// Generating the View
	 * 		// Main page content goes here
	 *
	 * // This calls SimpleTemplate::setPageContent($buffer), no need to set it manually.
	 * $template->saveBuffer(); // Saves content internally.
	 *
	 * // Template file will read the values of SimpleTemplate
	 * require_once '../template/page/main.template.php'; // Load template file
	 *
	 * // Inside the template you can read values like this
	 *
	 * 		// Outputting them directly
	 * 		<?= $template->getPageTitle(); ?>
	 * 		<?= $template->getPageDescription(); ?>
	 *
	 * 		// Or with 'echo', it's the same shit
	 * 		echo $template->getPageContent(); // Get buffered HTML
	 * 		echo $template->getRobotsBehaviour(); // Returns string like <meta name="robots...>
	 * 		echo $template->getSpecial('tracking_event'); // Here would return 'View page' as set before
	 * ```
	 *
	 * If you want to add elements not supported by default, you can use Specials.
	 * See SimpleTemplate::setSpecials(), SimpleTemplate::addSpecial() and SimpleTemplate::getSpecial()
	 *
	 * @package Goji\Toolkit
	 */
	class SimpleTemplate {

		/* <ATTRIBUTES> */

		private $m_webRoot;
		private $m_pageTitle;
		private $m_pageDescription;
		private $m_robotsBehaviour;
		private $m_showCanonicalPageAndAlternates;
		private $m_pageContent;
		private $m_specials;
		private $m_linkedFilesMode;

		/* <CONSTANTS> */

		const CONFIG_FILE = '../config/templating.json5';

		const ROBOTS_ALLOW_INDEX_AND_FOLLOW = 0;
		const ROBOTS_NOINDEX = 1;
		const ROBOTS_NOFOLLOW = 2;
		const ROBOTS_NOINDEX_NOFOLLOW = 3;

		const NORMAL = 'normal';
		const MERGED = 'merged';

		const CSS = 'css';
		const JAVASCRIPT = 'js';

		const E_UNSUPPORTED_FILE_TYPE = 0;

		/**
		 * SimpleTemplate constructor.
		 *
		 * @param string $pageTitle (optional) default = ''
		 * @param string $pageDescription (optional) default = ''
		 * @param int $robotsBehaviour (optional) default = SimpleTemplate::ROBOTS_ALLOW_INDEX_AND_FOLLOW
		 * @param bool $showCanonicalPageAndAlternates (optional) default = true
		 * @param string $configFile
		 */
		public function __construct(string $pageTitle = '',
		                            string $pageDescription = '',
		                            int $robotsBehaviour = self::ROBOTS_ALLOW_INDEX_AND_FOLLOW,
									bool $showCanonicalPageAndAlternates = true,
									$configFile = self::CONFIG_FILE) {

			$this->m_webRoot = WEBROOT;
			$this->m_pageTitle = $pageTitle;
			$this->m_pageDescription = $pageDescription;
			$this->m_robotsBehaviour = $robotsBehaviour;
			$this->m_showCanonicalPageAndAlternates = $showCanonicalPageAndAlternates;
			$this->m_pageContent = '';
			$this->m_specials = [];

			try {

				$config = ConfigurationLoader::loadFileToArray($configFile);

				if (isset($config['linked_files_mode'])
				    && ($config['linked_files_mode'] == self::NORMAL
				        || $config['linked_files_mode'] == self::MERGED))
							$this->m_linkedFilesMode = $config['linked_files_mode'];
				else
					$this->m_linkedFilesMode = self::NORMAL;

			} catch (Exception $e) {

				$this->m_linkedFilesMode = self::NORMAL;
			}
		}

		/* <GETTERS/SETTERS> */

		/**
		 * @return string
		 */
		public function getWebRoot(): string {
			return $this->m_webRoot;
		}

		/**
		 * Returns page <title>.
		 *
		 * @return string
		 */
		public function getPageTitle(): string {
			return $this->m_pageTitle;
		}

		/**
		 * Sets page <title>.
		 *
		 * @param string $title
		 */
		public function setPageTitle(string $title): void {
			$this->m_pageTitle = $title;
		}

		/**
		 * Returns page <meta name="description">.
		 *
		 * @return string
		 */
		public function getPageDescription(): string {
			return $this->m_pageDescription;
		}

		/**
		 * Sets page <meta name="description">.
		 *
		 * @param string $description
		 */
		public function setPageDescription(string $description): void {
			$this->m_pageDescription = $description;
		}

		/**
		 * Returns page <meta name="robots">.
		 *
		 * It can return four different values:
		 *
		 * ```html
		 * <meta name="robots" content="noindex">
		 * <meta name="robots" content="nofollow">
		 * <meta name="robots" content="noindex,nofollow">
		 * <!-- Fourth value is an empty string. -->
		 * ```
		 *
		 * @return string
		 */
		public function getRobotsBehaviour(): string {

			switch ($this->m_robotsBehaviour) {

				case self::ROBOTS_NOINDEX:			return '<meta name="robots" content="noindex">';			break;
				case self::ROBOTS_NOFOLLOW:			return '<meta name="robots" content="nofollow">';			break;
				case self::ROBOTS_NOINDEX_NOFOLLOW:	return '<meta name="robots" content="noindex,nofollow">';	break;
			}

			return ''; // Default, nothing
		}

		/**
		 * Sets page <meta name="robots">.
		 *
		 * It can take four different values:
		 *
		 * ```php`
		 * SimpleTemplate::ROBOTS_ALLOW_INDEX_AND_FOLLOW // Default
		 * SimpleTemplate::ROBOTS_NOINDEX
		 * SimpleTemplate::NOFOLLOW
		 * SimpleTemplate::NOINDEX_NOFOLLOW
		 * ``
		 *
		 * @param \Goji\Toolkit\SimpleTemplate::ROBOTS_BEHAVIOUR $behaviour
		 */
		public function setRobotsBehaviour(int $behaviour): void {
			$this->m_robotsBehaviour = $behaviour;
		}

		/**
		 * @return bool
		 */
		public function getShowCanonicalPageAndAlternates(): bool {
			return $this->m_showCanonicalPageAndAlternates;
		}

		/**
		 * Show canonical page link and alternate languages or not.
		 *
		 * @param bool $show
		 */
		public function setShowCanonicalPageAndAlternates(bool $show): void {
			$this->m_showCanonicalPageAndAlternates = $show;
		}

		/**
		 * Returns page main content.
		 *
		 * @return string
		 */
		public function getPageContent(): string {
			return $this->m_pageContent;
		}

		/**
		 * Sets page main content.
		 *
		 * @param string $content
		 */
		public function setPageContent(string $content): void {

			// Make sure it's valid
			if (is_string($content))
				$this->m_pageContent = $content;
		}

		/**
		 * Get the value of a specific special.
		 *
		 * For example:
		 *
		 * ```php
		 * <?= $template->getSpecial('tracking_event'); ?>
		 * ```
		 *
		 * @param string $key
		 * @return mixed|null
		 */
		public function getSpecial(string $key) {

			if (isset($this->m_specials[$key]))
				return $this->m_specials[$key];
			else
				return null;
		}

		/**
		 * Deletes all specials and replaces it with given array.
		 *
		 * Given array should be associative with string keys, like:
		 *
		 * ```php
		 * $template->setSpecials([
		 * 		'tracking_event' => 'View page',
		 * 		'og_image' => 'img/og.jpg'
		 * ]);
		 * ```
		 *
		 * @param array $arr
		 */
		public function setSpecials(array $arr): void {
			$this->m_specials = $arr;
		}

		/**
		 * Add a mixed value to the Specials.
		 *
		 * Key should be string.
		 * Mixed value means any type as long as it fits into an array.
		 *
		 * ```php
		 * $template->addSpecial('tracking_event', 'View page');
		 * ```
		 *
		 * @param string $key
		 * @param mixed $value
		 */
		public function addSpecial(string $key, $value): void {
			$this->m_specials[$key] = $value;
		}

		/* <TEMPLATE FUNCTIONS> */

		/**
		 * Starts buffering. Calls ob_start().
		 */
		public function startBuffer(): void {
			ob_start();
		}

		/**
		 * Closes buffer and loads fragment into variable.
		 *
		 * @return false|string
		 */
		public function readBuffer() {
			return ob_get_clean();
		}

		/**
		 * Closes buffer and discards content.
		 */
		public function closeBuffer(): void {
			ob_end_clean();
		}

		/**
		 * Closes buffer and saves content.
		 *
		 * This is equivalent to doing:
		 *
		 * ```php
		 * $content = $template->readBuffer();
		 * $template->setPageContent($content);
		 * ```
		 */
		public function saveBuffer(): void {

			// Get content && update page content
			$this->setPageContent($this->readBuffer());
		}

		/**
		 * @param string|array $files
		 * @param bool $renderAbsolutePaths css/main.css -> /WEBROOT/css/main.css
		 * @param bool $returnAsString
		 * @param string|null $forceMode
		 * @return string|null
		 * @throws \Exception
		 */
		public function linkFiles($files, bool $renderAbsolutePaths = true, bool $returnAsString = false, string $forceMode = null): ?string {

			// Make sure it's either string or array
			if (!is_array($files) && !is_string($files))
				return null;

			// If it's a string, make it an array
			$files = (array) $files;

			// If there's no element in the array, quit
			if (count($files) === 0)
				return null;

			if ($renderAbsolutePaths) {

				foreach ($files as &$f) {
					$slash =  mb_substr($f, 0, 1) == '/' ? '' : '/';
					$f = WEBROOT . $slash . $f;
				}
				unset($f);
			}

			$linkedFilesMode = $this->m_linkedFilesMode;

			// If force mode is set & valid, use it
			if (isset($forceMode)
			    && ($forceMode === self::NORMAL || $forceMode === self::MERGED))
					$linkedFilesMode = $forceMode;

			// Now we guess the file type
			$fileType = pathinfo($files[0], PATHINFO_EXTENSION);
				$fileType = mb_strtolower($fileType);

			$linkStatement = '';

			if ($fileType === self::CSS)
				$linkStatement = '<link rel="stylesheet" type="text/css" href="%{PATH}">';
			else if ($fileType === self::JAVASCRIPT)
				$linkStatement = '<script src="%{PATH}"></script>';
			else
				throw new Exception('Unsupported file type: ' . $fileType, self::E_UNSUPPORTED_FILE_TYPE);

			$output = '';

			if ($linkedFilesMode === self::MERGED) {

				$output = implode(rawurlencode('|'), $files);
				$output = str_replace('%{PATH}', $output, $linkStatement) . PHP_EOL;

			} else { // self::NORMAL

				foreach ($files as $file) {
					$output .= str_replace('%{PATH}', $file, $linkStatement) . PHP_EOL;
				}
			}

			if ($returnAsString)
				return $output;
			else
				echo $output;

			return null;
		}

		public static function anchorify(string $id): string {
			return SwissKnife::stringToID($id);
		}
	}
