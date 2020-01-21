<?php

namespace Goji\Form;

/**
 * Class InputButtonSubmit
 *
 * @package Goji\Form
 */
class InputButtonSubmit extends InputButton {

	/**
	 * InputButtonSubmit constructor.
	 */
	public function __construct() {

		parent::__construct();

		$this->m_openingTag = '<input type="submit" %{ATTRIBUTES}>';
	}
}
