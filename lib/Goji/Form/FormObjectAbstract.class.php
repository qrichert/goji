<?php

namespace Goji\Form;

use Goji\Blueprints\HtmlAttributesManagerAbstract;

/**
 * Class FormElementAbstract
 *
 * @package Goji\Form
 */
abstract class FormObjectAbstract extends HtmlAttributesManagerAbstract {

/* <RENDERING> */

	/**
	 * Output as HTML
	 */
	abstract public function render(): void;
}
