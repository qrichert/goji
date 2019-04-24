<?php

	use Goji\Toolkit\SimpleCache;
	use Goji\Toolkit\SimpleMinifierJS;

	header('Content-type: application/javascript; charset=utf-8');

	// Generating cache ID
	$cacheId = SimpleCache::cacheIDFromFileFullPath($FILE);

	if (SimpleCache::isValidFilePreprocessed($cacheId, $FILE)) { // Get cached version

		SimpleCache::loadFilePreprocessed($cacheId, true);

	} else { // Regenerate and cache

		$content = SimpleMinifierJS::minifyFile($FILE);

		SimpleCache::cacheFilePreprocessed($content, $FILE, $cacheId);

		echo $content;
	}
