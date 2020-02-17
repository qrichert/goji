<?php

namespace Goji\HumanResources;

use Exception;
use Goji\Core\App;
use Goji\Core\ConfigurationLoader;
use Goji\Debug\Logger;
use Goji\Security\Passwords;

/**
 * Class MemberManager
 *
 * @package Goji\HumanResources
 */
class MemberManager {

	/* <ATTRIBUTES> */

	protected $m_app;
	protected $m_id;
	protected $m_roles;
	protected $m_role;

	/* <CONSTANTS> */

	const CONFIG_FILE = ROOT_PATH . '/config/hr.json5';

	const DEFAULT_MEMBER_ROLES_LIST = [
		'member' => 1,
		'editor' => 5,
		'admin' => 7,
		'root' => 9 // For developers only, not for clients
	];
	const DEFAULT_MEMBER_ROLE = 1;
	const ANY_MEMBER_ROLE = 'any';

	const E_MEMBER_DOES_NOT_EXIST = 0;
	const E_MEMBER_ALREADY_EXISTS = 1;
	const E_ROLE_DOES_NOT_EXIST = 2;

	public function __construct(App $app, string $configFile = self::CONFIG_FILE) {

		$this->m_app = $app;
		$this->m_id = $this->m_app->getUser()->getId();

		try {

			$config = ConfigurationLoader::loadFileToArray($configFile);

			$this->m_roles = $config['roles'] ?? self::DEFAULT_MEMBER_ROLES_LIST;

			$this->m_role = self::DEFAULT_MEMBER_ROLE;

				$memberRoleQuery = $config['member_role_query'] ?? 'SELECT role FROM g_member WHERE id=%{ID}';
					$memberRoleQuery = str_replace('%{ID}', ':id', $memberRoleQuery);

				$query = $this->m_app->db()->prepare($memberRoleQuery);
				$query->execute([
					'id' => $this->m_id
				]);

				$reply = $query->fetch();

				$query->closeCursor();

				if ($reply !== false && !empty($reply['role']))
					$this->m_role = (int) $reply['role'];

		} catch (Exception $e) {

			$this->m_roles = self::DEFAULT_MEMBER_ROLES_LIST;
			$this->m_role = self::DEFAULT_MEMBER_ROLE;
		}

		// Just making sure it's really an int
		$this->m_role = (int) $this->m_role;
	}

	/**
	 * Returns member's ID
	 * @return int
	 */
	public function getId(): int {
		return $this->m_id;
	}

	/**
	 * Returns list of roles with id and value (as set in hr config file)
	 *
	 * @return array
	 */
	public function getRoles(): array {
		return $this->m_roles;
	}

	/**
	 * Returns member role
	 *
	 * @return int
	 */
	public function getMemberRole(): int {
		return $this->m_role;
	}

	/**
	 * Updates member role to new role
	 * @param $newRole
	 * @return bool
	 * @throws \Exception
	 */
	public function setMemberRole($newRole): bool {

		$newRoleExists = false;

		if (is_numeric($newRole)) { // Number, check if value is in the list

			foreach ($this->m_roles as $_ => $role) {

				if ($role == $newRole) {

					$newRoleExists = true;
					break;
				}
			}

		} else { // String (id), check if it exists

			if (!empty($this->m_roles[$newRole]) && is_numeric($this->m_roles[$newRole])) {

				$newRole = $this->m_roles[$newRole];
				$newRoleExists = true;
			}
		}

		if (!$newRoleExists) {
			trigger_error("Given role doesn't exist: '$newRole'.", E_USER_WARNING);
			return false;
		}

		$newRole = (int) $newRole;

		$query = $this->m_app->db()->prepare('UPDATE g_member
												SET role=:role
												WHERE id=:id');

		$query->execute([
			'role' => $newRole,
			'id' => $this->m_id
		]);

		$query->closeCursor();

		$this->m_role = $newRole;

		return true;
	}

	/**
	 * Checks whether the member can perform an action given his role
	 *
	 * Ex:
	 * if ($this->m_app->getUser()->isLoggedIn() && $this->m_app->getMemberManager()->memberIs('admin'))
	 *     ...
	 *
	 * Or:
	 * if ($this->m_app->getUser()->isLoggedIn() && $this->m_app->getMemberManager()->memberIs(7))
	 *     ...
	 *
	 * @param $roleRequired
	 * @param bool $exact If true, memberRole must be == to roleRequired. If false (default) memberRole must be >= roleRequired
	 * @return bool
	 */
	public function memberIs($roleRequired, bool $exact = false): bool {

		if ($roleRequired == self::ANY_MEMBER_ROLE)
			return true;

		// By ID (identifier)
		if (!is_numeric($roleRequired)) {

			// Check if identifier exists
			if (empty($this->m_roles[$roleRequired])) {
				trigger_error("Given role doesn't exist: '$roleRequired'.", E_USER_WARNING);
				return false;
			}

			// If it does, convert it to actual value
			$roleRequired = $this->m_roles[$roleRequired];
		}

		$roleRequired = (int) $roleRequired;

		if ($exact)
			return $this->m_role === $roleRequired;
		else
			return $this->m_role >= $roleRequired;
	}

	/**
	 * Returns all roles available to the current member (= roles where memberIs() === true)
	 *
	 * @return array
	 */
	public function getRolesAvailable(): array {

		$rolesAvailable = [];

		foreach ($this->m_roles as $role => $weight) {
			if ($weight <= $this->m_role)
				$rolesAvailable[$role] = $weight;
		}

		return $rolesAvailable;
	}

	/**
	 * Checks whether given (clear) password is valid for the current user
	 *
	 * @param string $password
	 * @return bool
	 * @throws \Exception
	 */
	public function getPasswordIsValid(string $password): bool {

		$storedPassword = self::getFieldsForId($this->m_app, $this->m_id, ['password'])['password'];

		return Passwords::verifyPassword($password, $storedPassword);
	}

	public function setPassword(string $password): bool {

		if (empty($password))
			return false;

		$password = Passwords::hashPassword($password);

		// Save to DB
		$query = $this->m_app->db()->prepare('UPDATE g_member
												SET password=:password
												WHERE id=:id');

		$query->execute([
			'password' => $password,
			'id' => $this->m_id
		]);

		$query->closeCursor();

		return true;
	}

/* <NOT LOGGED IN> */

/* --- Generic --- */

	/**
	 * Check if member exists & password is right (regular member only)
	 *
	 * @param \Goji\Core\App $app
	 * @param string $username
	 * @param string $password
	 * @param int|null $id
	 * @return bool
	 * @throws \Exception
	 */
	public static function isValidMember(App $app, string $username, string $password, int &$id = null): bool {

		// Database
		$reply = self::getFieldsForUsername($app, $username, ['id', 'password']);

		// Stored values
		$storedId = $reply['id'] ?? null;
		$storedPassword = $reply['password'] ?? null;

		if ($reply === false || empty($storedId) || empty($storedPassword)
		    || !Passwords::verifyPassword($password, $storedPassword)) {
			return false;
		}

		// Passed by reference
		$id = $storedId;

		return true;
	}

	/**
	 * (Helper Function) Select certain fields from user entry where id = id (regular member only, not tmp)
	 *
	 * @param \Goji\Core\App $app
	 * @param int $id
	 * @param array|null $fields (optional) If not set = select all
	 * @return array|false
	 * @throws \Exception
	 */
	public static function getFieldsForId(App $app, int $id, array $fields = null) {

		// Either all * or comma separated values
		$fields = empty($fields) ? '*' : implode(', ', $fields);

		$query = $app->db()->prepare("SELECT $fields
										FROM g_member
										WHERE id=:id");

		$query->execute([
			'id' => $id
		]);

		$reply = $query->fetch();

		$query->closeCursor();

		return $reply; // array if OK, false on error
	}

	/**
	 * (Helper Function) Select certain fields from user entry where username = $username (regular member only, not tmp)
	 *
	 * @param \Goji\Core\App $app
	 * @param string $username
	 * @param array|null $fields (optional) If not set = select all
	 * @return array|false
	 * @throws \Exception
	 */
	public static function getFieldsForUsername(App $app, string $username, array $fields = null) {

		// Either all * or comma separated values
		$fields = empty($fields) ? '*' : implode(', ', $fields);

		$query = $app->db()->prepare("SELECT $fields
										FROM g_member
										WHERE username=:username");

		$query->execute([
			'username' => $username
		]);

		$reply = $query->fetch();

		$query->closeCursor();

		return $reply; // array if OK, false on error
	}

/* --- Password Reset --- */

	/**
	 * @param \Goji\Core\App $app
	 * @param string $username
	 * @return string Token
	 * @throws \Exception
	 */
	public static function queueResetPasswordRequest(App $app, string $username): string {

		// Get member id from username
		$memberID = self::getFieldsForUsername($app, $username, ['id']);

		// Invalid username
		if ($memberID === false)
			throw new Exception("Member doesn't exist: '$username'.", self::E_MEMBER_DOES_NOT_EXIST);

		$memberID = $memberID['id'];

		// Get token
		$query = $app->db()->prepare('SELECT token
										FROM g_member_reset_password_request
										WHERE member_id=:member_id');

		$query->execute([
			'member_id' => $memberID
		]);

		$reply = $query->fetch();

		$query->closeCursor();

		$token = null;

		// If token exists, return it (request already made)
		if ($reply !== false)
			return $reply['token'];

		// Else create a new token
		$token = Passwords::generateSecureToken();

		// And save it
		$query = $app->db()->prepare('INSERT INTO g_member_reset_password_request
														( member_id,  token,  request_date)
												 VALUES (:member_id, :token, :request_date)');

		$query->execute([
			'member_id' => $memberID,
			'token' => $token,
			'request_date' => date('Y-m-d H:i:s')
		]);

		$query->closeCursor();

		return $token;
	}

	/**
	 * Check if a requests exists with given token and optionally token AND email
	 *
	 * @param \Goji\Core\App $app
	 * @param string $token
	 * @param string|null $email
	 * @param int|null $memberId Will contain id of member
	 * @return bool
	 * @throws \Exception
	 */
	public static function isValidResetPasswordRequest(App $app, string $token, string $email = null, int &$memberId = null): bool {

		$reply = null;

		// Check by token only
		if ($email === null) {

			$query = $app->db()->prepare('SELECT id, member_id
											FROM g_member_reset_password_request
											WHERE token=:token');

			$query->execute([
				'token' => $token
			]);

			$reply = $query->fetch();

			$query->closeCursor();

		} else {

			$query = $app->db()->prepare('SELECT request.id AS id, member.id AS member_id
											FROM g_member_reset_password_request AS request
											INNER JOIN g_member AS member
											ON request.member_id = member.id
											WHERE token=:token AND username=:username');

			$query->execute([
				'token' => $token,
				'username' => $email
			]);

			$reply = $query->fetch();

			$query->closeCursor();
		}

		if ($reply !== false && !empty($reply['member_id']))
			$memberId = (int) $reply['member_id'];

		if ($reply === false || empty($reply['id']) || !is_numeric($reply['id']))
			return false;
		else
			return true;
	}

	/**
	 * Removes reset password request for a member (given by member ID)
	 * @param \Goji\Core\App $app
	 * @param int $id
	 * @throws \Exception
	 */
	public static function clearResetPasswordRequestForUser(App $app, int $id): void {

		$query = $app->db()->prepare('DELETE FROM g_member_reset_password_request
		                               WHERE member_id=:member_id');

		$query->execute([
			'member_id' => $id
		]);

		$query->closeCursor();
	}

	/**
	 * Reset password for given username (for both normal & tmp accounts)
	 *
	 * Generates a new password, and send it by email to the member.
	 *
	 * @param \Goji\Core\App $app
	 * @param string $username
	 * @param array $detail
	 * @return bool
	 * @throws \Exception
	 */
	public static function resetPassword(App $app, string $username, array &$detail): bool {

		// Database
		$query = $app->db()->prepare('SELECT
											(SELECT COUNT(*)
											FROM g_member
											WHERE username=:username)
										+
											(SELECT COUNT(*)
											FROM g_member_tmp
											WHERE username=:username)
										AS nb');

		$query->execute([
			'username' => $username
		]);

		$reply = $query->fetch();
			$reply = (int) $reply['nb'];

		$query->closeCursor();

		if ($reply <= 0) { // User doesn't exist

			$detail['error'] = self::E_MEMBER_DOES_NOT_EXIST;
			return false;
		}

		// If we got here, credentials are valid -> SUCCESS -> reset password

		// Generate Password
		$newPassword = Passwords::generatePassword(7);
		$hashedPassword = Passwords::hashPassword($newPassword);

		/*********************/

		if ($app->getAppMode() === App::DEBUG) {
			// Log generated password to console
			Logger::log('Email: ' . $username, Logger::CONSOLE);
			Logger::log('Password: ' . $newPassword, Logger::CONSOLE);
		}

		/*********************/

		// Save to DB
		// Users
		$query = $app->db()->prepare('UPDATE g_member
										SET password=:password
										WHERE username=:username');

		$query->execute([
			'username' => $username,
			'password' => $hashedPassword
		]);

		// And tmp Users
		$query = $app->db()->prepare('UPDATE g_member_tmp
										SET password=:password
										WHERE username=:username');

		$query->execute([
			'username' => $username,
			'password' => $hashedPassword
		]);

		$query->closeCursor();

		$detail['password'] = $newPassword;

		return true;
	}

	/**
	 * When member resets password after having forgotten it.
	 *
	 * @param \Goji\Core\App $app
	 * @param int $memberId
	 * @param string $newPassword
	 * @return bool
	 * @throws \Exception
	 */
	public static function setNewPassword(App $app, int $memberId, string $newPassword): bool {

		/*********************/

		if ($app->getAppMode() === App::DEBUG) {
			// Log generated password to console
			Logger::log('New password: ' . $newPassword, Logger::CONSOLE);
		}

		/*********************/

		$newPassword = Passwords::hashPassword($newPassword);

		$query = $app->db()->prepare('UPDATE g_member
										SET password=:password
										WHERE id=:id');

		$query->execute([
			'id' => $memberId,
			'password' => $newPassword
		]);

		$query->closeCursor();

		return true;
	}

/* --- Sign Up --- */

	private static function usernameExists(App $app, string $username): bool {

		// Database
		$query = $app->db()->prepare('SELECT
											(SELECT COUNT(*)
											FROM g_member
											WHERE username=:username)
										+
											(SELECT COUNT(*)
											FROM g_member_tmp
											WHERE username=:username)
										AS nb');

		$query->execute([
			'username' => $username
		]);

		$reply = $query->fetch();
		$reply = (int) $reply['nb'];

		$query->closeCursor();

		if ($reply !== 0) // User already exists
			return true;
		else
			return false;
	}

	/**
	 * Creates member immediately (added by admin for example)
	 *
	 * @param \Goji\Core\App $app
	 * @param string $username
	 * @param string $password
	 * @param string|int $role
	 * @param array $detail
	 * @return bool
	 * @throws \Exception
	 */
	public static function createMember(App $app, string $username, string $password, int $role, array &$detail): bool {

		if (self::usernameExists($app, $username)) { // User already exists
			$detail['error'] = self::E_MEMBER_ALREADY_EXISTS;
			return false;
		}

		// Save to DB
		$query = $app->db()->prepare('INSERT INTO g_member
											   ( username,  password,  role,  date_registered)
										VALUES (:username, :password, :role, :date_registered)');

		$query->execute([
			'username' => $username,
			'password' => Passwords::hashPassword($password),
			'role' => $role,
			'date_registered' => date('Y-m-d H:i:s')
		]);

		$detail['id'] = $app->db()->lastInsertId();

		$query->closeCursor();

		return true;
	}

	/**
	 * Creates member in the temporary database
	 *
	 * @param \Goji\Core\App $app
	 * @param string $username
	 * @param array $detail
	 * @return bool
	 * @throws \Exception
	 */
	public static function createTemporaryMember(App $app, string $username, array &$detail): bool {

		if (self::usernameExists($app, $username)) { // User already exists
			$detail['error'] = self::E_MEMBER_ALREADY_EXISTS;
			return false;
		}

		// Generate Password
		$newPassword = Passwords::generatePassword(7);
		$hashedPassword = Passwords::hashPassword($newPassword);

		// Generate token
		$token = Passwords::generateSecureToken();

		/*********************/

		if ($app->getAppMode() === App::DEBUG) {
			// Log generated password to console
			Logger::log('Email: ' . $username, Logger::CONSOLE);
			Logger::log('Password: ' . $newPassword, Logger::CONSOLE);
		}

		/*********************/

		// Save to DB
		$query = $app->db()->prepare('INSERT INTO g_member_tmp
											   ( username,  password,  date_registered,  token)
										VALUES (:username, :password, :date_registered, :token)');

		$query->execute([
			'username' => $username,
			'password' => $hashedPassword,
			'date_registered' => date('Y-m-d H:i:s'),
			'token' => $token
		]);

		$detail['id'] = $app->db()->lastInsertId();

		$query->closeCursor();

		$detail['password'] = $newPassword;
		$detail['token'] = $token;

		return true;
	}

	/**
	 * Returns email on success, null on error
	 *
	 * @param \Goji\Core\App $app
	 * @param int $id
	 * @param string $token
	 * @return string|null
	 * @throws \Exception
	 */
	public static function getTemporaryMemberEmail(App $app, int $id, string $token): ?string {

		$query = $app->db()->prepare('SELECT username
										FROM g_member_tmp
										WHERE id=:id AND token=:token');

		$query->execute([
			'id' => $id,
			'token' => $token
		]);

		$reply = $query->fetch();

		$query->closeCursor();

		if ($reply === false)
			return null;

		return $reply['username'];
	}

	/**
	 * Move temporary user to permanent user list
	 *
	 * @param \Goji\Core\App $app
	 * @param string $username
	 * @param string $password
	 * @return bool
	 * @throws \Exception
	 */
	public static function moveTemporaryMemberToPermanentList(App $app, string $username, string $password): bool {

		// 1. We look if member is in the temporary list

		$query = $app->db()->prepare('SELECT *
										FROM g_member_tmp
										WHERE username=:username');

		$query->execute([
			'username' => $username
		]);

		$reply = $query->fetch();

		$query->closeCursor();

		// Not tmp user, quit
		if ($reply === false)
			return false;

		// 2. If the member is in the temporary list, check if password is good

		// It is a tmp user, check password
		if (empty($password) || !Passwords::verifyPassword($password, $reply['password'])) // Invalid password
			return false; // Quit

		// 3. If the password is right, we move the tmp member to permanent list

		// User is valid, move him to the real list
		$query = $app->db()->prepare('INSERT INTO g_member
											   ( username,  password,  role,  date_registered)
										VALUES (:username, :password, :role, :date_registered)');

		$query->execute([
			'username' => $reply['username'],
			'password' => $reply['password'],
			'role' => self::DEFAULT_MEMBER_ROLE,
			'date_registered' => $reply['date_registered']
		]);

		// And delete tmp entry
		$query = $app->db()->prepare('DELETE FROM g_member_tmp
										WHERE id=:id OR username=:username');

		$query->execute([
			'id' => $reply['id'],
			'username' => $reply['username']
		]);

		$query->closeCursor();

		return true;
	}
}
