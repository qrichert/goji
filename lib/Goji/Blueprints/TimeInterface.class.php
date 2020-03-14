<?php

namespace Goji\Blueprints;

/**
 * Interface TimeInterface
 *
 * @package Goji\Blueprints
 */
interface TimeInterface {

	const TIME_1MIN = 60;
	const TIME_15MIN = 15 * self::TIME_1MIN;
	const TIME_30MIN = 30 * self::TIME_1MIN;
	const TIME_1H = 60 * self::TIME_1MIN;
	const TIME_12H = 12 * self::TIME_1H;
	const TIME_1DAY = 24 * self::TIME_1H;
	const TIME_1WEEK = 7 * self::TIME_1DAY;
	const TIME_1MONTH = 30 * self::TIME_1DAY;
	const TIME_1YEAR = 365 * self::TIME_1DAY;
}
