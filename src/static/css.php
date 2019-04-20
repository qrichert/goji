<?php

	header('Content-type: text/css; charset=utf-8');

	require_once '../lib/Goji/SimpleCache.class.php';

	// Generating cache ID
	$cacheId = is_array($FILE) ? implode('|', $FILE) : $FILE;
		$cacheId = strtolower($cacheId); // css/main.css
		$cacheId = str_replace('.', '--', $cacheId); // css/main--css
		$cacheId = preg_replace('#\W#', '-', $cacheId); // css-main--css
		$cacheId = 'css-' . $cacheId; // css-css-main--css

	if (SimpleCache::isValidFilePreprocessed($cacheId, $FILE)) { // Get cached version

		SimpleCache::loadFilePreprocessed($cacheId, true);

	} else { // Regenerate and cache

		require_once '../lib/Goji/SimpleMinifierCSS.class.php';

		$content = SimpleMinifierCSS::minifyFile($FILE);

		SimpleCache::cacheFilePreprocessed($content, $FILE, $cacheId);

		echo $content;
	}
