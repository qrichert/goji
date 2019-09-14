<?php

	namespace App\Controller;

	use Goji\Blueprints\ControllerAbstract;

	class LogoutController extends ControllerAbstract {

		public function render() {

			$this->m_app->getUser()->logOut();

			$redirectTo = $this->m_app->getRouter()->getLinkForPage('home');

			$this->m_app->getRouter()->redirectTo($redirectTo);
		}
	}
