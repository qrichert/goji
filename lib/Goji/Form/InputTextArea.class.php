<?php

	namespace Goji\Form;

	/**
	 * Class InputTextArea
	 *
	 * @package Goji\Form
	 */
	class InputTextArea extends InputText {

		/**
		 * InputTextArea constructor.
		 *
		 * @param callable|null $isValidCallback
		 * @param bool $forceCallbackOnly
		 * @param callable|null $sanitizeCallback
		 */
		public function __construct(callable $isValidCallback = null,
		                            bool $forceCallbackOnly = false,
		                            callable $sanitizeCallback = null) {

			parent::__construct($isValidCallback, $forceCallbackOnly, $sanitizeCallback);

			$this->m_openingTag = '<textarea %{ATTRIBUTES}>';
			$this->m_closingTag = '</textarea>';
		}

		public function render(): void {

			$output = str_replace('%{ATTRIBUTES}', $this->renderAttributes(true, false), $this->m_openingTag);
			$output .= $this->hasAttribute('value') ? htmlspecialchars($this->getAttribute('value')) : '';
			$output .= $this->m_closingTag;

			echo $output;
		}
	}
