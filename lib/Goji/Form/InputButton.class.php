<?php

	namespace Goji\Form;

	class InputButton extends FormElementAbstract {

		/* <ATTRIBUTES> */

		public function __construct() {

			parent::__construct();

			$this->m_scheme = '<input type="button" %{ATTRIBUTES}>';
		}

		public function isValid(): bool {
			return true;
		}
	}
