<?php

namespace Admin\Controller;

use Goji\Blueprints\XhrControllerAbstract;
use Goji\Core\HttpResponse;
use Goji\Toolkit\Terminal;

class XhrUpdateController extends XhrControllerAbstract {

	public function render(): void {

		// To pull from Bitbucket without an SSH key configured (see comments in bin/bitbucket-git for details)
		// $output = Terminal::execute('bash ../bin/bitbucket-git pull <key> <secret> <user/repo.git>', $success);
		$output = Terminal::execute('git pull', $success);

		HttpResponse::JSON(['output' => $output], $success);
	}
}
