<?php

	namespace Admin\Controller;

	use Goji\Blueprints\XhrControllerAbstract;
	use Goji\Core\HttpResponse;
	use Goji\Toolkit\SimpleCache;
	use Goji\Toolkit\SwissKnife;
	use Goji\Translation\Translator;

	class XhrClearCacheController extends XhrControllerAbstract {

		public function render(): void {

			$tr = new Translator($this->m_app);
				$tr->loadTranslationResource('%{LOCALE}.tr.xml');


			// Clear cache
			$fragmentsRemoved = SimpleCache::purgeCache();

			// Format data
			$spaceSaved = 0; // Bytes

			foreach ($fragmentsRemoved as &$frag) {

				$spaceSaved += $frag['size'];
				$frag = basename($frag['fragment']);
			}
			unset($frag);

			$nbRemoved = count($fragmentsRemoved);
				$nbRemoved .= ' ' . $tr->_('ADMIN_CLEAR_CACHE_NB_FRAGMENTS_REMOVED', $nbRemoved);

			$spaceSaved = SwissKnife::bytesToFileSize($spaceSaved); // ex: ['value' => 5.72, 'unit' => SwissKnife::UNIT_MEGA_BYTE]
				$spaceSaved = (string) $spaceSaved['value'] . ' ' .
				              ($spaceSaved['unit'] == SwissKnife::UNIT_BYTE ?
					              $tr->_($spaceSaved['unit'], $spaceSaved['value']) : // Byte needs count (byte | bytes)
					              $tr->_($spaceSaved['unit'])
				              ) . ' ' .
				              $tr->_('ADMIN_CLEAR_CACHE_SPACE_SAVED', (int) $spaceSaved['value']);

			// Response
			HttpResponse::JSON([
				//'fragments' => $fragmentsRemoved,
				'nb_removed' => $nbRemoved,
				'space_saved' => $spaceSaved
			], true);
		}
	}
