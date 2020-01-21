<?php

namespace Goji\Form;

/**
 * Class InputTextPassword
 *
 * @package Goji\Form
 */
class InputTextPassword extends InputText {

	/**
	 * InputTextPassword constructor.
	 *
	 * @param callable|null $isValidCallback
	 * @param bool $forceCallbackOnly
	 * @param callable|null $sanitizeCallback
	 */
	public function __construct(callable $isValidCallback = null,
	                            bool $forceCallbackOnly = false,
	                            callable $sanitizeCallback = null) {

		parent::__construct($isValidCallback, $forceCallbackOnly, $sanitizeCallback);

		$this->m_openingTag = '<input type="password" %{ATTRIBUTES}>';
	}
}
