<?php

namespace Goji\Form;

/**
 * Class InputLabel
 *
 * @package Goji\Form
 */
class InputLabel extends FormElementAbstract {

	/* <ATTRIBUTES> */

	private $m_sideInfo;

	/**
	 * InputLabel constructor.
	 */
	public function __construct() {

		parent::__construct();

		$this->m_openingTag = '<label %{ATTRIBUTES}>';
		$this->m_closingTag = '</label>';

		$this->m_sideInfo = null;
	}

	/**
	 * @param string $tag
	 * @param array|null $attributes
	 * @param string $textContent
	 * @return \Goji\Form\InputLabel
	 */
	public function setSideInfo(string $tag, array $attributes = null, string $textContent = ''): InputLabel {

		if ($attributes === null)
			$attributes = [];

		if (array_key_exists('class', $attributes))
			$attributes['class'] = $attributes['class'] . ' form__side-info';
		else
			$attributes['class'] = 'form__side-info';

		$renderedAttributes = '';

		foreach ($attributes as $key => $value)
			$renderedAttributes .= $key . '="' . $value . '" ';

		$this->m_sideInfo = "<$tag $renderedAttributes>$textContent</$tag>";

		return $this;
	}

	public function render(): void {

		if ($this->m_sideInfo !== null) {
			echo '<div class="form__label-relative">';
		}

		echo str_replace('%{ATTRIBUTES}', $this->renderAttributes(), $this->m_openingTag);
		echo $this->hasAttribute('textContent') ? $this->getAttribute('textContent') : '';
		echo $this->m_closingTag;

		if ($this->m_sideInfo !== null) {
			echo $this->m_sideInfo;
			echo '</div>';
		}
	}
}
