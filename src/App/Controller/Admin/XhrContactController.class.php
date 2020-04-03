<?php

namespace App\Controller\Admin;

use Goji\Blueprints\XhrControllerAbstract;
use Goji\Core\HttpResponse;

class XhrContactController extends XhrControllerAbstract {

	public function render(): void {
		HttpResponse::JSON([], false);
	}
}
