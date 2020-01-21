<?php

namespace Goji\Form;

/**
 * Class InputButtonReset
 *
 * @package Goji\Form
 */
class InputButtonReset extends InputButton {

	/**
	 * InputButtonReset constructor.
	 */
	public function __construct() {

		parent::__construct();

		$this->m_openingTag = '<input type="reset" %{ATTRIBUTES}>';
	}
}
