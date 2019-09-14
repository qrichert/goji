<?php

	namespace Goji\Blueprints;

	use Goji\Core\App;

	/**
	 * Class ControllerAbstract
	 *
	 * @package Goji\Blueprints
	 */
	abstract class ControllerAbstract implements ControllerInterface {

		/* <ATTRIBUTES> */

		protected $m_app;
		protected $m_cacheId;
		protected $m_useCache = false;

		public function __construct(App $app) {

			$this->m_app = $app;

			$this->m_cacheId = '';
				$this->m_cacheId .= $this->m_app->getRequestHandler()->getRequestURI();
				$this->m_cacheId .= '<br>';
				$this->m_cacheId .= $this->m_app->getRequestHandler()->getRequestMethod();
				$this->m_cacheId .= '<br>';
				$this->m_cacheId .= $this->m_app->getRequestHandler()->getRedirectStatus();
				$this->m_cacheId .= '<br>';
				$this->m_cacheId .= $this->m_app->getLanguages()->getCurrentLocale();
				$this->m_cacheId .= '<br>';
				$this->m_cacheId .= $this->m_app->getLanguages()->getCurrentCountryCode();
				$this->m_cacheId .= '<br>';
				$this->m_cacheId .= $this->m_app->getLanguages()->getCurrentCountryCode();
		}

		public function getCacheId(): string {
			echo $this->m_cacheId;exit;
			return $this->m_cacheId;
		}

		public function getUseCache(): bool {
			return $this->m_useCache;
		}

		public function setUseCache(bool $useCache): void {
			$this->m_useCache = $useCache;
		}
	}
