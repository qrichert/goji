/**
 * Gallery
 * -------
 *
 * parent
 *   <img>
 *   <video data-width data-height>
 *   <div.gallery__image data-width data-height>
 *
 * let parent = document.querySelector('#gallery');
 * let eventListeners = {
 *     'click': clickCallback,
 *     'mouseover': mouseOverCallback,
 *     'contextmenu': contextMenuCallback
 * };
 *
 * new Gallery(parent, eventListeners);
 *
 * Callbacks will be called on given event with the event as parameter
 *
 * let clickCallback = e => {
 *     console.log(e.target);
 * };
 */
class Gallery {

	/**
	 * @param {HTMLElement} parent
	 * @param {Object} eventListeners
	 * @param {Number} gutter
	 */
	constructor(parent, eventListeners = false, gutter = null) {

		this.m_parent = parent;
		this.m_eventListeners = eventListeners || {};
		this.m_gutter = gutter !== null ? gutter : 4;
		this.m_elements = [];
		this.m_galleryReady = false; // true after elements have been populated

		window.addEventListener('resize', () => { this.recalculateSizes(); }, false);

		this.applyStyling();
		this.updateElements();
		this.addListeners();
	}

	addListeners() {

		/**
		 * Recalculate sizes after page has fully loaded.
		 *
		 * 'load' event is not called consistently, so some images never get
		 * their natural size updated from the default 'placeholder' one.
		 *
		 * Here we force a recalculation after we know for a fact they have loaded.
		 * window.load is fired when DOM & images are ready (document.load just DOM)
		 *
		 * @hack
		 */
		window.addEventListener('load', () => {

			for (let el of this.m_elements) {
				if (el.get('node_type') === 'img')
					this.recalculateImageNaturalSize(el);
			}

			this.recalculateSizes();

		}, false);
	}

	applyStyling() {
		this.m_parent.style.display = 'flex';
		this.m_parent.style.flexDirection = 'row';
		this.m_parent.style.flexWrap = 'wrap';
		this.m_parent.style.justifyContent = 'space-between';
		this.m_parent.style.alignItems = 'flex-start';
		this.m_parent.style.alignContent = 'flex-start';
	}

	/**
	 * @public
	 */
	updateElements() {

		this.m_elements = []; // Reset this.m_elements

		let elements = this.m_parent.querySelectorAll('img, video, div.gallery_image');
		let nbElements = elements.length;
		let nbImages = 0;
		let nbImagesLoaded = 0;

		for (let el of elements) {

			let nodeType = el.tagName.toLowerCase();

			if (nodeType === 'img')
				nbImages++;

			let newElement = new Map();
				newElement.set('node', el);
				newElement.set('node_type', nodeType);

			// Adding event listeners
			for (let event in this.m_eventListeners) {

				if (!this.m_eventListeners.hasOwnProperty(event))
					continue;

				el.addEventListener(event, e => {
					this.m_eventListeners[event](e);
				});
			}

			newElement.set('width', 1);
			newElement.set('height', 1);

			if (nodeType === 'img') {

				// These values will be overwritten once the image has loaded
				newElement.set('nat_width', el.clientWidth);
				newElement.set('nat_height', el.clientHeight);

				el.addEventListener('load', () => {

					this.recalculateImageNaturalSize(newElement);

					nbImagesLoaded++;

					// Force sizes recalculation when all images have loaded
					if (nbImagesLoaded === nbImages)
						this.recalculateSizes();

				}, false);

			} else { // <video>, <div>

				let width = 1;
				let height = 1;

				if ('width' in el.dataset)
					width = el.dataset.width;

				if ('height' in el.dataset)
					height = el.dataset.height;

				el.set('nat_width', width);
				el.set('nat_height', height);
			}

			this.m_elements.push(newElement);
		}

		this.m_galleryReady = true;

		this.recalculateSizes();
	}

	/**
	 * Recalculate the natural width and height of an image & update collection.
	 *
	 * @param img
	 */
	recalculateImageNaturalSize(img) {

		let imageNode = img.get('node');

		// Calculate sizes only for images
		if (img.get('node_type') !== 'img')
			return;

		// No need to update anything if image hasn't loaded properly
		if (imageNode.naturalWidth === 0 && imageNode.naturalHeight === 0)
			return;

		img.set('nat_width', imageNode.naturalWidth);
		img.set('nat_height', imageNode.naturalHeight);
	}

	/**
	 * @private
	 */
	recalculateSizes() {

		if (!this.m_galleryReady)
			return;

		let parentStyle = window.getComputedStyle(this.m_parent, null);
		let parentPadding = parseInt(parentStyle.getPropertyValue('padding-left'), 10)
							+ parseInt(parentStyle.getPropertyValue('padding-right'), 10);

		let parentWidth = this.m_parent.clientWidth - parentPadding;

		let nbImagesPerRow = 0;

			if (parentWidth <= 600 ) nbImagesPerRow = 1;
			else if (parentWidth <= 750 ) nbImagesPerRow = 2;
			else if (parentWidth <= 1020) nbImagesPerRow = 3;
			else if (parentWidth <= 1600) nbImagesPerRow = 4;
			else if (parentWidth <= 2500) nbImagesPerRow = 5;
			else if (parentWidth > 2500) nbImagesPerRow = 6;

		for (let el of this.m_elements) {
			let node = el.get('node');
				node.style.display = 'inline-block';
				node.style.margin = '0';
				node.style.padding = '0';
		}

		let nbRows = Math.trunc(this.m_elements.length / nbImagesPerRow);
		let lastRowFirstIndex = nbRows * nbImagesPerRow;
		// How many elements in last row
		let nbElementsOverflowing = this.m_elements.length % nbImagesPerRow;
		// If more than one (aligned left) and not a full row
		if (nbElementsOverflowing > 1 && nbElementsOverflowing < nbImagesPerRow) {

			for (let i = 1; i < nbElementsOverflowing; i++)
				this.m_elements[this.m_elements.length - i].get('node').style.marginLeft = this.m_gutter + 'px';

			this.m_elements[this.m_elements.length - 1].get('node').style.marginRight = 'auto';
		}

		let totalRowGap = this.m_gutter * (nbImagesPerRow - 1);
		let totalRowImagesWidth = parentWidth - totalRowGap;

		for (let i = 0; i < this.m_elements.length; i += nbImagesPerRow) {

			let nbImagesInCurrentRow = 0;
			let currentRowImagesNaturalWidth = 0;

			let firstImageHeight = 0;

			// Calculate total natural width of images when images have the same height
			for (let j = 0; j < nbImagesPerRow; j++) {

				let currentIndex = i + j;

				if (currentIndex >= this.m_elements.length)
					break; // Past last image

				nbImagesInCurrentRow++;

				let currentElement = this.m_elements[currentIndex];

				if (j === 0)
					firstImageHeight = currentElement.get('nat_height');

				// We need to scale the width so that they are all of the same height (first image is reference)
				currentElement.set('width', currentElement.get('nat_width') * (firstImageHeight / currentElement.get('nat_height')));
				currentElement.set('height', firstImageHeight);

				currentRowImagesNaturalWidth += currentElement.get('width');
			}

			// We need to decrease width if not enough elements to fill last row
			// We decrease it proportionally to the number of elements
			let totalRowImagesWidthForCurrentRow = totalRowImagesWidth * (nbImagesInCurrentRow / nbImagesPerRow);

			let scaleFactor = totalRowImagesWidthForCurrentRow / currentRowImagesNaturalWidth;

			// Calculate total natural width of images
			for (let j = 0; j < nbImagesPerRow; j++) {

				let currentIndex = i + j;

				if (currentIndex >= this.m_elements.length)
					break; // Past last image

				let node = this.m_elements[currentIndex].get('node');
				node.style.width = Math.floor(this.m_elements[currentIndex].get('width') * scaleFactor) + 'px';
				node.style.height = Math.floor(this.m_elements[currentIndex].get('height') * scaleFactor) + 'px';
					node.style.marginBottom = (currentIndex < lastRowFirstIndex ? this.m_gutter : 0) + 'px';
			}
		}
	}

	/**
	 * Returns index of given image
	 *
	 * @public
	 * @param img
	 */
	getIndexForImage(img) {
		let i = 0;

		for (let el of this.m_elements) {
			if (el.get('node') === img)
				return i;
			i++;
		}

		return null;
	}

	/**
	 * Returns image at given index
	 *
	 * @public
	 * @param index
	 */
	getImageForIndex(index) {

		if (typeof this.m_elements[index] === 'undefined')
			return null;

		let image = this.m_elements[index].get('node');

		if (typeof image === 'undefined')
			return null;

		return image;
	}

	/**
	 * Returns true if image index is the first one, else false.
	 *
	 * @public
	 * @returns {boolean}
	 */
	getIndexIsFirstIndex(index) {
		return index <= 0;
	}

	/**
	 * Returns true if image index is the last one, else false.
	 *
	 * @public
	 * @returns {boolean}
	 */
	getIndexIsLastIndex(index) {
		return index >= (this.m_elements.length - 1);
	}
}
