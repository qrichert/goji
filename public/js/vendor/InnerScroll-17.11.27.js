/*
	Copyright © 2017 Quentin Richert <https://www.quentinrichert.com/>
	Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
	See http://creativecommons.org/licenses/by-nc-nd/4.0/
	Please contact the author for commercial use.

	* This script returns the inner scroll of an element.
	* The returned value is 0 at the top and 1 at the bottom

	Instructions:

		1. Create a container.

			<div id="inner-scroll"></div>

		2. Create a new InnerScroll with this object as reference.

			var innerScroll = new InnerScroll(document.querySelector("#inner-scroll"));

		3. There are two ways for reading the value:

			1) By calling getInnerScroll() at any time

				innerScroll.getInnerScroll();

			2) By adding a listener for "innerscroll" event on the container and reading "(event).detail.scroll."

				document.querySelector("#inner-scroll").addEventListener("innerscroll", function(e) {
					var scroll = e.detail.scroll;
				}, false);

	Example:

		<div id="inner-scroll">
			<div></div>
		</div>

		<style>
			#inner-scroll {
				width: 400px;
				height: 300px;
				background-color: red;
				overflow: scroll;
			}

			#inner-scroll > div {
				height: 900px;
				background-color: blue;
				border: 10px dashed white;
				box-sizing: border-box;
			}
		</style>

		<script src="js/InnerScroll-17.11.27.min.js"></script>
		<script>
			var innerScrollParent = document.querySelector("#inner-scroll");
			var innerScrollChild = document.querySelector("#inner-scroll > div");

			var innerScroll = new InnerScroll(innerScrollParent, innerScrollChild);

				innerScrollParent.addEventListener("innerscroll", function(e) {
					console.log((e.detail.scroll * 100) + '%');
				}, false);
		</script>
*/

function InnerScroll(innerScrollParent, innerScrollChild) {
	var _this = this;

	this.m_innerScrollParent = innerScrollParent;
	this.m_innerScrollChild = innerScrollChild;
	this.m_innerScroll = 0;

		this.getInnerScroll = function() {
			return this.m_innerScroll;
		};

	this.m_scrollEvent = function() {

		var s = _this.m_innerScrollParent.scrollTop; // Container scroll
		var d = _this.m_innerScrollParent.clientHeight; // Container height
		var c = _this.m_innerScrollChild.clientHeight; // Content height

		var output = s / (d - c);
			output = (s < 0) ? -Math.abs(output) : Math.abs(output);

		_this.m_innerScroll = output;

		var innerScrollEvent = new CustomEvent('innerscroll', {
			detail: {
				scroll: output
			}
		});

		_this.m_innerScrollParent.dispatchEvent(innerScrollEvent);
	};

	this.m_scrollEvent();

	this.m_innerScrollParent.addEventListener("scroll", this.m_scrollEvent, false);
	this.m_innerScrollParent.addEventListener("resize", this.m_scrollEvent, false);
}
