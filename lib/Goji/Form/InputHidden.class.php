<?php

	namespace Goji\Form;

	class InputHidden extends FormElementAbstract {

		/* <ATTRIBUTES> */

		public function __construct() {

			parent::__construct();

			$this->m_scheme = '<input type="hidden" %{ATTRIBUTES}>';
		}

		public function isValid(): bool {
			return $this->isValidCallback();
		}
	}
