<?php

	namespace Goji\Form;

	/**
	 * Class InputRadioButton
	 *
	 * @package Goji\Form
	 */
	class InputRadioButton extends InputCheckBox {

		/**
		 * InputRadioButton constructor.
		 *
		 * @param callable|null $isValidCallback
		 * @param bool $forceCallbackOnly
		 * @param callable|null $sanitizeCallback
		 */
		public function __construct(callable $isValidCallback = null,
		                            bool $forceCallbackOnly = false,
		                            callable $sanitizeCallback = null) {

			parent::__construct($isValidCallback, $forceCallbackOnly, $sanitizeCallback);

			$this->m_openingTag = '<input type="radio" %{ID} %{ATTRIBUTES}><label %{FOR}><span></span>';
			$this->m_closingTag = '</label>';
		}
	}
