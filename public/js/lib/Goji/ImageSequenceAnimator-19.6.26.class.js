/**
 * ImageSequenceAnimator class
 * ---------------------------
 *
 * How to use it
 *
 * <div>
 *     <img src="image-sequence.png" alt="">
 * </div>
 *
 * let imageSequenceAnimator = new ImageSequenceAnimator(document.querySelector('div')
 *                                                       45, // 45 images
 *                                                       9); // 9 images per row (or 9 columns)
 *
 * imageSequenceAnimator.setProgress(0.5); // Between 0 and 1
 * imageSequenceAnimator.setCurrentIndex(3); // Starts at 0
 * imageSequenceAnimator.setCurrentGridIndex(1, 4); // column, row (or x, y), both start at 0
 */
class ImageSequenceAnimator {

	/**
	 * @param parent
	 * @param nbImages
	 * @param nbImagesPerRow
	 */
	constructor(parent, nbImages, nbImagesPerRow) {

		this.m_parent = parent;
		this.m_imageSequence = this.m_parent.querySelector('img');
		this.m_nbImages = nbImages;
		this.m_nbImagesPerRow = nbImagesPerRow;
		this.m_nbRows = Math.ceil(this.m_nbImages / this.m_nbImagesPerRow);
		this.m_currentImageIndex = 0;

		this.m_ready = false;

		this.m_parentWidth = 0;
		this.m_parentHeight = 0;
		this.m_imageSequenceNaturalWidth = 0;
		this.m_imageSequenceNaturalHeight = 0;
		this.m_imageSequenceWidth = 0;
		this.m_imageSequenceHeight = 0;

		this.m_imageAlreadyLoaded = false;

		// We can't proceed before we know the image's real dimensions
		// And to know that the image must have been fully loaded
		this.m_imageSequence.addEventListener('load', () => {
			this.imageSequenceLoaded();
		}, false);

		// In case image doesn't fire a load event
		window.addEventListener('load', () => {
			this.imageSequenceLoaded();
		}, false);
	}

	/**
	 * @private
	 */
	imageSequenceLoaded() {

		if (this.m_imageAlreadyLoaded)
			return;

		this.m_imageAlreadyLoaded = true;

		this.m_imageSequenceNaturalWidth = this.m_imageSequence.naturalWidth;
		this.m_imageSequenceNaturalHeight = this.m_imageSequence.naturalHeight;

		// Image sequence is loaded, now we can proceed to initialization.
		this.init();
	}

	/**
	 * @private
	 */
	init() {

		this.addListeners();
		this.recalculateSizes();

		this.m_parent.style.overflow = 'hidden';

		this.m_ready = true;
	}

	/**
	 * @private
	 */
	addListeners() {

		window.addEventListener('resize', () => { this.recalculateSizes(); }, false);
	}

	/**
	 * @private
	 */
	recalculateSizes() {

		this.m_parentWidth = this.m_parent.clientWidth; // We don't want the padding if there is any
		//this.m_parentHeight = ...; -> later

		// Image sequence width according to parent width
		this.m_imageSequenceWidth = this.m_parentWidth * this.m_nbImagesPerRow;
		// Image sequence height according to resized width
		this.m_imageSequenceHeight = this.m_imageSequenceNaturalHeight * (this.m_imageSequenceWidth / this.m_imageSequenceNaturalWidth);

		// Width
		this.m_imageSequence.style.width = this.m_imageSequenceWidth + 'px';

		// Height
		this.m_parentHeight = this.m_imageSequenceHeight / this.m_nbRows;
		this.m_parent.style.height =  this.m_parentHeight + 'px';

	}

	/**
	 * Set animation progress. 0 = first image, 1 = last image.
	 *
	 * @public
	 */
	setProgress(progress) {

		if (!this.m_ready)
			return;

		if (progress < 0)
			progress = 0;
		else if (progress > 1)
			progress = 1;

		// Decimals are taken care of in this.setCurrentImage()
		this.setCurrentIndex(this.m_nbImages * progress);
	}

	/**
	 * Set the displayed image by giving its index.
	 *
	 * If index > nb images, last image shown
	 * If index < 1, first image shown
	 * Index is floored
	 *
	 * @param index
	 */
	setCurrentIndex(index) {

		if (!this.m_ready)
			return;

		if (index > this.m_nbImages)
			index = this.m_nbImages;

		index = Math.floor(index);
		this.m_currentImageIndex = index;

		// 0 = 1st row or column
		let row = Math.ceil(this.m_currentImageIndex / this.m_nbImagesPerRow) - 1; // 17 / 10 = 1.7 = 2 -> row 2 -> 1
		let column = this.m_currentImageIndex - (row * this.m_nbImagesPerRow) - 1; // 17 - (1 * 10) = 17 - 10 = 7 -> column 7 -> 6

		this.setCurrentGridIndex(column, row);
	}

	/**
	 * Set index on grid by giving row and column (0 = 1st)
	 *
	 * @public
	 * @param row
	 * @param column
	 */
	setCurrentGridIndex(column, row) {

		if (!this.m_ready)
			return;

		if (row > this.m_nbRows - 1)
			row = this.m_nbRows - 1;
		else if (row < 0)
			row = 0;

		if (column > this.m_nbImagesPerRow - 1)
			column = this.m_nbImagesPerRow - 1;
		else if (column < 0)
			column = 0;

		let offsetX = column * this.m_parentWidth;
		let offsetY = row * this.m_parentHeight;

		this.m_imageSequence.style.transform = `translate(-${offsetX}px, -${offsetY}px)`;
	}
}
