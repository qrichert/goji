<?php

	namespace Goji\Core;

	/**
	 * Class Router
	 *
	 * @package Goji\Core
	 */
	class Router {

		private $m_app;
		private $m_requestHandler;

		/**
		 * Router constructor.
		 *
		 * @param \Goji\Core\App $app
		 */
		public function __construct(App $app) {

			$this->m_app = $app;
			$this->m_requestHandler = $app->getRequestHandler();
		}

		/**
		 * Loads the appropriate controller.
		 */
		public function route() {

			$page = $this->m_requestHandler->getRequestPage();

			/*
			 * try {
			 *      $controller = new ControllerFactory($page);
			 * } catch (Exception $e) {
			 *      $this->redirect404(); -> ErrorController(ErrorController::E_404)
			 *      return;
			 * }
			 *
			 * $controller->run(); / render();
			 *
			 * class ControllerFactory {
			 *      public static function getController($page) {
			 *          switch ($page)
			 *              case:
			 *                  return new Controller();
			 *              case:
			 *                  return new Controller();
			 *      }
			 * }
			 */

//			echo $page;
		}
	}
