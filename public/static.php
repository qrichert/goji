<?php

	require_once '../lib/RootPath.php';
	require_once '../lib/AutoLoad.php';

	// File type must be specified and supported
	$TYPE = null;

		if (!empty($_GET['type'])) {
			$_GET['type'] = mb_strtolower($_GET['type']);
		}

		if (in_array($_GET['type'], array('css', 'js'))) {

			$TYPE = $_GET['type'];
		} // else $TYPE = null;


	// File must be given and exist
	$FILE = null;

		if (isset($_GET['file'])
			&& !empty($_GET['file'])) {

			if (mb_strpos($_GET['file'], '|') !== false) { // If there are several files given

				// css/main.css|css/responsive.css
				$_GET['file'] = explode('|', $_GET['file']);
				$FILE = array();

				foreach ($_GET['file'] as $f) {

					if (file_exists($f))
						$FILE[] = $f;
				}

				// If only one, it's not an array
				if (count($FILE) === 1) {

					$FILE = $FILE[0];

				} else if (count($FILE) === 0) {

					$FILE = null;
				}

			} else {

				if (file_exists($_GET['file']))
					$FILE = $_GET['file'];
			}
		}

	// If there is something missing
	if ($TYPE === null || $FILE === null) {

		header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404);
		exit;
	}

	switch ($TYPE) {
		case 'css':		require_once '../src/static/css.php';	break;
		case 'js':		require_once '../src/static/js.php';	break;
	}
