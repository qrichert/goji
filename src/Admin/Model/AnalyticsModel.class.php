<?php

namespace Admin\Model;

use Generator;
use Goji\Core\Database;
use Goji\Toolkit\SimpleMetrics;

class AnalyticsModel {

	private $m_db;

	const TIME_FRAME_PAST_7_DAYS = 'past-7-days';
	const TIME_FRAME_PAST_30_DAYS = 'past-30-days';
	const TIME_FRAME_PAST_90_DAYS = 'past-90-days';
	const TIME_FRAME_PAST_6_MONTHS = 'past-6-months';
	const TIME_FRAME_PAST_12_MONTHS = 'past-12-months';
	const TIME_FRAME_PAST_5_YEARS = 'past-5-years';
	const TIME_FRAME_ALL_TIME = 'all-time';

	public function __construct() {

		$dataBaseFile = Database::DATABASES_SAVE_PATH . 'goji.analytics.sqlite3';

		// If analytics database file doesn't exist, copy it from blueprint
		if (!is_file($dataBaseFile))
			if (is_file($dataBaseFile . '.blueprint'))
				copy($dataBaseFile . '.blueprint', $dataBaseFile);

		$this->m_db = new Database('analytics');

		$this->buildAnalyticsDataFromSimpleMetrics();
	}

	private function buildAnalyticsDataFromSimpleMetrics(): void {

		$BASE_DIR = SimpleMetrics::getMetricsFolderPath(SimpleMetrics::PAGE_VIEW);

		// YEARS

		$years = glob("{$BASE_DIR}[0-9][0-9][0-9][0-9]", GLOB_ONLYDIR | GLOB_NOSORT);
		sort($years, SORT_NATURAL);

		foreach ($years as $year) {

			$year = basename($year);

			$months = glob("{$BASE_DIR}$year/[0-9][0-9]", GLOB_ONLYDIR | GLOB_NOSORT);
			sort($months, SORT_NATURAL);

			foreach ($months as $month) {

				$month = basename($month);

				$days = glob("{$BASE_DIR}$year/$month/[0-9][0-9]", GLOB_ONLYDIR | GLOB_NOSORT);
				sort($days, SORT_NATURAL);

				foreach ($days as $day) {

					$day = basename($day);

					// Don't analyse for today, we'ill compile the data tomorrow
					if ($year == date('Y') && $month == date('m') && $day == date('d'))
						continue;

					$pages = glob("{$BASE_DIR}$year/$month/$day/*" . SimpleMetrics::METRICS_FILE_EXTENSION, GLOB_NOSORT);

					foreach ($pages as $page) {

						$nbViews = @file_get_contents($page);

						if ($nbViews === false) {
							@unlink($page);
							continue;
						}

						// We don't need it anymore
						@unlink($page);

						$pageName = pathinfo($page, PATHINFO_FILENAME);

						$this->savePageViewsToDatabase($year, $month, $day, $pageName, $nbViews);
					}
				}
			}
		}

		SimpleMetrics::cleanup();
	}

	private function savePageViewsToDatabase(string $year, string $month, string $day, string $page, int $nbViews): void {

		$query = $this->m_db->prepare('INSERT INTO g_pageview
												(snapshot_date,   page,  nb_views)
										 VALUES (:snapshot_date, :page, :nb_views)');

		$query->execute([
			'snapshot_date' => "$year-$month-$day 00:00:00",
			'page' => $page,
			'nb_views' => $nbViews
		]);

		$query->closeCursor();
	}

	public function getPageViewsForPageAndTimeFrame(string $page, string $timeFrame): Generator {

		switch ($timeFrame) {
			case self::TIME_FRAME_PAST_7_DAYS:      $timeFrame = '-7 DAY';      break;
			case self::TIME_FRAME_PAST_30_DAYS:     $timeFrame = '-30 DAY';     break;
			case self::TIME_FRAME_PAST_90_DAYS:     $timeFrame = '-90 DAY';     break;
			case self::TIME_FRAME_PAST_6_MONTHS:    $timeFrame = '-6 MONTH';    break;
			case self::TIME_FRAME_PAST_12_MONTHS:   $timeFrame = '-12 MONTH';   break;
			case self::TIME_FRAME_PAST_5_YEARS:     $timeFrame = '-5 YEAR';     break;
			default:                                $timeFrame = '';            break;
		}

		if (!empty($timeFrame))
			$timeFrame = "AND snapshot_date > DATE('NOW', '$timeFrame')";

		$query = $this->m_db->prepare("SELECT DATE(snapshot_date) AS snapshot_date, nb_views
										FROM g_pageview
										WHERE page=:page $timeFrame
										ORDER BY snapshot_date ASC");

		$query->execute([
			'page' => $page
		]);

		while ($reply = $query->fetch()) {
			$reply['nb_views'] = (int) $reply['nb_views'];
			yield $reply;
		}

		$query->closeCursor();

		// TODAY (Read current metrics file)
		$dataFileForToday = SimpleMetrics::getMetricsFolderPath(SimpleMetrics::PAGE_VIEW);
			$dataFileForToday .= date('Y/m/d') . '/';
			$dataFileForToday .= $page . SimpleMetrics::METRICS_FILE_EXTENSION;

		if (is_file($dataFileForToday)) {

			$nbViews = @file_get_contents($dataFileForToday);

			if ($nbViews !== false) {
				yield [
					'snapshot_date' => date('Y-m-d'),
					'nb_views' => (int) $nbViews
				];
			}
		}
	}
}
