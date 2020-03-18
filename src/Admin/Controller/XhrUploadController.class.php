<?php

namespace Admin\Controller;

use Admin\Model\UploadForm;
use Goji\Blueprints\XhrControllerAbstract;
use Goji\Core\HttpResponse;
use Goji\Form\Form;
use Goji\Rendering\SimpleTemplate;
use Goji\Toolkit\SaveImage;
use Goji\Toolkit\SwissKnife;
use Goji\Translation\Translator;

class XhrUploadController extends XhrControllerAbstract {

	private function treatForm(Form $form) {

		$tr = $this->m_app->getTranslator();

		$detail = [];

		if (!$form->isValid($detail)) {

			HttpResponse::JSON([
				'detail' => $detail,
				'message' => $tr->_('UPLOAD_ERROR')
			], false);
		}

		/*
		 * [name] => Hello.png
		 * [type] => image/png
		 * [tmp_name] => /tmp/phpbrRy01
		 * [error] => 0
		 * [size] => 5274813
		 */
		$formFile = $form->getInputByName('upload[file]')->getValue();

		if (!SaveImage::isValid($formFile, 8000000)) {
			HttpResponse::JSON([
				'message' => $tr->_('UPLOAD_ERROR')
			], false);
		}

		$newImageSavePath = 'img/' . date('Y/m/');

		$newImageName = SaveImage::save($formFile, SaveImage::UPLOAD_DIRECTORY . '/' . $newImageSavePath);

		// Save thumb as well
		$thumbName = pathinfo($newImageName, PATHINFO_FILENAME);
		SaveImage::save($formFile, SaveImage::UPLOAD_DIRECTORY . '/' . $newImageSavePath, 'thumb_', $thumbName, true, 450);

		$newImageSavePath = 'upload/' . $newImageSavePath;

		HttpResponse::JSON([
			'file_path' => SimpleTemplate::rsc($newImageSavePath),
			'file_name' => $newImageName,
			'message' => $tr->_('UPLOAD_SUCCESS')
		], true);
	}

	public function render(): void {

		$tr = new Translator($this->m_app);
			$tr->loadTranslationResource('%{LOCALE}.tr.xml');

		$form = new UploadForm($tr, $this->m_app);
			$form->hydrate();

		$this->treatForm($form);
	}
}
