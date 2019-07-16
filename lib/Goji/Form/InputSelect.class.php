<?php

	namespace Goji\Form;

	/**
	 * Class InputSelect
	 *
	 * @package Goji\Form
	 */
	class InputSelect extends FormElementAbstract {

		/* <ATTRIBUTES> */

		private $m_options;

		/**
		 * InputSelect constructor.
		 *
		 * @param callable|null $isValidCallback
		 * @param bool $forceCallbackOnly
		 * @param callable|null $sanitizeCallback
		 */
		public function __construct(callable $isValidCallback = null,
		                            bool $forceCallbackOnly = false,
		                            callable $sanitizeCallback = null) {

			parent::__construct($isValidCallback, $forceCallbackOnly, $sanitizeCallback);

			$this->m_openingTag = '<div class="select-wrapper"><select %{ATTRIBUTES}>';
			$this->m_closingTag = '</select></div>';

			$this->m_options = [];
		}

		/**
		 * @param $option
		 * @return mixed
		 */
		public function addOption($option) {

			if ($option instanceof InputSelectOption
				|| $option instanceof InputSelectOptionGroup) {

				$this->m_options[] = $option;

				return $option;
			}
		}

		/**
		 * @param $value
		 * @param bool $updateValueAttribute
		 * @return \Goji\Form\FormElementAbstract
		 */
		public function setValue($value, $updateValueAttribute = false): FormElementAbstract {

			foreach ($this->m_options as $option) {

				if ($option instanceof InputSelectOption) {

					if (!empty($value) && $value == $option->getAttribute('value'))
						$option->setAttribute('selected');

				} else if ($option instanceof InputSelectOptionGroup) {

					$optGroupOptions = $option->getOptions();

					foreach ($optGroupOptions as $optGroupOption) {

						if ($optGroupOption instanceof InputSelectOption) {

							if (!empty($value) && $value == $optGroupOption->getAttribute('value'))
								$optGroupOption->setAttribute('selected');
						}
					}
				}
			}

			return parent::setValue($value, $updateValueAttribute);
		}

		/**
		 * @return bool
		 */
		public function isValid(): bool {
			return $this->isValidCallback();
		}

		public function render(): void {

			echo str_replace('%{ATTRIBUTES}', $this->renderAttributes(), $this->m_openingTag), PHP_EOL;

			foreach ($this->m_options as $option) {
				$option->render();
				echo PHP_EOL;
			}

			echo $this->m_closingTag, PHP_EOL;
		}
	}
