<?php

	namespace Goji\Form;

	class InputButtonElement extends InputButton {

		/* <ATTRIBUTES> */

		public function __construct(callable $isValidCallback = null,
		                            bool $forceCallbackOnly = false,
		                            callable $sanitizeCallback = null) {

			parent::__construct($isValidCallback, $forceCallbackOnly, $sanitizeCallback);

			$this->m_scheme = '<button %{ATTRIBUTES}>';
		}

		public function render(): void {

			$output = str_replace('%{ATTRIBUTES}', $this->renderAttributes(true), $this->m_scheme);
			$output .= $this->hasAttribute('value') ? htmlspecialchars($this->getAttribute('value')) : '';
			$output .= '</button>';

			echo $output;
		}
	}
