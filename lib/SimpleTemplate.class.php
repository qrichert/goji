<?php

	/*
		Easily handles page templates.

		Ex:

			require_once '../lib/SimpleTemplate.class.php';

			$_TEMPLATE = new SimpleTemplate();
				$_TEMPLATE->setPageTitle('Title'); // Set page title
				$_TEMPLATE->setPageDescription('Description'); // Set page description
				$_TEMPLATE->setRobotsBehaviour(SimpleTemplate::ROBOTS_NOINDEX_NOFOLLOW); // Disallow robot indexing
				$_TEMPLATE->setSpecials(array(
					'tracking_event' => 'View page',
					'og_image' => 'img/og.jpg'
				));

			$_TEMPLATE->startBuffer(); // Start buffering HTML

				// Main page content goes here

			$_TEMPLATE->saveBuffer(); // Saves content internally.

			require_once '../template/page/app_t.php'; // Load template file

			// Inside the template you can read values like this

				echo SimpleTemplate::getPageTitle();
				echo SimpleTemplate::getPageDescription();
				echo SimpleTemplate::getPageContent(); // Get buffered HTML
				echo SimpleTemplate::getRobotsBehaviour(); // Returns string like <meta name="robots...>
				echo SimpleTemplate::getSpecial('tracking_event'); // Here would return 'View page' as set before
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

		function __construct($pageTitle = '',
							 $pageDescription = '',
							 $robotsBehaviour = self::ROBOTS_ALLOW_INDEX_AND_FOLLOW) {

			$this->m_pageTitle = $pageTitle;
			$this->m_pageDescription = $pageDescription;
			$this->m_robotsBehaviour = $robotsBehaviour;
			$this->m_pageContent = '';
			$this->m_specials = array();
		}

		/* <GETTERS/SETTERS> */

		public function getPageTitle() {
			return $this->m_pageTitle;
		}

		public function setPageTitle($title) {
			$this->m_pageTitle = $title;
		}

		public function getPageDescription() {
			return $this->m_pageDescription;
		}

		public function setPageDescription($description) {
			$this->m_pageDescription = $description;
		}

		public function getRobotsBehaviour() {

			switch ($this->m_robotsBehaviour) {

				case self::ROBOTS_NOINDEX:			return '<meta name="robots" content="noindex">';			break;
				case self::ROBOTS_NOFOLLOW:			return '<meta name="robots" content="nofollow">';			break;
				case self::ROBOTS_NOINDEX_NOFOLLOW:	return '<meta name="robots" content="noindex,nofollow">';	break;
			}

			return ''; // Default, nothing
		}

		public function setRobotsBehaviour($behaviour) {
			$this->m_robotsBehaviour = $behaviour;
		}

		public function getPageContent() {
			return $this->m_pageContent;
		}

		public function setPageContent($content) {
			$this->m_pageContent = $content;
		}

		// Get specific index from array, ex: $_TEMPLATE->getSpecial('tracking_event');
		public function getSpecial($key) {

			if (isset($this->m_specials[$key]))
				return $this->m_specials[$key];
			else
				return null;
		}

		public function setSpecials($arr) {
			$this->m_specials = $arr;
		}

		public function addSpecial($key, $value) {
			$this->m_specials[$key] = $value;
		}

		/* <TEMPLATE FUNCTIONS> */

		// Start buffering
		public function startBuffer() {
			ob_start();
		}

		// Close buffer and load fragment into variable
		public static function readBuffer() {
			return ob_get_clean();
		}

		// Close buffer and discard content
		public function closeBuffer() {
			ob_end_clean();
		}

		// Close buffer and save content
		public function saveBuffer() {

			// Get content
			$this->m_pageContent = self::readBuffer();

			// Make sure it's valid
			if ($this->m_pageContent === false)
				$this->m_pageContent = '';
		}
	}
