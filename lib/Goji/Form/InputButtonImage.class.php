<?php

	namespace Goji\Form;

	/**
	 * Class InputButtonImage
	 *
	 * @package Goji\Form
	 */
	class InputButtonImage extends InputButton {

		/**
		 * InputButtonImage constructor.
		 */
		public function __construct() {

			parent::__construct();

			$this->m_openingTag = '<input type="image" %{ATTRIBUTES}>';
		}
	}
