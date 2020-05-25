<?php

require_once __DIR__ . '/../lib/Goji/Goji.php';

use Goji\Core\Database;
use Goji\Toolkit\BackUp;

$db = new Database(); // Main database

	// Backup database before working on it
	BackUp::database($db);
	BackUp::database(Database::DATABASES_SAVE_PATH . 'goji.analytics.sqlite3');

	// Delete tmp users older than 2 days (48h)
	$db->exec("DELETE FROM g_member_tmp
							WHERE date_registered <= DATE('NOW', '-2 DAY')");

	// Delete password reset requests older than 2 days (48h)
	$db->exec("DELETE FROM g_member_reset_password_request
							WHERE request_date <= DATE('NOW', '-2 DAY')");
