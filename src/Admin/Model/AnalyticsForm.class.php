<?php

namespace Admin\Model;

use Goji\Core\App;
use Goji\Form\Form;
use Goji\Form\InputSelect;
use Goji\Form\InputSelectOption;
use Goji\Translation\Translator;

class AnalyticsForm extends Form {

	function __construct(Translator $tr, App $app) {

		parent::__construct();

		$this->setAction($app->getRouter()->getLinkForPage('xhr-admin-analytics'));

		$this->addClass('inline')
		     ->setId('analytics__form');

		$inputSelectPage = new InputSelect();
			$this->addInput($inputSelectPage);
			$inputSelectPage->setName('analytics[page]')
							->setId('analytics__page')
							->setAttribute('required');

			$routes = $app->getRouter()->getRoutesAvailable();

			foreach ($routes as $id => $route) {
				$inputSelectPage->addOption(new InputSelectOption())
								->setAttribute('value', $id)
								->setAttribute('textContent', $route);
			}

		$inputSelectTimeFrame = new InputSelect();
			$this->addInput($inputSelectTimeFrame);
			$inputSelectTimeFrame->setName('analytics[time-frame]')
								 ->setId('analytics__time-frame')
								 ->setAttribute('required');

			$inputSelectTimeFrame->addOption(new InputSelectOption())
			                     ->setAttribute('value', AnalyticsModel::TIME_FRAME_PAST_7_DAYS)
			                     ->setAttribute('textContent', $tr->_('ANALYTICS_TIME_FRAME_PAST_7_DAYS'));

			$inputSelectTimeFrame->addOption(new InputSelectOption())
								 ->setAttribute('value', AnalyticsModel::TIME_FRAME_PAST_30_DAYS)
								 ->setAttribute('textContent', $tr->_('ANALYTICS_TIME_FRAME_PAST_30_DAYS'));

			$inputSelectTimeFrame->addOption(new InputSelectOption())
			                     ->setAttribute('value', AnalyticsModel::TIME_FRAME_PAST_90_DAYS)
			                     ->setAttribute('textContent', $tr->_('ANALYTICS_TIME_FRAME_PAST_90_DAYS'));

			$inputSelectTimeFrame->addOption(new InputSelectOption())
			                     ->setAttribute('value', AnalyticsModel::TIME_FRAME_PAST_6_MONTHS)
			                     ->setAttribute('textContent', $tr->_('ANALYTICS_TIME_FRAME_PAST_6_MONTHS'));

			$inputSelectTimeFrame->addOption(new InputSelectOption())
			                     ->setAttribute('value', AnalyticsModel::TIME_FRAME_PAST_12_MONTHS)
			                     ->setAttribute('textContent', $tr->_('ANALYTICS_TIME_FRAME_PAST_12_MONTHS'));

			$inputSelectTimeFrame->addOption(new InputSelectOption())
			                     ->setAttribute('value', AnalyticsModel::TIME_FRAME_PAST_5_YEARS)
			                     ->setAttribute('textContent', $tr->_('ANALYTICS_TIME_FRAME_PAST_5_YEARS'));

			$inputSelectTimeFrame->addOption(new InputSelectOption())
			                     ->setAttribute('value', AnalyticsModel::TIME_FRAME_ALL_TIME)
			                     ->setAttribute('textContent', $tr->_('ANALYTICS_TIME_FRAME_ALL_TIME'));
	}
}
