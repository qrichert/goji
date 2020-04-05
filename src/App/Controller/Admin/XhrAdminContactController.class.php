<?php

namespace App\Controller\Admin;

use App\Model\ContactManager;
use Goji\Blueprints\XhrControllerAbstract;
use Goji\Core\App;
use Goji\Core\HttpResponse;

class XhrAdminContactController extends XhrControllerAbstract {

	private $m_contactManager;

	public function __construct(App $app) {
		parent::__construct($app);
		$this->m_contactManager = new ContactManager($this->m_app);
	}

	private function deleteMessage(int $id): bool {
		return $this->m_contactManager->delete($id);
	}

	private function deleteAllMessages(): bool {
		return $this->m_contactManager->deleteAll();
	}

	public function render(): void {

		if (!empty($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id']) && $_GET['id'] === 'all')
			HttpResponse::JSON([], $this->deleteAllMessages());

		else if (!empty($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id']))
			HttpResponse::JSON([], $this->deleteMessage($_GET['id']));

		HttpResponse::JSON([], false);
	}
}
