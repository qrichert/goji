<?php

	namespace Goji\Blueprints;

	use Goji\Core\App;

	/**
	 * Class CachedControllerAbstract
	 *
	 * @package Goji\Blueprints
	 */
	abstract class CachedControllerAbstract extends ControllerAbstract {

		public function __construct(App $app) {

			parent::__construct($app);

			$this->activateCacheIfRolePermits();
		}
	}
