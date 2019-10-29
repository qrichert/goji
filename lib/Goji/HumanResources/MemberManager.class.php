<?php

	namespace Goji\HumanResources;

	use Goji\Core\App;
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

		/* <CONSTANTS> */

		const E_MEMBER_ALREADY_EXISTS = 0;

		public function __construct(App $app) {

			$this->m_app = $app;
			$this->m_id = $this->m_app->getUser()->getId();
		}

		public static function createMember(App $app, $username, array &$detail): bool {

			// Database
			$query = $app->db()->prepare('SELECT
												(SELECT COUNT(*)
												FROM g_user
												WHERE username=:username)
											+
												(SELECT COUNT(*)
												FROM g_user_tmp
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

			/*********************/

			if ($app->getAppMode() === App::DEBUG) {
				// Log generated password to console
				Logger::log('Email: ' . $username, Logger::CONSOLE);
				Logger::log('Password: ' . $newPassword, Logger::CONSOLE);
			}

			/*********************/

			// Save to DB
			$query = $app->db()->prepare('INSERT INTO g_user_tmp
												   ( username,  password,  date_registered)
											VALUES (:username, :password, :date_registered)');

			$query->execute([
				'username' => $username,
				'password' => $hashedPassword,
				'date_registered' => date('Y-m-d H:i:s')
			]);

			$query->closeCursor();

			$detail['username'] = $username;
			$detail['password'] = $newPassword;

			return true;
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
											FROM g_user
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
		 * @param $username
		 * @param $password
		 * @return bool
		 * @throws \Exception
		 */
		public static function moveTemporaryUserToPermanentList(App $app, $username, $password): bool {

			// 1. We look if user is in the temporary list

			$query = $app->db()->prepare('SELECT *
											FROM g_user_tmp
											WHERE username=:username');

			$query->execute([
				'username' => $username
			]);

			$reply = $query->fetch();

			$query->closeCursor();

			// Not tmp user, quit
			if ($reply === false)
				return false;

			// 2. If the user is in the temporary list, check if password is good

			// It is a tmp user, check password
			if (empty($password) || !Passwords::verifyPassword($password, $reply['password'])) // Invalid password
				return false; // Quit

			// 3. If the password is right, we move the tmp user to permanent list

			// User is valid, move him to the real list
			$query = $app->db()->prepare('INSERT INTO g_user
												   ( username,  password,  date_registered)
											VALUES (:username, :password, :date_registered)');

			$query->execute([
				'username' => $reply['username'],
				'password' => $reply['password'],
				'date_registered' => $reply['date_registered']
			]);

			// And delete tmp entry
			$query = $app->db()->prepare('DELETE FROM g_user_tmp
											WHERE id=:id OR username=:username');

			$query->execute([
				'id' => $reply['id'],
				'username' => $reply['username']
			]);

			$query->closeCursor();

			return true;
		}
	}
