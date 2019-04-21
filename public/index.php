<?php

	use Goji\Core\App;
	use Goji\Core\DataBase;
	use Goji\SwissKnife;
	use Goji\SimpleMetrics;

	session_start();

//	ini_set('display_errors', 1);
//	ini_set('display_startup_errors', 1);
//	error_reporting(E_ALL);

/* <MODE> */

	// CURRENT_MODE = debug || release;
	require_once '../src/include/mode.inc.php';

/* <GENERAL> */

	// TODO: Remove these in other files (template, mail, maybe others too)
	define('SITE_URL',			"https://www.SITE_URL.com");
	define('SITE_NAME',			"SITE_NAME");
	define('SITE_DOMAIN',		"SITE_URL.com"); // domain.com
	define('SITE_DOMAIN_FULL',	""); // subdomain.domain.com
	define('COOKIES_PREFIX',	"prfx-");

	require_once '../lib/AutoLoad.php';

	// TODO: Read from config file
//	$db = new DataBase();

	$app = new App();
		$app->setSiteURL('https://www.SITE_URL.com');
		$app->setSiteName('SITE_NAME');
		$app->setSiteDomainName('www.SITE_URL.com');
		$app->setSiteFullDomain('www.SITE_URL.com');
		$app->setCookiesPrefix('prfx-');
		$app->setIsLocalEnvironment(true);
		$app->setAppMode(App::DEBUG);

/* <INCLUDES> */

	require_once '../src/include/passwords.inc.php';
	require_once '../translation/table.tr.php';
	require_once '../src/include/lang.inc.php';

//	require_once '../src/model/Member.class.php';
//	require_once '../src/include/keep-me-logged-in.inc.php';
//	require_once '../src/include/connected.inc.php';

/* <LOCAL TESTING> */

	// TODO: Use App->getIsLocalTesting();
	if (!isset($_LOCAL_TESTING) || !is_bool($_LOCAL_TESTING)) {

		if (CURRENT_MODE == 'release')
			$_LOCAL_TESTING = false; // If release, default = no
		else
			$_LOCAL_TESTING = true; // If debug, default = yes
	}

	define('LOCAL_TESTING', $_LOCAL_TESTING);

/* <LINKED FILES MERGING> */

	// TODO: Integrate it to SimpleTemplate
	$_LINKED_FILES_MODE = 'normal'; // Separate

	if (CURRENT_MODE != 'debug')
		$_LINKED_FILES_MODE = 'merged';

	define('LINKED_FILES_MODE', $_LINKED_FILES_MODE);

/* <PAGE> */

	// Make sure the user isn't trying to cheat
	$_GET['page'] = SwissKnife::getFirstParamOccurrence('page', $_SERVER['QUERY_STRING']);

	$_PAGE = 'no-page'; // default

	if (isset($_GET['page']))
		$_PAGE = $_GET['page'];

	if ($_PAGE == 'no-page') // No page specified -> show home
		$_PAGE = 'home';

/* <REDIRECTIONS> */

/*
	if ($_CONNECTED) {

		// CONNECTED

	} else {

		// NOT CONNECTED

	}
*/

/* <SIMPLE METRICS> */

	SimpleMetrics::addPageView($_PAGE);

/* <PAGE SELECTION> */

	define('CURRENT_PAGE', $_PAGE);

	switch ($_PAGE) {

// <PAGES>

		case 'home':						require_once '../src/controller/home_c.php';							break;

// <XHR>
// <OPERATORS>

		case 'lang':						require_once '../src/operator/lang_o.php';								break;

// <EXTRAS>
// <ERRORS>

		case 'error':						require_once '../src/controller/error_c.php';							break;
		default:							require_once '../src/controller/error_c.php';							break;
	}
