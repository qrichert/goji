<?php

	namespace Goji\Blueprints;

	use Goji\Core\App;
	use Goji\Toolkit\SimpleCache;

	/**
	 * Class ControllerAbstract
	 *
	 * @package Goji\Blueprints
	 */
	abstract class ControllerAbstract implements ControllerInterface {

		/* <ATTRIBUTES> */

		protected $m_app;
		protected $m_cacheId;

		public function __construct(App $app) {

			$this->m_app = $app;

			/*
			 * Using query string or variable parameters with infinite possibilities
			 * can lead to DOS attacks (request: page?id=1, id=2, id=3, etc.) so here
			 * we use only restricted values.
			 *
			 * Append any variable (and verified) data, like a blog post id making the page
			 * unique, by calling getCacheId(string) with the variable data as parameter.
			 */
			$this->m_cacheId = SimpleCache::cacheIDFromString(
				$this->m_app->getRouter()->getCurrentPage() . '-' .
				$this->m_app->getRequestHandler()->getRequestMethod() . '-' .
				$this->m_app->getRequestHandler()->getRedirectStatus() . '-' .
				$this->m_app->getLanguages()->getCurrentLocale() . '-' .
				($this->m_app->getUser()->isLoggedIn() ? 'logged-in' : 'not-logged-in')
			);
		}

		public function getApp(): App {
			return $this->m_app;
		}

		public function getCacheId(string $append = null): string {

			$append = $append ?? '';

				if (!empty($append))
					$append = '--' . SimpleCache::cacheIDFromString($append);

			return $this->m_cacheId . $append;
		}
	}
