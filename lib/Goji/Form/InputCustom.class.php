<?php

	namespace Goji\Form;

	class InputCustom extends FormElementAbstract {

		/* <ATTRIBUTES> */

		public function __construct(string $type,
									callable $isValidCallback = null,
		                            bool $forceCallbackOnly = false,
		                            callable $sanitizeCallback = null) {

			parent::__construct($isValidCallback, $forceCallbackOnly, $sanitizeCallback);

			$this->m_scheme = '<input type="' . $type . '" %{ATTRIBUTES}>';
		}

		public function isValid(): bool {
			return $this->isValidCallback();
		}
	}
