/**
 * Class Visible
 *
 * new Visible(
 *     document.querySelector('#visible'),
 *     () => { alert("appear"); },
 *     () => { alert("disappear"); },
 *     0.1,
 *     0.9
 * );
 *
 * Appear callback is called whenever element appears on screen (not just once, but every time).
 * Disappear callback is called whenever element disappears from screen (not just once, but every time).
 */
class Visible {

	/**
	 * @param parent
	 * @param callbackAppear
	 * @param callbackDisappear
	 * @param appearRatio 0 to 1, at what ratio is the element considered visible (start)
	 * @param disappearRatio 0 to 1, at what ratio is the element considered non-visible (end), must be > appearRatio
	 */
	constructor(parent, callbackAppear = null, callbackDisappear = null, appearRatio = 0, disappearRatio = 1) {

		this.m_parent = parent;
		this.m_callbackAppear = callbackAppear;
		this.m_callbackDisappear = callbackDisappear;
		this.m_appearRatio = (appearRatio >= 0 && appearRatio < 1) ? appearRatio : 0;
		this.m_disappearRatio = (disappearRatio > this.m_appearRatio && disappearRatio <= 1) ? disappearRatio : 1;
		this.m_visible = false;

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
	 * Returns true if element is visible, else returns false
	 *
	 * @public
	 * @return {boolean}
	 */
	getVisible() {
		return this.m_visible;
	}

	/**
	 * Called on scroll or window resize
	 *
	 * @private
	 * @param e
	 */
	scrollEvent(e = null) {

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

		if (!this.m_visible && this.m_scroll > this.m_appearRatio && this.m_scroll < this.m_disappearRatio) {

			this.m_visible = true;

			if (this.m_callbackAppear !== null)
				this.m_callbackAppear();

		} else if (this.m_visible && (this.m_scroll <= this.m_appearRatio || this.m_scroll >= this.m_disappearRatio)) {

			this.m_visible = false;

			if (this.m_callbackDisappear !== null)
				this.m_callbackDisappear();
		}
	}
}
