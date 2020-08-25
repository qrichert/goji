/**
 * DocumentScroll class
 *
 * Get the amount of pixels scrolled (0px = top).
 *
 * How to use it:
 * --------------
 *
 * DocumentScroll::construct(callback = null);
 *
 * let documentScroll = new DocumentScroll(
 *     scroll => console.log(scroll)
 * );
 *
 * You don't necessarily need a callback. You can use DocumentScroll.getScroll(): float to get
 * the scroll at any given time. (This is not a static method!)
 */
class DocumentScroll {

	/**
	 * @param callback
	 * @param ratio Use a ratio (0 = top, 1 = bottom) instead of an absolute value in pixels
	 */
	constructor(callback = null, useRatio = false) {

		this.m_callback = callback;
		this.m_useRatio = useRatio;

		this.m_scroll = 0;

		window.addEventListener('scroll', e => this.scrollEvent(e), false);
		window.addEventListener('resize', e => this.scrollEvent(e), false);

		this.scrollEvent();
	}

	/**
	 * Returns scroll in pixels
	 *
	 * @public
	 * @returns {number} scroll
	 */
	getScroll() {
		return this.m_scroll;
	}

	/**
	 * Called on scroll or window resize
	 *
	 * @private
	 * @param e
	 */
	scrollEvent(e = null) {

		if (this.m_useRatio) {

			let scroll = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;

			if (scroll === 0)
				this.m_scroll = 0;
			else
				this.m_scroll =  scroll / (document.body.clientHeight - window.innerHeight);

		} else {

			this.m_scroll = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
		}

		if (this.m_callback !== null)
			this.m_callback(this.m_scroll);
	}
}
