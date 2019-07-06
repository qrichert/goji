<?php

	namespace Goji\Form;

	class InputButtonReset extends InputButton {

		/* <ATTRIBUTES> */

		public function __construct() {

			parent::__construct();

			$this->m_scheme = '<input type="reset" %{ATTRIBUTES}>';
		}
	}
