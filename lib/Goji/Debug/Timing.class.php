<?php

namespace Goji\Debug;

class Timing {

	static $m_timeStart = null;
	static $m_timeEnd = null;
	static $m_step = 0;

	public static function logStart(): void {

		self::$m_timeStart = microtime(true);

		error_log('----------------------');
		error_log('Start: ' . (string) self::$m_timeStart);
	}

	public static function logEnd(): void {

		if (self::$m_timeStart === null)
			self::logStart();

		self::$m_timeEnd = microtime(true);
		$delay = self::$m_timeEnd - self::$m_timeStart;

		error_log('End: ' . (string) self::$m_timeEnd . ' (' . (string) $delay . ')');
	}

	public static function logStep(string $stepId = ''): void {

		if (self::$m_timeStart === null)
			self::logStart();

		self::$m_step++;

		$timeStep = microtime(true);
		$delay = $timeStep - self::$m_timeStart;

		$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
			$backtrace = $backtrace['class'] . '::' . $backtrace['function'] . '()';

		$message = ' ' . self::$m_step . '. ';
			$message .= !empty($stepId) ? "($stepId) " : '';
			$message .= "$backtrace: $timeStep ($delay)";

		error_log($message);
	}
}
