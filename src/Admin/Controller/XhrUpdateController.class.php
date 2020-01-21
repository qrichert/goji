<?php

namespace Admin\Controller;

use Goji\Blueprints\XhrControllerAbstract;
use Goji\Core\HttpResponse;
use Goji\Toolkit\Terminal;

class XhrUpdateController extends XhrControllerAbstract {

	public function render(): void {

		$output = Terminal::execute('git pull', $success);

		HttpResponse::JSON(['output' => $output], $success);
	}
}
