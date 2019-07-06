<?php

	namespace Goji\Form;

	class InputTextEmail extends InputText {

		/* <ATTRIBUTES> */

		public function __construct() {

			parent::__construct();

			$this->m_scheme = '<input type="email" %{ATTRIBUTES}>';
		}

		private function isValidEmail(): bool {
			return filter_var($this->getValue(), FILTER_VALIDATE_EMAIL) !== false;
		}

		public function isValid(): bool {

			$valid = true;

			if (!$this->m_forceCallbackOnly) {

				$valid = (
					$this->isNotEmptyIfRequired()
					&& $this->isValidEmail()
				);
			}

			return $valid && $this->isValidCallback();
		}
	}
