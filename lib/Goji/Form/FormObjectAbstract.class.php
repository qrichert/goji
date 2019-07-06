<?php

	namespace Goji\Form;

	/**
	 * Class FormElementAbstract
	 *
	 * @package Goji\Form
	 */
	abstract class FormObjectAbstract {

		private $m_attributes;

		/**
		 * FormElementAbstract constructor
		 */
		public function __construct() {
			$this->m_attributes = array();
		}

		/**
		 * Whether the element possesses a specific key
		 *
		 * @param string $key
		 * @return bool
		 */
		public function hasAttribute(string $key): bool {
			return array_key_exists($key, $this->m_attributes);
		}

		/**
		 * Get the value of a specific attribute
		 *
		 * @param $key
		 * @return string
		 */
		public function getAttribute($key): string {
			return $this->m_attributes[$key] ?? '';
		}

		/**
		 * Returns all attributes.
		 *
		 * @return array
		 */
		public function getAttributes(): array {
			return $this->m_attributes;
		}

		/**
		 * Add a single attribute to the form.
		 *
		 * Returns a pointer/reference to the object receiving the attribute, so that
		 * you can do:
		 *
		 * $obj->setAttribute('foo', 'bar')
		 *     ->setAttribute('bar', 'foo');
		 *
		 * @param $key
		 * @param $value (optional)
		 * @return \Goji\Form\FormObjectAbstract
		 */
		public function setAttribute($key, $value = null): FormObjectAbstract {

			if ($value === null)
				$value = '';

			if (is_bool($value) && $value)
				$value = 'true';
			else if (is_bool($value))
				$value = 'false';

			$this->m_attributes[$key] = (string) $value;

			return $this;
		}

		/**
		 * Add multiple attributes in one go.
		 *
		 * @param array $attributes
		 */
		public function setAttributes(array $attributes): void {
			foreach ($attributes as $key => $value)
				$this->setAttribute($key, $value);
		}

/* <RENDERING> */

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

				if ($skipValueAttribute && $key == 'value')
					continue;

				if (!empty($value))
					$attr .= ' ' . $key . '="' . addslashes($value) . '"';
				else
					$attr .= ' ' . $key;
			}

			return trim($attr);
		}

		abstract public function render(): void;
	}
