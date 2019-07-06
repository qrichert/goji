<?php

	namespace Goji\Form;

	class InputTextSearch extends InputText {

		/* <ATTRIBUTES> */

		public function __construct() {

			parent::__construct();

			$this->m_scheme = '<input type="search" %{ATTRIBUTES}>';
		}
	}
