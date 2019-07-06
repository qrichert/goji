<?php

	namespace Goji\Form;

	abstract class FormElementAbstract extends FormObjectAbstract {

		/* <ATTRIBUTES> */

		protected $m_scheme;
		protected $m_isValidCallback;
		protected $m_forceCallbackOnly;
		protected $m_value;

		/**
		 * FormElementAbstract constructor.
		 *
		 * @param callable|null $isValidCallback Callback to custom check validity of the input
		 * @param bool $forceCallbackOnly If true, use only the callback to check validity, not default check (default = false)
		 */
		public function __construct(callable $isValidCallback = null, bool $forceCallbackOnly = false) {

			parent::__construct();

			$this->m_scheme = '';
			$this->m_isValidCallback = $isValidCallback;
			$this->m_forceCallbackOnly = $forceCallbackOnly;
			$this->m_value = null;
		}

		/**
		 * @return |null
		 */
		public function getValue() {
			return $this->m_value;
		}

		/**
		 * @param $value
		 */
		public function setValue($value): void {
			$this->m_value = $value;
		}

/* <VALIDITY> */

		/**
		 * @return bool
		 */
		abstract public function isValid(): bool;

		/**
		 * Is the result of the callback function (if any) true.
		 *
		 * @return bool
		 */
		protected function isValidCallback(): bool {

			if ($this->m_isValidCallback !== null)
				return $this->m_isValidCallback();
			else
				return true;
		}

		/**
		 * @return bool
		 */
		protected function isNotEmptyIfRequired(): bool {

			if (!$this->hasAttribute('required'))
				return true;

			// Here, is required

			return !empty($this->getValue());
		}

		/**
		 * @return bool
		 */
		protected function isShorterThanMaxLength(): bool {

			if (!$this->hasAttribute('maxlength'))
				return true;

			$maxlength = (int) $this->getAttribute('maxlength');

			return is_numeric($this->getValue()) && mb_strlen((string) $this->getValue()) <= $maxlength;
		}

/* <RENDERING> */

		/**
		 * Render the element
		 */
		public function render(): void {

			$output = str_replace('%{ATTRIBUTES}', $this->renderAttributes(), $this->m_scheme);

			echo $output;
		}
	}

