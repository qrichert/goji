<?php

namespace Admin\Model;

use Goji\Core\Database;
use Goji\Toolkit\SimpleMetrics;

class AnalyticsModel {

	private $m_db;

	public function __construct() {

		$this->m_db = new Database('analytics');

		$this->buildAnalyticsDataFromSimpleMetrics();
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

					$pages = glob("{$BASE_DIR}$year/$month/$day/*.txt", GLOB_NOSORT);

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
}
