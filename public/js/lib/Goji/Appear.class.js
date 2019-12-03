/**
 * Class Appear
 *
 * let callback = () => { doSomething(); }
 *
 * document.querySelectorAll('.appear').forEach(el => { new Appear(el, callback); });
 *
 * Callback is called when the element first appears on screen.
 */
class Appear {

	/**
	 * @param parent
	 * @param callback
	 * @param scrollRatio between 0 and 0.99
	 */
	constructor(parent, callback, scrollRatio = 0) {

		this.m_parent = parent;
		this.m_callback = callback;
		this.m_scrollRatio = (scrollRatio >= 0 && scrollRatio < 1) ? scrollRatio : 0;
		this.m_shown = false;

		this.m_scroll = 0;

		window.addEventListener('scroll', e => this.scrollEvent(e), false);
		window.addEventListener('resize', e => this.scrollEvent(e), false);

		this.scrollEvent()
	}

	/**
	 * Returns scroll (between 0 and 1)
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

		if (this.m_shown)
			return;

		let scroll = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
		let top = this.m_parent.offsetTop;
		let height = this.m_parent.offsetHeight;
		let windowHeight = window.innerHeight;

		let currentPos = top - scroll + height;

		const MIN = windowHeight + height;
		const MAX = 0;

		let output = null;

		if (currentPos < MAX)
			output = 1;
		else if (currentPos > MIN)
			output = 0;
		else
			output = 1 - (currentPos / MIN);

		this.m_scroll = output;

		if (this.m_scroll > this.m_scrollRatio) {
			this.m_shown = true;
			this.m_callback(output);
		}
	}
}
