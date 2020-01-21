<?php

namespace Goji\Form;

/**
 * Class InputButtonElement
 *
 * @package Goji\Form
 */
class InputButtonElement extends InputButton {

	/**
	 * InputButtonElement constructor.
	 */
	public function __construct() {

		parent::__construct();

		$this->m_openingTag = '<button %{ATTRIBUTES}>';
		$this->m_closingTag = '</button>';
	}
}
