<?php

/*
 * TODO:
 * <input type="color">
 * <input type="date">
 * <input type="datetime">
 * <input type="datetime-local">
 * <input type="month">
 * <input type="range">
 * <input type="time">
 * <input type="week">
 */

namespace Goji\Form;

use Goji\Parsing\RegexPatterns;

/**
 * Class Form
 *
 * @package Goji\Form
 */
class Form extends FormObjectAbstract {

	/* <ATTRIBUTES> */

	private $m_inputs;

	/* <CONSTANTS> */

	const METHOD_POST = 'post';
	const METHOD_GET = 'get';

	const ENCTYPE_URLENCODED = 'application/x-www-form-urlencoded';
	const ENCTYPE_MULTIPART = 'multipart/form-data';

	/**
	 * Form constructor.
	 *
	 * @param string|null $action
	 * @param string $method
	 * @param string $enctype
	 */
	public function __construct(string $action = null,
	                            string $method = self::METHOD_POST,
	                            string $enctype = self::ENCTYPE_URLENCODED) {

		parent::__construct();

		$this->m_inputs = [];

		$this->setAction($action);
		$this->setMethod($method);
		$this->setEnctype($enctype);
	}

	/**
	 * Get <form> action attribute
	 *
	 * @return string
	 */
	public function getAction(): string {
		return $this->getAttribute('action') ?? '#';
	}

	/**
	 * Set <form> action attribute
	 *
	 * @param string|null $action
	 */
	public function setAction(?string $action): void {

		if (empty($action))
			$action = '#';

		$this->setAttribute('action', $action);
	}

	/**
	 * Get <form> method attribute
	 *
	 * @return string
	 */
	public function getMethod(): string {
		return $this->getAttribute('method') ?? self::METHOD_POST;
	}

	/**
	 * Set <form> method attribute
	 *
	 * @param string $method
	 */
	public function setMethod(string $method): void {

		if ($method === self::METHOD_GET)
			$this->setAttribute('method', self::METHOD_GET);
		else
			$this->setAttribute('method', self::METHOD_POST); // Default
	}

	/**
	 * Get <form> enctype attribute
	 *
	 * @return string
	 */
	public function getEnctype(): string {
		return $this->getAttribute('enctype') ?? self::ENCTYPE_URLENCODED;
	}

	/**
	 * Set <form> enctype attribute
	 *
	 * @param string $enctype
	 */
	public function setEnctype(string $enctype): void {

		if ($enctype === self::ENCTYPE_MULTIPART)
			$this->setAttribute('enctype', self::ENCTYPE_MULTIPART);
		else
			$this->setAttribute('enctype', self::ENCTYPE_URLENCODED); // Default
	}

	/**
	 * Will recover a value in a multi-level array, following given keys
	 *
	 * keys = 1stLevel, 2ndLevel, 3rdLevel
	 *
	 * subject = [
	 *     '1stLevel' => [
	 *         '2ndLevel' => [
	 *             '3rdLevel' => VALUE
	 *         ]
	 *     ]
	 * ]
	 *
	 * => VALUE
	 *
	 * @param array $keys
	 * @param array $subject
	 * @return mixed|null
	 */
	private function getValueFromArrayKeys(array $keys, array &$subject) {

		$key = array_shift($keys); // Get the first key and remove it
		$value = &$subject[$key] ?? null;

		if ($value === null)
			return null;

		if (empty($keys)) { // It was the last key, se we good

			return $value;

		} else {

			return $this->getValueFromArrayKeys($keys, $value);
		}
	}

	/**
	 * Get checkbox value
	 *
	 * If it exists (typically has an "on" value, or value of the value="" attribute if set),
	 * returns true. If it doesn't exist, returns false. If it is an array (because au checkbox[])
	 * then returns the array and will be to the user to validate with a callback.
	 *
	 * @param array $keys
	 * @param array $subject
	 * @return bool|mixed|null
	 */
	private function getCheckBoxValue(array $keys, array &$subject) {

		$value = $this->getValueFromArrayKeys($keys, $subject);

		if (empty($value))
			return false;

		if (is_array($value))
			return $value;

		else
			return true;
	}

	private function getRadioButtonValue(InputRadioButton $input, array $keys, array &$subject): bool {

		$value = $this->getValueFromArrayKeys($keys, $subject);

		if ($value == $input->getAttribute('value'))
			return true;
		else
			return false;
	}

	/**
	 * Fill-in all POSTed values
	 */
	public function hydrate(): void {

		foreach ($this->m_inputs as $input) {

			$inputName = $input->getAttribute('name'); // foo[bar][baz][]

			if (empty($inputName))
				continue;

			preg_match_all(RegexPatterns::htmlInputNameArrayKeys(), $inputName, $matches, PREG_PATTERN_ORDER);
			$matches = $matches[1]; // first capturing group (contains index name without the brackets [])

			$matches = (array) $matches;

			if ($input instanceof InputFile)
				$inputValue = $_FILES[$matches[0]] ?? null;
			else if ($input instanceof InputRadioButton) // Before InputCheckBox because of inheritance
				$inputValue = $this->getRadioButtonValue($input, $matches, $_POST);
			else if ($input instanceof InputCheckBox)
				$inputValue = $this->getCheckBoxValue($matches, $_POST);
			else
				$inputValue = $this->getValueFromArrayKeys($matches, $_POST);

			$input->setValue($inputValue);
		}
	}

	/**
	 * Append an input to the form
	 *
	 * If input is of type InputFile, enctype will automatically be set to multipart/form-data.
	 *
	 * @param \Goji\Form\FormElementAbstract $input
	 * @return \Goji\Form\FormElementAbstract
	 */
	public function addInput(FormElementAbstract $input): FormElementAbstract {

		$this->m_inputs[] = $input;

		if ($input instanceof InputFile)
			$this->setAttribute('enctype', self::ENCTYPE_MULTIPART);

		return $input;
	}

/* <VALIDITY> */

	/**
	 * Check form validity.
	 *
	 * If you pass an array (by reference) to the method, it will contain
	 * a list of all invalid element names.
	 *
	 * Names are name="" attributes. So if you didn't set them, the array will
	 * only contain empty strings.
	 *
	 * @param array $detail
	 * @return bool
	 */
	public function isValid(&$detail = null): bool {

		$detail = [];
		$valid = true;

		foreach ($this->m_inputs as $input) {

			if (!$input->isValid()) {

				if ($detail !== null)
					$detail[] = $input->getAttribute('name');

				$valid = false;
			}
		}

		return $valid;
	}

/* <RENDERING> */

	/**
	 * Render the raw form (<form> + inputs)
	 *
	 * You can render the inputs separately by selecting them (see getInputBy(ø|Name|ID)())
	 * and calling their render() method directly.
	 */
	public function render(): void {

		echo '<form ', $this->renderAttributes(), '>', PHP_EOL;

			foreach ($this->m_inputs as $input) {
				$input->render();
				echo PHP_EOL;
			}

		echo '</form>', PHP_EOL;
	}

	/**
	 * Select an input by looking at the value of a specific attribute
	 *
	 * $this->m_form->getInputBy('class', 'password')->render();
	 *
	 * @param string $attribute
	 * @param $value
	 * @return \Goji\Form\FormElementAbstract|null
	 */
	public function getInputBy(string $attribute, $value): ?FormElementAbstract {

		$value = (string) $value;

		foreach ($this->m_inputs as $input) {
			if ($input->getAttribute($attribute) == $value)
				return $input;
		}

		return null;
	}

	/**
	 * Select an input by looking for its name
	 *
	 * $this->m_form->getInputByName('login[password]')->render();
	 *
	 * @param string $name
	 * @return \Goji\Form\FormElementAbstract|null
	 */
	public function getInputByName(string $name): ?FormElementAbstract {
		return $this->getInputBy('name', $name);
	}

	/**
	 * Select an input by looking for its ID
	 *
	 * $this->m_form->getInputByID('login__password')->render();
	 *
	 * @param string $id
	 * @return \Goji\Form\FormElementAbstract|null
	 */
	public function getInputByID(string $id): ?FormElementAbstract {
		return $this->getInputBy('id', $id);
	}
}
