<?php

namespace Goji\Blueprints;

use Goji\Core\App;
use Goji\Core\HttpResponse;

abstract class TrackingImageAbstract extends ControllerAbstract {

	public function __construct(App $app) {

		parent::__construct($app);

		HttpResponse::setRobotsHeader(HttpResponse::ROBOTS_NOINDEX_NOFOLLOW);
		HttpResponse::setHttpCachingPolicy([
			'restriction' => HttpResponse::HTTP_CACHE_CONTROL_NO_STORE,
			'privacy' => HttpResponse::HTTP_CACHE_CONTROL_PRIVATE,
			'max-age' => 0
		]);
	}

	public function render(): void {

		HttpResponse::setContentType('image/gif', null);

		/**
		 * Now, as for what imagecreatetruecolor does as opposed to just imagecreate,
		 * you need to understand there are two main types of colour representation:
		 *
		 * - Indexed Colour, where the file consists of a palette of colours, and the rest
		 *   of the image resembles a "colour-by-numbers" book. The indexes have no meaning
		 *   other than referring to a colour in this index.
		 *
		 * - True Colour, where each pixel of the image literally specifies its colour in full.
		 */
		$pixel = imagecreate(1, 1);

		$fill = imagecolorallocate($pixel, 0, 0, 0);
		imagecolortransparent($pixel, $fill);

		imagegif($pixel);
		imagedestroy($pixel);
	}
}
