<?php

	namespace Goji;

	class TagManager {

		public static function sanitizeTags($tagsArray) {

			// Converting tags to Array
			if (!is_array($tagsArray))
				$tagsArray = array($tagsArray);

			// Converting tags to String
			for ($i = 0; $i < count($tagsArray); $i++) {
				if (!is_string($tagsArray[$i]))
					$tagsArray[$i] = strval($tagsArray[$i]);
			}

			$tagsArray = array_unique($tagsArray); // Removing doubles
						 sort($tagsArray); // Sorting to alphabetical order

			return $tagsArray;
		}

		public static function encode($array, $sanitize = true) {

			if ($sanitize)
				$array = self::sanitizeTags($array);

			return json_encode($array);
		}

		public static function decode($json, $sanitize = false) {

			if ($sanitize)
				$array = self::sanitizeTags($array);

			return json_decode($json);
		}

		public static function toString($tagsArray) {
			return implode(', ', $tagsArray);
		}

		public static function addTags($newTags, $tagsArray = null) {

			/*
				$tags = TagManager::addTags('single-tag');
				$tags = TagManager::addTags(array('array', 'of', 'tags'));
				$tags = TagManager::addTags(array('array', 'of', 'tags'), $tags);
			*/

			// Making sure $newTags is an Array
			if (!is_array($newTags)) {
				$newTags = array($newTags);
			}

			// If no parent Array specified, we create one
			if ($tagsArray === null)
				$tagsArray = array();

			$tagsArray = array_merge($tagsArray, $newTags); // Merging the arrays
			$tagsArray = self::sanitizeTags($tagsArray);

			return $tagsArray;
		}

		public static function removeTags($tagsToRemove, $tagsArray) {

			// Making sure $tagsToRemove is an Array
			if (!is_array($tagsToRemove)) {
				$tagsToRemove = array($tagsToRemove);
			}

			$tagsArray = self::sanitizeTags($tagsArray);

			foreach ($tagsToRemove as $tag) {
				if (in_array($tag, $tagsArray)) {
					array_splice($tagsArray, array_search($tag, $tagsArray), 1);
				}
			}

			return $tagsArray;
		}
	}
