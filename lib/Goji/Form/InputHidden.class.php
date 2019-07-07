<?php

	namespace Goji\Form;

	/**
	 * Class InputHidden
	 *
	 * @package Goji\Form
	 */
	class InputHidden extends FormElementAbstract {

		/**
		 * InputHidden constructor.
		 *
		 * @param callable|null $isValidCallback
		 * @param bool $forceCallbackOnly
		 * @param callable|null $sanitizeCallback
		 */
		public function __construct(callable $isValidCallback = null,
		                            bool $forceCallbackOnly = false,
		                            callable $sanitizeCallback = null) {

			parent::__construct($isValidCallback, $forceCallbackOnly, $sanitizeCallback);

			$this->m_openingTag = '<input type="hidden" %{ATTRIBUTES}>';
		}

		/**
		 * @param $value
		 * @param bool $updateValueAttribute
		 * @return \Goji\Form\FormElementAbstract
		 */
		public function setValue($value, $updateValueAttribute = true): FormElementAbstract {
			return parent::setValue($value, $updateValueAttribute);
		}

		/**
		 * @return bool
		 */
		public function isValid(): bool {
			return $this->isValidCallback();
		}
	}
