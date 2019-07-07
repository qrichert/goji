<?php

	namespace Goji\Form;

	class InputButton extends FormElementAbstract {

		/* <ATTRIBUTES> */

		public function __construct(callable $isValidCallback = null,
		                            bool $forceCallbackOnly = false,
		                            callable $sanitizeCallback = null) {

			parent::__construct($isValidCallback, $forceCallbackOnly, $sanitizeCallback);

			$this->m_scheme = '<input type="button" %{ATTRIBUTES}>';
		}

		public function isValid(): bool {
			return true;
		}
	}
