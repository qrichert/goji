(function () {

	let heroImage = document.querySelector('.header__wrapper > img');
	let heroHeight = null;

	let recalculateHeroHeight = () => {
		heroHeight = heroImage.offsetHeight;
	};

	window.addEventListener('resize', recalculateHeroHeight, false);
	window.addEventListener('load', recalculateHeroHeight, false);

	let scrollEvent = () => {

		let scroll = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;

		let ratio = 0; // top

		if (heroHeight === null)
			ratio = 0;
		else if (scroll === 0)
			ratio = 0;
		else if (scroll >= heroHeight)
			ratio = 1;
		else
			ratio = scroll / heroHeight;

		/*
		 * Starts at center 50%
		 * From there we have an amplitude of 107% (-57% - +157%), hence ratio * 107
		 * But we also want to invert the movement, so ratio * 107 * -1 (= ratio * -107)
		 * Then we add it to the base: 50% + -57% || 50% + +157%
		 */
		ratio = ratio * -107;
		ratio = Math.round(ratio * 100) / 100; // Round @ 2 decimals
		ratio = 50 + ratio;

		// Trigger the repaint at the best time for the browser
		requestAnimationFrame(() => {
			heroImage.style.objectPosition = `center ${ratio}%`;
		});
	};

	window.addEventListener('scroll', scrollEvent, false);
	window.addEventListener('resize', scrollEvent, false);

})();
