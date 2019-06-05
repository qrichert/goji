/**
 * LightBox class
 *
 * How to use it:
 *
 * Look at SliderLightBoxInterface for a good example. It's short, just a contructor().
 *
 * Connection to a Slider:
 * -----------------------
 *
 * You can connect it manually to a Slider or use SliderLightBoxInterface
 * to automatically link a Goji Slider to a Goji LightBox.
 *
 * Styling:
 * --------
 *
 * These are the classes you can style.
 *
 * .lightbox
 * .lightbox__close-cross
 * .lightbox__center-element
 *      .lightbox__image
 *      .lightbox__text
 *      .lightbox__navigation.previous
 *      .lightbox__navigation.next
 */
class LightBox {

	constructor(options = null) {

		this.m_lightBox = null;
		this.m_closeCross = null;
		this.m_centerElement = null;
		this.m_image = null;
		this.m_text = null;
		this.m_navigationPrevious = null;
		this.m_navigationNext = null;

		this.m_isShown = false;

		this.m_currentImageType = null;
		this.m_currentImageNaturalWidth = 0;
		this.m_currentImageClientHeight = 0;
		this.m_currentImageNaturalHeight = 0;
		this.m_currentImageOffsetTop = 0;

		this.m_previousImageCallback = null;
		this.m_nextImageCallback = null;
		this.m_closeCallback = null;

		this.m_navigationUsed = this.coalesce(options, 'navigation_used', true);
		this.m_navigationBlocksMinimumWidth = this.coalesce(options, 'navigation_blocks_minimum_width', '100px');
		this.m_navigationArrowsWidth = this.coalesce(options, 'navigation_arrows_width', 50);
		this.m_navigationArrowsColor = this.coalesce(options, 'navigation_arrows_color', 'white');
		this.m_navigationArrowsDisplayed = this.coalesce(options, 'navigation_arrows_displayed', true);
		this.m_useArrowKeysToNavigate = this.coalesce(options, 'use_arrow_keys_to_navigate', true);
		this.m_lightBoxZIndex = this.coalesce(options, 'lightbox_zindex', '1');
		this.m_lightBoxBackgroundColor = this.coalesce(options, 'lightbox_background_color', 'rgba(0, 0, 17, 0.17)');
		this.m_lightBoxTextPadding = this.coalesce(options, 'lightbox_text_padding', 20);
		this.m_closeCrossShown = this.coalesce(options, 'close_cross_shown', true);
		this.m_closeCrossColor = this.coalesce(options, 'close_cross_color', 'white');
		this.m_closeCrossSize = this.coalesce(options, 'close_cross_size', 20);
		this.m_closeCrossMargin = this.coalesce(options, 'close_cross_margin', '20px');
		this.m_closeCrossPosition = this.coalesce(options, 'close_cross_position', 'top-right');
		this.m_transitionDuration = this.coalesce(options, 'transition_duration', 300);

		this.init();
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
	 * Builds DOM Elements & add listeners
	 */
	init() {

		let docFrag = document.createDocumentFragment();

		this.m_lightBox = document.createElement('div');
			this.m_lightBox.classList.add('lightbox');
			this.m_lightBox.setAttribute('tabindex', '0'); // For receiving keyboard events
			this.m_lightBox.style.backgroundColor = this.m_lightBoxBackgroundColor;
			this.m_lightBox.style.position = 'fixed';
			this.m_lightBox.style.top = '0';
			this.m_lightBox.style.left = '0';
			this.m_lightBox.style.width = '100vw';
			this.m_lightBox.style.height = '100vh';
			this.m_lightBox.style.flexDirection = 'row';
			this.m_lightBox.style.justifyContent = 'center';
			this.m_lightBox.style.alignItems = 'center';
			this.m_lightBox.style.overflow = 'hidden';
			this.m_lightBox.style.zIndex = this.m_lightBoxZIndex;
			this.showLightBox(false);
				docFrag.appendChild(this.m_lightBox);

		this.m_closeCross = document.createElement('div');
			this.m_closeCross.classList.add('lightbox__close-cross');
			this.m_closeCross.style.width = this.m_closeCrossSize + 'px';
			this.m_closeCross.style.height = this.m_closeCrossSize + 'px';
			this.m_closeCross.style.cursor = 'pointer';
			this.m_closeCross.style.position = 'absolute';
			this.m_closeCross.style.display = this.m_closeCrossShown ? 'block' : 'none';

			switch (this.m_closeCrossPosition) {
				case 'top-left':
				case 'left':
					this.m_closeCross.style.top = this.m_closeCrossMargin;
					this.m_closeCross.style.left = this.m_closeCrossMargin;
					break;
				case 'bottom-left':
					this.m_closeCross.style.bottom = this.m_closeCrossMargin;
					this.m_closeCross.style.left = this.m_closeCrossMargin;
					break;
				case 'bottom-right':
					this.m_closeCross.style.bottom = this.m_closeCrossMargin;
					this.m_closeCross.style.right = this.m_closeCrossMargin;
					break;
				case 'top-right':
				case 'right':
				default:
					this.m_closeCross.style.top = this.m_closeCrossMargin;
					this.m_closeCross.style.right = this.m_closeCrossMargin;
					break;
			}

				this.m_lightBox.appendChild(this.m_closeCross);

			let svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
				svg.setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xlink', 'http://www.w3.org/1999/xlink');
				svg.setAttributeNS(null, 'version', '1.1');
				svg.setAttributeNS(null, 'x', '0px');
				svg.setAttributeNS(null, 'y', '0px');
				svg.setAttributeNS(null, 'viewBox', '0 0 150 150');
				svg.style.width = this.m_closeCrossSize + 'px';
				svg.style.height = this.m_closeCrossSize + 'px';
					this.m_closeCross.appendChild(svg);

				let polygon = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
					polygon.style.fill = this.m_closeCrossColor;
					polygon.setAttributeNS(null, 'points', '128.9,147.5 75,93.6 21.1,147.5 2.5,128.9 56.4,75 2.5,21.1 21.1,2.5 75,56.4 128.9,2.5 147.5,21.1 93.6,75 147.5,128.9 ');
						svg.appendChild(polygon);


		this.m_centerElement = document.createElement('div');
			this.m_centerElement.classList.add('lightbox__center-element');
			this.m_centerElement.style.position = 'relative';
			this.m_centerElement.style.margin = 'auto';
			this.m_centerElement.style.width = '90vw';
			this.m_centerElement.style.height = '80vh';
			// this.m_centerElement.style.backgroundColor = 'red';
			this.m_centerElement.style.display = 'flex';
			this.m_centerElement.style.flexDirection = 'column';
			this.m_centerElement.style.justifyContent = 'center';
			this.m_centerElement.style.alignItems = 'center';
				this.m_lightBox.appendChild(this.m_centerElement);

		this.m_text = document.createElement('div');
			this.m_text.classList.add('lightbox__text');
			this.m_text.style.backgroundColor = 'blue';
			this.m_text.style.width = '100%';
			this.m_text.style.boxSizing = 'border-box';
			this.m_text.style.flexShrink = '0';
			this.m_text.style.padding = '0px'; // When empty
			// this.m_text.style.margin = 'auto 0 0 0';
				this.m_centerElement.appendChild(this.m_text);

		// Navigation
		this.m_navigationPrevious = document.createElement('a');
		this.m_navigationNext = document.createElement('a');

		for (let i of [this.m_navigationPrevious, this.m_navigationNext]) {

			i.classList.add('lightbox__navigation');
			i.style.cursor = 'pointer';
			i.style.position = 'absolute';
			i.style.display = this.m_navigationUsed ? 'flex' : 'none';
			i.style.flexDirection = 'row';
			i.style.justifyContent = 'center';
			i.style.alignItems = 'center';
			i.style.top = '0px';
			i.style.width = 'calc(100% / 3)';
			i.style.height = '100%';
			i.style.minWidth = this.m_navigationBlocksMinimumWidth;
			i.style.maxWidth = '50%';
			// i.style.backgroundColor = 'rgba(200, 100, 100, 0.3)';
			i.style.transition = 'width ' + this.m_transitionDuration + 'ms ease,' +
			                     'opacity ' + this.m_transitionDuration + 'ms ease';
			i.style.webkitTouchCallout = 'none';
			i.style.webkitUserSelect = 'none';
			i.style.mozUserSelect = 'none';
			i.style.msUserSelect = 'none';
			i.style.userSelect = 'none';
				this.m_centerElement.appendChild(i);

			let icon = document.createElement('div');
				// icon.style.backgroundColor = 'red';
				icon.style.display = this.m_navigationArrowsDisplayed ? 'flex' : 'none';
				icon.style.flexDirection = 'row';
				icon.style.justifyContent = 'center';
				icon.style.alignItems = 'center';
				icon.style.width = this.m_navigationArrowsWidth + 'px';
				icon.style.height = this.m_navigationArrowsWidth + 'px';
					i.appendChild(icon);


			let svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
				svg.setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xlink', 'http://www.w3.org/1999/xlink');
				svg.setAttributeNS(null, 'version', '1.1');
				svg.setAttributeNS(null, 'x', '0px');
				svg.setAttributeNS(null, 'y', '0px');
				svg.setAttributeNS(null, 'viewBox', '0 0 50 50');
				svg.style.width = (this.m_navigationArrowsWidth * 0.5) + 'px';
				svg.style.height = (this.m_navigationArrowsWidth * 0.5) + 'px';
					icon.appendChild(svg);

				let polygon = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
					polygon.style.fill = this.m_navigationArrowsColor;
						svg.appendChild(polygon);

			if (i == this.m_navigationPrevious) {
				i.classList.add('previous');
				i.style.left = '0px';
				polygon.setAttributeNS(null, 'points', '34.5,46.1 36.6,43.9 17.6,25 36.6,6.1 34.5,3.9 13.4,25 ');
			} else {
				i.classList.add('next');
				i.style.right = '0px';
				polygon.setAttributeNS(null, 'points', '15.5,46.1 13.4,43.9 32.3,25 13.4,6.1 15.5,3.9 36.6,25 ');
			}
		}

		document.body.appendChild(docFrag);

		this.addListeners();
	}

	/**
	 * Calculate previous & next click area size.
	 */
	recalculateNavigationSize() {

		if (this.m_currentImageType != 'img') {

			// min-width
			this.m_navigationPrevious.style.width = '0px';
			this.m_navigationNext.style.width = '0px';

			this.m_navigationPrevious.style.height = this.m_text.offsetTop + 'px';
			this.m_navigationNext.style.height = this.m_text.offsetTop + 'px';

			this.m_navigationPrevious.style.top = '0px';
			this.m_navigationNext.style.top = '0px';

		}  else if (this.m_currentImageNaturalWidth === 0
			|| this.m_currentImageClientHeight === 0
			|| this.m_currentImageNaturalHeight === 0) {

			return;

		} else {
			// Images always takes 100% width because of object-fit, but height is OK
			// So we calculate the actual displayed width from the height
			let width = this.m_currentImageNaturalWidth * (this.m_currentImageClientHeight / this.m_currentImageNaturalHeight);
			let navigationWidth = (this.m_centerElement.clientWidth / 2) - (width / 2);

			this.m_navigationPrevious.style.width = navigationWidth + 'px';
			this.m_navigationNext.style.width = navigationWidth + 'px';

			this.m_navigationPrevious.style.height = this.m_currentImageClientHeight + 'px';
			this.m_navigationNext.style.height = this.m_currentImageClientHeight + 'px';

			this.m_navigationPrevious.style.top = this.m_currentImageOffsetTop + 'px';
			this.m_navigationNext.style.top = this.m_currentImageOffsetTop + 'px';
		}
	}

	/**
	 * Set center image from <img> element (can be a <div> also).
	 *
	 * @public
	 * @param img
	 */
	setImage(img) {

		let el = null;

			if (img.tagName.toLowerCase() == 'img') {

				el = document.createElement('img');
					el.src = img.src;
					el.addEventListener('load', () => {

						this.m_currentImageType = 'img';

						this.m_currentImageNaturalWidth = el.naturalWidth;
						this.m_currentImageClientHeight = el.clientHeight;
						this.m_currentImageNaturalHeight = el.naturalHeight;
						this.m_currentImageOffsetTop = el.offsetTop;

						this.recalculateNavigationSize();
					}, false);
					el.alt = img.alt;
					el.style.minHeight = '1px'; // flex will make it grow to take max size ('height' would make it overflow)

			} else {

				el = img.cloneNode(true);
				el.style.height = '100%';

				this.m_currentImageType = 'div';
				this.m_currentImageNaturalWidth = 0;
				this.m_currentImageClientHeight = 0;
				this.m_currentImageNaturalHeight = 0;
				this.m_currentImageOffsetTop = 0;

				this.recalculateNavigationSize();
			}

			el.style.margin = '0';
			el.style.width = '100%';
			el.style.objectFit = 'contain';
			el.style.boxSizing = 'border-box';
			el.classList.add('lightbox__image');

		this.m_centerElement.insertBefore(el, this.m_text);

		if (this.m_image !== null)
			this.m_centerElement.removeChild(this.m_image);

		this.m_image = el;
		this.m_image.addEventListener('click', e => { e.stopPropagation(); }, false);
	}

	/**
	 * Set center image by giving its source URL.
	 *
	 * @public
	 * @param url
	 * @param alt
	 */
	setImageFromURL(url, alt = '') {

		let img = document.createElement('img');
			img.src = url;
			img.alt = alt;

		this.setImage(img);
	}

	/**
	 * Empty text description.
	 *
	 * @public
	 */
	clearText() {
		this.m_text.innerHTML = '';
		this.m_text.style.padding = '0px';
	}

	/**
	 * Sets legend as plain text (<p> wrapper added).
	 *
	 * @public
	 * @param text
	 */
	setText(text) {

		if (!text || text.length === 0) { // If empty text
			this.clearText();
			return;
		}

		this.m_text.innerHTML = '';
		this.m_text.style.padding = this.m_lightBoxTextPadding + 'px';

		let p = document.createElement('p');
			p.style.margin = '0';
			p.style.padding = '0';
			p.textContent = text;
				this.m_text.appendChild(p);
	}

	/**
	 * Sets legend as HTML (no wrapper added).
	 *
	 * @public
	 * @param text
	 */
	setTextHTML(text) {

		if (!text || text.length === 0) { // If empty text
			this.clearText();
			return;
		}

		this.m_text.innerHTML = text;
		this.m_text.style.padding = this.m_lightBoxTextPadding + 'px';
	}

	/**
	 * Set the callback function for previous image.
	 *
	 * @public
	 * @param callback
	 */
	setPreviousImageCallback(callback) {
		this.m_previousImageCallback = callback;
	}

	/**
	 * Requests previous image by calling the callback function.
	 */
	previousImage() {

		if (!this.m_navigationUsed)
			return;

		if (this.m_previousImageCallback !== null)
			this.m_previousImageCallback();
	}

	/**
	 * Set the callback function for next image.
	 *
	 * @public
	 * @param callback
	 */
	setNextImageCallback(callback) {
		this.m_nextImageCallback = callback;
	}

	/**
	 * Requests next image by calling the callback function.
	 */
	nextImage() {

		if (!this.m_navigationUsed)
			return;

		if (this.m_nextImageCallback !== null)
			this.m_nextImageCallback();
	}

	/**
	 * Returns if lightbox is currently shown or not.
	 *
	 * @returns {boolean}
	 */
	getIsLightBoxShown() {
		return this.m_isShown;
	}

	/**
	 * Set the callback function for close event.
	 *
	 * @public
	 * @param callback
	 */
	setCloseCallback(callback) {
		this.m_closeCallback = callback;
	}

	/**
	 * Show or hide lightbox.
	 *
	 * @public
	 * @param show
	 */
	showLightBox(show) {
		this.m_isShown = show;
		this.m_lightBox.style.display = show ? 'flex' : 'none';

		if (show)
			this.m_lightBox.focus();

		if (!show && this.m_closeCallback !== null)
			this.m_closeCallback();
	}

	showPreviousArrow(show) {

		if (!this.m_navigationArrowsDisplayed)
			return;

		this.m_navigationPrevious.style.opacity = show ? '1' : '0';
	}

	showNextArrow(show) {

		if (!this.m_navigationArrowsDisplayed)
			return;

		this.m_navigationNext.style.opacity = show ? '1' : '0';
	}

	/**
	 * Adds all necessary event listeners.
	 */
	addListeners() {

		let closeLightBox = e => {
			e.preventDefault();
			e.stopPropagation();
			this.showLightBox(false)
		};

		this.m_lightBox.addEventListener('click', closeLightBox, false);
		this.m_closeCross.addEventListener('click', closeLightBox, false);

		window.addEventListener('resize', () => { this.recalculateNavigationSize(); }, false);

		let previousImage = e => {
			e.preventDefault();
			e.stopPropagation();
			this.previousImage();
		};

		let nextImage = e => {
			e.preventDefault();
			e.stopPropagation();
			this.nextImage();
		};

		this.m_navigationPrevious.addEventListener('click', previousImage, false);
		this.m_navigationNext.addEventListener('click', nextImage, false);

		if (this.m_useArrowKeysToNavigate) {

			document.addEventListener('keyup', e => {

				if (e.target !== this.m_lightBox)
					return;

				switch (e.key) {
					case 'ArrowLeft':
						previousImage(e);
						break;
					case 'ArrowRight':
						nextImage(e);
						break;
				}
			}, false);
		}

		document.addEventListener('keyup', e => {

			if (e.target !== this.m_lightBox)
				return;

			if (e.key == 'Escape')
				this.showLightBox(false);

		}, false);
	}
}
