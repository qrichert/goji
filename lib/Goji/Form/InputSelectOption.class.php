<?php

	namespace Goji\Form;

	/**
	 * Class InputSelectOption
	 *
	 * @package Goji\Form
	 */
	class InputSelectOption extends FormElementAbstract {

		/**
		 * InputSelectOption constructor.
		 */
		public function __construct() {

			parent::__construct();

			$this->m_openingTag = '<option %{ATTRIBUTES}>';
			$this->m_closingTag = '</option>';
		}

		/**
		 * Renders attribute as HTML
		 *
		 * action="#" method="post" etc.
		 *
		 * @param bool $skipValueAttribute
		 * @return string
		 */
		public function renderAttributes($skipValueAttribute = false): string {

			$attr = '';

			foreach ($this->m_attributes as $key => $value) {

				if ($key == 'textContent')
					continue;

				if (!empty($value))
					$attr .= ' ' . $key . '="' . addslashes($value) . '"';
				else
					$attr .= ' ' . $key;
			}

			return trim($attr);
		}
	}
