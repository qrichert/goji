<?php

namespace Goji\Blueprints;

use Goji\Core\App;
use Goji\HumanResources\MemberManager;
use Goji\Toolkit\SimpleCache;

/**
 * Class ControllerAbstract
 *
 * @package Goji\Blueprints
 */
abstract class ControllerAbstract implements ControllerInterface {

	/* <ATTRIBUTES> */

	protected $m_app;
	protected $m_cacheMaxAge;
	protected $m_cacheId;

	/* <CONSTANTS> */

	const DEFAULT_PAGE_CACHE_DURATION = TimeInterface::TIME_30MIN;

	public function __construct(App $app) {

		$this->m_app = $app;

		$this->m_cacheMaxAge = 0;

		/*
		 * Using query string or variable parameters with infinite possibilities
		 * can lead to DOS attacks (request: page?id=1, id=2, id=3, etc.) so here
		 * we use only restricted values.
		 *
		 * Append any variable (and verified) data, like a blog post id making the page
		 * unique, by calling getCacheId(string) with the variable data as parameter.
		 */
		$this->m_cacheId = SimpleCache::cacheIDFromString(
			'page---' .
			$this->m_app->getRouter()->getCurrentPage() . '-' .
			$this->m_app->getRequestHandler()->getRequestMethod() . '-' .
			$this->m_app->getRequestHandler()->getRedirectStatus() . '-' .
			$this->m_app->getLanguages()->getCurrentLocale() . '-' .
			($this->m_app->getUser()->isLoggedIn() ? 'logged-in' : 'not-logged-in') . '-' .
			($this->m_app->getUser()->isLoggedIn() ? $this->m_app->getUser()->getId() . '-' : '') .
			($this->m_app->getUser()->isLoggedIn() ? $this->m_app->getMemberManager()->getMemberRole() : MemberManager::ANY_MEMBER_ROLE)
		);
	}

	public function getApp(): App {
		return $this->m_app;
	}

	protected function setCacheMaxAge(int $duration): void {

		if ($duration < 0)
			$duration = 0;

		$this->m_cacheMaxAge = $duration;
	}

	protected function activateCache(int $duration = self::DEFAULT_PAGE_CACHE_DURATION): void {
		$this->setCacheMaxAge($duration);
	}

	protected function activateCacheIfRolePermits(string $role = 'editor', int $duration = self::DEFAULT_PAGE_CACHE_DURATION): void {

		// Don't use cache for editors, as they need to see changes immediately
		if ($this->m_app->getUser()->isLoggedIn()
			&& $this->m_app->getMemberManager()->memberIs($role))
				return;

		$this->setCacheMaxAge($duration);
	}

	public function useCache(): bool {
		return $this->m_cacheMaxAge > 0;
	}

	public function getCacheId(string $append = null): string {

		$append = $append ?? '';

			if (!empty($append))
				$append = '--' . SimpleCache::cacheIDFromString($append);

		return $this->m_cacheId . $append;
	}

	public function startCacheBuffer(): void {
		SimpleCache::startBuffer();
	}

	public function saveCacheBuffer(bool $output = true): void {

		if ($output)
			echo SimpleCache::cacheBuffer($this->m_cacheId, true);
		else
			SimpleCache::cacheBuffer($this->m_cacheId);
	}

	public function renderCachedVersion(): bool {

		if (!SimpleCache::isValid($this->m_cacheId, $this->m_cacheMaxAge))
			return false;

		SimpleCache::loadFragment($this->m_cacheId, true);

		return true;
	}
}
