<?php

	namespace App\Controller\Admin;

	use Goji\Blueprints\XhrControllerAbstract;
	use Goji\Core\HttpResponse;

	class XhrAdminUpdate extends XhrControllerAbstract {

		public function render(): void {

			// 'Already up-to-date.'
			HttpResponse::JSON([], true);
		}
	}
