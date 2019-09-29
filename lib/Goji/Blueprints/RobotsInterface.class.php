<?php

	namespace Goji\Blueprints;

	/**
	 * Interface RobotsInterface
	 *
	 * @package Goji\Blueprints
	 */
	interface RobotsInterface {

		const ROBOTS_ALLOW_INDEX_AND_FOLLOW = 0;
		const ROBOTS_NOINDEX = 1;
		const ROBOTS_NOFOLLOW = 2;
		const ROBOTS_NOINDEX_NOFOLLOW = 3;
	}
