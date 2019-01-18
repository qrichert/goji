<?php

	header('Content-type: application/javascript; charset=utf-8');

	require_once '../lib/SimpleCache.class.php';

	// Generating cache ID
	$cacheId = is_array($FILE) ? implode('|', $FILE) : $FILE;
		$cacheId = strtolower($cacheId); // js/main.js
		$cacheId = str_replace('.', '--', $cacheId); // js/main--js
		$cacheId = preg_replace('#\W#', '-', $cacheId); // js-main--js
		$cacheId = 'js-' . $cacheId; // js-js-main--js

	if (SimpleCache::isValidFilePreprocessed($cacheId, $FILE)) { // Get cached version

		SimpleCache::loadFilePreprocessed($cacheId, true);

	} else { // Regenerate and cache

		require_once '../lib/SimpleMinifierJS.class.php';

		$content = SimpleMinifierJS::minifyFile($FILE);

		SimpleCache::cacheFilePreprocessed($content, $FILE, $cacheId);

		echo $content;
	}
