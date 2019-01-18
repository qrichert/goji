/*
	Copyright © 2017 Quentin Richert <https://www.quentinrichert.com/>
	Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
	See http://creativecommons.org/licenses/by-nc-nd/4.0/
	Please contact the author for commercial use.

	* This script allows to add some perspective to images.
	* Perspective works on devices supporting Device Motion. (Mainly smartphones and tablets.)

	Instructions:

		1. Create a container for the images.

			<div id="perspective"></div>

		2. Each image (<video> works too with playsinline) needs to be inside a parent element (either a <div>, an <a>, or anything).
		   The image also needs a "perspective-element" class. The parent element can have other children.

		   /!\ The parent element for each image needs to have a set width and a set height.

			<div id="perspective">
				<div> // ← This one needs to have a size in CSS
					// <IMAGE> (or <VIDEO>)
				</div>
			</div>

		3. Create a new Perspective object with these parameters:

			var perspective = new Perspective(
										parent,			// A reference to the images container, in our example it is #perspective
										speed,			// (optional) The speed of the moving image. 0.7 by default. 2 would be 2x the original speed and 0.5 half of it
										scaleFactor,	// (optional) The scaled size of the image. 1.3 by default. Can't be less than 1
										sensibility		// The precision of the motion. The lower the more accurate. 1 by default, can be 0 also
										frameRate		// (optional) The frame rate of the animation in fps. 30 fps by default.
									 );

		6. You can edit speed, scaleFactor, sensibility, and frameRate at any time by
		   calling:

			void setSpeed(float)
			void setScaleFactor(float) // Call updateImages() immediately after
			void setSensibility(float)
			void setFrameRate(int)

		7. Please note you can add or remove images from the DOM.
		   Just call updateImages() immediately after.
		   (Don't forget the parent div on adding images).

			var newImageParent = document.createElement('div');

			var newImage = document.createElement('img');
				newImage.src = "some_random_image.jpg";
				newImage.alt = "Some Random Image";
				newImage.classList.add("perspective-element");

					newImageParent.appendChild(newImage);

			document.querySelector("#perspective").appendChild(newImageParent);
			perspective.updateImages(); // perspective = Perspective object

		8. You can also reset the default orientation (the device's orientation on page load)
		   by calling:

			void resetOrientation()

	Example:

		<div id="perspective">
			<div style="width: 300px; height: 300px;"><img src="img/gallery/1.jpg" alt="Galerie 1" class="perspective-element"></div>
		</div>

		<script src="js/Perspective-17.10.31.min.js"></script>
		<script>
			var perspective = new Perspective(document.querySelector("#perspective"));

			// That's all you need. We will just add an image for the example

			var newImageParent = document.createElement('div');

			var newImage = document.createElement('img');
				newImage.src = "some_random_image.jpg";
				newImage.alt = "Some Random Image";
				newImage.classList.add("perspective-element");

					newImageParent.appendChild(newImage);

			document.querySelector("#perspective").appendChild(newImageParent);
			perspective.updateImages();
		</script>
*/

function Perspective(parent,				// The element that will contain the images
					 speed = 0.7,			// The speed of the moving image
					 scaleFactor = 1.3,		// The scaled size of the image
					 sensibility = 1,		// The precision of the motion. The lower the more accurate
					 frameRate = 30			// The animation frame rate in fps
					 ) {

	var _this = this;

	this.m_parent = parent;

	this.m_speed = null;

		this.setSpeed = function(speed) {
			this.m_speed = speed;
		};

		this.getSpeed = function() {
			return this.m_speed;
		};

			this.setSpeed(speed);

	this.m_scaleFactor = null;

		this.setScaleFactor = function(scale) {
			if (scale < 1)
				scale = 1;

			this.m_scaleFactor = scale;
		};

		this.getScaleFactor = function() {
			return this.m_scaleFactor;
		};

			this.setScaleFactor(scaleFactor);

	this.m_sensibility = null;

		this.setSensibility = function(sensibility) {
			this.m_sensibility = sensibility;
		};

		this.getSensibility = function() {
			return this.m_sensibility;
		};

			this.setSensibility(sensibility);

	this.m_frameRate = null;

		this.setFrameRate = function(frameRate) {
			this.m_frameRate = 1000 / frameRate;
		};

		this.getFrameRate = function() {
			return this.m_frameRate;
		};

			this.setFrameRate(frameRate);

	this.m_parents = [];
	this.m_images = [];

		this.updateImages = function() {
			this.m_parents = [];
			this.m_images = [];

			for (var i = 0; i < this.m_parent.children.length; i++) {

				this.m_parents.push(this.m_parent.children[i]);
				this.m_images.push(this.m_parents[i].getElementsByClassName('perspective-element')[0]);
				// this.m_images.push(this.m_parents[i].getElementsByTagName('img')[0]);

				this.m_images[i].style.width = "100%";
				this.m_images[i].style.height = "100%";
				this.m_images[i].style.objectFit = "cover";
				this.m_images[i].style.objectPosition = "center center";
				this.m_images[i].style.transform = "scale(" + this.m_scaleFactor + ")";

					this.m_parent.children[i].style.overflow = "hidden";

						var originalWidth = this.m_images[i].offsetWidth;
						var originalHeight = this.m_images[i].offsetHeight;

						var scaledWidth = originalWidth * (this.m_scaleFactor - 0.1);
						var scaledHeight = originalHeight * (this.m_scaleFactor - 0.1);

					this.m_images[i].dataset.maxMoveX = Math.trunc((scaledWidth - originalWidth) / 2);
					this.m_images[i].dataset.maxMoveY = Math.trunc((scaledHeight - originalHeight) / 2);
			}
		};

			this.updateImages();

	window.addEventListener('resize', function() {
		_this.updateImages();
	}, false);

	this.m_initialX = null;	// X on page load
	this.m_initialY = null;	// Y on page load
	var initialOrientationSet = false;

	this.m_oldAccelerationX = null;
	this.m_oldAccelerationY = null;

		this.resetOrientation = function() {
			this.m_initialX = null;
			this.m_initialY = null;
			initialOrientationSet = false;

			this.m_oldAccelerationX = null;
			this.m_oldAccelerationY = null;
		};

	this.m_timer = Date.now();

	function applyPerspective(e) {

	// Timer
		var currentTimer = Date.now();

		if ((currentTimer - _this.m_timer) < _this.m_frameRate) // 30 fps by default
			return;

		timer = currentTimer;

	// Calculations

		var x = e.accelerationIncludingGravity.x;
		var y = e.accelerationIncludingGravity.y;

	// Acceleration according to initial acceleration

		if (initialOrientationSet == false) {
			_this.m_initialX = x;
			_this.m_initialY = y;

			initialOrientationSet = true;
		}

		if (x > _this.m_initialX)
			x = Math.abs(_this.m_initialX - x);
		else if (x < _this.m_initialX)
			x = -Math.abs(_this.m_initialX - x);

		if (y > _this.m_initialY)
			y = Math.abs(_this.m_initialY - y);
		else if (y < _this.m_initialY)
			y = -Math.abs(_this.m_initialY - y);

	// Sensibility

		if (_this.m_oldAccelerationX == null || _this.oldAccelerationY == null) {
			_this.m_oldAccelerationX = x;
			_this.m_oldAccelerationY = y;
		}

		if (Math.abs(_this.m_oldAccelerationX - x) < _this.m_sensibility)
			x = _this.m_oldAccelerationX;

		if (Math.abs(_this.m_oldAccelerationY - y) < _this.m_sensibility)
			y = _this.m_oldAccelerationY;

			_this.m_oldAccelerationX = x;
			_this.m_oldAccelerationY = y;

	// Tilt

		var tiltX = x / 10; // 10 = max -> value between 0 and 1
		var tiltY = y / 10; // 10 = max -> value between 0 and 1

		for (var i = 0; i < _this.m_images.length; i++) {

			var image = _this.m_images[i];

			// <GAMMA/X>
				var maxMoveX = Number(image.dataset.maxMoveX);
				var moveX = tiltX * maxMoveX * _this.m_speed;

					if (moveX > maxMoveX) moveX = maxMoveX;
					if (moveX < -maxMoveX) moveX = -maxMoveX;

			// <BETA/Y>
				var maxMoveY = Number(image.dataset.maxMoveY);
				var moveY = tiltY * maxMoveY * _this.m_speed;

					if (moveY > maxMoveY) moveY = maxMoveY;
					if (moveY < -maxMoveY) moveY = -maxMoveY;

			image.style.transform = "scale(" + _this.m_scaleFactor + ") translate(" + moveX + "px, " + moveY + "px)";
		}
	}

	if (window.DeviceOrientationEvent) {
		window.addEventListener("devicemotion", applyPerspective, false);
	}
}