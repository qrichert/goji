<?php

	namespace Goji\HumanResources;

	use Goji\Core\App;
	use Goji\Core\Session;

	/**
	 * Class User
	 *
	 * Represents a single person visiting the site, logged in or not.
	 * TODO: Member & MemberManager
	 * A logged in user is also called a member and has a Goji\HumanResources\Member
	 * profile and a Goji\HumanResources\MemberManager manager.
	 *
	 * @package Goji\HumanResources
	 */
	class User {

		/* <ATTRIBUTES> */

		private $m_app;

		private $m_isLoggedIn;
		private $m_id;

		/* <CONSTANTS> */

		public function __construct(App $app) {

			$this->m_app = $app;

			if (is_numeric(Session::get('user-id'))) {

				$this->m_isLoggedIn = true;
				$this->m_id = intval(Session::get('user-id'));

			} else {

				$this->m_isLoggedIn = false;
				$this->m_id = null;
			}
		}

		/**
		 * @return bool
		 */
		public function isLoggedIn(): bool {
			return $this->m_isLoggedIn;
		}

		/**
		 * @return int
		 */
		public function getID(): int {
			return $this->m_id;
		}
	}
