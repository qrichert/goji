<?php

namespace System\Controller;

use Goji\Blueprints\ControllerAbstract;
use Goji\Core\HttpResponse;

class UploadFileController extends ControllerAbstract {

	public function render(): void {

		$requestFile = $this->m_app->getRequestHandler()->getRequestPage();

		if (empty($requestFile))
			$this->m_app->getRouter()->redirectToErrorDocument(self::HTTP_ERROR_NOT_FOUND);

		$requestFile = '../var/' . $requestFile;

		if (!is_file($requestFile))
			$this->m_app->getRouter()->redirectToErrorDocument(self::HTTP_ERROR_NOT_FOUND);

		$fileType = mime_content_type($requestFile);

		if ($fileType === false)
			$fileType = 'text/plain';

		HttpResponse::setContentType($fileType, null);

		readfile($requestFile);
	}
}
