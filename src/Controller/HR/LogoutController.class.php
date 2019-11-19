<?php

	namespace App\Controller\HR;

	use Goji\Blueprints\ControllerAbstract;
	use Goji\Core\HttpResponse;

	class LogoutController extends ControllerAbstract {

		public function render(): void {

			$this->m_app->getUser()->logOut();

			HttpResponse::setRobotsHeader(HttpResponse::ROBOTS_NOINDEX);

			$redirectTo = $this->m_app->getRouter()->getLinkForPage('home');

			$this->m_app->getRouter()->redirectTo($redirectTo);
		}
	}
