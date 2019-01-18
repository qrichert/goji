/*
	Copyright © 2017 Quentin Richert <https://www.quentinrichert.com/>
	Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
	See http://creativecommons.org/licenses/by-nc-nd/4.0/
	Please contact the author for commercial use.

	* This script allows to overlay two images with a slider to reveal one or the other.

	Instructions:

		1. Create a container for the images.

			<div id="compare"></div>

		2. Each image needs to be inside a parent element (either a <div>, an <a>, or anything).
		   One of the parents needs to have the class "compare-left," and the other
		   the class "compare-right." They can have other children.

		   /!\ Be aware that the images and the parents are positioned in absolute.

			<div id="compare">
				<div class="compare-left">
					<img src="before.jpg">
				</div>
				<div class="compare-right">
					<img src="after.jpg">
				</div>
			</div>

		3. Create a new Compare object with these parameters:

			var compare = new Compare(
										parent,						// A reference to the compare container, in our example it is #compare
										initialSeparatorPosition,	// (optional) The position of the separation at page load in percents. 50% by default.
										manualOnly					// (optional) If set to true, the clicks and cursor moves will have no effet. You'll have to
																	   manually set the position of the separation using setSeparatorPosition(px). "false" by default.
									 );

		4. Please note you can interact with the object with a few functions:

			void setAlwaysFollowCursor(bool)	// If set to true, the separation will always follow the cursor, regarless of whether a click has happened beforehand.
			bool getAlwaysFollowCursor()

			[Object] getSeparator()				// Returns the separation bar
			[Object] getSeparatorHandle()		// Returns the handle on the separation bar

			void setSeparatorPosition(int)		// Sets the position of the separation relative to the parent's left border (value in pixels).
												   This function is useful when manualOnly is set to true.

	Example:

		<div id="compare">
			<div class="compare-left"><img src="before.jpg"></div>
			<div class="compare-right"><img src="after.jpg"></div>
		</div>

		<script src="js/Compare-17.11.6.js"></script>
		<script>
			var compare = new Compare(document.querySelector("#compare"));
				compare.getSeparator().style.backgroundColor = "#60a3ff";		// Changing the color of the separation bar...
				compare.getSeparatorHandle().style.backgroundColor = "white";	// ... and the handle gets its new color too

				// Or maybe you want no separator at all
				// compare.getSeparator().style.display = "none";
		</script>
*/

function Compare(parent,
				 initialSeparatorPosition = 50,	// Initial position of the separation is percent
				 manualOnly = false				// If the slider should only be set from outside
				) {

	var _this = this;

	this.m_parent = parent;
		this.m_parent.style.position = "relative";

		this.m_parentHeight = null;
		this.m_parentWidth = this.m_parent.offsetWidth;

			this.setParentHeight = function(w, h) {
				h = (_this.m_parentWidth / w) * h;
				h = Math.trunc(h);

				if (_this.m_parentHeight != null && h > _this.m_parentHeight) // Keeping the smallest
					return;

				_this.m_parentHeight = h;
				_this.m_parent.style.height = _this.m_parentHeight + "px";
			};

	this.m_alwaysFollowCursor = null;

		this.getAlwaysFollowCursor = function() {
			return this.m_alwaysFollowCursor;
		};

		this.setAlwaysFollowCursor = function(afc) {
			this.m_alwaysFollowCursor = afc;
		};

			this.setAlwaysFollowCursor(false);

	this.m_compareLeft = parent.getElementsByClassName("compare-left")[0];
		this.m_compareLeft.style.position = "absolute";
		this.m_compareLeft.style.top = "0";
		this.m_compareLeft.style.left = "0";
		this.m_compareLeft.style.width = initialSeparatorPosition + "%";
		this.m_compareLeft.style.height = "100%";
		this.m_compareLeft.style.overflow = "hidden";
		this.m_compareLeft.style.zIndex = "1";

		this.m_imageLeft = this.m_compareLeft.getElementsByTagName("img")[0];
			this.m_imageLeft.style.position = "absolute";
			this.m_imageLeft.style.top = "0";
			this.m_imageLeft.style.left = "0";
			this.m_imageLeft.style.width = this.m_parent.offsetWidth + "px";

				this.m_imageLeft.addEventListener("load", function() {
					_this.setParentHeight(_this.m_imageLeft.naturalWidth, _this.m_imageLeft.naturalHeight);
				}, false);

	this.m_compareRight = parent.getElementsByClassName("compare-right")[0];
		this.m_compareRight.style.position = "absolute";
		this.m_compareRight.style.top = "0";
		this.m_compareRight.style.left = "0";
		this.m_compareRight.style.width = "100%";
		this.m_compareRight.style.height = "100%";
		this.m_compareRight.style.overflow = "hidden";
		this.m_compareRight.style.zIndex = "0";

		this.m_imageRight = this.m_compareRight.getElementsByTagName("img")[0];
			this.m_imageRight.style.position = "absolute";
			this.m_imageRight.style.top = "0";
			this.m_imageRight.style.left = "0";
			this.m_imageRight.style.width = this.m_parent.offsetWidth + "px";

				this.m_imageRight.addEventListener("load", function() {
					_this.setParentHeight(_this.m_imageRight.naturalWidth, _this.m_imageRight.naturalHeight);
				}, false);

	this.m_separator = document.createElement('div');
		this.m_separator.style.width = "10px";
		this.m_separator.style.height = "100%";
		this.m_separator.style.position = "absolute";
		this.m_separator.style.top = "0";
		this.m_separator.style.left = (this.m_parentWidth / (100 / initialSeparatorPosition)) - (this.m_separator.offsetWidth / 2) + "px";
		this.m_separator.style.zIndex = "3";
		this.m_separator.style.backgroundColor = "#191919";
		this.m_separator.style.display = "flex";
		this.m_separator.style.cursor = "grab";

			this.getSeparator = function() {
				return this.m_separator;
			};

				this.m_parent.appendChild(this.m_separator);

	this.m_separatorHandle = document.createElement('div');
		this.m_separatorHandle.style.width = "4px";
		this.m_separatorHandle.style.height = "40px";
		this.m_separatorHandle.style.backgroundColor = "#919191";
		this.m_separatorHandle.style.margin = "auto";
		this.m_separatorHandle.style.borderRadius = "2px";

			this.getSeparatorHandle = function() {
				return this.m_separatorHandle;
			};

				this.m_separator.appendChild(this.m_separatorHandle);

	var mouseDown = false;

	var TOUCH_START = 'ontouchstart' in window ? 'touchstart' : 'mousedown';
	var TOUCH_END   = 'ontouchend'   in window ? 'touchend'   : 'mouseup';
	var TOUCH_MOVE  = 'ontouchmove'  in window ? 'touchmove'  : 'mousemove';

	function setSeparationPosX(posX) {
		_this.m_separator.style.cursor = "grabbing";
		var ratio = (posX - _this.m_parent.offsetLeft) / _this.m_parentWidth;

		if (ratio > 1)
			ratio = 1;
		else if (ratio < 0)
			ratio = 0;

			_this.m_compareLeft.style.width = ratio * 100 + "%";

		var separatorLeft = (_this.m_parentWidth * ratio) - (_this.m_separator.offsetWidth / 2);

		if (separatorLeft < 0)
			separatorLeft = 0;
		else if (separatorLeft > _this.m_parentWidth - _this.m_separator.offsetWidth)
			separatorLeft = _this.m_parentWidth - _this.m_separator.offsetWidth;

		_this.m_separator.style.left = separatorLeft + "px";
	}

	this.setSeparatorPosition = function(posX) {
		/*
			This function is to be called from the outside.
			We need to add parents offsetLeft px to posX before
			setSeparationPosX() can use the value.
		*/

		posX += this.m_parent.offsetLeft;
		setSeparationPosX(posX);
	};

	if (!manualOnly) {
		document.addEventListener(TOUCH_MOVE, function(e) {
			if (!mouseDown && !_this.m_alwaysFollowCursor)
				return;

			if (TOUCH_MOVE == 'touchmove')
				setSeparationPosX(e.changedTouches[0].pageX);
			else
				setSeparationPosX(e.pageX);
		}, false);

		this.m_parent.addEventListener(TOUCH_START, function(e) {
			mouseDown = true;
			e.preventDefault();
			e.stopImmediatePropagation();

			if (TOUCH_START == 'touchstart')
				setSeparationPosX(e.changedTouches[0].pageX);
			else
				setSeparationPosX(e.pageX);
		}, false);

		document.addEventListener(TOUCH_END, function() {
			mouseDown = false;
			_this.m_separator.style.cursor = "grab";
		}, false);
	}

	window.addEventListener('resize', function() {
		_this.m_parentHeight = null;
		_this.m_parentWidth = _this.m_parent.offsetWidth;
		_this.setParentHeight(_this.m_imageLeft.naturalWidth, _this.m_imageLeft.naturalHeight);
		_this.setParentHeight(_this.m_imageRight.naturalWidth, _this.m_imageRight.naturalHeight);
		_this.m_imageLeft.style.width = _this.m_parent.offsetWidth + "px";
		_this.m_imageRight.style.width = _this.m_parent.offsetWidth + "px";
		_this.setSeparatorPosition(_this.m_compareLeft.offsetWidth);
	}, false);
}