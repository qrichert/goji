<?php

namespace Admin\Controller;

use Goji\Blueprints\XhrControllerAbstract;
use Goji\Core\Database;
use Goji\Core\HttpResponse;
use Goji\Toolkit\BackUp;

class XhrBackUpDatabaseController extends XhrControllerAbstract {

	public function render(): void {

		HttpResponse::JSON([], (
			BackUp::database($this->m_app->db())
			&& BackUp::database(Database::DATABASES_SAVE_PATH . 'goji.analytics.sqlite3')
		));
	}
}
