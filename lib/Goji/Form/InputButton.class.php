<?php

	namespace Goji\Form;

	/**
	 * Class InputButton
	 *
	 * @package Goji\Form
	 */
	class InputButton extends FormElementAbstract {

		/**
		 * InputButton constructor.
		 */
		public function __construct() {

			parent::__construct();

			$this->m_openingTag = '<input type="button" %{ATTRIBUTES}>';
		}
	}
