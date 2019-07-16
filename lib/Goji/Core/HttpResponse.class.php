<?php

	namespace Goji\Core;

	class HttpResponse {

		/**
		 * Adds JSON header, status if given, json_encode()s array and exit;s.
		 *
		 * @param array|null $data (associative array)
		 * @param bool|null $success
		 */
		public static function JSON(?array $data = null, ?bool $success = null): void {

			if ($data === null)
				$data = [];

			if ($success === true)
				$data['status'] = 'SUCCESS';
			else if ($success === false)
				$data['status'] = 'ERROR';

			header('Content-Type: application/json');

			echo json_encode($data);
			exit;
		}
	}
