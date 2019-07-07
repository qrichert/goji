<?php

	namespace Goji\Form;

	/**
	 * Class InputCustom
	 *
	 * @package Goji\Form
	 */
	class InputCustom extends FormElementAbstract {

		/**
		 * InputCustom constructor.
		 *
		 * @param string $openingTag
		 * @param string $closingTag
		 * @param callable|null $isValidCallback
		 * @param bool $forceCallbackOnly
		 * @param callable|null $sanitizeCallback
		 */
		public function __construct(string $openingTag,
									string $closingTag = '',
									callable $isValidCallback = null,
									bool $forceCallbackOnly = false,
									callable $sanitizeCallback = null) {

			parent::__construct($isValidCallback, $forceCallbackOnly, $sanitizeCallback);

			$this->m_openingTag = $openingTag;
			$this->m_closingTag = $closingTag;
		}

		/**
		 * @return bool
		 */
		public function isValid(): bool {
			return $this->isValidCallback();
		}
	}
