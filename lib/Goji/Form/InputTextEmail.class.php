<?php

	namespace Goji\Form;

	class InputTextEmail extends InputText {

		/* <ATTRIBUTES> */

		public function __construct(callable $isValidCallback = null,
		                            bool $forceCallbackOnly = false,
		                            callable $sanitizeCallback = null) {

			parent::__construct($isValidCallback, $forceCallbackOnly, $sanitizeCallback);

			$this->m_scheme = '<input type="email" %{ATTRIBUTES}>';
		}

		private function isValidEmail(): bool {
			return filter_var($this->m_value, FILTER_VALIDATE_EMAIL) !== false;
		}

		public function isValid(): bool {

			// Must be valid as a regular InputText
			if (!parent::isValid())
				return false;

			// It is not a required, otherwise parent::isValid() would have returned false
			// And since it's empty, we don't mind it
			if ($this->isEmpty())
				return true;

			// Here, we have a valid, non-empty text input
			// On top of that, it must also be a valid email

			return $this->isValidEmail();
		}
	}
