/**
 * GalleryLightBoxInterface class
 *
 * Interface between a Goji Gallery and LightBox.
 * Just connects the two so they work together.
 *
 * How to use it:
 * --------------
 *
 * Just do as you would if you wanted just a gallery:
 *
 * new GalleryLightBoxInterface(galleryParent);
 *
 * This will create a Gallery with galleryParent as parent.
 * You can also add gallery and lightbox options.
 */
class GalleryLightBoxInterface {

	/**
	 * @param galleryElement
	 * @param eventListeners
	 * @param gutter
	 * @param lightBoxOptions
	 */
	constructor(galleryElement, eventListeners = false, gutter = null, lightBoxOptions = null) {

		// <LIGHTBOX>
		let lightBox = new LightBox(lightBoxOptions);

		// <GALLERY>
		let currentGalleryImageIndex = 0;

		if (eventListeners === false)
			eventListeners = {};

		eventListeners['click'] = e => {
			e.preventDefault();

			let image = e.target || null;

			if (typeof image === 'undefined' || image === null)
				return;

			// Cannot be called before gallery init, but just to make sure
			if (typeof gallery === 'undefined')
				return;

			changeLightBoxImage(gallery.getIndexForImage(image));
		};

		let gallery = new Gallery(galleryElement, eventListeners, gutter);

		// Previous / Next
		let changeLightBoxImage = index => {

			if (typeof gallery === 'undefined')
				return;

			let image = gallery.getImageForIndex(index);

			if (image === null) // Invalid index
				return;

			currentGalleryImageIndex = index;

			lightBox.setImage(image);

			// let nextArrowVisible = gallery.getCurrentIndexIsLastIndex();
			lightBox.showPreviousArrow(!gallery.getIndexIsFirstIndex(currentGalleryImageIndex)); // bool
			lightBox.showNextArrow(!gallery.getIndexIsLastIndex(currentGalleryImageIndex)); // bool

			if (!lightBox.getIsLightBoxShown())
				lightBox.showLightBox(true);
		};

		// Connect LightBox previous/next request to Gallery
		lightBox.setPreviousImageCallback(() => {
			changeLightBoxImage(currentGalleryImageIndex - 1); // Gallery.previousImage() returns new index
		});

		lightBox.setNextImageCallback(() => {
			changeLightBoxImage(currentGalleryImageIndex + 1); // Gallery.nextImage() returns new index
		});
	}
}
