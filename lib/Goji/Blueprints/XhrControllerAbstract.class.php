<?php

	namespace Goji\Blueprints;

	use Goji\Core\App;
	use Goji\Core\HttpResponse;

	abstract class XhrControllerAbstract extends ControllerAbstract {

		public function __construct(App $app) {

			parent::__construct($app);

			HttpResponse::setRobotsHeader(HttpResponse::ROBOTS_NOINDEX);

			if (!$this->m_app->getRequestHandler()->isAjaxRequest())
				$this->m_app->getRouter()->redirectTo($this->m_app->getRouter()->getLinkForPage('home'));
		}
	}
