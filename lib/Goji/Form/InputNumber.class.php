<?php

namespace Goji\Form;

/**
 * Class InputText
 *
 * @package Goji\Form
 */
class InputNumber extends FormElementAbstract {

	/**
	 * InputText constructor.
	 *
	 * @param callable|null $isValidCallback
	 * @param bool $forceCallbackOnly
	 * @param callable|null $sanitizeCallback
	 */
	public function __construct(callable $isValidCallback = null,
	                            bool $forceCallbackOnly = false,
	                            callable $sanitizeCallback = null) {

		parent::__construct($isValidCallback, $forceCallbackOnly, $sanitizeCallback);

		$this->m_openingTag = '<input type="number" %{ATTRIBUTES}>';
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
	protected function isEmpty(): bool {
		return (
			!is_numeric($this->m_value) // empty('0') and empty(0) both evaluate to true...
			&& empty($this->m_value)
		);
	}

	/**
	 * @return bool
	 */
	public function isValid(): bool {

		$valid = true;

		if (!$this->m_forceCallbackOnly) {

			if ($this->isRequiredButEmpty()) { // required && empty

				$valid = false;

			} else {

				if (!$this->isEmpty()) { // not required / not empty

					$valid = (
						is_numeric($this->getValue())
						&& $this->isLesserThanMax()
						&& $this->isGreaterThanMin()
					);
				}
				// else {} not required / empty
			}
		}

		return $valid && $this->isValidCallback();
	}
}
