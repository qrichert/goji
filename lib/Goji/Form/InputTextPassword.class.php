<?php

	namespace Goji\Form;

	class InputTextPassword extends InputText {

		/* <ATTRIBUTES> */

		public function __construct() {

			parent::__construct();

			$this->m_scheme = '<input type="password" %{ATTRIBUTES}>';
		}

		public function isValid(): bool {

			$valid = true;

			if (!$this->m_forceCallbackOnly) {

				$valid = (
					$this->isNotEmptyIfRequired()
				);
			}

			return $valid && $this->isValidCallback();
		}
	}
