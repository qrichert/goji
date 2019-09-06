/**
 * DocumentScroll class
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
	 */
	constructor(callback = null) {

		this.m_callback = callback;

		this.m_scroll = 0;

		window.addEventListener('scroll', e => this.scrollEvent(e), false);
		window.addEventListener('resize', e => this.scrollEvent(e), false);

		this.scrollEvent()
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

		this.m_scroll = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;;

		if (this.m_callback !== null)
			this.m_callback(this.m_scroll);
	}
}
