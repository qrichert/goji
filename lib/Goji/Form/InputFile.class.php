<?php

	namespace Goji\Form;

	class InputFile extends FormElementAbstract {

		/* <ATTRIBUTES> */

		public function __construct() {

			parent::__construct();

			$this->m_scheme = '<input type="file" %{ATTRIBUTES}>';
		}

		public function isValid(): bool {

			$valid = true;

			if (!$this->m_forceCallbackOnly) {

				$valid = (
					$this->isNotEmptyIfRequired()
				);
			}

			return $this->isValidCallback();
		}
	}
