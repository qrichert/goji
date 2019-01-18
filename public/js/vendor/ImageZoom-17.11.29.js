/*
	Copyright © 2017 Quentin Richert <https://www.quentinrichert.com/>
	Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
	See http://creativecommons.org/licenses/by-nc-nd/4.0/
	Please contact the author for commercial use.

	* This scripts makes images zoom-in on hover (or touch).

	Instructions:

		1. Create an image:

			<img src="img/my_image.jpg alt="My Image">

		2. This image needs to be inside a parent container.

			// Like that
				<div>
					<img src="img/my_image.jpg alt="My Image">
				</div>

		3. Create a new ImageZoom object

			var imgZoom = new ImageZoom(image,		// An image node
										zoom = 1.7	// (optional) The zoom factor which will be applied (1.7 by default)
									   );

		4. Two methods can be called:

			void setZoom(int) // To set the zoom factor
			int getZoom()	  // TO get the current zoom factor

	Example:

		<img src="img/my_image.jpg alt="My Image" >
		<img src="img/my_image_2.jpg alt="My Image 2">
		<img src="img/my_image_3.jpg alt="My Image 3">

		<script src="js/ImageZoom.17.11.29.min.js"></script>
		<script>
			document.querySelectorAll('img').forEach(function(el) {
				var imageZoom = new ImageZoom(el);
					imageZoom.setZoom(3); // We could have changed this value directly by calling ImageZoom(el, 3);
											 But we do it like that for the sake of the example.
			});
		</script>

*/

function ImageZoom(image, zoom = 1.7) {
	var _this = this;

	var TOUCH_START = 'ontouchstart' in window ? 'touchstart' : 'mouseenter';
	var TOUCH_END   = 'ontouchend'   in window ? 'touchend'   : 'mouseleave';
	var TOUCH_MOVE  = 'ontouchmove'  in window ? 'touchmove'  : 'mousemove';

	this.m_image = image;
	this.m_parent = image.parentNode;

	this.m_zoom = (zoom < 1) ? 1 : zoom;

		this.setZoom = function(zoom) {
			this.m_zoom = (zoom < 1) ? 1 : zoom;
		};

		this.getZoom = function() {
			return this.m_zoom;
		};

	this.m_containerWidth = 0;
	this.m_containerHeight = 0;

	this.m_containerOffsetLeft = 0;
	this.m_containerOffsetTop = 0;

	this.m_imageWidth = 0;
	this.m_imageHeight = 0;

	this.m_widthDifference = 0;
	this.m_heightDifference = 0;

	this.m_xMin = 0;
	this.m_xMax = 0;

	this.m_yMin= 0;
	this.m_yMax = 0;

		this.m_image.addEventListener('load', function() {
			construct(this);
		}, false);

	function recalculateSizes() {
		_this.m_containerWidth = _this.m_parent.offsetWidth;
		_this.m_containerHeight = _this.m_parent.offsetHeight;

		_this.m_containerOffsetLeft = _this.m_parent.offsetLeft;
		_this.m_containerOffsetTop = _this.m_parent.offsetTop;

		_this.m_imageWidth = _this.m_containerWidth * _this.m_zoom;
		_this.m_imageHeight = _this.m_containerHeight * _this.m_zoom;

		_this.m_widthDifference = Math.abs(_this.m_imageWidth - _this.m_containerWidth);
		_this.m_heightDifference = Math.abs(_this.m_imageHeight - _this.m_containerHeight);

		_this.m_xMin = 0;
		_this.m_xMax = -_this.m_widthDifference;

		_this.m_yMin= 0;
		_this.m_yMax = -_this.m_heightDifference;
	}

	function addStyle() {
		_this.m_parent.style.overflow = "hidden";
		_this.m_parent.style.position = "relative";
		_this.m_parent.style.width = _this.m_containerWidth + "px";
		_this.m_parent.style.height = _this.m_containerHeight + "px";

			_this.m_image.style.position = "absolute";
			_this.m_image.style.width = _this.m_containerWidth + "px";
			_this.m_image.style.height = _this.m_containerHeight + "px";
			_this.m_image.style.top = "0px";
			_this.m_image.style.left = "0px";
			_this.m_image.style.objectFit = "cover";
	}

	function removeStyle() {
		_this.m_parent.style.overflow = null;
		_this.m_parent.style.position = null;
		_this.m_parent.style.width = null;
		_this.m_parent.style.height = null;

			_this.m_image.style.position = null;
			_this.m_image.style.width = null;
			_this.m_image.style.height = null;
			_this.m_image.style.top = null;
			_this.m_image.style.left = null;
			_this.m_image.style.objectFit = null;
	}

	function construct(image) {
		removeStyle();
		recalculateSizes();
		addStyle();

			_this.m_parent.addEventListener(TOUCH_MOVE, function(e) {
				var posX = 0;
				var posY = 0;

				if (TOUCH_MOVE == 'touchmove') {
					posX = e.changedTouches[0].pageX - _this.m_containerOffsetLeft;
					posY = e.changedTouches[0].pageY - _this.m_containerOffsetTop;
				} else {
					posX = e.pageX - _this.m_containerOffsetLeft;
					posY = e.pageY - _this.m_containerOffsetTop;
				}

					if (posX < 0 || posX > _this.m_containerWidth
						|| posY < 0 || posY > _this.m_containerHeight)
							return;

				var percentX = posX / _this.m_containerWidth;
				var percentY = posY / _this.m_containerHeight;

				_this.m_image.style.top = _this.m_yMax * percentY + "px";
				_this.m_image.style.left = _this.m_xMax * percentX + "px";
			}, false);

			_this.m_parent.addEventListener(TOUCH_START, function(e) {
				_this.m_image.style.width = _this.m_imageWidth + "px";
				_this.m_image.style.height = _this.m_imageHeight + "px";
			}, false);

			_this.m_parent.addEventListener(TOUCH_END, function(e) {
				_this.m_image.style.width = _this.m_containerWidth + "px";
				_this.m_image.style.height = _this.m_containerHeight + "px";
				_this.m_image.style.top = "0px";
				_this.m_image.style.left = "0px";
			}, false);

			window.addEventListener('resize', function() {
				removeStyle();
				recalculateSizes();
				addStyle();
			}, false);
	}
}