<?php

	namespace Goji\HumanResources;

	use Goji\Core\App;

	/**
	 * Class MemberManager
	 *
	 * @package Goji\HumanResources
	 */
	class MemberManager {

		/* <ATTRIBUTES> */

		protected $m_app;
		protected $m_id;

		public function __construct(App $app) {

			$this->m_app = $app;
			$this->m_id = $this->m_app->getUser()->getId();
		}
	}
