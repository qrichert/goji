<?php

	namespace Goji\Form;

	/**
	 * Class FormElementAbstract
	 *
	 * @package Goji\Form
	 */
	abstract class FormElementAbstract extends FormObjectAbstract {

		/* <ATTRIBUTES> */

		protected $m_openingTag;
		protected $m_closingTag;
		protected $m_isValidCallback;
		protected $m_forceCallbackOnly;
		protected $m_sanitizeCallback;
		protected $m_value;

		/**
		 * FormElementAbstract constructor.
		 *
		 * @param callable|null $isValidCallback Callback to custom check validity of the input
		 * @param bool $forceCallbackOnly If true, use only the callback to check validity, not default check (default = false)
		 * @param callable|null $sanitizeCallback
		 */
		public function __construct(callable $isValidCallback = null,
		                            bool $forceCallbackOnly = false,
									callable $sanitizeCallback = null) {

			parent::__construct();

			$this->m_openingTag = '';
			$this->m_closingTag = '';
			$this->m_isValidCallback = $isValidCallback;
			$this->m_forceCallbackOnly = $forceCallbackOnly;
			$this->m_sanitizeCallback = $sanitizeCallback;
			$this->m_value = null;
		}

		/**
		 * @return mixed
		 */
		public function getValue() {
			return $this->m_value;
		}

		/**
		 * @param $value
		 * @param bool $updateValueAttribute
		 * @return \Goji\Form\FormElementAbstract
		 */
		public function setValue($value, $updateValueAttribute = false): FormElementAbstract {

			$sanitizeCallback = $this->m_sanitizeCallback;

			if (is_callable($sanitizeCallback))
				$value = $sanitizeCallback($value);

			if ($updateValueAttribute)
				$this->setAttribute('value', $value);

			$this->m_value = $value;
			return $this;
		}

/* <VALIDITY> */

		/**
		 * @return bool
		 */
		public function isValid(): bool {
			return true;
		}

		/**
		 * @return bool
		 */
		protected function isRequired(): bool {
			return $this->hasAttribute('required');
		}

		/**
		 * @return bool
		 */
		protected function isEmpty(): bool {
			return empty($this->m_value);
		}

		/**
		 * @return bool
		 */
		protected function isRequiredButEmpty(): bool {
			// true = problem
			return $this->isRequired() && $this->isEmpty();
		}

		/**
		 * @return bool
		 */
		protected function isEmptyButNotRequired(): bool {
			return $this->isEmpty() && !$this->isRequired();
		}

		/**
		 * Is the result of the callback function (if any) true.
		 *
		 * @return bool
		 */
		protected function isValidCallback(): bool {

			if ($this->isEmptyButNotRequired())
				return true;

			$callback = $this->m_isValidCallback; // Doesn't work with $this->m_isValidCallback(),
												  // takes it as a method that doesn't exist

			if (is_callable($callback))
				return $callback($this->m_value);
			else
				return true;
		}

		/**
		 * @return bool
		 */
		protected function isNotEmptyIfRequired(): bool {

			if (!$this->isRequired()) // If not required
				return true; // valid

			// Here, if required

			return !$this->isEmpty(); // false if empty, true if not
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

		/**
		 * @return bool
		 */
		protected function isLongerThanMinLength(): bool {

			if (!$this->hasAttribute('minlength'))
				return true;

			$minlength = (int) $this->getAttribute('minlength');

			return is_numeric($this->getValue()) && mb_strlen((string) $this->getValue()) >= $minlength;
		}

/* <RENDERING> */

		/**
		 * Render the element
		 */
		public function render(): void {

			$output = str_replace('%{ATTRIBUTES}', $this->renderAttributes(), $this->m_openingTag);
			$output .= $this->hasAttribute('textContent') ? htmlspecialchars($this->getAttribute('textContent')) : '';
			$output .= $this->m_closingTag;

			echo $output;
		}
	}

