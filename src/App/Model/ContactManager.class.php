<?php

namespace App\Model;

use Goji\Core\App;
use Goji\Toolkit\SwissKnife;

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

	public function getMail(int $offset = 0, int $count = -1): array {

		if ($offset < 0)
			$offset = 0;

		$q = 'SELECT * FROM g_contact ORDER BY date_sent DESC ';

		if ($count > -1) // LIMIT supplied
			$q .= "LIMIT $count OFFSET $offset ";

		$query = $this->m_db->prepare($q);
		$query->execute();

		$reply = $query->fetchAll();

		$query->closeCursor();

		foreach ($reply as &$el) {
			$el['date_sent'] = SwissKnife::dateToComponents($el['date_sent']);
			$el['opened'] = SwissKnife::sqlBool($el['opened']);
		}
		unset($el);

		return $reply;
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

	public function markAllAsOpened(): void {
		$this->m_db->exec('UPDATE g_contact SET opened=1');
	}

	public function markAllAsUnopened(): void {
		$this->m_db->exec('UPDATE g_contact SET opened=0');
	}
}
