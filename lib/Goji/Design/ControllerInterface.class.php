<?php

	namespace Goji\Design;

	use Goji\Core\App;

	/**
	 * Interface ControllerInterface
	 *
	 * @package Goji\Design
	 */
	interface ControllerInterface {

		public function __construct(App $app);
		public function render();
	}
