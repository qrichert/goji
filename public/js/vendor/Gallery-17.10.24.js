/*
	Copyright © 2017 Quentin Richert <https://www.quentinrichert.com/>
	Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
	See http://creativecommons.org/licenses/by-nc-nd/4.0/
	Please contact the author for commercial use.

	* This script allows to make a Flickr-like photo gallery.
	* It displays images in rows where all the images have the same height.

	Instructions:

		1. Create a container for the images.

			<div id="gallery"></div>

		2. Each image (<video> works too with playsinline) needs to be inside a parent element (either a <div>, an <a>, or anything).
		   The parent element can have other children.

			<div id="gallery">
				<div>
					// <IMAGE> (or <VIDEO>)
				</div>
			</div>

		3. All the images must have at least these three attributes:

			<img
				src=""					// Link to the image
				data-width=""			// Image's original width in pixels
				data-height=""			// Image's original height in pixels
				class="gallery-element"	// Required class
			>

		4. Create a new Gallery object with these parameters:

			var gallery = new Gallery(
										parent,			// A reference to the gallery container, in our example it is #gallery
										spaceAround,	// (optional) The space between each image horizontally. 4px by default
										spaceUnder		// (optional) The space between each row. Equals spaceAround by default.
									 );

		5. Please note that the gallery container (#gallery in our example) must not contain any padding.
		   Style it with margins only. If you put a padding it shall be removed.

		6. Please note you can add or remove images from the DOM.
		   Just call updateImages() immediately after.
		   (Don't forget the parent div on adding images).

			var newImageParent = document.createElement('a');
				newImageParent.href = "https://www.quentinrichert.com/";

			var newImage = document.createElement('img');
				newImage.src = "some_random_image.jpg";
				newImage.alt = "Some Random Image";
				newImage.classList.add("gallery-element");
				newImage.dataset.width = 750;			// Don't forget these two.
				newImage.dataset.height = 500;			//

					newImageParent.appendChild(newImage);

			document.querySelector("#gallery").appendChild(newImageParent);
			gallery.updateImages(); // gallery = Gallery object

	Example:

		<div id="gallery">
			<a href="#"><img src="img/gallery/1.jpg"	alt="Image 1"	class="gallery-element"		data-width="819"	data-height="1024"></a>
			<a href="#"><img src="img/gallery/2.jpg"	alt="Image 2"	class="gallery-element"		data-width="819"	data-height="1024"></a>
			<a href="#"><img src="img/gallery/3.jpg"	alt="Image 3"	class="gallery-element"		data-width="1024"	data-height="1024"></a>
			<a href="#"><img src="img/gallery/4.jpg"	alt="Image 4"	class="gallery-element"		data-width="819"	data-height="1024"></a>
			<a href="#"><img src="img/gallery/5.jpg"	alt="Image 5"	class="gallery-element"		data-width="1024"	data-height="1024"></a>
			<a href="#"><img src="img/gallery/6.jpg"	alt="Image 6"	class="gallery-element"		data-width="1024"	data-height="1024"></a>
			<a href="#"><img src="img/gallery/7.jpg"	alt="Image 7"	class="gallery-element"		data-width="1024"	data-height="1024"></a>
			<a href="#"><img src="img/gallery/8.jpg"	alt="Image 8"	class="gallery-element"		data-width="819"	data-height="1024"></a>
			<a href="#"><img src="img/gallery/9.jpg"	alt="Image 9"	class="gallery-element"		data-width="1024"	data-height="1024"></a>
			<a href="#"><img src="img/gallery/10.jpg"	alt="Image 10"	class="gallery-element"		data-width="1024"	data-height="1024"></a>
			<a href="#"><img src="img/gallery/11.jpg"	alt="Image 11"	class="gallery-element"		data-width="1024"	data-height="1024"></a>
			<a href="#"><img src="img/gallery/12.jpg"	alt="Image 12"	class="gallery-element"		data-width="1024"	data-height="1024"></a>
		</div>

		<script src="js/Gallery-17.10.24.min.js"></script>
		<script>
			var gallery = new Gallery(document.querySelector("#gallery"), 7); // spaceUnder will be 7px too

			// That's all you need. We will just add an image for the example

			var newImageParent = document.createElement('a');
				newImageParent.href = "https://www.quentinrichert.com/";

			var newImage = document.createElement('img');
				newImage.src = "some_random_image.jpg";
				newImage.alt = "Some Random Image";
				newImage.classList.add("gallery-element");
				newImage.dataset.width = 750;			// Don't forget these two.
				newImage.dataset.height = 500;			//

					newImageParent.appendChild(newImage);

			document.querySelector("#gallery").appendChild(newImageParent);
			gallery.updateImages();
		</script>
*/

function Gallery(parent,					// The element that will contain the images
				 spaceAround = 4,			// Space between images
				 spaceUnder = spaceAround	// Space between rows
				) {

	var States = { // According to parent width
		XS:		1, // <= 600
		S:		2, // <= 750
		M:		3, // <= 1020
		L:		4, // <= 1600
		XL:		5, // <= 2500
		XXL:	6  //  > 2500
	};

	var _this = this;
	var i = 0;

	this.m_parent = parent;
		this.m_parent.style.display = "flex";
		this.m_parent.style.flexDirection = "row";
		this.m_parent.style.flexWrap = "wrap";
		this.m_parent.style.justifyContent = "space-between";
		this.m_parent.style.alignItems = "flex-start";
		this.m_parent.style.alignContent = "flex-start";
		this.m_parent.style.padding = "0";

	this.m_parentWidth = this.m_parent.offsetWidth;

		window.addEventListener('resize', function() {
			_this.m_parentWidth = _this.m_parent.offsetWidth;
			_this.m_currentState = _this.getCurrentState();
			_this.recalculateSizes();
		}, false);

	this.m_parents = [];
	this.m_images = [];
	this.m_spaceAround = Number(spaceAround);
	this.m_spaceUnder = Number(spaceUnder);

	this.updateImages = function() {

		this.m_parents = [];
		this.m_images = [];

		// Getting all images' parents and all images in parents

		for (i = 0; i < this.m_parent.children.length; i++) {
			this.m_parents.push(this.m_parent.children[i]);
			this.m_images.push(this.m_parents[i].getElementsByClassName('gallery-element')[0]);

			this.m_parents[i].style.marginTop = "0";
			this.m_parents[i].style.marginRight = "0";
			this.m_parents[i].style.marginBottom = this.m_spaceUnder + "px";
			this.m_parents[i].style.marginLeft = "0";
			this.m_images[i].style.width = "100%";
			this.m_images[i].style.height = "100%";
			this.m_images[i].style.padding = "0";
			this.m_images[i].style.margin = "0";
			this.m_images[i].style.objectFit = "cover";
		}

		// Getting max height

		var maxHeight = null;

		for (i = 0; i < this.m_images.length; i++) {
			var height = Number(this.m_images[i].dataset.height);
			if (maxHeight == null)
				maxHeight = height;

			if (height > maxHeight)
				maxHeight = height;
		}

		// Converting size to match maxHeight

		for (i = 0; i < this.m_images.length; i++) {
			var width = Number(this.m_images[i].dataset.width);
			var height = Number(this.m_images[i].dataset.height);

			if (height != maxHeight) {
				var ratio = (height / maxHeight);

				this.m_images[i].dataset.width = width / ratio;
				this.m_images[i].dataset.height = height / ratio;
			}
		}

		this.recalculateSizes();
	};

	this.m_currentState = null;

		this.getCurrentState = function() {
			if (this.m_parentWidth <= 600)
				return States.XS;
			else if (this.m_parentWidth <= 750)
				return States.S;
			else if (this.m_parentWidth <= 1020)
				return States.M;
			else if (this.m_parentWidth <= 1600)
				return States.L;
			else if (this.m_parentWidth <= 2500)
				return States.XL;
			else if (this.m_parentWidth > 2500)
				return States.XXL;
		};

			this.m_currentState = this.getCurrentState();

	this.recalculateSizes = function() {

		var n = -1; // So that when we add 1, n=0 => first item in the list
		var imagesLength = this.m_images.length;

		var defaultRowCount = 3;

		switch (this.m_currentState) {
			case States.XS:		defaultRowCount = 1;	break;
			case States.S:		defaultRowCount = 2;	break;
			case States.M:		defaultRowCount = 3;	break;
			case States.L:		defaultRowCount = 4;	break;
			case States.XL:		defaultRowCount = 5;	break;
			case States.XXL:	defaultRowCount = 6;	break;
		}

		if (this.m_currentState == States.XS) {
			for (var i = 0; i < this.m_images.length; i++){
				this.m_parents[i].style.width = "100%";
				this.m_parents[i].style.height = "100%";
				this.m_parents[i].style.marginRight = "0";
				this.m_parents[i].style.marginLeft = "0";
			}

			return;
		}

		while (n < (imagesLength - 1)) { // Once per row

				var currentRowCountTry = defaultRowCount;
				var rightCount = -1;
				var rightRowScaleFactor = -1;

					if (n + currentRowCountTry > (imagesLength - 1)) { // Not to exceed array size
						currentRowCountTry = (imagesLength - 1) - n;
					}

					var scaleFactors = [];
					var addedWidths = 0;
					var firstItemIndex = n + 1;

					for (i = firstItemIndex; i <= (n + currentRowCountTry); i++)
					{
						if (i == firstItemIndex) { // first item
							scaleFactors.push(1);
							addedWidths += Number(this.m_images[firstItemIndex].dataset.width);
						}

						else {
							var factor = Number(this.m_images[firstItemIndex].dataset.height) / Number(this.m_images[i].dataset.height);
							scaleFactors.push(factor);
							addedWidths += Number(this.m_images[i].dataset.width) * factor;
						}
					}

					var rowScaleFactor = addedWidths / this.m_parentWidth;

						rightCount = currentRowCountTry;
						rightRowScaleFactor = rowScaleFactor;

				var j = 0;
				var addedWidths = 0;

				for (var i = (n + 1); i <= (n + rightCount); i++) {
					var originalWidth = Number(this.m_images[i].dataset.width);
					var originalHeight = Number(this.m_images[i].dataset.height);

					var actualWidth = originalWidth / rightRowScaleFactor;
					var actualHeight = originalHeight / rightRowScaleFactor;

					var spaceAroundSubstract = (this.m_spaceAround * (rightCount - 1)) / rightCount;

					var actualWidthInPixels = (actualWidth / this.m_parentWidth) * this.m_parentWidth; // percent
					var newWidth = actualWidthInPixels - spaceAroundSubstract;

					addedWidths += newWidth;

					if (i == (n + rightCount) && (addedWidths > this.m_parentWidth)) {
						/* Sometimes the added widths are a pixel too large and mess up
						   the layout. If this is the case, we take what's too much
						   and substract it from the last image's width. */
						newWidth -= addedWidths - this.m_parentWidth;
					}

					this.m_parents[i].style.width = newWidth + "px";
					this.m_parents[i].style.height = actualHeight + "px";

					j++;
				}

				n += rightCount;
		}
	};

	this.updateImages();
	this.recalculateSizes();
}
