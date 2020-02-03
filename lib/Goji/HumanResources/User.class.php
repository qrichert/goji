<?php

namespace Goji\HumanResources;

use Goji\Core\App;
use Goji\Core\Session;

/**
 * Class User
 *
 * Represents a single person visiting the site, logged in or not.
 * A logged in user is also called a member and has a Goji\HumanResources\MemberManager manager.
 *
 * @package Goji\HumanResources
 */
class User {

	/* <ATTRIBUTES> */

	protected $m_app;
	protected $m_id;
	protected $m_isLoggedIn;

	/* <CONSTANTS> */

	const USER_ID = 'user-id';

	public function __construct(App $app) {

		$this->m_app = $app;

		if (is_numeric(Session::get(self::USER_ID))) {

			$this->m_id = (int) Session::get(self::USER_ID);
			$this->m_isLoggedIn = true;

		} else {

			$this->m_id = null;
			$this->m_isLoggedIn = false;
		}

		// Must be called from outside! Because it calls App's setMemberManager()
		// which calls $this isLoggedIn() but $this is null because we didn't return
		// from the constructor yet.
		//$this->updateMemberManager();
	}

	/**
	 * Creates MemberManager if logged in, or else sets it to null
	 */
	public function updateMemberManager(): void {

		if (!$this->m_isLoggedIn) {
			$this->m_app->setMemberManager(null);
			return;
		}

		// else, logged in
		$memberManager = HrFactory::getMemberManager($this->m_app);
		$this->m_app->setMemberManager($memberManager);
	}

	/**
	 * @return bool
	 */
	public function isLoggedIn(): bool {
		return $this->m_isLoggedIn;
	}

	/**
	 * @param int $id
	 * @throws \Exception
	 */
	public function logIn(int $id): void {
		Session::set(self::USER_ID, $id);
		$this->m_id = $id;
		$this->m_isLoggedIn = true;
		$this->updateMemberManager();

		// Clear reset password request
		MemberManager::clearResetPasswordRequestForUser($this->m_app, $id);
	}

	/**
	 *
	 */
	public function logOut(): void {
		Session::unset(self::USER_ID);
		$this->m_id = null;
		$this->m_isLoggedIn = false;
		$this->updateMemberManager();
	}

	/**
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->m_id;
	}
}
