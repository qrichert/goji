<?php

	namespace Goji\Blueprints;

	/**
	 * Class ControllerAbstract
	 *
	 * @package Goji\Blueprints
	 */
	abstract class ControllerAbstract implements ControllerInterface {

		/* <ATTRIBUTES> */

		protected $m_useCache = false;

		protected function getUseCache(): bool {
			return $this->m_useCache;
		}

		protected function setUseCache(bool $useCache): void {
			$this->m_useCache = $useCache;
		}
	}
