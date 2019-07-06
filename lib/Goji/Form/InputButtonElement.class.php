<?php

	namespace Goji\Form;

	class InputButtonElement extends InputButton {

		/* <ATTRIBUTES> */

		public function __construct() {

			parent::__construct();

			$this->m_scheme = '<button %{ATTRIBUTES}>';
		}

		public function render(): void {

			$output = str_replace('%{ATTRIBUTES}', $this->renderAttributes(true), $this->m_scheme);
			$output .= $this->hasAttribute('value') ? htmlspecialchars($this->getAttribute('value')) : '';
			$output .= '</button>';

			echo $output;
		}
	}
