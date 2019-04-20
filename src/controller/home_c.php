<?php

	use Goji\SimpleTemplate;

/* <RENDERING> */

	$_TEMPLATE = new SimpleTemplate(TITLE_HOME,
									DESCRIPTION_HOME);

		$_TEMPLATE->startBuffer();

			require_once '../src/view/home_v.php';

		$_TEMPLATE->saveBuffer();

	require_once '../template/page/main_t.php';

/* </RENDERING> */
