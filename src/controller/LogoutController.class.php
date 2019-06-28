<?php

	namespace App\Controller;

	use Goji\Core\App;
	use Goji\Blueprints\ControllerInterface;

	class LogoutController implements ControllerInterface {

		/* <ATTRIBUTES> */

		private $m_app;

		public function __construct(App $app) {
			$this->m_app = $app;
		}

		public function render() {

			$this->m_app->getUser()->logOut();

			$redirectTo = $this->m_app->getRouter()->getLinkForPage('home');

			header("Location: $redirectTo");
			exit;
		}
	}
