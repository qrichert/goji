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

		$newImageSavePath = date('Y/m/');

		$newImageName = SaveImage::save($formFile, SaveImage::UPLOAD_DIRECTORY . '/' . $newImageSavePath);

		// Save thumb as well
		$thumbName = pathinfo($newImageName, PATHINFO_FILENAME);
		SaveImage::save($formFile, SaveImage::UPLOAD_DIRECTORY . '/' . $newImageSavePath, 'thumb_', $thumbName, true, 450);

		$fileType = SwissKnife::mime_content_type($formFile['tmp_name']);

		$newImageSavePath = 'upload/' . $newImageSavePath;

		$query = $this->m_app->db()->prepare('INSERT INTO g_upload
															   ( path,  name,  type,  size,  uploaded_by,  upload_date)
														VALUES (:path, :name, :type, :size, :uploaded_by, :upload_date)');

		$query->execute([
			'path' => $newImageSavePath,
			'name' => $newImageName,
			'type' => $fileType,
			'size' => (int) $formFile['size'],
			'uploaded_by' => $this->m_app->getUser()->getId(),
			'upload_date' => date('Y-m-d H:i:s')
		]);

		$query->closeCursor();

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
