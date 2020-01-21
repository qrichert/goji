<?php

namespace Goji\Form;

/**
 * Class InputSelectOption
 *
 * @package Goji\Form
 */
class InputSelectOption extends FormElementAbstract {

	/**
	 * InputSelectOption constructor.
	 */
	public function __construct() {

		parent::__construct();

		$this->m_openingTag = '<option %{ATTRIBUTES}>';
		$this->m_closingTag = '</option>';
	}
}
