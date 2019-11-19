<?php

	namespace Goji\Blueprints;

	use Goji\Core\App;

	/**
	 * Interface ControllerInterface
	 *
	 * @package Goji\Blueprints
	 */
	interface ControllerInterface extends HttpStatusInterface {

		public function __construct(App $app);
		public function render();
		public function getApp(): App;
		public function useCache(): bool;
		public function getCacheId(string $append = null): string;
		public function startCacheBuffer(): void;
		public function saveCacheBuffer(bool $output = true): void;
		public function renderCachedVersion(): bool;
	}
