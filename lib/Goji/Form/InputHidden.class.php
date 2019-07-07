<?php

	namespace Goji\Form;

	class InputHidden extends FormElementAbstract {

		/* <ATTRIBUTES> */

		public function __construct(callable $isValidCallback = null,
		                            bool $forceCallbackOnly = false,
		                            callable $sanitizeCallback = null) {

			parent::__construct($isValidCallback, $forceCallbackOnly, $sanitizeCallback);

			$this->m_scheme = '<input type="hidden" %{ATTRIBUTES}>';
		}

		public function setValue($value, $updateValueAttribute = true): FormElementAbstract {
			return parent::setValue($value, $updateValueAttribute);
		}

		public function isValid(): bool {
			return $this->isValidCallback();
		}
	}
