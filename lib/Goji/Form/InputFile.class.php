<?php

namespace Goji\Form;

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
