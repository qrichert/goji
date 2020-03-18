<?php

namespace Goji\Form;

use Goji\Toolkit\SwissKnife;

/**
 * Class InputFile
 *
 * @package Goji\Form
 */
class InputFile extends FormElementAbstract {

	/**
	 * InputFile constructor.
	 *
	 * @param callable|null $isValidCallback
	 * @param bool $forceCallbackOnly
	 * @param callable|null $sanitizeCallback
	 */
	public function __construct(callable $isValidCallback = null,
	                            bool $forceCallbackOnly = false,
	                            callable $sanitizeCallback = null) {

		parent::__construct($isValidCallback, $forceCallbackOnly, $sanitizeCallback);

		$this->m_openingTag = '<input type="file" %{ATTRIBUTES}>';
	}

	/**
	 * If the name of the file is something like upload[file], then the result
	 * $_FILES[] will contain [name][file] => value, instead of just [name] => value.
	 *
	 * So what we do is we detect if the name contains brackets, and if so, we loop
	 * though the values to unarrayify them, So that [name][file] => value, becomes
	 * [name] => value again, an so it's standardized for other checkings.
	 *
	 * @param $value
	 * @param bool $updateValueAttribute
	 * @return \Goji\Form\FormElementAbstract
	 */
	public function setValue($value, $updateValueAttribute = false): FormElementAbstract {

		parent::setValue($value, $updateValueAttribute);

		// If no name, or no opening bracket '[' found in the name
		if (!is_array($this->m_value) || !$this->hasName() || strpos($this->getName(), '[') === false)
			return $this;

		foreach ($this->m_value as $key => &$value) {
			$value = SwissKnife::extractFirstNonArrayValueFromRecursiveArray($value);
		}
		unset($value);

		return $this;
	}

	/**
	 * @return bool
	 */
	protected function isEmpty(): bool {

		if (empty($this->m_value))
			return true;

		$uploadError = $this->m_value['error'] ?? null;

		return $uploadError == UPLOAD_ERR_NO_FILE;
	}

	/**
	 * @return bool
	 */
	private function isUploadOk(): bool {

		$uploadError = $this->m_value['error'] ?? null;

		return $uploadError == UPLOAD_ERR_OK;
	}

	/**
	 * @return bool
	 */
	public function isValid(): bool {

		$valid = true;

		if (!$this->m_forceCallbackOnly) {

			if ($this->isRequiredButEmpty()) {

				$valid = false;

			} else { // not required, but may be empty

				if (!$this->isEmpty()) {

					$valid = $this->isUploadOk();
				}
			}
		}

		return $valid && $this->isValidCallback();
	}
}
