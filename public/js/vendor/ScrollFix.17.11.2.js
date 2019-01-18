/*
	Copyright © 2017 Quentin Richert <https://www.quentinrichert.com/>
	Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
	See http://creativecommons.org/licenses/by-nc-nd/4.0/
	Please contact the author for commercial use.

	* This script allows to make an element stop scrolling while another continues.
	* The first element scrolls normally until filling up the screen's height,
	* then waits for the end of the second element, to scroll normally again

	Instructions:

		1. Create a container for the elements.

			<div id="scroll-fix"></div>

		2. The structure for the children elements needs to be like this
		   You can add multiple elements of each.

		   /!\ Please note all "class" attributes shown here are mandatory

			<div id="scroll-fix">
				<div class="scroll-fix-fixed">
					<div class="scroll-fix-fixed-content">
						// Content of the sticky element
					</div>
				</div>
				<div class="scroll-fix-scroll">
					// Content of the element which always scrolls
				</div>
			</div>

		3. Create a new ScrollFix object like this:

			var scrollFix = new ScrollFix(
										parent // A reference to the parent container, in our example it is #scroll-fix
									 );

		4. You have to set the width of the elements independently in CSS.
		   Please note minimum height is 100vh for each element.
		   Please note also the parent container is made a "flex," with "flex-flow" set to "row."
		   You can style it accordingly but in JavaScript because CSS won't be able to override the JS properties.

		   /!\ The sum of widths has to be lesser than, or equal to 100%
		       If it is lesser than 100%, content will be centered in the parent.
			   This behavior can be modified with the "justify-content" property on the parent.

		   .scroll-fix-fixed {
				width: 50%;
				max-width: 50%; // "max-width" is mandatory and has to be the same as "width"
			}

			.scroll-fix-scroll {
				width: 50%;
			}

	Example:

		<div id="scroll-fix">
			<div class="scroll-fix-fixed">
				<div class="scroll-fix-fixed-content">
					// Content of the sticky element
				</div>
			</div>
			<div class="scroll-fix-scroll">
				// Content of the element which always scrolls
			</div>

			<!-- Just making four columns for the example -->
			<div class="scroll-fix-fixed">
				<div class="scroll-fix-fixed-content">
					// Content of the sticky element
				</div>
			</div>
			<div class="scroll-fix-scroll">
				// Content of the element which always scrolls
			</div>
		</div>

		<style>
			.scroll-fix-fixed {
				width: 25%;
				max-width: 25%;
			}

			.scroll-fix-scroll {
				width: 25%;
			}
		</style>

		<script src="js/ScrollFix-17.11.2.min.js"></script>
		<script>
			var scrollFix = new ScrollFix(document.querySelector("#scroll-fix"));
		</script>
*/

function ScrollFix(parent) {

	var States = {
		STICK_TO_TOP:    0,
		STICK_TO_VIEW:   1,
		STICK_TO_BOTTOM: 2
	};

	var _this = this;
	var i = null;

	this.m_parent = parent;
		this.m_parent.style.display = "flex";
		this.m_parent.style.flexDirection = "row";
		this.m_parent.style.justifyContent = "center";
		this.m_parent.style.alignItems = "flex-start";


	this.m_scrolls = parent.getElementsByClassName("scroll-fix-scroll");

		for (i = 0; i < this.m_scrolls.length; i++) {
			this.m_scrolls[i].style.minHeight = "100vh";
			this.m_parent.style.backgroundColor = window.getComputedStyle(this.m_scrolls[i], null).getPropertyValue("background-color");
		}

	this.m_fixed = parent.getElementsByClassName("scroll-fix-fixed");
	this.m_contents = [];

		for (i = 0; i < this.m_fixed.length; i++) {
			this.m_fixed[i].style.minHeight = "100vh";
			this.m_fixed[i].style.position = "relative";
			this.m_fixed[i].dataset.offsetLeft = this.m_fixed[i].offsetLeft;

			this.m_contents[i] = this.m_fixed[i].getElementsByClassName("scroll-fix-fixed-content")[0];
				this.m_contents[i].style.minHeight = "100vh";
				this.m_contents[i].style.width = "100%";

			this.m_fixed[i].style.backgroundColor = window.getComputedStyle(this.m_contents[i], null).getPropertyValue("background-color");
		}

	this.m_currentState = null;

	this.m_scrollEvent = function() {

		var j = 0;

		var scroll = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;

		if (parent.offsetTop > scroll) { // stick to top, the section is below

			if (_this.m_currentState == States.STICK_TO_TOP)
				return;
			else
				_this.m_currentState = States.STICK_TO_TOP;

			for (j = 0; j < _this.m_contents.length; j++) {
				_this.m_contents[j].style.position = "absolute";
				_this.m_contents[j].style.top = "0";
				_this.m_contents[j].style.bottom = "auto";
				_this.m_contents[j].style.left = "0";
				_this.m_contents[j].style.maxWidth = "none";
			}
		}

		else if (parent.offsetTop < scroll
			 && (parent.offsetTop + parent.offsetHeight) > (scroll + window.innerHeight)) { // stick to view, the section is visible

			if (_this.m_currentState == States.STICK_TO_VIEW)
				return;
			else
				_this.m_currentState = States.STICK_TO_VIEW;

			for (j = 0; j < _this.m_contents.length; j++) {
				_this.m_contents[j].style.position = "fixed";
				_this.m_contents[j].style.top = "0";
				_this.m_contents[j].style.bottom = "auto";
				_this.m_contents[j].style.left = _this.m_fixed[j].dataset.offsetLeft + "px";
				_this.m_contents[j].style.maxWidth = "inherit";
			}
		}

		else { // stick to bottom, the section is on top

			if (_this.m_currentState == States.STICK_TO_BOTTOM)
				return;
			else
				_this.m_currentState = States.STICK_TO_BOTTOM;

			for (j = 0; j < _this.m_contents.length; j++) {
				_this.m_contents[j].style.position = "absolute";
				_this.m_contents[j].style.top = "auto";
				_this.m_contents[j].style.bottom = "0";
				_this.m_contents[j].style.left = "0";
				_this.m_contents[j].style.maxWidth = "none";
			}
		}
	};

	this.m_scrollEvent();

	document.addEventListener("scroll", this.m_scrollEvent, false);

	this.m_recalculateSizes = function() {

		var j = 0;

		var maxScrollHeight = null;

		for (j = 0; j < _this.m_scrolls.length; j++) {
			var scrollHeight = Number(_this.m_scrolls[j].offsetHeight);

			if (maxScrollHeight == null)
				maxScrollHeight = scrollHeight;

			if (scrollHeight > maxScrollHeight)
				maxScrollHeight = scrollHeight;
		}

		for (j = 0; j < _this.m_fixed.length; j++) {
			_this.m_fixed[j].style.height = maxScrollHeight + "px";
			_this.m_fixed[j].dataset.offsetLeft = _this.m_fixed[j].offsetLeft;
		}

		_this.m_scrollEvent();
	};

	this.m_recalculateSizes();

	// Recaltulate once the page is definitively set up

	setTimeout(this.m_recalculateSizes, 500);
	setTimeout(this.m_recalculateSizes, 2500);
	setTimeout(this.m_recalculateSizes, 3000);

	window.addEventListener("resize", this.m_recalculateSizes, false);
}