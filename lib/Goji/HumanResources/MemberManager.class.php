<?php

	namespace Goji\HumanResources;

	use Exception;
	use Goji\Core\App;
	use Goji\Core\ConfigurationLoader;
	use Goji\Core\Logger;
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
		protected $m_memberRole;

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

				$this->m_memberRole = self::DEFAULT_MEMBER_ROLE;

					$memberRoleQuery = $config['member_role_query'] ?? 'SELECT role FROM g_member WHERE id=%{ID}';
						$memberRoleQuery = str_replace('%{ID}', ':id', $memberRoleQuery);

					$query = $this->m_app->db()->prepare($memberRoleQuery);
					$query->execute([
						'id' => $this->m_id
					]);

					$reply = $query->fetch();

					$query->closeCursor();

					if ($reply !== false && !empty($reply['role']))
						$this->m_memberRole = (int) $reply['role'];

			} catch (Exception $e) {

				$this->m_roles = self::DEFAULT_MEMBER_ROLES_LIST;
				$this->m_memberRole = self::DEFAULT_MEMBER_ROLE;
			}

			// Just making sure it's really an int
			$this->m_memberRole = (int) $this->m_memberRole;
		}

		/**
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
			return $this->m_memberRole;
		}

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

			$query = $this->m_app->db()->prepare('UPDATE g_member SET role=:role WHERE id=:id');
			$query->execute([
				'role' => $newRole,
				'id' => $this->m_id
			]);

			$query->closeCursor();

			$this->m_memberRole = $newRole;

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
				return $this->m_memberRole === $roleRequired;
			else
				return $this->m_memberRole >= $roleRequired;
		}

		/**
		 * @param \Goji\Core\App $app
		 * @param string $username
		 * @param array $detail
		 * @return bool
		 * @throws \Exception
		 */
		public static function createTemporaryMember(App $app, string $username, array &$detail): bool {

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

			if ($reply !== 0) { // User already exists
				$detail['error'] = self::E_MEMBER_ALREADY_EXISTS;
				return false;
			}

			// Generate Password
			$newPassword = Passwords::generatePassword(7);
			$hashedPassword = Passwords::hashPassword($newPassword);

			// Generate token
			$token = uniqid();

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
		 * Select certain fields from user entry where username = $username
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
	}
