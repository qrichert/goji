<!DOCTYPE html>
<!-- TODO: we need a propoer controller for this, so we can use $this->m_app -->
<html lang="<?= $this->m_app->getLanguages()->getCurrentHyphenLocale(); ?>">
	<head>
		<?php
			if ($this->m_app->getAppMode() !== \Goji\Core\App::DEBUG)
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
