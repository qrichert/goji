<?php

namespace Goji\Form;

/**
 * Class InputSelectOptionGroup
 *
 * @package Goji\Form
 */
class InputSelectOptionGroup extends FormElementAbstract {

	/* <ATTRIBUTES> */

	private $m_options;

	/**
	 * InputSelectOptionGroup constructor.
	 */
	public function __construct() {

		parent::__construct();

		$this->m_openingTag = '<optgroup %{ATTRIBUTES}>';
		$this->m_closingTag = '</optgroup>';

		$this->m_options = [];
	}

	/**
	 * @return array
	 */
	public function getOptions(): array {
		return $this->m_options;
	}

	/**
	 * @param \Goji\Form\InputSelectOption $option
	 * @return \Goji\Form\InputSelectOption
	 */
	public function addOption(InputSelectOption $option): InputSelectOption {
		$this->m_options[] = $option;
		return $option;
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
