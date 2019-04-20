<?php

	use Goji\SimpleCache;
	use Goji\SimpleMinifierCSS;

	header('Content-type: text/css; charset=utf-8');

	// Generating cache ID
	$cacheId = is_array($FILE) ? implode('|', $FILE) : $FILE;
		$cacheId = strtolower($cacheId); // css/main.css
		$cacheId = str_replace('.', '--', $cacheId); // css/main--css
		$cacheId = preg_replace('#\W#', '-', $cacheId); // css-main--css
		$cacheId = 'css-' . $cacheId; // css-css-main--css

	if (SimpleCache::isValidFilePreprocessed($cacheId, $FILE)) { // Get cached version

		SimpleCache::loadFilePreprocessed($cacheId, true);

	} else { // Regenerate and cache

		$content = SimpleMinifierCSS::minifyFile($FILE);

		SimpleCache::cacheFilePreprocessed($content, $FILE, $cacheId);

		echo $content;
	}
