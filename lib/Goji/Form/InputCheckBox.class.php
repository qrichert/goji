<?php

namespace Goji\Form;

/**
 * Class InputCheckBox
 *
 * @package Goji\Form
 */
class InputCheckBox extends FormElementAbstract {

	/**
	 * InputCheckBox constructor.
	 *
	 * @param callable|null $isValidCallback
	 * @param bool $forceCallbackOnly
	 * @param callable|null $sanitizeCallback
	 */
	public function __construct(callable $isValidCallback = null,
	                            bool $forceCallbackOnly = false,
	                            callable $sanitizeCallback = null) {

		parent::__construct($isValidCallback, $forceCallbackOnly, $sanitizeCallback);

		$this->m_openingTag = '<input type="checkbox" %{ID} %{ATTRIBUTES}><label %{FOR}><span></span>';
		$this->m_closingTag = '</label>';
	}

	/**
	 * Array case must be handled via callback and forceCallbackOnly
	 *
	 * @return bool
	 */
	public function isValid(): bool {

		$valid = true;

		if (!$this->m_forceCallbackOnly) {

			if ($this->isRequired() && $this->m_value !== true) { // required && empty

				$valid = false;
			}
		}

		return $valid && $this->isValidCallback();
	}

	public function render(): void {

		$id = $this->hasAttribute('id') ? $this->getAttribute('id') : '';
		$for = $id;

			if (!empty($id)) {
				$id = 'id="' . $id . '"';
				$for = 'for="' . $for . '"';
			}

		if ($this->m_value === true && !$this->hasAttribute('checked')) {
			$this->setAttribute('checked');
		}

		$openingTag = $this->m_openingTag;
			$openingTag = str_replace('%{ID}', $id, $openingTag);
			$openingTag = str_replace('%{FOR}', $for, $openingTag);
			$openingTag = str_replace('%{ATTRIBUTES}', $this->renderAttributes(true, true, ['id']), $openingTag);

		$output = $openingTag;
		$output .= $this->hasAttribute('textContent') ? $this->getAttribute('textContent') : '';
		$output .= $this->m_closingTag;

		echo $output;
	}
}
