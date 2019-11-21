<?php

	namespace App\Controller\Admin;

	use Goji\Blueprints\XhrControllerAbstract;
	use Goji\Core\HttpResponse;
	use Goji\Toolkit\SimpleCache;
	use Goji\Toolkit\SwissKnife;

	class XhrAdminClearCache extends XhrControllerAbstract {

		public function render(): void {

			$fragmentsRemoved = SimpleCache::purgeCache();

			$totalSize = 0; // Bytes

			foreach ($fragmentsRemoved as &$frag) {

				$totalSize += $frag['size'];
				$frag = basename($frag['fragment']);
			}
			unset($frag);

			$totalSize = SwissKnife::bytesToFileSize($totalSize);

			HttpResponse::JSON([
				'fragments' => $fragmentsRemoved,
				'nb_removed' => count($fragmentsRemoved),
				'space_saved' => $totalSize
			], true);
		}
	}
