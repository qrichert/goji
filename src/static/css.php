<?php

	use Goji\Toolkit\SimpleCache;
	use Goji\Parsing\SimpleMinifierCSS;

	header('Content-type: text/css; charset=utf-8');

	// Generating cache ID
	$cacheId = SimpleCache::cacheIDFromFileFullPath($FILE);

	if (SimpleCache::isValidFilePreprocessed($cacheId, $FILE)) { // Get cached version

		SimpleCache::loadFilePreprocessed($cacheId, true);

	} else { // Regenerate and cache

		$content = SimpleMinifierCSS::minifyFile($FILE);

		SimpleCache::cacheFilePreprocessed($content, $FILE, $cacheId);

		echo $content;
	}
