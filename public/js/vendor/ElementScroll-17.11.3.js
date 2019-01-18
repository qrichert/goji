/*
	Copyright © 2017 Quentin Richert <https://www.quentinrichert.com/>
	Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
	See http://creativecommons.org/licenses/by-nc-nd/4.0/
	Please contact the author for commercial use.

	* This script returns the scroll of an element.
	* The returned value is 0 when the element first appears
	* on the bottom of the screen, 0.5 when it is in the middle
	* and 1 when is disappears at the top

	Instructions:

		1. Create a container.

			<div id="element-scroll"></div>

		2. Create a new ElementScroll with this object as reference.

			var elementScroll = new ElementScroll(document.querySelector("#element-scroll"));

		3. There are two ways for reading the value:

			1) By calling getElementScroll() at any time

				elementScroll.getElementScroll();

			2) By adding a listener for "elementscroll" event on the container and reading "(event).detail.scroll."

				document.querySelector("#element-scroll").addEventListener("elementscroll", function(e) {
					var scroll = e.detail.scroll;
				}, false);

	Example:

		<div id="element-scroll">
			<div></div>
		</div>

		<style>
			#element-scroll {
				height: 300px;
				background-color: red;
				display: flex;
			}

			#element-scroll > div {
				width: 150px;
				height: 150px;
				background-color: blue;
				margin: auto;
			}
		</style>

		<script src="js/ElementScroll-17.11.3.min.js"></script>
		<script>
			var elementScrollParent = document.querySelector("#element-scroll");
			var elementScrollSquare = document.querySelector("#element-scroll > div");

			var elementScroll = new ElementScroll(elementScrollParent);

				elementScrollParent.addEventListener("elementscroll", function(e) {
					elementScrollSquare.style.transform = "rotate(" + (e.detail.scroll * 360) + "deg)";
				}, false);
		</script>
*/

function ElementScroll(element) {
	var _this = this;

	this.m_element = element;
	this.m_elementScroll = 0;

		this.getElementScroll = function() {
			return this.m_elementScroll;
		};

	this.m_scrollEvent = function() {

		var scroll = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
		var top = _this.m_element.offsetTop;
		var height = _this.m_element.offsetHeight;
		var windowHeight = window.innerHeight;

		// min = windowHeight
		// max = -height

		var currentPos = top - scroll + height;

		var MIN = windowHeight + height;
		var MAX = 0;

		if (currentPos < MAX)
			output = 1;
		else if (currentPos > MIN)
			output = 0;
		else
			output = 1 - (currentPos / MIN);

		_this.m_elementScroll = output;

		var elementScrollEvent = new CustomEvent('elementscroll', {
			detail: {
				scroll: output
			}
		});

		_this.m_element.dispatchEvent(elementScrollEvent);
	};

	this.m_scrollEvent();

	window.addEventListener("scroll", this.m_scrollEvent, false);
	window.addEventListener("resize", this.m_scrollEvent, false);
}
