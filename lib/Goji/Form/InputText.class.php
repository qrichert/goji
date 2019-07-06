<?php

	namespace Goji\Form;

	class InputText extends FormElementAbstract {

		/* <ATTRIBUTES> */

		public function __construct() {

			parent::__construct();

			$this->m_scheme = '<input type="text" %{ATTRIBUTES}>';
		}

		public function isValid(): bool {

			$valid = true;

			if (!$this->m_forceCallbackOnly) {

				$valid = (
					$this->isNotEmptyIfRequired()
					&& $this->isShorterThanMaxLength()
				);
			}

			return $valid && $this->isValidCallback();
		}
	}
