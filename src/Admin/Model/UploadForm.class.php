<?php

namespace Admin\Model;

use Goji\Core\App;
use Goji\Form\Form;
use Goji\Form\InputFile;
use Goji\Form\InputLabel;
use Goji\Translation\Translator;

class UploadForm extends Form {

	function __construct(Translator $tr, App $app) {

		parent::__construct();

		$this->setAction($app->getRouter()->getLinkForPage('xhr-admin-upload'));

		$this->setId('upload__form');

			$this->addInput(new InputFile())
				 ->setName('upload[file]')
				 ->setId('upload__file')
				 ->setAttribute('required')
				 ->setAttribute('multiple')
				 ->setFileMaxSize(8000000)
				 ->setAllowedFileTypes(['gif', 'jpg', 'png', 'svg']);

			$this->addInput(new InputLabel())
				 ->setId('upload__dropzone')
				 ->addClass('dropzone')
				 ->setAttribute('for', 'upload__file')
				 ->setAttribute('textContent', 'Click to select files or drop them here.');
	}
}
