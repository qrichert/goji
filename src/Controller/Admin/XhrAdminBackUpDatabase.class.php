<?php

	namespace App\Controller\Admin;

	use Goji\Blueprints\XhrControllerAbstract;
	use Goji\Core\HttpResponse;
	use Goji\Toolkit\BackUp;

	class XhrAdminBackUpDatabase extends XhrControllerAbstract {

		public function render(): void {

			HttpResponse::JSON([], BackUp::database($this->m_app->db()));
		}
	}
