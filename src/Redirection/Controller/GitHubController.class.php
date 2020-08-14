<?php

namespace Redirection\Controller;

use Goji\Core\App;

class GitHubController extends RedirectionControllerAbstract {
	public function __construct(App $app) {
		parent::__construct($app);
		$this->setRedirectTo('https://github.com/qrichert/goji');
	}
}
