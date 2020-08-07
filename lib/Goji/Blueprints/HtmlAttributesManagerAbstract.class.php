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
	 * @return \Goji\Blueprints\HtmlAttributesManagerAbstract
	 */
	public function addClasses($classes): HtmlAttributesManagerAbstract {

		// If empty, no need to process further
		if (empty($classes))
			return $this;

		// Make sure it's an array
		if (!is_array($classes))
			$classes = explode(' ', (string) $classes);

		foreach ($classes as $class) {

			// Make sure it's a string (could be an array of int initially)
			$class = (string) $class;

			if (!$this->hasClass($class))
				$this->m_classes[] = $class;
		}

		return $this;
	}

	/**
	 * Alias for addClasses()
	 *
	 * @param array $args
	 * @return \Goji\Blueprints\HtmlAttributesManagerAbstract
	 */
	public function addClass(...$args): HtmlAttributesManagerAbstract {
		return $this->addClasses(...$args);
	}

	/**
	 * Overwrite current classes and replaces them by $classes
	 *
	 * If string, explode on spaces
	 *
	 * 'hello world foo bar' -> ['hello', 'world', 'foo', 'bar']
	 *
	 * @param string|array $classes
	 * @return \Goji\Blueprints\HtmlAttributesManagerAbstract
	 */
	public function setClasses($classes): HtmlAttributesManagerAbstract {

		// Empty existing classes
		$this->clearClasses();

		if (empty($classes))
			return $this;

		$this->addClasses($classes);

		return $this;
	}

	/**
	 * Remove multiple classes
	 *
	 * If string, explode on spaces
	 *
	 * 'hello world foo bar' -> ['hello', 'world', 'foo', 'bar']
	 *
	 * @param $classes
	 * @return \Goji\Blueprints\HtmlAttributesManagerAbstract
	 */
	public function removeClasses($classes): HtmlAttributesManagerAbstract {

		// If empty, no need to process further
		if (empty($classes))
			return $this;

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

		return $this;
	}

	/**
	 * Alias for removeClasses()
	 *
	 * @param array $args
	 * @return \Goji\Blueprints\HtmlAttributesManagerAbstract
	 */
	public function removeClass(...$args): HtmlAttributesManagerAbstract {
		return $this->removeClasses(...$args);
	}

	/**
	 * Remove all classes
	 * @return \Goji\Blueprints\HtmlAttributesManagerAbstract
	 */
	public function clearClasses(): HtmlAttributesManagerAbstract {
		$this->m_classes = [];
		return $this;
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

	/**
	 * @return bool
	 */
	public function hasId(): bool {
		return $this->hasAttribute('id');
	}

	/**
	 * @return string
	 */
	public function getId(): string {
		return $this->getAttribute('id');
	}

	/**
	 * @param string $id
	 * @return \Goji\Blueprints\HtmlAttributesManagerAbstract
	 */
	public function setId(string $id): HtmlAttributesManagerAbstract {
		return $this->setAttribute('id', $id);
	}

	/**
	 * @return \Goji\Blueprints\HtmlAttributesManagerAbstract
	 */
	public function removeId(): HtmlAttributesManagerAbstract {
		return $this->removeAttribute('id');
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
	 * @return \Goji\Blueprints\HtmlAttributesManagerAbstract
	 */
	public function setAttributes(array $attributes): HtmlAttributesManagerAbstract {

		foreach ($attributes as $key => $value)
			$this->setAttribute($key, $value);

		return $this;
	}

	/**
	 * Remove a single attribute by key.
	 *
	 * @param string $key
	 * @return \Goji\Blueprints\HtmlAttributesManagerAbstract
	 */
	public function removeAttribute(string $key): HtmlAttributesManagerAbstract {

		if (isset($this->m_attributes[$key]))
			unset($this->m_attributes[$key]);

		return $this;
	}

	/**
	 * Remove multiple attributes by key at once.
	 *
	 * @param array $keys
	 * @return \Goji\Blueprints\HtmlAttributesManagerAbstract
	 */
	public function removeAttributes(array $keys): HtmlAttributesManagerAbstract {

		if (empty($keys))
			return $this;

		foreach ($keys as $key) {
			$this->removeAttribute($key);
		}

		return $this;
	}

/* <RENDERING> */

	/**
	 * Renders attribute as HTML
	 *
	 * action="#" method="post" etc.
	 *
	 * @param bool $skipValueAttribute
	 * @param bool $replaceQuotsWithHtmlEntity
	 * @param string|array $dontRender
	 * @return string
	 */
	public function renderAttributes(bool $skipValueAttribute = false, bool $replaceQuotsWithHtmlEntity = true, $dontRender = []): string {

		$dontRender = (array) $dontRender;

		$attr = '';

		// Render classes
		if (!empty($this->getClasses()))
			$attr .= 'class="' . $this->renderClassList() . '"';

		// Render other attributes
		foreach ($this->m_attributes as $key => $value) {

			if ($skipValueAttribute && $key == 'value')
				continue;

			if (in_array($key, $dontRender))
				continue;

			// Never render textContent attribute
			if ($key == 'textContent')
				continue;

			$empty = (
				!is_numeric($value) // empty('0') and empty(0) both evaluate to true...
				&& empty($value)
			);

			if (!$empty && $replaceQuotsWithHtmlEntity)
				$attr .= ' ' . $key . '="' . str_replace('"', '&quot;', $value) . '"';
			else if (!$empty)
				$attr .= ' ' . $key . '="' . $value . '"';
			else
				$attr .= ' ' . $key;
		}

		return trim($attr);
	}
}
