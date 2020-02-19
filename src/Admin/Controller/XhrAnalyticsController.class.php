<?php

namespace Admin\Controller;

use Admin\Model\AnalyticsForm;
use Admin\Model\AnalyticsModel;
use Goji\Blueprints\XhrControllerAbstract;
use Goji\Core\HttpResponse;
use Goji\Form\Form;
use Goji\Translation\Translator;

class XhrAnalyticsController extends XhrControllerAbstract {

	private function treatForm(Form $form) {

		$detail = [];

		if (!$form->isValid($detail)) {

			HttpResponse::JSON([
				'detail' => $detail,
			], false);
		}

		$formPage = $form->getInputByName('analytics[page]')->getValue();
		$formTimeFrame = $form->getInputByName('analytics[time-frame]')->getValue();

		$analyticsModel = new AnalyticsModel();

		$data = [];

		foreach ($analyticsModel->getPageViewsForPageAndTimeFrame($formPage, $formTimeFrame) as $dataPoint)
			$data[] = $dataPoint;

		HttpResponse::JSON([
			'data' => $data,
		], true);
	}

	public function render(): void {

		$tr = new Translator($this->m_app);
			$tr->loadTranslationResource('%{LOCALE}.tr.xml');

		$form = new AnalyticsForm($tr, $this->m_app);
			$form->hydrate();

		$this->treatForm($form);
	}
}
