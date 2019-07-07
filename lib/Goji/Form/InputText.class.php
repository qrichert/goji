<?php

	namespace Goji\Form;

	class InputText extends FormElementAbstract {

		/* <ATTRIBUTES> */

		public function __construct(callable $isValidCallback = null,
		                            bool $forceCallbackOnly = false,
		                            callable $sanitizeCallback = null) {

			parent::__construct($isValidCallback, $forceCallbackOnly, $sanitizeCallback);

			$this->m_scheme = '<input type="text" %{ATTRIBUTES}>';
		}

		public function setValue($value, $updateValueAttribute = true): FormElementAbstract {
			return parent::setValue($value, $updateValueAttribute);
		}

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
