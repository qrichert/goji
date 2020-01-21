<?php

namespace Goji\Form;

/**
 * Class InputText
 *
 * @package Goji\Form
 */
class InputText extends FormElementAbstract {

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

		$this->m_openingTag = '<input type="text" %{ATTRIBUTES}>';
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

		$valid = true;

		if (!$this->m_forceCallbackOnly) {

			if ($this->isRequiredButEmpty()) { // required && empty

				$valid = false;

			} else {

				if (!$this->isEmpty()) { // not required / not empty

					$valid = (
						$this->isShorterThanMaxLength()
						&& $this->isLongerThanMinLength()
					);
				}
				// else {} not required / empty
			}
		}

		return $valid && $this->isValidCallback();
	}
}
