<?php

	namespace Goji\Form;

	class InputButtonReset extends InputButton {

		/* <ATTRIBUTES> */

		public function __construct(callable $isValidCallback = null,
		                            bool $forceCallbackOnly = false,
		                            callable $sanitizeCallback = null) {

			parent::__construct($isValidCallback, $forceCallbackOnly, $sanitizeCallback);

			$this->m_scheme = '<input type="reset" %{ATTRIBUTES}>';
		}
	}
