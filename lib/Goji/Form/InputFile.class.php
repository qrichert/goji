<?php

	namespace Goji\Form;

	class InputFile extends FormElementAbstract {

		/* <ATTRIBUTES> */

		public function __construct(callable $isValidCallback = null,
		                            bool $forceCallbackOnly = false,
		                            callable $sanitizeCallback = null) {

			parent::__construct($isValidCallback, $forceCallbackOnly, $sanitizeCallback);

			$this->m_scheme = '<input type="file" %{ATTRIBUTES}>';
		}

		private function isUploadOk(): bool {

			$uploadError = $this->m_value['error'] ?? null;

			return $uploadError == UPLOAD_ERR_OK;
		}

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
