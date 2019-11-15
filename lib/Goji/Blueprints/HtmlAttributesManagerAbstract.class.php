<?php

	namespace Goji\Blueprints;

	/**
	 * Class HtmlAttributesManagerAbstract
	 *
	 * @package Goji\Blueprints
	 */
	abstract class HtmlAttributesManagerAbstract {

		/* <ATTRIBUTES> */

		protected $m_classes;
		protected $m_attributes;

		/**
		 * HtmlAttributesManager constructor.
		 */
		public function __construct() {
			$this->m_classes = [];
			$this->m_attributes = [];
		}

/* <CLASSES> */

		/**
		 * Whether the element possesses a specific class
		 *
		 * @param string $class
		 * @return bool
		 */
		public function hasClass(string $class): bool {
			return in_array($class, $this->m_classes);
		}

		/**
		 * Get the list of classes
		 *
		 * @return array
		 */
		public function getClasses(): array {
			return $this->m_classes;
		}

		/**
		 * Add multiple classes
		 *
		 * If string, explode on spaces
		 *
		 * 'hello world foo bar' -> ['hello', 'world', 'foo', 'bar']
		 *
		 * @param string|array $classes
		 */
		public function addClasses($classes): void {

			// If empty, no need to process further
			if (empty($classes))
				return;

			// Make sure it's an array
			if (!is_array($classes))
				$classes = explode(' ', (string) $classes);

			foreach ($classes as $class) {

				// Make sure it's a string (could be an array of int initially)
				$class = (string) $class;

				if (!$this->hasClass($class))
					$this->m_classes[] = $class;
			}
		}

		/**
		 * Alias for addClasses()
		 *
		 * @param array $args
		 */
		public function addClass(...$args): void {
			$this->addClasses(...$args);
		}

		/**
		 * Overwrite current classes and replaces them by $classes
		 *
		 * If string, explode on spaces
		 *
		 * 'hello world foo bar' -> ['hello', 'world', 'foo', 'bar']
		 *
		 * @param string|array $classes
		 */
		public function setClasses($classes): void {

			// Empty existing classes
			$this->clearClasses();

			if (empty($classes))
				return;

			$this->addClasses($classes);
		}

		/**
		 * Remove multiple classes
		 *
		 * If string, explode on spaces
		 *
		 * 'hello world foo bar' -> ['hello', 'world', 'foo', 'bar']
		 *
		 * @param $classes
		 */
		public function removeClasses($classes): void {

			// If empty, no need to process further
			if (empty($classes))
				return;

			// Make sure it's an array
			if (!is_array($classes))
				$classes = explode(' ', (string) $classes);

			foreach ($classes as $class) {

				// Make sure it's a string (could be an array of int initially)
				$class = (string) $class;

				$index = array_search($class, $this->m_classes);

				while ($index !== false) {
					array_splice($this->m_classes, $index, 1);
					$index = array_search($class, $this->m_classes);
				}
			}
		}

		/**
		 * Alias for removeClasses()
		 *
		 * @param array $args
		 */
		public function removeClass(...$args): void {
			$this->removeClasses(...$args);
		}

		/**
		 * Remove all classes
		 */
		public function clearClasses(): void {
			$this->m_classes = [];
		}

		/**
		 * Returns classes as a single space-separated strings
		 *
		 * ['hello', 'world', 'foo', 'bar'] -> 'hello world foo bar'
		 *
		 * @return string
		 */
		public function renderClassList(): string {
			return implode(' ', $this->m_classes);
		}

/* <ID> */

		public function hasId(): bool {
			return $this->hasAttribute('id');
		}

		public function getId(): string {
			return $this->getAttribute('id');
		}

		public function setId(string $id): HtmlAttributesManagerAbstract {
			return $this->setAttribute('id', $id);
		}

/* <ATTRIBUTES> */

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
		 * @param string $key
		 * @return string
		 */
		public function getAttribute(string $key): string {
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
		 * Add a single attribute.
		 *
		 * Returns a pointer/reference to the object receiving the attribute, so that
		 * you can do:
		 *
		 * $obj->setAttribute('foo', 'bar')
		 *     ->setAttribute('bar', 'foo');
		 *
		 * @param $key
		 * @param $value (optional)
		 * @return \Goji\Blueprints\HtmlAttributesManagerAbstract
		 */
		public function setAttribute(string $key, $value = null): HtmlAttributesManagerAbstract {

			if ($key == 'class') {
				$this->addClass($value);
				return $this;
			}

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
		 * @param bool $addSlashes
		 * @return string
		 */
		public function renderAttributes(bool $skipValueAttribute = false, bool $addSlashes = true): string {

			$attr = '';

			// Render classes
			$attr .= 'class="' . $this->renderClassList() . '"';

			// Render other attributes
			foreach ($this->m_attributes as $key => $value) {

				if ($skipValueAttribute && $key == 'value')
					continue;

				// Never render textContent attribute
				if ($key == 'textContent')
					continue;

				if (!empty($value) && $addSlashes)
					$attr .= ' ' . $key . '="' . addcslashes($value, '"') . '"';
				else if (!empty($value))
					$attr .= ' ' . $key . '="' . $value . '"';
				else
					$attr .= ' ' . $key;
			}

			return trim($attr);
		}
	}
