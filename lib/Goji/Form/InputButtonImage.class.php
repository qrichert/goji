<?php

	namespace Goji\Form;

	class InputButtonImage extends InputButton {

		/* <ATTRIBUTES> */

		public function __construct() {

			parent::__construct();

			$this->m_scheme = '<input type="image" %{ATTRIBUTES}>';
		}
	}
