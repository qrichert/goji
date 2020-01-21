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
	 * @param bool $sort
	 * @return array
	 */
	public static function sanitizeTags($tagsArray, $sort = true): array {

		// Converting tags to Array
		$tagsArray = (array) $tagsArray;

		// Removing doubles
		$tagsArray = array_unique($tagsArray, SORT_STRING);

		// Converting tags to String
		foreach ($tagsArray as &$tag) {
			$tag = (string) $tag;
		}
		unset($tag);

		if ($sort)
			sort($tagsArray); // Sorting to alphabetical order

		return $tagsArray;
	}

	/**
	 * Converts array to JSON string.
	 *
	 * @param array $tagsArray
	 * @param bool $sanitize default = true
	 * @return string|false
	 */
	public static function encode(array $tagsArray, $sanitize = true) {

		if ($sanitize)
			$tagsArray = self::sanitizeTags($tagsArray);

		return json_encode($tagsArray);
	}

	/**
	 * Converts JSON string to array.
	 *
	 * @param string $json
	 * @param bool $sanitize default = false
	 * @return array
	 */
	public static function decode($json, $sanitize = false): array {

		$tagsArray = json_decode($json, true);

		if ($sanitize && is_array($tagsArray))
			$tagsArray = self::sanitizeTags($tagsArray);

		return $tagsArray;
	}

	/**
	 * Converts array of tags to string for display.
	 *
	 * Example with ['list', 'of', 'tags']:
	 * - toString() -> 'list, of, tags'
	 * - toString($surrounding = '<em>%{TAG}</em>') -> '<em>list</em>, <em>of</em>, <em...>tags</em>'
	 *
	 * @param array $tagsArray
	 * @param string $surrounding (optional) '%{TAG}' will re replaced by the value of the tag
	 * @param string $glue (optional) what separates the tags
	 * @return string
	 */
	public static function toString($tagsArray, $surrounding = '', $glue = ', '): string {

		if (!empty($surrounding)) {

			foreach ($tagsArray as &$tag) {
				$tag = str_replace('%{TAG}', $tag, $surrounding);
			}
			unset($tag);
		}

		return implode($glue, $tagsArray);
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
		$newTags = (array) $newTags;

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
		$tagsToRemove = (array) $tagsToRemove;

		$tagsArray = self::sanitizeTags($tagsArray);

		foreach ($tagsToRemove as $tag) {

			// Returns index or false if not found
			$index = array_search($tag, $tagsArray);

			if ($index !== false)
				array_splice($tagsArray, $index, 1);
		}

		return $tagsArray;
	}
}
