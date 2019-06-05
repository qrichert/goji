/**
 * SliderLightBoxInterface class
 *
 * Interface between a Goji Slider and LightBox.
 * Just connects the two so they work together.
 *
 * How to use it:
 * --------------
 *
 * Just do as you would if you wanted just a slider :
 *
 * new SliderLightBoxInterface(sliderParent);
 *
 * This will create a Slider with sliderParent as parent.
 * You can also add slider and lightbox options (second and third parameter respectively)
 */
class SliderLightBoxInterface {

	constructor(sliderElement, sliderOptions = null, lightBoxOptions = null) {

		// <LIGHTBOX>
		let lightBox = new LightBox(lightBoxOptions);

		// <SLIDER>
		// On slider image click
		let callback = (p, e, a) => { // p = slider, e = event, a = clicked slider link

			e.preventDefault();

			let element = p.getElementFor(p.getCurrentIndex());

			if (element.get('node_type') == 'img') {
				lightBox.setImageFromURL(element.get('href')); // href, not src because it can be different w/ data-href
			} else {
				lightBox.setImage(element.get('node')); // If it's a <div> we pass the node
			}

			lightBox.showLightBox(true); // Show LightBox
		};

		let slider = new Slider(sliderElement, callback, sliderOptions);

		let changeLightBoxImage = (index) => {

			let element = slider.getElementFor(index);

			if (element.get('node_type') == 'img') {
				lightBox.setImageFromURL(element.get('href')); // href, not src because it can be different w/ data-href
			} else {
				lightBox.setImage(element.get('node')); // If it's a <div> we pass the node
			}

			// let nextArrowVisible = slider.getCurrentIndexIsLastIndex();
			lightBox.showPreviousArrow(!slider.getCurrentIndexIsFirstIndex()); // bool
			lightBox.showNextArrow(!slider.getCurrentIndexIsLastIndex()); // bool
		};

		// Connect LightBox previous/next request to Slider
		lightBox.setPreviousImageCallback(() => {
			changeLightBoxImage(slider.previousImage()); // Slider.previousImage() returns new index
		});

		lightBox.setNextImageCallback(() => {
			changeLightBoxImage(slider.nextImage()); // Slider.nextImage() returns new index
		});

		lightBox.setCloseCallback(() => {
			slider.focus(); // Give focus back to Slider
		});

		// Connect Slider previous/next request to LightBox
		slider.setImageChangeCallback((index) => {
			changeLightBoxImage(index);
		});
	}
}
