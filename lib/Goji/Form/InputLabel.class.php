<?php

	namespace Goji\Form;

	class InputLabel extends FormElementAbstract {

		/* <ATTRIBUTES> */

		public function __construct() {

			parent::__construct();

			$this->m_scheme = '<label %{ATTRIBUTES}>';
		}

		public function isValid(): bool {
			return true;
		}

		public function render(): void {

			$output = str_replace('%{ATTRIBUTES}', $this->renderAttributes(true), $this->m_scheme);
			$output .= $this->hasAttribute('value') ? htmlspecialchars($this->getAttribute('value')) : '';
			$output .= '</label>';

			echo $output;
		}
	}
