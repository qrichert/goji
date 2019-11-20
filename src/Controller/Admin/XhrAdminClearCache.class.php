<?php

	namespace App\Controller\Admin;

	use Goji\Blueprints\XhrControllerAbstract;
	use Goji\Core\HttpResponse;
	use Goji\Toolkit\SimpleCache;

	class XhrAdminClearCache extends XhrControllerAbstract {

		public function render(): void {

			SimpleCache::purgeCache();

			HttpResponse::JSON([], true);
		}
	}
