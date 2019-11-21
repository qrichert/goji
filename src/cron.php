<?php

	require_once '../lib/Settings.php';
	require_once '../lib/RootPath.php';
	require_once '../lib/AutoLoad.php';

	use Goji\Core\Database;

	$db = new Database();

		// Delete tmp users older than 2 days (48h)
		$db->exec("DELETE FROM g_member_tmp
								WHERE date_registered <= DATE('NOW', '-2 DAY')");
