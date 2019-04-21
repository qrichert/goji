<?php

	namespace Goji;

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
	 * use Goji\SimpleTemplate;
	 *
	 * // Template is an object containing all the data the template
	 * // file may need to be generated, like page title and content.
	 * $template = new SimpleTemplate();
	 * 		$template->setPageTitle('Title'); // Set page title
	 * 		$template->setPageDescription('Description'); // Set page description
	 * 		$template->setRobotsBehaviour(SimpleTemplate::ROBOTS_NOINDEX_NOFOLLOW); // Disallow robot indexing
	 * 		$template->setSpecials(array(
	 * 			'tracking_event' => 'View page',
	 * 			'og_image' => 'img/og.jpg'
	 * 		));
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
	 * require_once '../template/page/app_t.php'; // Load template file
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
	 * @package Goji
	 */
	class SimpleTemplate {

		/* <ATTRIBUTES> */

		private $m_pageTitle;
		private $m_pageDescription;
		private $m_robotsBehaviour;
		private $m_pageContent;
		private $m_specials;

		/* <CONSTANTS> */

		const ROBOTS_ALLOW_INDEX_AND_FOLLOW = 0;
		const ROBOTS_NOINDEX = 1;
		const ROBOTS_NOFOLLOW = 2;
		const ROBOTS_NOINDEX_NOFOLLOW = 3;

		/**
		 * SimpleTemplate constructor.
		 *
		 * @param string $pageTitle (optional) default = ''
		 * @param string $pageDescription (optional) default = ''
		 * @param int $robotsBehaviour (optional) default = SimpleTemplate::ROBOTS_ALLOW_INDEX_AND_FOLLOW
		 */
		public function __construct($pageTitle = '',
							 $pageDescription = '',
							 $robotsBehaviour = self::ROBOTS_ALLOW_INDEX_AND_FOLLOW) {

			$this->m_pageTitle = $pageTitle;
			$this->m_pageDescription = $pageDescription;
			$this->m_robotsBehaviour = $robotsBehaviour;
			$this->m_pageContent = '';
			$this->m_specials = array();
		}

		/* <GETTERS/SETTERS> */

		/**
		 * Returns page <title>.
		 *
		 * @return string
		 */
		public function getPageTitle() {
			return $this->m_pageTitle;
		}

		/**
		 * Sets page <title>.
		 *
		 * @param string $title
		 */
		public function setPageTitle($title) {
			$this->m_pageTitle = $title;
		}

		/**
		 * Returns page <meta name="description">.
		 *
		 * @return string
		 */
		public function getPageDescription() {
			return $this->m_pageDescription;
		}

		/**
		 * Sets page <meta name="description">.
		 *
		 * @param string $description
		 */
		public function setPageDescription($description) {
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
		public function getRobotsBehaviour() {

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
		 * @param \Goji\SimpleTemplate::ROBOTS_BEHAVIOUR $behaviour
		 */
		public function setRobotsBehaviour($behaviour) {
			$this->m_robotsBehaviour = $behaviour;
		}

		/**
		 * Returns page main content.
		 *
		 * @return string
		 */
		public function getPageContent() {
			return $this->m_pageContent;
		}

		/**
		 * Sets page main content.
		 *
		 * @param string $content
		 */
		public function setPageContent($content) {

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
		public function getSpecial($key) {

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
		 * $template->setSpecials(array(
		 * 		'tracking_event' => 'View page',
		 * 		'og_image' => 'img/og.jpg'
		 * ));
		 * ```
		 *
		 * @param array $arr
		 */
		public function setSpecials($arr) {

			if (is_array($arr))
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
		public function addSpecial($key, $value) {
			$this->m_specials[$key] = $value;
		}

		/* <TEMPLATE FUNCTIONS> */

		/**
		 * Starts buffering. Calls ob_start().
		 */
		public function startBuffer() {
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
		public function closeBuffer() {
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
		public function saveBuffer() {

			// Get content && update page content
			$this->setPageContent($this->readBuffer());
		}
	}
