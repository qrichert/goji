<?php

namespace App\Model;

use Goji\Core\App;

class ContactManager {

	private $m_app;
	private $m_db;

	public function __construct(App $app) {
		$this->m_app = $app;
		$this->m_db = $this->m_app->getDatabase();
	}

	public function create(string $name = null, string $email = null, string $message = null): bool {

		if (empty($message))
			return false;

		$query = $this->m_db->prepare('INSERT INTO g_contact
											   ( name,  email,  message,  date_sent)
										VALUES (:name, :email, :message, :date_sent)');

		$query->execute([
			'name' => $name,
			'email' => $email,
			'message' => $message,
			'date_sent' => date('Y-m-d H:i:s')
		]);

		$query->closeCursor();

		return true;
	}

	public function getUnopenedMailCount(): int {

		$query = $this->m_db->prepare('SELECT COUNT(*) AS nb
										FROM g_contact
										WHERE opened=0');

		$query->execute();
		$reply = $query->fetch();
		$query->closeCursor();

		return (int) $reply['nb'];
	}
}
