<?php

	namespace Goji\Form;

	/**
	 * Class InputTextUrl
	 *
	 * @package Goji\Form
	 */
	class InputTextUrl extends InputText {

		/**
		 * InputTextUrl constructor.
		 *
		 * @param callable|null $isValidCallback
		 * @param bool $forceCallbackOnly
		 * @param callable|null $sanitizeCallback
		 */
		public function __construct(callable $isValidCallback = null,
		                            bool $forceCallbackOnly = false,
		                            callable $sanitizeCallback = null) {

			parent::__construct($isValidCallback, $forceCallbackOnly, $sanitizeCallback);

			$this->m_openingTag = '<input type="url" %{ATTRIBUTES}>';
		}
	}
