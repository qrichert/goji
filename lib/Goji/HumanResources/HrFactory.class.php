<?php

namespace Goji\HumanResources;

use Exception;
use Goji\Core\App;
use Goji\Core\ConfigurationLoader;

class HrFactory {

	/* <ATTRIBUTES> */

	private static $m_isInitialized;
	private static $m_userClass;
	private static $m_memberManagerClass;

	/* <CONSTANTS> */

	const CONFIG_FILE = ROOT_PATH . '/config/hr.json5';

	/**
	 * Read configuration and initialize attributes.
	 *
	 * This function is designed to load configuration only on the first use of
	 * a class method.
	 *
	 * @param string $configFile
	 */
	private static function initialize(string $configFile = self::CONFIG_FILE): void {

		if (self::$m_isInitialized)
			return;

		try {

			$config = ConfigurationLoader::loadFileToArray($configFile);

			self::$m_userClass = $config['user'] ?? '';
			self::$m_memberManagerClass = $config['member_manager'] ?? '';

		} catch (Exception $e) {

			self::$m_userClass = '';
			self::$m_memberManagerClass = '';
		}

		self::$m_isInitialized = true;
	}

	/**
	 * Returns a pointer to a new User class.
	 *
	 * Either the default \Goji\HumanResources\User or the one set in config file.
	 *
	 * @param \Goji\Core\App $app
	 * @return \Goji\HumanResources\User
	 */
	public static function getUser(App $app): User {

		self::initialize();

		$user = null;

		if (!empty(self::$m_userClass) && class_exists(self::$m_userClass))
			$user = new self::$m_userClass($app);
		else
			$user = new User($app); // Default

		return $user;
	}

	/**
	 * Returns a pointer to a new MemberManager class.
	 *
	 * Either the default \Goji\HumanResources\MemberManager or the one set in config file.
	 *
	 * @param \Goji\Core\App $app
	 * @return \Goji\HumanResources\MemberManager
	 */
	public static function getMemberManager(App $app): MemberManager {

		self::initialize();

		$memberManager = null;

		if (!empty(self::$m_memberManagerClass) && class_exists(self::$m_memberManagerClass))
			$memberManager = new self::$m_memberManagerClass($app);
		else
			$memberManager = new MemberManager($app); // Default

		return $memberManager;
	}
}
