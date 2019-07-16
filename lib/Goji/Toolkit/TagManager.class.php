<?php

	namespace Goji\Toolkit;

	/**
	 * Class TagManager
	 *
	 * @package Goji\Toolkit
	 */
	class TagManager {

		/**
		 * Sorts tags, makes sure they are strings and removes duplicates.
		 *
		 * @param array|string $tagsArray
		 * @return array
		 */
		public static function sanitizeTags($tagsArray): array {

			// Converting tags to Array
			if (!is_array($tagsArray))
				$tagsArray = [$tagsArray];

			// Converting tags to String
			$count = count($tagsArray);
			for ($i = 0; $i < $count; $i++) {
				if (!is_string($tagsArray[$i]))
					$tagsArray[$i] = strval($tagsArray[$i]);
			}

			$tagsArray = array_unique($tagsArray); // Removing doubles
			sort($tagsArray); // Sorting to alphabetical order

			return $tagsArray;
		}

		/**
		 * Converts array to JSON string.
		 *
		 * @param array $array
		 * @param bool $sanitize default = true
		 * @return string|false
		 */
		public static function encode($array, $sanitize = true) {

			if ($sanitize)
				$array = self::sanitizeTags($array);

			return json_encode($array);
		}

		/**
		 * Converts JSON string to array.
		 *
		 * @param string $json
		 * @param bool $sanitize default = false
		 * @return array
		 */
		public static function decode($json, $sanitize = false): array {

			$array = json_decode($json, true);

			if ($sanitize)
				$array = self::sanitizeTags($array);

			return $array;
		}

		/**
		 * Converts array of tags to string for display.
		 *
		 * ['list', 'of', 'tags'] -> 'list, of, tags'
		 *
		 * @param array $tagsArray
		 * @return string
		 */
		public static function toString($tagsArray): string {
			return implode(', ', $tagsArray);
		}

		/**
		 * Adds a single or multiple tags to a tag list and sanitizes the list.
		 *
		 * If no parent array specified, the function is equal to TagManager::sanitizeTags().
		 *
		 * ```php
		 * $tags = TagManager::addTags('single-tag');
		 * $tags = TagManager::addTags(['array', 'of', 'tags']);
		 * $tags = TagManager::addTags(['array', 'of', 'tags'], $tags);
		 * ```
		 *
		 * @param array|string $newTags
		 * @param array $tagsArray (optional)
		 * @return array
		 */
		public static function addTags($newTags, $tagsArray = null): array {

			// Making sure $newTags is an Array
			if (!is_array($newTags))
				$newTags = [$newTags];

			// If no parent Array specified, we create one
			if ($tagsArray === null)
				$tagsArray = [];

			$tagsArray = array_merge($tagsArray, $newTags); // Merging the arrays
			$tagsArray = self::sanitizeTags($tagsArray);

			return $tagsArray;
		}

		/**
		 * Removes a single or multiple tags from a tag list.
		 *
		 * @param array|string $tagsToRemove
		 * @param array $tagsArray
		 * @return array
		 */
		public static function removeTags($tagsToRemove, $tagsArray): array {

			// Making sure $tagsToRemove is an Array
			if (!is_array($tagsToRemove))
				$tagsToRemove = [$tagsToRemove];

			$tagsArray = self::sanitizeTags($tagsArray);

			foreach ($tagsToRemove as $tag) {
				if (in_array($tag, $tagsArray)) {
					array_splice($tagsArray, array_search($tag, $tagsArray), 1);
				}
			}

			return $tagsArray;
		}
	}
