/**
 * Slider class
 *
 * How to use it:
 * --------------
 *
 * If you put this into your HTML
 *
 * <div class="slider noscript">
 *     <img src="1.jpg" alt="">
 *     <img src="2.jpg" alt="" data-href="link-on-click">
 *     <img src="3.jpg" alt="" data-href="#">
 *     <div class="slider__image" data-width="16" data-height="9">Hello</div>
 * </div>
 *
 * new Slider(document.querySelector('slider'));
 *
 * It will transform into, and work:
 *
 * <div class="slider">
 *     <div class="slider__main-wrapper">
 *         <div class="slider__image-wrapper">
 *             <a href="1.jpg"><img src="1.jpg"></a>
 *             <a href="link-on-click"><img src="2.jpg"></a>
 *             <a href="#"><img src="3.jpg"></a>
 *             <a href="#><div class="slider__image" data-width="16" data-height="9">Hello</div></a>
 *         </div>
 *     </div>
 * </div>
 * <a class="slider__navigation previous"></a>
 * <a class="slider__navigation next"></a>
 *
 * 'noscript' class will be removed immediately, it is for default styling in case JS is turned off.
 *
 * By default, the link (<a>) points to the image file (<img src> is copied into <a href>). If you
 * want to change the href, use the 'data-href' attribute.
 *
 * As you see, you can add a <div> instead of an image, but with a few conditions:
 * - It must be a <div> element
 * - It must have a class .slider__image
 * - (Optional) data-href attribute, if not, the <a href> will be '#'
 * - (Optional) data-width, if not, 1 (this is not an absolute value !!! it is to calculate the aspect ratio)
 * - (Optional) data-height, if not, 1
 *
 * Just a note on the aspect ratio thing. By default, the div will have an aspect ratio of 1:1 (square).
 * You can change both values independently. If you just set data-width="2", it will have an aspect ratio
 * of 2:1. In the example above we have data-width="16" and data-height="9", which give an aspect ratio of 16:9
 *
 * Styling:
 * --------
 *
 * The slider will receive some basic styling, just to make it work. Then on top of that you can add you own
 * style using the classes that are added automatically, like slider__main-wrapper or slider__navigation
 * for example.
 *
 * Note that if you give a 'noscript' class to the parent container, it will be remove. This is so you can
 * add a default styling, in case JavaScript is disabled. So that it doesn't look too bad.
 *
 * Here's some good 'noscript' base styling:
 *
 * .slider.noscript {
 *  	width: 100%;
 *  	height: 350px;
 *  	display: flex;
 *  	flex-direction: row;
 *  	justify-content: flex-start;
 *  	align-items: center;
 *  	overflow-x: scroll;
 *  	overflow-y: hidden;
 * }
 *
 * .slider.noscript > * {
 *  	height: 100%;
 * }
 *
 * .slider.noscript > * + * {
 *  	margin-left: 20px;
 * }
 *
 * Callback:
 * ---------
 *
 * Function called on image click.
 *
 * Click event and link (<a>) node are passed as arguments.
 *
 * (e, a) => {
 *     e.preventDefault();
 *     alert(a.href);
 * }
 *
 * Options:
 * --------
 *
 * You can pass an object as the third parameter with options (example uses defaults):
 *
 * new Slider(parent, null, {
 *      base_slider_height: 350, // Height of the slider
 *      images_gap: 50, // Space between images
 *      transition_duration: 300, // Transition time in milliseconds
 *      navigation_arrows_color: "#24292e", // Color of the arrows (CSS value)
 *      navigation_arrows_displayed: true, // Show arrows or not
 *      navigation_blocks_fixed_width: false, // Arrows won't move if true
 *      navigation_blocks_default_width: "calc(100% / 3)", // Location of the arrows, by default 1/3 from left/right border (CSS Value)
 *      navigation_blocks_minimum_width: "100px", // Minimum arrow distance from border (CSS Value)
 *      jump_to_first_and_last_image: false // Jump to first beyond last & to last beyond first
 * });
 *
 * Of course, you can leave out those you want to keep as default:
 *
 * new Slider(parent, null, {
 *      transition_duration: 0
 * });
 *
 * Public methods:
 * ---------------
 *
 * - Slider::getCurrentIndex(): int
 * - Slider::previousImage(): void
 * - Slider::nextImage(): void
 *
 */
class Slider {

	/**
	 * @param parent
	 * @param callback
	 * @param options
	 */
	constructor(parent, callback = null, options = null) {

		this.m_parent = parent;
			this.m_parent.classList.remove('noscript');

		this.m_sliderReady = false; // true after init() & all images have loaded

		this.m_parentWidth = 0;

			window.addEventListener('resize', () => { this.recalculateSizes(); }, false);
			this.recalculateSizes();

		this.m_mainWrapper = null;
		this.m_imageWrapper = null;
		this.m_navigationPrevious = null;
		this.m_navigationNext = null;

		this.m_nbImages = 0;
		this.m_currentIndex = 0;

		this.m_callbackFunction = callback;

		this.m_baseSliderHeight = this.coalesce(options, 'base_slider_height', 350);
			this.m_sliderHeight = this.m_baseSliderHeight; // Responsive height, current slider height
		this.m_imageGap = this.coalesce(options, 'images_gap', 50);
		this.m_transitionDuration = this.coalesce(options, 'transition_duration', 300);
		this.m_navigationArrowsColor = this.coalesce(options, 'navigation_arrows_color', '#24292e');
		this.m_navigationArrowsDisplayed = this.coalesce(options, 'navigation_arrows_displayed', true);
		this.m_navigationBlocksFixedWidth = this.coalesce(options, 'navigation_blocks_fixed_width', false);
		this.m_navigationBlocksDefaultWidth = this.coalesce(options, 'navigation_blocks_default_width', 'calc(100% / 3)');
		this.m_navigationBlocksMinimumWidth = this.coalesce(options, 'navigation_blocks_minimum_width', '100px');
		this.m_jumpToFirstAndLastImage = this.coalesce(options, 'jump_to_first_and_last_image', false);

		this.m_images = [];
			this.populateImages(); // Create an abstract image tree.
								   // Then call init() when all images are loaded
	}

	/**
	 * Returns current index (starting at 0).
	 *
	 * @public
	 * @returns {number}
	 */
	getCurrentIndex() {
		return this.m_currentIndex;
	}

	/**
	 * Null-coalescing polyfill.
	 *
	 * In PHP you could do '$object['property'] ?? defaultValue' and it would work
	 * as expected. But in JavaScript 'object.property || defaultValue' doesn't work
	 * if 'property' doesn't exist.
	 *
	 * So basically this function means:
	 * - If object exists and property exists in object, return it.
	 * - Else, return default value.
	 *
	 * @param object
	 * @param property
	 * @param defaultValue
	 * @returns {*}
	 */
	coalesce(object, property, defaultValue) {

		if (typeof object == 'undefined' || object === null)
			return defaultValue;

		if (!object.hasOwnProperty(property))
			return defaultValue;

		return object[property];
	}

	/**
	 * Recalculate the sizes for when the parent container is resized.
	 */
	recalculateSizes() {

		this.m_parentWidth = this.m_parent.clientWidth;

		if (!this.m_sliderReady)
			return;

		if (this.m_parentWidth < 450)
			this.m_sliderHeight = this.m_parentWidth / 2;
		else
			this.m_sliderHeight = this.m_baseSliderHeight;

		this.resizeImageLinks();
		this.resizeImages();
		this.moveTo(this.m_currentIndex); // Recenter image
	}

	/**
	 * Collect infos about the given images and call init() once they're all loaded.
	 */
	populateImages() {

		let images = this.m_parent.querySelectorAll('img, div.slider__image'); // Image elements only

		this.m_nbImages = images.length;
		let nbRealImages = 0; // <img> tags, not <divs>
		let nbImagesProcessed = 0; // Number images processed/added to collection
		let nbImagesLoaded = 0;

		for (let i of images) {

			let nodeType = i.tagName.toLowerCase();

			if (nodeType == 'img')
				nbRealImages++;

			let img = new Map();
				img.set('node', i);
				img.set('node_type', nodeType);
				img.set('src', i.src || '#');

			if (nodeType == 'img') { // Images must be loaded first

				// Set a default 3:2 aspect ratio (typical photograph)
				// These values will be overwritten once the image has loaded
				img.set('width', 1);
				img.set('height', 1);
				img.set('nat_width', 3);
				img.set('nat_height', 2);
				img.set('nat_width', 1);//TODO
				img.set('nat_height', 3);

				i.addEventListener('load', () => { // TODO: try to create a fake image & load from it https://www.thefutureoftheweb.com/blog/image-onload-isnt-being-called

					img.set('width', i.clientWidth);
					img.set('height', i.clientHeight);

					nbImagesLoaded++;

					// Force sizes recalculation when all images have loaded
					this.recalculateNaturalSize(img);

					if (nbImagesLoaded == nbRealImages)
						this.recalculateSizes();

				}, false);

				nbImagesProcessed++;

			} else { // div, Get aspect ratio, default is square (1:1)

				let width = 1;
				let height = 1;

				if ('width' in i.dataset)
					width = i.dataset.width;

				if ('height' in i.dataset)
					height = i.dataset.height;

				img.set('width', width);
				img.set('height', height);
				img.set('nat_width', width);
				img.set('nat_height', height);

				nbImagesProcessed++;
			}

			this.m_images.push(img);

			// Once collection is complete
			if (nbImagesProcessed == this.m_nbImages) {
				// Initialize everything
				this.init();
			}
		}
	}

	/**
	 * Recalculate the natural width and height of an image & update collection.
	 *
	 * @param img
	 */
	recalculateNaturalSize(img) {

		let imageNode = img.get('node');

		// Calculate sizes only for images
		if (img.get('node_type') !== 'img')
			return;

		// No need to update anything if image hasn't loaded properly
		if (imageNode.naturalWidth === 0 && imageNode.naturalHeight === 0)
			return;

		// Same if sizes are the same
		if (img.get('nat_width') === imageNode.naturalWidth && img.get('nat_height') === imageNode.naturalHeight)
			return;

		img.set('nat_width', imageNode.naturalWidth);
		img.set('nat_height', imageNode.naturalHeight);
	}

	/**
	 * Create all necessary elements and call applyStyling() and recalculateSizes()
	 */
	init() {

		// Clear parent
		while (this.m_parent.firstChild) {
			this.m_parent.removeChild(this.m_parent.firstChild);
		}

		let docFrag = document.createDocumentFragment();

			this.m_mainWrapper = document.createElement('div');
				this.m_mainWrapper.classList.add('slider__main-wrapper');
					docFrag.appendChild(this.m_mainWrapper);

			this.m_imageWrapper = document.createElement('div');
				this.m_imageWrapper.classList.add('slider__image-wrapper');
					this.m_mainWrapper.appendChild(this.m_imageWrapper);

				for (let i of this.m_images) {

					let a = document.createElement('a');

						let img = i.get('node');

						if ('href' in img.dataset)
							a.href = img.dataset.href;
						else
							a.href = i.get('src');

						a.appendChild(img);

						if (this.m_callbackFunction !== null) {
							a.addEventListener('click', e => {
								this.m_callbackFunction(e, a);
							}, false);
						}

					this.m_imageWrapper.appendChild(a);
				}

			this.m_navigationPrevious = document.createElement('a');
				this.m_navigationPrevious.classList.add('slider__navigation');
				this.m_navigationPrevious.classList.add('previous');
					docFrag.appendChild(this.m_navigationPrevious);

			this.m_navigationNext = document.createElement('a');
				this.m_navigationNext.classList.add('slider__navigation');
				this.m_navigationNext.classList.add('next');
					docFrag.appendChild(this.m_navigationNext);

		this.m_parent.appendChild(docFrag);

		this.applyStyling();

		this.m_sliderReady = true;

		this.recalculateSizes();
		this.addListeners();
	}

	/**
	 * Apply minimum CSS to elements.
	 */
	applyStyling() {

		// Parent
		this.m_parent.style.position = 'relative';
		this.m_parent.style.padding = '0';

		// Navigation
		for (let i of [this.m_navigationPrevious, this.m_navigationNext]) {

			i.style.display = 'flex';
			i.style.flexDirection = 'row';
			i.style.alignItems = 'center';
			i.style.position = 'absolute';
			i.style.top = '0';
			i.style.width = this.m_navigationBlocksDefaultWidth; // By default, will be resized according to image width
			i.style.minWidth = this.m_navigationBlocksMinimumWidth;
			i.style.height = '100%';
			//i.style.backgroundColor = 'rgba(0, 0, 0, 0.3)';
			i.style.webkitTouchCallout = 'none';
			i.style.webkitUserSelect = 'none';
			i.style.mozUserSelect = 'none';
			i.style.msUserSelect = 'none';
			i.style.userSelect = 'none';

			if (i == this.m_navigationPrevious) {

				i.style.left = '0';
				i.style.justifyContent = 'flex-end';

			} else if (i == this.m_navigationNext) {

				i.style.right = '0';
				i.style.justifyContent = 'flex-start';
			}

			if (!this.m_navigationArrowsDisplayed)
				continue;

			let icon = document.createElement('div');
				//icon.style.backgroundColor = 'red';
				icon.style.display = 'flex';
				icon.style.flexDirection = 'row';
				icon.style.justifyContent = 'center';
				icon.style.alignItems = 'center';
				icon.style.width = this.m_imageGap + 'px';
				icon.style.height = this.m_imageGap + 'px';
					i.appendChild(icon);

			let svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
				svg.setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xlink', 'http://www.w3.org/1999/xlink');
				svg.setAttributeNS(null, 'version', '1.1');
				svg.setAttributeNS(null, 'x', '0px');
				svg.setAttributeNS(null, 'y', '0px');
				svg.setAttributeNS(null, 'viewBox', '0 0 50 50');
				svg.style.width = (this.m_imageGap * 0.5) + 'px';
				svg.style.height = (this.m_imageGap * 0.5) + 'px';
					icon.appendChild(svg);

				let polygon = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
					polygon.style.fill = this.m_navigationArrowsColor;
						svg.appendChild(polygon);

					if (i == this.m_navigationPrevious)
						polygon.setAttributeNS(null, 'points', '34.5,46.1 36.6,43.9 17.6,25 36.6,6.1 34.5,3.9 13.4,25 ');

					else if (i == this.m_navigationNext)
						polygon.setAttributeNS(null, 'points', '15.5,46.1 13.4,43.9 32.3,25 13.4,6.1 15.5,3.9 36.6,25 ');
		}

		// Main Wrapper
		this.m_mainWrapper.style.overflow = 'hidden';

		// Images Wrapper
		this.m_imageWrapper.style.display = 'flex';
		this.m_imageWrapper.style.flexDirection = 'row';
		this.m_imageWrapper.style.justifyContent = 'flex-start';
		this.m_imageWrapper.style.alignItems = 'center';

		// Image Links
		for (let i of this.m_imageWrapper.children) { // .slider__image-wrapper > a

			i.style.marginLeft = (this.m_imageGap / 2) + 'px';
			i.style.marginRight = (this.m_imageGap / 2) + 'px';
			i.style.flexShrink = '0'; // Firefox...
			i.style.cursor = 'pointer';
			i.style.overflowY = 'hidden';
		}

		this.resizeImageLinks();

		// Images
		this.resizeImages();

		this.moveTo(this.getMiddleIndex());
	}

	/**
	 * Add event listeners.
	 */
	addListeners() {

		let previousImage = e => {
			e.preventDefault();
			this.previousImage();
		};

		let nextImage = e => {
			e.preventDefault();
			this.nextImage();
		};

		this.m_navigationPrevious.addEventListener('click', previousImage, false);
		this.m_navigationNext.addEventListener('click', nextImage, false);

		document.addEventListener('keyup', e => {

			if (e.target !== document.body)
				return;

			switch (e.key) {
				case 'ArrowLeft':  previousImage(e); break;
				case 'ArrowRight': nextImage(e);     break;
			}
		}, false);

		// Activate transitions after DOM has loaded + some micro delay to compensate for
		// other 'load' listeners. We want it really to be fired last
		window.addEventListener('load', () => {

			setTimeout(() => {

				this.m_imageWrapper.style.transition = 'transform ' + this.m_transitionDuration + 'ms ease';

				this.m_navigationPrevious.style.transition = 'width ' + this.m_transitionDuration + 'ms ease,' +
				                                             'opacity ' + this.m_transitionDuration + 'ms ease';

				this.m_navigationNext.style.transition = 'width ' + this.m_transitionDuration + 'ms ease,' +
				                                         'opacity ' + this.m_transitionDuration + 'ms ease';
			}, 1);
		}, false);

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

			for (let i of this.m_images) {
				this.recalculateNaturalSize(i);
			}
			this.recalculateSizes();

		}, false);
	}

	/**
	 * Go to previous image.
	 *
	 * @public
	 */
	previousImage() {
		this.moveTo(this.m_currentIndex - 1);
	}

	/**
	 * Go to next image.
	 *
	 * @public
	 */
	nextImage() {
		this.moveTo(this.m_currentIndex + 1);
	}

	/**
	 * Resize link elements to fit slider height.
	 */
	resizeImageLinks() {

		for (let i of this.m_imageWrapper.children) {
			i.style.height = this.m_sliderHeight + 'px';
		}
	}

	/**
	 * Resize images to fit slider height.
	 */
	resizeImages() {

		for (let i of this.m_images) {

			let img = i.get('node');
			let width = i.get('nat_width');
			let height = i.get('nat_height');

			let ratio = this.m_sliderHeight / height;

			width *= ratio;
			height *= ratio;

			i.set('width', width);
			i.set('height', height);

			img.style.width = width + 'px';
			img.style.height = height + 'px';
		}
	}

	/**
	 * Resize navigation zones (and so move navigation arrows).
	 *
	 * @param currentImageWidth Width of the current image (at current index)
	 */
	resizeNavigation(currentImageWidth) {

		if (this.m_navigationBlocksFixedWidth)
			return;

		let navigationWidth = (this.m_parentWidth - currentImageWidth) / 2;

		this.m_navigationPrevious.style.width = navigationWidth + 'px';
		this.m_navigationNext.style.width = navigationWidth + 'px';
	}

	/**
	 * Show or hide Previous button.
	 *
	 * @param show
	 */
	showNavigationPrevious(show) {
		this.m_navigationPrevious.style.opacity = show ? '1' : '0';
	}

	/**
	 * Show or hide Next button.
	 *
	 * @param show
	 */
	showNavigationNext(show) {
		this.m_navigationNext.style.opacity = show ? '1' : '0';
	}

	/**
	 * Returns the index of the image that should be centered
	 *
	 * @returns {number}
	 */
	getMiddleIndex() {

		if (this.m_nbImages == 1)
			return 0;

		return Math.ceil(this.m_nbImages / 2) - 1;
	}

	/**
	 * Returns true if current image is the first one, else false.
	 *
	 * @returns {boolean}
	 */
	currentIndexIsFirstIndex() {
		return this.m_currentIndex <= 0;
	}

	/**
	 * Returns true if current image is the last one, else false.
	 *
	 * @returns {boolean}
	 */
	currentIndexIsLastIndex() {
		return this.m_currentIndex >= (this.m_nbImages - 1);
	}

	/**
	 * Move to a given index (starting at 0)
	 *
	 * @param index
	 */
	moveTo(index) {

		// Handling 'overflows'
		if (index >= this.m_nbImages && this.m_jumpToFirstAndLastImage) // Last -> Jump to First
			index = 0;
		else if (index >= this.m_nbImages) // Last -> Hold on last
			index = this.m_nbImages - 1;
		else if (index < 0 && this.m_jumpToFirstAndLastImage) // First -> Jump to Last
			index = this.m_nbImages - 1;
		else if (index < 0) // First -> Hold on first
			index = 0;

		this.m_currentIndex = index;

		// Handling navigation arrows visibility
		if (this.m_navigationArrowsDisplayed && !this.m_jumpToFirstAndLastImage) {
			this.showNavigationPrevious(!this.currentIndexIsFirstIndex());
			this.showNavigationNext(!this.currentIndexIsLastIndex());
		}

		let totalOffsetWidth = this.m_imageGap / 2; // offset first image left gap

		for (let i = 0; i < index; i++) {
			totalOffsetWidth += this.m_images[i].get('width') + this.m_imageGap;
		}

		let currentImageWidth = this.m_images[index].get('width');

		this.center(totalOffsetWidth, currentImageWidth);
		this.resizeNavigation(currentImageWidth);
	}

	/**
	 * Offsets the offset so that the image is centered and not glue to the left border.
	 *
	 * @param px
	 * @param imageWidth
	 */
	center(px, imageWidth) {
		px -= (this.m_parentWidth / 2) - (imageWidth / 2);
		this.translate(px);
	}

	/**
	 * Move the image strip by px pixels.
	 *
	 * @param px
	 */
	translate(px) {

		if (px <= 0)
			px = Math.abs(px);
		else
			px = -px;

		this.m_imageWrapper.style.transform = 'translate(' + px + 'px)';
	}
}
