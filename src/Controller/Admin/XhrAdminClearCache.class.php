<?php

	namespace App\Controller\Admin;

	use Goji\Blueprints\XhrControllerAbstract;
	use Goji\Core\HttpResponse;

	class XhrAdminClearCache extends XhrControllerAbstract {

		public function render(): void {
			sleep(5);
			HttpResponse::JSON([], false);
			HttpResponse::JSON([], true);
		}
	}
