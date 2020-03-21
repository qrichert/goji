<?php

namespace Admin\Controller;

use Goji\Blueprints\XhrControllerAbstract;
use Goji\Core\HttpResponse;
use Goji\Toolkit\SimpleCache;
use Goji\Toolkit\SwissKnife;
use Goji\Translation\Translator;

class XhrDiskSpaceUsageController extends XhrControllerAbstract {

	public function render(): void {

		$tr = new Translator($this->m_app);
			$tr->loadTranslationResource('%{LOCALE}.tr.xml');

		$baseDir = realpath('../');

		$diskSpaceUsed = null;

		// Refresh twice per day max., or if forced
		$cacheId = 'xhr-disk-space-usage-controller__disk-space-used';

		if (SimpleCache::isValid($cacheId, SimpleCache::TIME_12H) && !isset($_GET['refresh'])) {
			$diskSpaceUsed = (int) SimpleCache::loadFragment($cacheId);
		} else {
			$diskSpaceUsed = SwissKnife::dirsize($baseDir);

			if ($diskSpaceUsed !== -1)
				SimpleCache::cacheFragment($diskSpaceUsed, $cacheId);
		}

		if ($diskSpaceUsed === -1)
			HttpResponse::JSON([], false);

		// Format value to human readable number
		$diskSpaceUsedFormatted = SwissKnife::bytesToFileSize($diskSpaceUsed);
			$diskSpaceUsedFormatted = $diskSpaceUsedFormatted['value'] . ' ' . $tr->_($diskSpaceUsedFormatted['unit']);

		HttpResponse::JSON([
			'used_bytes' => $diskSpaceUsed,
			'used_formatted' => $diskSpaceUsedFormatted
		], true);
	}
}
