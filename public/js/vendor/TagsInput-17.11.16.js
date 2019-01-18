/*
	Copyright © 2017 Quentin Richert <https://www.quentinrichert.com/>
	Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
	See http://creativecommons.org/licenses/by-nc-nd/4.0/
	Please contact the author for commercial use.

	* This script gives you the possibility to make a text input with tags.

	Instructions:

		1. Create a parent for the tags list

			<div id="tags-display"></div>

		2. Create a text input

			<input type="text" id="tags-input">

		3. Create a new TagsInput with a reference to the previous elements as parameters

			var tagsInput = new TagsInput(document.querySelector("#tags-display"), document.querySelector("#tags-input"));

		4. You can add a third parameter which is the maximum number of tags (-1 by default = infinite).

			new TagsInput(tagsDisplay, tagsInput, 20); // For a maximum of 20 tags.

		5. You're basically done here. Just call [Array] getTags(); to collect the list of tags.

		6. You also have three events to work with (these are to be listened from the text input).

			tagadded		// A tag has been added
			tagremoved		// A tag has been removed
			maximumreached	// The maximum number of tags has been reached

		7. You can manually add tags by calling void addTag(string);. (You won't be able to add more tags than maximum)

			var tagsInput = new TagsInput(...);
				tagsInput.addTag("tag1");
				tagsInput.addTag("tag2");
				tagsInput.addTag("tag3");

	Example:

		<div id="tags-display"></div>
		<input type="text" id="tags-text-input" placeholder="Write a tag and hit space or enter to add it...">

		<script src="js/TagsInput-17.11.16.min.js"></script>
		<script>
			var tagsDisplay = document.querySelector("#tags-display");
			var tagsTextInput = document.querySelector("#tags-text-input");

			var tagsInput = new TagsInput(tagsDisplay, tagsTextInput, 3); // Maximum 3 tags

				tagsTextInput.addEventListener("tagadded", function() {
					console.log("A tag has been added!");
				}, false);

				tagsTextInput.addEventListener("tagremoved", function() {
					console.log("A tag has been removed!");
				}, false);

				tagsTextInput.addEventListener("maximumreached", function() {
					console.log("Maximum has been reached!");
				}, false);

		// Adding tags manually. (Add the tags after the event listener if you want them to fire the events)
			tagsInput.addTag("tag1");
			tagsInput.addTag("tag2");
		</script>

	CSS:

		Of course, those elements will need some styling to be user-friendly.
		Here you have a solid start:

		/!\ Please note that all the tags added to the display div will have a "tag" class.

			#tags-display {
				display: flex;
				flex-direction: row;
				justify-content: flex-start;
				align-items: flex-start;
				align-content: flex-start;
				flex-wrap: wrap;

				box-sizing: border-box;
				min-height: 40px;

				width: 400px;
			}

			#tags-display .tag {
				color: white;
				background-color: #ff005c;
				padding: 3px 5px 3px 5px;
				margin: 0 4px 4px 0;
				border-radius: 5px;
				font-family: -apple-system, sans-serif;
				font-size: 13px;
				line-height: 18px;
				cursor: pointer;
			}

			#tags-display .tag:after {
				content: "\d7";
				color: #ffcee0;
				display: inline-block;
				padding: 0 0 0 7px;
				transform: translateY(-1px);
			}

			#tags-text-input {
				outline: none;
				-webkit-appearance: none;
				border: 1px solid lightgrey;
				border-radius: 5px;
				padding: 5px;
				font-size: 14px;
				box-sizing: border-box;
				margin-top: 5px;

				width: 400px;
			}

*/

(function () { // Polyfill for CustomEvent
	if (typeof window.CustomEvent === "function" )
		return false;

	function CustomEvent(event, params) {
		params = params || { bubbles: false, cancelable: false, detail: undefined };
		var evt = document.createEvent('CustomEvent');
		evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);
		return evt;
	}

	CustomEvent.prototype = window.Event.prototype;

	window.CustomEvent = CustomEvent;
})();

function TagsInput(tagsDisplay, tagsInput, maxNumberOfTags = -1) {
	var _this = this;
	var TOUCH_EVENT = 'ontouchend' in window ? 'touchend' : 'click';

	this.m_tagsDisplay = tagsDisplay;
	this.m_tagsInput = tagsInput;

	this.m_tagsElements = []; // Divs
	this.m_tags = []; // List of tags

		this.getTags = function() {
			return this.m_tags;
		};

	this.m_maxNumberOfTags = maxNumberOfTags;

	this.addTag = function(tagValue) {
		if (_this.m_maxNumberOfTags != -1 && _this.m_maxNumberOfTags <= _this.m_tagsElements.length)
			return;

		if (tagValue == '')
			return;

		if (/\s/.test(tagValue)) {
			var tagArray = tagValue.split(/\s/);

			tagArray.forEach(_this.addTag);

			return;
		}

		var tag = document.createElement('div');
			tag.classList.add("tag");
			tag.textContent = tagValue;

			_this.m_tagsDisplay.appendChild(tag);

		_this.m_tagsElements.push(tag);
		_this.m_tags.push(tagValue);

		_this.m_tagsInput.dispatchEvent(new CustomEvent('tagadded'));

		tag.addEventListener(TOUCH_EVENT, function(e) {
			e.preventDefault();
			e.stopImmediatePropagation();
			e.stopPropagation();

			var index = _this.m_tagsElements.indexOf(this);

			_this.m_tagsElements.splice(index, 1);
			_this.m_tags.splice(index, 1);
			_this.m_tagsDisplay.removeChild(this);
			_this.m_tagsInput.dispatchEvent(new CustomEvent('tagremoved'));
		}, false);

		if (_this.m_maxNumberOfTags != -1 && _this.m_maxNumberOfTags <= _this.m_tagsElements.length) {
			_this.m_tagsInput.dispatchEvent(new CustomEvent('maximumreached'));
		}
	};

	this.m_tagsInput.addEventListener("keydown", function(e) {
		if (_this.m_maxNumberOfTags != -1 && _this.m_maxNumberOfTags <= _this.m_tagsElements.length) {
			e.preventDefault();
			return;
		}

		if (!(e.key == "Enter" || e.key == " " || e.key == "Spacebar")) {
			return;
		}

		e.preventDefault();

		var tagValue = _this.m_tagsInput.value;
		_this.m_tagsInput.value = '';

		if (tagValue == '')
			return;

		_this.addTag(tagValue);
	});
}