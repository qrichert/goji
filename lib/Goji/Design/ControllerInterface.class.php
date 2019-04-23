<?php

	namespace Goji\Design;

	use Goji\Core\App;

	interface ControllerInterface {

		public function __construct(App $app);
		public function render();
	}
