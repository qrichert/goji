<?php

namespace HR\Model;

use Goji\Blueprints\ModelObjectAbstract;
use Goji\Core\App;
use Goji\Core\Database;

class MemberProfile extends ModelObjectAbstract {

	/* <ATTRIBUTES> */

	protected $m_app;
	protected $m_memberId;

	public function __construct(App $app, int $memberId) {

		$this->m_app = $app;
		$this->m_memberId = $memberId;

		parent::__construct($app, 'g_member_profile', 'member_id=' . $memberId);

		$this->writeLock('member_id');
	}

	public static function getDisplayNameForMemberId(Database $db, int $memberId): ?string {

		$query = $db->prepare('SELECT display_name
								FROM g_member_profile
								WHERE member_id=:member_id');

		$query->execute([
			'member_id' => $memberId
		]);

		$reply = $query->fetch();

		$query->closeCursor();

		if ($reply === false)
			return null;
		else
			return $reply['display_name'];
	}
}
