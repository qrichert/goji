<!DOCTYPE html>
<html lang="<?= CURRENT_LANGUAGE; ?>">
	<head>
		<?php
			if (!LOCAL_TESTING)
				require_once '../template/page/include/tracking_v.inc.php';
		?>

		<meta charset="utf-8">

		<base href="/">

		<title><?= $ERROR; ?></title>
		<meta name="description" content="<?= $ERROR; ?>">

		<meta name="robots" content="noindex,nofollow">
	</head>
	<body>
		<h1><?= $ERROR; ?></h1>
	</body>
</html>
