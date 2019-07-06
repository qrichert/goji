<?php

	namespace Goji\Form;

	class InputTextArea extends InputText {

		/* <ATTRIBUTES> */

		public function __construct() {

			parent::__construct();

			$this->m_scheme = '<textarea type="text" %{ATTRIBUTES}>';
		}

		public function isValid(): bool {

			$valid = true;

			if (!$this->m_forceCallbackOnly) {

				$valid = (
					$this->isNotEmptyIfRequired()
					&& $this->isShorterThanMaxLength()
				);
			}

			return $valid && $this->isValidCallback();
		}

		public function render(): void {

			$output = str_replace('%{ATTRIBUTES}', $this->renderAttributes(true), $this->m_scheme);
			$output .= $this->hasAttribute('value') ? htmlspecialchars($this->getAttribute('value')) : '';
			$output .= '</textarea>';

			echo $output;
		}
	}
