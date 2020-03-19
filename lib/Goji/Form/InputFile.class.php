<?php

namespace Goji\Form;

use Goji\Toolkit\SwissKnife;

/**
 * Class InputFile
 *
 * @package Goji\Form
 */
class InputFile extends FormElementAbstract {

	protected $m_maxFileSize;
	protected $m_allowedFileTypes;

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

		$this->m_maxFileSize = -1; // Infinite
		$this->m_allowedFileTypes = []; // All
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
	 * Set max file size in octets
	 *
	 * @param int $maxFileSize
	 * @return \Goji\Form\FormElementAbstract
	 */
	public function setFileMaxSize(int $maxFileSize): FormElementAbstract {
		$this->m_maxFileSize = $maxFileSize;
		$this->setAttribute('data-max-file-size', $this->m_maxFileSize);

		return $this;
	}

	public function setAllowedFileTypes(array $allowedFileTypes): FormElementAbstract {

		$this->m_allowedFileTypes = $allowedFileTypes;

		// Formatting file types for the accept="" attribute

		if (in_array('jpg', $allowedFileTypes) && !in_array('jpeg', $allowedFileTypes))
			$allowedFileTypes[] = 'jpeg';
		else if (in_array('jpeg', $allowedFileTypes) && !in_array('jpg', $allowedFileTypes))
			$allowedFileTypes[] = 'jpg';

		foreach ($allowedFileTypes as &$type) {
			$type = '.' . $type;
		}
		unset($type);

		$allowedFileTypes = implode(',', $allowedFileTypes);

		$this->setAttribute('accept', $allowedFileTypes);

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
		return ($this->m_value['error'] ?? null) == UPLOAD_ERR_OK;
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

					// Could use a series of 'if's here, but when one is false, but
					// 'else if's spare some checking since if one is false it's considered
					// invalid anyway.

					$fileExtension = pathinfo($this->m_value['name'], PATHINFO_EXTENSION);
						$fileExtension = mb_strtolower($fileExtension);

					if (!$this->isUploadOk())
						$valid = false;

					else if ($this->m_maxFileSize > -1 && (int) $this->m_value['size'] > $this->m_maxFileSize)
						$valid = false;

					else if (!in_array($fileExtension, $this->m_allowedFileTypes))
						$valid = false;
				}
			}
		}

		return $valid && $this->isValidCallback();
	}
}
