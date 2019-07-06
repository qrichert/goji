<?php

	namespace Goji\Form;

	class InputButtonSubmit extends InputButton {

		/* <ATTRIBUTES> */

		public function __construct() {

			parent::__construct();

			$this->m_scheme = '<input type="submit" %{ATTRIBUTES}>';
		}
	}
